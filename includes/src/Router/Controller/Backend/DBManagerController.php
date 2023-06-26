<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\Permissions;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use JTL\Update\DBManager;
use JTLShop\SemVer\Parser as SemVerParser;
use PDOException;
use PhpMyAdmin\SqlParser\Components\Limit;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Formatter;
use PhpMyAdmin\SqlParser\Utils\Query;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DBManagerController
 * @package JTL\Router\Controller\Backend
 */
class DBManagerController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::DBCHECK_VIEW);
        $this->getText->loadAdminLocale('pages/dbmanager');

        $tables = DBManager::getStatus(DB_NAME);
        $smarty->assign('tables', $tables)
            ->assign('route', $this->route);

        $valid  = Form::validateToken();
        $jsTypo = (object)['tables' => []];
        foreach ($tables as $table => $info) {
            $columns                = DBManager::getColumns($table);
            $columns                = \array_map(
                static function ($n) {
                    return null;
                },
                $columns
            );
            $jsTypo->tables[$table] = $columns;
        }
        $smarty->assign('jsTypo', $jsTypo);

        switch (true) {
            case isset($_GET['table']) && $valid:
                return $this->actionGetTable($_GET['table']);

            case isset($_GET['select']) && $valid:
                return $this->actionSelect($_GET['select']);

            case isset($_GET['command']) && $valid:
                return $this->actionQuery();

            default:
                $definedTables = \array_keys(self::getDBFileStruct() ?: []);

                return $smarty->assign('definedTables', $definedTables)
                    ->assign('sub', 'default')
                    ->assign('columns', [])
                    ->getResponse('dbmanager.tpl');
        }
    }

    /**
     * @param string $table
     * @return ResponseInterface
     */
    private function actionGetTable(string $table): ResponseInterface
    {
        return $this->smarty->assign('selectedTable', $table)
            ->assign('status', DBManager::getStatus(DB_NAME, $table))
            ->assign('columns', DBManager::getColumns($table))
            ->assign('indexes', DBManager::getIndexes($table))
            ->assign('sub', 'table')
            ->getResponse('dbmanager.tpl');
    }

    /**
     * @return ResponseInterface
     */
    private function actionQuery(): ResponseInterface
    {
        $restrictedTables = ['tadminlogin', 'tbrocken', 'tsession', 'tsynclogin'];
        $query            = null;
        if (isset($_POST['query'])) {
            $query = $_POST['query'];
        } elseif (isset($_POST['sql_query_edit'])) {
            $query = $_POST['sql_query_edit'];
        }
        if ($query !== null) {
            try {
                $parser = new Parser($query);
                if (\is_array($parser->errors) && \count($parser->errors) > 0) {
                    throw $parser->errors[0];
                }
                $q = Query::getAll($query);
                if ($q['is_select'] !== true) {
                    throw new Exception('Query is restricted to SELECT statements');
                }
                foreach ($q['select_tables'] ?? [] as $t) {
                    [$table, $dbname] = $t;
                    if ($dbname !== null && \strcasecmp($dbname, DB_NAME) !== 0) {
                        throw new Exception('Well, at least you tried :)');
                    }
                    if (\in_array(\mb_convert_case($table, \MB_CASE_LOWER), $restrictedTables, true)) {
                        throw new Exception(\sprintf('Permission denied for table `%s`', $table));
                    }
                }
                $stmt = $q['statement'];
                if ($q['limit'] === false) {
                    $stmt->limit = new Limit(50, 0);
                }
                $newQuery = $stmt->build();
                $query    = Formatter::format($newQuery, ['type' => 'text']);
                $result   = $this->executeQuery($newQuery);
                $this->smarty->assign('result', $result);
            } catch (Exception $e) {
                $this->smarty->assign('error', $e);
            }
            $this->smarty->assign('query', $query);
        } elseif (isset($_GET['query'])) {
            $this->smarty->assign('query', Text::filterXSS($_GET['query']));
        }

        return $this->smarty->assign('sub', 'command')
            ->assign('columns', [])
            ->getResponse('dbmanager.tpl');
    }

    /**
     * @param string $table
     * @return ResponseInterface
     */
    private function actionSelect(string $table): ResponseInterface
    {
        if (!\preg_match('/^\w+$/i', $table, $m)) {
            die('Not allowed.');
        }
        $columns       = DBManager::getColumns($table);
        $defaultFilter = [
            'limit'  => 50,
            'offset' => 0,
            'where'  => []
        ];
        $filter        = $_GET['filter'] ?? [];
        $filter        = \array_merge($defaultFilter, $filter);
        // validate filter
        $filter['limit'] = (int)$filter['limit'];
        $page            = Request::getInt('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        if ($filter['limit'] < 1) {
            $filter['limit'] = 1;
        }

        $filter['offset'] = ($page - 1) * $filter['limit'];

        // query parts
        $queryParams = [];
        $queryParts  = ['table' => ' FROM ' . $table . ' '];

        // where
        if (isset($filter['where']['col'])) {
            $whereParts  = [];
            $columnCount = \count($filter['where']['col']);
            for ($i = 0; $i < $columnCount; $i++) {
                if (!empty($filter['where']['col'][$i]) && !empty($filter['where']['op'][$i])) {
                    $col = $filter['where']['col'][$i];
                    $val = $filter['where']['val'][$i];
                    $op  = \mb_convert_case($filter['where']['op'][$i], \MB_CASE_UPPER);
                    if ($op === 'LIKE %%') {
                        $op  = 'LIKE';
                        $val = \sprintf('%%%s%%', \trim($val, '%'));
                    } elseif ($op === 'IS NULL' || $op === 'IS NOT NULL') {
                        $whereParts[] = \sprintf('`%s` %s', $col, $op);
                        continue;
                    }
                    if ($op === 'IN' || $op === 'NOT IN') {
                        $values   = \explode(',', \trim($val, '() '));
                        $part     = \sprintf('`%s` %s (', $col, $op);
                        $prepared = [];
                        foreach ($values as $j => $value) {
                            $prepared[] = \sprintf(':where_%d_%d_val', $i, $j);

                            $queryParams['where_' . $i . '_' . $j . '_val'] = $value;
                        }
                        $part        .= \implode(',', $prepared) . ')';
                        $whereParts[] = $part;
                        continue;
                    }
                    $whereParts[] = \sprintf('`%s` %s :where_%d_val', $col, $op, $i);
                    /** @var string[] $queryParams */
                    $queryParams['where_' . $i . '_val'] = $val;
                }
            }
            if (\count($whereParts) > 0) {
                $queryParts['where'] = 'WHERE ' . \implode(' AND ', $whereParts);
            }
        }
        $fromWhere = \implode(' ', $queryParts);
        // count without limit
        $count = $this->db->getSingleArray(
            'SELECT COUNT(*) as allRowsFound' . $fromWhere,
            $queryParams
        )['allRowsFound'] ?? 0;
        $pages = (int)\ceil($count / $filter['limit']);
        // limit
        $queryParams['limit_count']  = $filter['limit'];
        $queryParams['limit_offset'] = $filter['offset'];
        $offsetLimit                 = ' LIMIT :limit_offset, :limit_count';

        $query = 'SELECT * ' . $fromWhere . $offsetLimit;
        $info  = null;
        $data  = $this->db->queryPrepared(
            $query,
            $queryParams,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS,
            false,
            static function ($o) use (&$info) {
                $info = $o;
            }
        );

        return $this->smarty->assign('selectedTable', $table)
            ->assign('data', $data)
            ->assign('page', $page)
            ->assign('query', $query)
            ->assign('count', $count)
            ->assign('pages', $pages)
            ->assign('filter', $filter)
            ->assign('columns', $columns)
            ->assign('info', $info)
            ->assign('sub', 'select')
            ->getResponse('dbmanager.tpl');
    }

    /**
     * @param string $query
     * @return array
     * @throws PDOException
     */
    private function executeQuery(string $query): array
    {
        try {
            $this->db->beginTransaction();
            $result = $this->db->getArrays($query);
            $this->db->commit();

            return $result;
        } catch (PDOException $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * @return array
     */
    public static function getDBFileStruct(): array
    {
        $version    = SemVerParser::parse(\APPLICATION_VERSION);
        $versionStr = $version->getMajor() . '-' . $version->getMinor() . '-' . $version->getPatch();
        if ($version->hasPreRelease()) {
            $preRelease  = $version->getPreRelease();
            $versionStr .= '-' . $preRelease->getGreek();
            if ($preRelease->getReleaseNumber() > 0) {
                $versionStr .= '-' . $preRelease->getReleaseNumber();
            }
        }
        $fileList = PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5 . 'dbstruct_' . $versionStr . '.json';
        if (!\file_exists($fileList)) {
            return [];
        }
        try {
            return \get_object_vars(\json_decode(\file_get_contents($fileList), false, 512, \JSON_THROW_ON_ERROR));
        } catch (\JsonException) {
            return [];
        }
    }
}
