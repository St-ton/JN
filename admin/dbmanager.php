<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

$oAccount->permission('DBCHECK_VIEW', true, true);

if (isset($_POST['command'])) {
	$result = null;
	$command = $_POST['command'];
	
	try {
		Shop::DB()->beginTransaction();
		$result = Shop::DB()->executeQuery($command, 9);
		Shop::DB()->commit();
	}
	catch (PDOException $e) {
		Shop::DB()->rollback();
		$result = $e;
	}
	
	$command = SqlFormatter::compress($command);
	
	$cmd = (object)[
		'compressed' => $command,
		'formattedPlain' => SqlFormatter::format($command, false),
		'formattedHtml' => SqlFormatter::format($command, true)
	];

	$smarty->assign('command', $cmd)
		->assign('result', $result);
}

$tables = DBManager::getStatus(DB_NAME);
$definedTables = array_keys(getDBFileStruct() ?: array());

$tableColumns = array();
foreach ($tables as $table => $info) {
	$columns = DBManager::getColumns($table);
	$columns = array_map(create_function('$n', 'return null;'), $columns);
	$tableColumns[$table] = $columns;
}

$t = (object)['tables' => $tableColumns];

$smarty->assign('tables', $tables)
       ->assign('definedTables', $definedTables)
	   ->assign('tableColumns', $t)
       ->display('dbmanager.tpl');