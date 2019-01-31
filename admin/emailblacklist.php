<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_EMAIL_BLACKLIST_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'emailblacklist';
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_EMAILBLACKLIST, $_POST);
}
if (isset($_POST['emailblacklist']) && (int)$_POST['emailblacklist'] === 1 && Form::validateToken()) {
    $addresses = explode(';', $_POST['cEmail']);
    if (is_array($addresses) && count($addresses) > 0) {
        Shop::Container()->getDB()->query('TRUNCATE temailblacklist', \DB\ReturnType::AFFECTED_ROWS);
        foreach ($addresses as $mail) {
            $mail = strip_tags(trim($mail));
            if (mb_strlen($mail) > 0) {
                Shop::Container()->getDB()->insert('temailblacklist', (object)['cEmail' => $mail]);
            }
        }
    }
}
$blacklist = Shop::Container()->getDB()->query(
    'SELECT * 
        FROM temailblacklist',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$blocked   = Shop::Container()->getDB()->query(
    "SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
        FROM temailblacklistblock
        ORDER BY dLetzterBlock DESC
        LIMIT 100",
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('oEmailBlacklist_arr', $blacklist)
       ->assign('oEmailBlacklistBlock_arr', $blocked)
       ->assign('oConfig_arr', getAdminSectionSettings(CONF_EMAILBLACKLIST))
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('emailblacklist.tpl');
