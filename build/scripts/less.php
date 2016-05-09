<?php

$rootPath = realpath(dirname(__FILE__) . '/../..');
require_once $rootPath . '/includes/globalinclude.php';

$oPlugin = Plugin::getPluginById('evo_editor');

require_once $oPlugin->cAdminmenuPfad . '/cli.php';
