<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Update;

use JTL\DB\ReturnType;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Smarty;

/**
 * Class MigrationHelper
 * @package JTL\Update
 */
class MigrationHelper
{
    /**
     * @var string
     */
    public const DATE_FORMAT = 'YmdHis';

    /**
     * @var string
     */
    public const MIGRATION_CLASS_NAME_PATTERN = '/^Migration_(\d+)$/i';

    /**
     * @var string
     */
    public const MIGRATION_FILE_NAME_PATTERN = '/^(\d+)_([\w_]+).php$/i';

    /**
     * Gets the migration path.
     *
     * @return string
     */
    public static function getMigrationPath(): string
    {
        return \PFAD_ROOT . \PFAD_UPDATE . 'migrations' . \DIRECTORY_SEPARATOR;
    }

    /**
     * Gets an array of all the existing migration class names.
     *
     * @return array
     */
    public static function getExistingMigrationClassNames(): array
    {
        $classNames = [];
        $path       = static::getMigrationPath();
        $phpFiles   = \glob($path . '*.php');
        foreach ($phpFiles as $filePath) {
            if (\preg_match(static::MIGRATION_FILE_NAME_PATTERN, \basename($filePath))) {
                $classNames[] = static::mapFileNameToClassName(\basename($filePath));
            }
        }

        return $classNames;
    }

    /**
     * Get the id from a file name.
     *
     * @param string $fileName File Name
     * @return string|null
     */
    public static function getIdFromFileName($fileName): ?string
    {
        $matches = [];
        if (\preg_match(static::MIGRATION_FILE_NAME_PATTERN, \basename($fileName), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get the info from a file name.
     *
     * @param string $fileName
     * @return string|null
     */
    public static function getInfoFromFileName(string $fileName): ? string
    {
        $matches = [];
        if (\preg_match(static::MIGRATION_FILE_NAME_PATTERN, \basename($fileName), $matches)) {
            return \preg_replace_callback(
                '/(^|_)([a-z])/',
                function ($m) {
                    return (\mb_strlen($m[1]) ? ' ' : '') . \mb_convert_case($m[2], \MB_CASE_UPPER);
                },
                $matches[2]
            );
        }

        return null;
    }

    /**
     * Returns names like 'Migration_12345678901234'.
     *
     * @param string $fileName File Name
     * @return string
     */
    public static function mapFileNameToClassName($fileName): string
    {
        return 'Migration_' . static::getIdFromFileName($fileName);
    }

    /**
     * Returns names like '12345678901234'.
     *
     * @param string $className File Name
     * @return string|null
     */
    public static function mapClassNameToId($className): ?string
    {
        $matches = [];
        if (\preg_match(static::MIGRATION_CLASS_NAME_PATTERN, $className, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if a migration file name is valid.
     *
     * @param string $fileName File Name
     * @return bool|int
     */
    public static function isValidMigrationFileName($fileName)
    {
        $matches = [];

        return \preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches);
    }

    /**
     * Check database integrity
     */
    public static function verifyIntegrity(): void
    {
        Shop::Container()->getDB()->query(
            "CREATE TABLE IF NOT EXISTS tmigration 
            (
                kMigration bigint(14) NOT NULL, 
                nVersion int(3) NOT NULL, 
                dExecuted datetime NOT NULL,
                PRIMARY KEY (kMigration)
            ) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'",
            ReturnType::DEFAULT
        );
        Shop::Container()->getDB()->query(
            "CREATE TABLE IF NOT EXISTS tmigrationlog 
            (
                kMigrationlog int(10) NOT NULL AUTO_INCREMENT, 
                kMigration bigint(20) NOT NULL, 
                cDir enum('up','down') NOT NULL, 
                cState varchar(6) NOT NULL, 
                cLog text NOT NULL, 
                dCreated datetime NOT NULL, 
                PRIMARY KEY (kMigrationlog)
            ) ENGINE=InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'",
            ReturnType::DEFAULT
        );
    }

    /**
     * @param string $idxTable
     * @param string $idxName
     * @return array
     */
    public static function indexColumns(string $idxTable, string $idxName): array
    {
        return Shop::Container()->getDB()->queryPrepared(
            "SHOW INDEXES FROM `$idxTable` WHERE Key_name = :idxName",
            ['idxName' => $idxName],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param string      $idxTable
     * @param array       $idxColumns
     * @param string|null $idxName
     * @param bool        $idxUnique
     * @return bool
     */
    public static function createIndex(string $idxTable, array $idxColumns, $idxName = null, $idxUnique = false): bool
    {
        if (empty($idxName)) {
            $idxName = \implode('_', $idxColumns) . '_' . ($idxUnique ? 'UQ' : 'IDX');
        }

        if (\count(self::indexColumns($idxTable, $idxName)) === 0 || self::dropIndex($idxTable, $idxName)) {
            $ddl = 'CREATE' . ($idxUnique ? ' UNIQUE' : '')
                . ' INDEX `' . $idxName . '` ON `' . $idxTable . '` '
                . '(`' . \implode('`, `', $idxColumns) . '`)';

            return !Shop::Container()->getDB()->executeQuery($ddl, ReturnType::DEFAULT) ? false : true;
        }

        return false;
    }

    /**
     * @param string $idxTable
     * @param string $idxName
     * @return bool
     */
    public static function dropIndex(string $idxTable, string $idxName): bool
    {
        if (\count(self::indexColumns($idxTable, $idxName)) > 0) {
            return !Shop::Container()->getDB()->executeQuery(
                'DROP INDEX `' . $idxName . '` ON `' . $idxTable . '` ',
                ReturnType::DEFAULT
            ) ? false : true;
        }

        return true;
    }

    /**
     * @param string $description
     * @param string $author
     * @return string
     * @throws \SmartyException
     * @throws \Exception
     */
    public static function create(string $description, string $author)
    {
        $datetime  = new \DateTime('NOW');
        $timestamp = $datetime->format('YmdHis');

        $asFilePath = function ($text) {
            $text = preg_replace('/\W/', '_', $text);
            $text = preg_replace('/_+/', '_', $text);

            return strtolower($text);
        };

        $filePath = implode(
            '_',
            array_filter([$timestamp, $asFilePath($description)])
        );

        $relPath       = 'update/migrations';
        $migrationPath = $relPath.'/'.$filePath.'.php';
        $fileSystem    = new Filesystem(new LocalFilesystem(['root' => PFAD_ROOT]));

        if (!$fileSystem->exists($relPath)) {
            throw new \Exception('Migrations path doesn\'t exist!');
        }

        $smartyCli  = Shop::Smarty(true, 'cli');
        $smartyCli->setCaching(Smarty::CACHING_OFF);
        $content = $smartyCli->assign('description', $description)
            ->assign('author', $author)
            ->assign('created', $datetime->format(\DateTime::RSS))
            ->assign('timestamp', $timestamp)
            ->fetch(PFAD_ROOT.'includes/src/Console/Command/Migration/Template/migration.class.tpl');

        $fileSystem->put($migrationPath, $content);

        return $migrationPath;
    }
}
