<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

$oAccount->permission('DBCHECK_VIEW', true, true);

$tables = DBManager::getStatus(DB_NAME);
$smarty->assign('tables', $tables);

////////////////////////////////////////////////////////////////////////////
$restrictedTables = ['tadminlogin', 'tbrocken', 'tsession', 'tsynclogin'];

function exec_query($query) {
    try {
        Shop::DB()->beginTransaction();
        $result = Shop::DB()->executeQuery($query, 9);
        Shop::DB()->commit();
        return $result;
    }
    catch (PDOException $e) {
        Shop::DB()->rollback();
        throw $e;
    }
}
////////////////////////////////////////////////////////////////////////////

switch (true) {
    case isset($_GET['table']): {
        $table = $_GET['table'];
        $status = DBManager::getStatus(DB_NAME, $table);
        $columns = DBManager::getColumns($table);
        $indexes = DBManager::getIndexes($table);
        
        $smarty->assign('selectedTable', $table)
               ->assign('status', $status)
               ->assign('columns', $columns)
               ->assign('indexes', $indexes)
               ->assign('sub', 'table')
               ->display('dbmanager.tpl');
        break;
    }
    
    case isset($_GET['select']): {
        $table = $_GET['select'];
        $status = DBManager::getStatus(DB_NAME, $table);
        $columns = DBManager::getColumns($table);
        $indexes = DBManager::getIndexes($table);

        $table_ = Shop::DB()->escape($table);
        $data = Shop::DB()->executeQuery("SELECT * FROM `{$table_}` LIMIT 50", 9);

        $smarty->assign('selectedTable', $table)
               ->assign('data', $data)
               ->assign('columns', $columns)
               ->assign('sub', 'select')
               ->display('dbmanager.tpl');
        break;
    }
    
    case isset($_GET['command']): {
        $command = $_GET['command'];

        $jsTypo = (object)['tables' => []];
        foreach ($tables as $table => $info) {
            $columns = DBManager::getColumns($table);
            $columns = array_map(create_function('$n', 'return null;'), $columns);
            $jsTypo->tables[$table] = $columns;
        }
        
        if (isset($_POST['query'])) {
            $query = $_POST['query'];
            
            try {
                $parser = new SqlParser\Parser($query);

                if (is_array($parser->errors) && count($parser->errors) > 0) {
                    throw $parser->errors[0];
                }
                else {                   
                    $q = SqlParser\Utils\Query::getAll($query);
                    
                    if ($q['is_select'] !== true) {
                        throw new \Exception(sprintf('Query is restricted to SELECT statements'));
                    }

                    foreach ($q['select_tables'] as $t) {
                        $table = $t[0]; $dbname = $t[1];
                        if ($dbname !== null && strcasecmp($dbname, DB_NAME) !== 0) {
                            throw new \Exception(sprintf('Well, at least u tried :)'));
                        }
                        if (in_array(strtolower($table), $restrictedTables)) {
                            throw new \Exception(sprintf('Permission denied for table `%s`', $table));
                        }
                    }

                    $stmt = $q['statement'];

                    if ($q['limit'] === false) {
                        $stmt->limit = new SqlParser\Components\Limit(50, 0);
                    }
                    
                    $newQuery = $stmt->build();
                    $query = SqlParser\Utils\Formatter::format($newQuery, ['type' => 'text']);

                    $result = exec_query($newQuery);

                    $smarty->assign('result', $result);
                }
            }
            catch (Exception $e) {
                $smarty->assign('error', $e);
            }

            $smarty->assign('query', $query);
        }

        $smarty->assign('jsTypo', $jsTypo)
               ->assign('sub', 'command')
               ->display('dbmanager.tpl');
        break;
    }
    
    default: {
        $jsTypo = (object)['tables' => []];
        $definedTables = array_keys(getDBFileStruct() ?: array());

        foreach ($tables as $table => $info) {
            $columns = DBManager::getColumns($table);
            $columns = array_map(create_function('$n', 'return null;'), $columns);
            $jsTypo->tables[$table] = $columns;
        }

        $smarty->assign('definedTables', $definedTables)
               ->assign('jsTypo', $jsTypo)
               ->assign('sub', 'default')
               ->display('dbmanager.tpl');
        break;
    }
}