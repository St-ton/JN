<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */


require_once __DIR__ . '/includes/admininclude.php';

$smarty->assign('blub', 'blub')
       ->display('setup_assistant.tpl');
