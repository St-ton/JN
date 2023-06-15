<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Session\Backend;
use JTL\Smarty\JTLSmarty;
use JTL\Update\DBMigrationHelper;
use JTLShop\SemVer\Parser;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\every;

/**
 * Class DBCheckController
 * @package JTL\Router\Controller\Backend
 */
class DBCheckController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::DBCHECK_VIEW);
        $this->getText->loadAdminLocale('pages/dbcheck');
        $this->cache->flush(Status::CACHE_ID_DATABASE_STRUCT);

        $errorMsg          = '';
        $dbErrors          = [];
        $dbFileStruct      = $this->getDBFileStruct();
        $maintenanceResult = null;
        $engineUpdate      = null;
        $fulltextIndizes   = null;
        $valid             = Form::validateToken();

        if (Request::postVar('update') === 'script' && $valid) {
            $scriptName = 'innodb_and_utf8_update_'
                . \str_replace('.', '_', $this->db->getConfig()['host']) . '_'
                . $this->db->getConfig()['database'] . '_'
                . \date('YmdHis') . '.sql';

            return new TextResponse(
                $this->doEngineUpdateScript($scriptName, \array_keys($dbFileStruct)),
                200,
                ['Content-Disposition' => 'attachment; filename="' . $scriptName . '"']
            );
        }

        $dbStruct = $this->getDBStruct(true, true);
        if (empty($dbFileStruct)) {
            $errorMsg = \__('errorReadStructureFile');
        } elseif ($valid && !empty($_POST['action']) && !empty($_POST['check'])) {
            $ok                = every($_POST['check'], function ($elem) use ($dbFileStruct): bool {
                return \array_key_exists($elem, $dbFileStruct);
            });
            $maintenanceResult = $ok ? $this->doDBMaintenance($_POST['action'], $_POST['check']) : false;
        }

        if ($errorMsg === '') {
            $dbErrors = $this->compareDBStruct($dbFileStruct, $dbStruct);
        }

        if (\count($dbErrors) > 0) {
            $engineErrors = \array_filter($dbErrors, static function ($item) {
                return $item->isEngineError;
            });
            if (\count($engineErrors) > 5) {
                $engineUpdate    = $this->determineEngineUpdate($dbStruct);
                $fulltextIndizes = DBMigrationHelper::getFulltextIndizes();
            }
        }
        $this->alertService->addError($errorMsg, 'errorDBCheck');

        return $smarty->assign('cDBFileStruct_arr', $dbFileStruct)
            ->assign('cDBStruct_arr', $dbStruct)
            ->assign('cDBError_arr', $dbErrors)
            ->assign('maintenanceResult', $maintenanceResult)
            ->assign('scriptGenerationAvailable', ADMIN_MIGRATION)
            ->assign('tab', isset($_REQUEST['tab']) ? Text::filterXSS($_REQUEST['tab']) : '')
            ->assign('DB_Version', DBMigrationHelper::getMySQLVersion())
            ->assign('FulltextIndizes', $fulltextIndizes)
            ->assign('engineUpdate', $engineUpdate)
            ->assign('route', $this->route)
            ->getResponse('dbcheck.tpl');
    }

    /**
     * @param bool $extended
     * @param bool $clearCache
     * @return array|null
     */
    private function getDBStruct(bool $extended = false, bool $clearCache = false): ?array
    {
        static $dbStruct = [
            'normal'   => null,
            'extended' => null,
        ];

        $db           = $this->db;
        $cache        = $this->cache;
        $dbLocked     = [];
        $database     = $db->getConfig()['database'];
        $mysqlVersion = DBMigrationHelper::getMySQLVersion();

        if ($clearCache) {
            if ($cache->isActive()) {
                $cache->flushTags([\CACHING_GROUP_CORE . '_getDBStruct']);
            } else {
                Backend::set('getDBStruct_extended', false);
                Backend::set('getDBStruct_normal', false);
            }
            $dbStruct['extended'] = null;
            $dbStruct['normal']   = null;
        }

        if ($extended) {
            $cacheID = 'getDBStruct_extended';
            if ($dbStruct['extended'] === null) {
                $dbStruct['extended'] = $cache->isActive()
                    ? $cache->get($cacheID)
                    : Backend::get($cacheID, false);
            }
            $dbStructure =& $dbStruct['extended'];

            if (\version_compare($mysqlVersion->innodb->version, '5.6', '>=')) {
                $dbStatus = $db->getObjects(
                    'SHOW OPEN TABLES
                    WHERE `Database` LIKE :schema',
                    ['schema' => $database]
                );
                if ($dbStatus) {
                    foreach ($dbStatus as $oStatus) {
                        if ((int)$oStatus->In_use > 0) {
                            $dbLocked[$oStatus->Table] = 1;
                        }
                    }
                }
            }
        } else {
            $cacheID = 'getDBStruct_normal';
            if ($dbStruct['normal'] === null) {
                $dbStruct['normal'] = $cache->isActive()
                    ? $cache->get($cacheID)
                    : Backend::get($cacheID);
            }
            $dbStructure =& $dbStruct['normal'];
        }

        if ($dbStructure === false) {
            $dbStructure = [];
            $dbData      = $db->getObjects(
                "SELECT t.`TABLE_NAME`, t.`ENGINE`, `TABLE_COLLATION`, t.`TABLE_ROWS`, t.`TABLE_COMMENT`,
                    t.`DATA_LENGTH` + t.`INDEX_LENGTH` AS DATA_SIZE,
                    COUNT(IF(c.DATA_TYPE = 'text', c.COLUMN_NAME, NULL)) TEXT_FIELDS,
                    COUNT(IF(c.DATA_TYPE = 'tinyint', c.COLUMN_NAME, NULL)) TINY_FIELDS,
                    COUNT(IF(c.COLLATION_NAME RLIKE 'utf8(mb3)?_unicode_ci', NULL, c.COLLATION_NAME)) FIELD_COLLATIONS
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.COLUMNS c ON c.TABLE_NAME = t.TABLE_NAME
                    AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
                    AND (c.DATA_TYPE = 'text'
                        OR (c.DATA_TYPE = 'tinyint' AND SUBSTRING(c.COLUMN_NAME, 1, 1) = 'k')
                        OR c.COLLATION_NAME NOT RLIKE 'utf8(mb3)?_unicode_ci')
                WHERE t.`TABLE_SCHEMA` = :schema
                    AND t.`TABLE_NAME` NOT LIKE 'xplugin_%'
                GROUP BY t.`TABLE_NAME`, t.`ENGINE`, `TABLE_COLLATION`, t.`TABLE_ROWS`, t.`TABLE_COMMENT`,
                    t.`DATA_LENGTH` + t.`INDEX_LENGTH`
                ORDER BY t.`TABLE_NAME`",
                ['schema' => $database]
            );

            foreach ($dbData as $data) {
                $table = $data->TABLE_NAME;
                if ($extended) {
                    $dbStructure[$table]            = $data;
                    $dbStructure[$table]->Columns   = [];
                    $dbStructure[$table]->Migration = DBMigrationHelper::MIGRATE_NONE;

                    if (\version_compare($mysqlVersion->innodb->version, '5.6', '<')) {
                        $dbStructure[$table]->Locked = (int)\str_contains($data->TABLE_COMMENT, ':Migrating');
                    } else {
                        $dbStructure[$table]->Locked = $dbLocked[$table] ?? 0;
                    }
                } else {
                    $dbStructure[$table] = [];
                }

                $columns = $db->getObjects(
                    'SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_TYPE`, `CHARACTER_SET_NAME`, `COLLATION_NAME`
                    FROM information_schema.COLUMNS
                    WHERE `TABLE_SCHEMA` = :schema
                        AND `TABLE_NAME` = :table
                    ORDER BY `ORDINAL_POSITION`',
                    [
                        'schema' => $database,
                        'table'  => $table
                    ]
                );
                foreach ($columns as $column) {
                    if ($extended) {
                        $dbStructure[$table]->Columns[$column->COLUMN_NAME] = $column;
                    } else {
                        $dbStructure[$table][] = $column->COLUMN_NAME;
                    }
                }
                if ($extended) {
                    $dbStructure[$table]->Migration = DBMigrationHelper::isTableNeedMigration($data);
                }
            }
            if ($cache->isActive()) {
                $cache->set(
                    $cacheID,
                    $dbStructure,
                    [\CACHING_GROUP_CORE, \CACHING_GROUP_CORE . '_getDBStruct']
                );
            } else {
                Backend::set($cacheID, $dbStructure);
            }
        } elseif ($extended) {
            foreach (\array_keys($dbStructure) as $table) {
                $dbStructure[$table]->Locked = $dbLocked[$table] ?? 0;
            }
        }

        return $dbStructure;
    }

    /**
     * @return array
     */
    private function getDBFileStruct(): array
    {
        $version    = Parser::parse(\APPLICATION_VERSION);
        $versionStr = $version->getMajor() . '-' . $version->getMinor() . '-' . $version->getPatch();
        if ($version->hasPreRelease()) {
            $preRelease  = $version->getPreRelease();
            $versionStr .= '-' . $preRelease->getGreek();
            if ($preRelease->getReleaseNumber() > 0) {
                $versionStr .= '-' . $preRelease->getReleaseNumber();
            }
        }

        $fileList = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5 . 'dbstruct_' . $versionStr . '.json';
        if (!\file_exists($fileList)) {
            return [];
        }
        $struct = \json_decode(\file_get_contents($fileList));

        return \is_object($struct) ? \get_object_vars($struct) : [];
    }

    /**
     * @param string $msg
     * @param bool   $engineError
     * @return stdClass
     */
    private function createDBStructError(string $msg, bool $engineError = false): stdClass
    {
        return (object)[
            'errMsg'        => $msg,
            'isEngineError' => $engineError,
        ];
    }

    /**
     * @param array $dbFileStruct
     * @param array $dbStruct
     * @return object[]
     */
    private function compareDBStruct(array $dbFileStruct, array $dbStruct): array
    {
        $errors = [];
        foreach ($dbFileStruct as $table => $columns) {
            if (!\array_key_exists($table, $dbStruct)) {
                $errors[$table] = $this->createDBStructError(\__('errorNoTable'));
                continue;
            }
            if (($dbStruct[$table]->Migration & DBMigrationHelper::MIGRATE_INNODB)
                === DBMigrationHelper::MIGRATE_INNODB
            ) {
                $errors[$table] = $this->createDBStructError(\sprintf(\__('errorNoInnoTable'), $table), true);
                continue;
            }
            if (($dbStruct[$table]->Migration & DBMigrationHelper::MIGRATE_UTF8) === DBMigrationHelper::MIGRATE_UTF8) {
                $errors[$table] = $this->createDBStructError(\sprintf(\__('errorWrongCollation'), $table), true);
                continue;
            }

            foreach ($columns as $column) {
                if (!\in_array($column, isset($dbStruct[$table]->Columns)
                    ? \array_keys($dbStruct[$table]->Columns)
                    : $dbStruct[$table], true)
                ) {
                    $errors[$table] = $this->createDBStructError(\sprintf(\__('errorRowMissing'), $column, $table));
                    break;
                }

                if (isset($dbStruct[$table]->Columns[$column])) {
                    if (!empty($dbStruct[$table]->Columns[$column]->COLLATION_NAME)
                        && !\in_array(
                            $dbStruct[$table]->Columns[$column]->COLLATION_NAME,
                            ['utf8_unicode_ci', 'utf8mb3_unicode_ci']
                        )
                    ) {
                        $errors[$table] = $this->createDBStructError(\sprintf(\__('errorWrongCollationRow'), $column));
                        break;
                    }
                    if ($dbStruct[$table]->Columns[$column]->DATA_TYPE === 'text') {
                        $errors[$table] = $this->createDBStructError(
                            \sprintf(\__('errorDataTypeTextInRow'), $column),
                            true
                        );
                        break;
                    }
                    if ($dbStruct[$table]->Columns[$column]->DATA_TYPE === 'tinyint'
                        && \str_starts_with($dbStruct[$table]->Columns[$column]->COLUMN_NAME, 'k')
                    ) {
                        $errors[$table] = $this->createDBStructError(
                            \sprintf(\__('errorDataTypeTinyInRow'), $column),
                            true
                        );
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param string $action
     * @param array  $tables
     * @return stdClass[]|false
     */
    private function doDBMaintenance(string $action, array $tables): array|bool
    {
        $cmd = match ($action) {
            'optimize' => 'OPTIMIZE TABLE ',
            'analyze'  => 'ANALYZE TABLE ',
            'repair'   => 'REPAIR TABLE ',
            'check'    => 'CHECK TABLE ',
            default    => false
        };

        return \count($tables) > 0 && $cmd !== false
            ? $this->db->getObjects($cmd . \implode(', ', $tables))
            : false;
    }

    /**
     * @param array $dbStruct
     * @return stdClass
     */
    private function determineEngineUpdate(array $dbStruct): stdClass
    {
        $result             = new stdClass();
        $result->tableCount = 0;
        $result->dataSize   = 0;
        foreach ($dbStruct as $meta) {
            if (isset($meta->Migration) && $meta->Migration !== DBMigrationHelper::MIGRATE_NONE) {
                $result->tableCount++;
                $result->dataSize += $meta->DATA_SIZE;
            }
        }

        $result->estimated = [
            $result->tableCount * 1.60 + $result->dataSize / 1048576 * 1.15,
            $result->tableCount * 2.40 + $result->dataSize / 1048576 * 2.50,
        ];

        return $result;
    }

    /**
     * @param string   $fileName
     * @param string[] $shopTables
     * @return string
     */
    private function doEngineUpdateScript(string $fileName, array $shopTables): string
    {
        $nl = "\r\n";

        $database    = $this->db->getConfig()['database'];
        $host        = $this->db->getConfig()['host'];
        $mysqlVer    = DBMigrationHelper::getMySQLVersion();
        $recreateFKs = '';

        $result  = '-- ' . $fileName . $nl;
        $result .= '-- ' . $nl;
        $result .= '-- @host: ' . $host . $nl;
        $result .= '-- @database: ' . $database . $nl;
        $result .= '-- @created: ' . \date(\DATE_RFC822) . $nl;
        $result .= '-- ' . $nl;
        $result .= '-- @important: !!! PLEASE MAKE A BACKUP OF STRUCTURE AND DATA FOR `' . $database . '` !!!' . $nl;
        $result .= '-- ' . $nl;
        $result .= $nl;
        $result .= '-- ---------------------------------------------------------'
            . '-------------------------------------------' . $nl;
        $result .= '-- ' . $nl;
        $result .= 'use `' . $database . '`;' . $nl;

        foreach (DBMigrationHelper::getTablesNeedMigration() as $table) {
            $fulltextSQL = [];
            $migration   = DBMigrationHelper::isTableNeedMigration($table);

            if (!\in_array($table->TABLE_NAME, $shopTables, true)) {
                continue;
            }

            if (\version_compare($mysqlVer->innodb->version, '5.6', '<')) {
                // Fulltext indizes are not supported for innoDB on MySQL < 5.6
                $fulltextIndizes = DBMigrationHelper::getFulltextIndizes($table->TABLE_NAME);

                if ($fulltextIndizes) {
                    $result .= $nl . '--' . $nl;
                    $result .= '-- remove fulltext indizes because there is no support for innoDB on MySQL < 5.6 '
                        . $nl;
                    foreach ($fulltextIndizes as $fulltextIndex) {
                        $fulltextSQL[] = /** @lang text */
                            'ALTER TABLE `' . $table->TABLE_NAME . '` DROP KEY `' . $fulltextIndex->INDEX_NAME . '`';
                    }
                }
            }

            if (($migration & DBMigrationHelper::MIGRATE_TABLE) !== DBMigrationHelper::MIGRATE_NONE) {
                $result .= $nl . '--' . $nl;
                if (($migration & DBMigrationHelper::MIGRATE_TABLE) === DBMigrationHelper::MIGRATE_TABLE) {
                    $result .= '-- migrate engine and collation for ' . $table->TABLE_NAME . $nl;
                } elseif (($migration & DBMigrationHelper::MIGRATE_INNODB) === DBMigrationHelper::MIGRATE_INNODB) {
                    $result .= '-- migrate engine for ' . $table->TABLE_NAME . $nl;
                } elseif (($migration & DBMigrationHelper::MIGRATE_UTF8) === DBMigrationHelper::MIGRATE_UTF8) {
                    $result .= '-- migrate collation for ' . $table->TABLE_NAME . $nl;
                }
            } else {
                $result .= $nl;
            }

            if (\count($fulltextSQL) > 0) {
                $result .= \implode(';' . $nl, $fulltextSQL) . ';' . $nl;
            }

            $sql    = DBMigrationHelper::sqlMoveToInnoDB($table);
            $fkSQLs = DBMigrationHelper::sqlRecreateFKs($table->TABLE_NAME);
            if (!empty($sql)) {
                $result .= '--' . $nl;
                foreach ($fkSQLs->dropFK as $fkSQL) {
                    $result .= $fkSQL . ';' . $nl;
                }
                $result .= $sql . ';' . $nl;
                foreach ($fkSQLs->createFK as $fkSQL) {
                    $recreateFKs .= $fkSQL . ';' . $nl;
                }
            }

            $sql = DBMigrationHelper::sqlConvertUTF8($table, $nl);
            if (!empty($sql)) {
                $result .= '--' . $nl;
                $result .= '-- migrate collation and / or datatype for columns in ' . $table->TABLE_NAME . $nl;
                $result .= '--' . $nl;
                $result .= $sql . ';' . $nl;
            }
        }

        $result .= $nl;

        if (\version_compare($mysqlVer->innodb->version, '5.6', '<')) {
            // Fulltext search is not available on MySQL < 5.6
            $result .= '--' . $nl;
            $result .= '-- Fulltext search is not available on MySQL < 5.6' . $nl;
            $result .= '--' . $nl;
            $result .= "UPDATE `teinstellungen` SET `cWert` = 'N' WHERE `cName` = 'suche_fulltext';" . $nl;
            $result .= $nl;
        }

        if (!empty($recreateFKs)) {
            $result .= '--' . $nl;
            $result .= '-- Recreate foreign keys' . $nl;
            $result .= '--' . $nl;
            $result .= $recreateFKs;
            $result .= $nl;
        }

        return $result;
    }
}
