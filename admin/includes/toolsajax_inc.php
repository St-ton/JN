<?php

require_once PFAD_ROOT . PFAD_ADMIN . 'toolsajax.server.php';

$smarty->assign('xajax_javascript', $xajax->getJavascript(Shop::getURL() . '/' . PFAD_XAJAX));
