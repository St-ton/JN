<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$smarty = JTLSmarty::getInstance(false, false);
executeHook(HOOK_SMARTY_INC);

return $smarty;
