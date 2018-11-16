<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_EMAIL_BLACKLIST_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_EMAILBLACKLIST]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'emailblacklist';
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_EMAILBLACKLIST, $_POST);
}
if (isset($_POST['emailblacklist']) && (int)$_POST['emailblacklist'] === 1 && FormHelper::validateToken()) {
    $cEmail_arr = explode(';', $_POST['cEmail']);

    if (is_array($cEmail_arr) && count($cEmail_arr) > 0) {
        Shop::Container()->getDB()->query('TRUNCATE temailblacklist', \DB\ReturnType::AFFECTED_ROWS);

        foreach ($cEmail_arr as $cEmail) {
            $cEmail = strip_tags(trim($cEmail));
            if (strlen($cEmail) > 0) {
                $oEmailBlacklist         = new stdClass();
                $oEmailBlacklist->cEmail = $cEmail;
                Shop::Container()->getDB()->insert('temailblacklist', $oEmailBlacklist);
            }
        }
    }
}
$oEmailBlacklist_arr = Shop::Container()->getDB()->query(
    'SELECT * 
        FROM temailblacklist',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$oEmailBlacklistBlock_arr = Shop::Container()->getDB()->query(
    "SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
        FROM temailblacklistblock
        ORDER BY dLetzterBlock DESC
        LIMIT 100",
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('oEmailBlacklist_arr', $oEmailBlacklist_arr)
       ->assign('oEmailBlacklistBlock_arr', $oEmailBlacklistBlock_arr)
       ->assign('oConfig_arr', getAdminSectionSettings(CONF_EMAILBLACKLIST))
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('emailblacklist.tpl');
