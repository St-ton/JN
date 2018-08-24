<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_EMAIL_BLACKLIST_VIEW', true, true);
/** @global JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_EMAILBLACKLIST]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'emailblacklist';

// Einstellungen
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_EMAILBLACKLIST, $_POST);
}
// Kundenfelder
if (isset($_POST['emailblacklist']) && (int)$_POST['emailblacklist'] === 1 && FormHelper::validateToken()) {
    // Speichern
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

$oConfig_arr = Shop::Container()->getDB()->selectAll(
    'teinstellungenconf',
    'kEinstellungenSektion',
    CONF_EMAILBLACKLIST,
    '*',
    'nSort'
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }

    $oSetValue = Shop::Container()->getDB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_EMAILBLACKLIST,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
}

// Emails auslesen und in Smarty assignen
$oEmailBlacklist_arr = Shop::Container()->getDB()->query(
    'SELECT * 
        FROM temailblacklist',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
// Geblockte Emails auslesen und assignen
$oEmailBlacklistBlock_arr = Shop::Container()->getDB()->query(
    "SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
        FROM temailblacklistblock
        ORDER BY dLetzterBlock DESC
        LIMIT 100",
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('oEmailBlacklist_arr', is_array($oEmailBlacklist_arr) ? $oEmailBlacklist_arr : [])
       ->assign('oEmailBlacklistBlock_arr', is_array($oEmailBlacklistBlock_arr) ? $oEmailBlacklistBlock_arr : [])
       ->assign('oConfig_arr', $oConfig_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('emailblacklist.tpl');
