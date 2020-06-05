<?php

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SEARCH_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty     $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suche_inc.php';

$query = $_GET['cSuche'];

adminSearch(\trim($query), true);
