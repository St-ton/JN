<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_EMAIL_BLACKLIST_VIEW', true, true);

$Einstellungen = Shop::getSettings(array(CONF_EMAILBLACKLIST));
$cHinweis      = '';
$cFehler       = '';
$step          = 'emailblacklist';

// Einstellungen
if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_EMAILBLACKLIST, $_POST);
}
// Kundenfelder
if (isset($_POST['emailblacklist']) && intval($_POST['emailblacklist']) === 1 && validateToken()) {
    // Speichern
    $cEmail_arr = explode(';', $_POST['cEmail']);

    if (is_array($cEmail_arr) && count($cEmail_arr) > 0) {
        Shop::DB()->query("truncate temailblacklist", 3);

        foreach ($cEmail_arr as $cEmail) {
            $cEmail = strip_tags(trim($cEmail));
            if (strlen($cEmail) > 0) {
                $oEmailBlacklist         = new stdClass();
                $oEmailBlacklist->cEmail = $cEmail;
                Shop::DB()->insert('temailblacklist', $oEmailBlacklist);
                unset($oEmailBlacklist);
            }
        }
    }
}

$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenSektion = " . CONF_EMAILBLACKLIST . "
        ORDER BY nSort", 2
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
                ORDER BY nSort", 2
        );
    }

    $oSetValue = Shop::DB()->query(
        "SELECT cWert
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . CONF_EMAILBLACKLIST . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
}

// Emails auslesen und in Smarty assignen
$oEmailBlacklist_arr = Shop::DB()->query("SELECT * FROM temailblacklist", 2);
if (is_array($oEmailBlacklist_arr) && count($oEmailBlacklist_arr) > 0) {
    $smarty->assign('oEmailBlacklist_arr', $oEmailBlacklist_arr);
}
// Geblockte Emails auslesen und assignen
$oEmailBlacklistBlock_arr = Shop::DB()->query(
    "SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
        FROM temailblacklistblock
        ORDER BY dLetzterBlock DESC
        LIMIT 100", 2
);

if (is_array($oEmailBlacklistBlock_arr) && count($oEmailBlacklistBlock_arr) > 0) {
    $smarty->assign('oEmailBlacklistBlock_arr', $oEmailBlacklistBlock_arr);
}
$smarty->assign('Sprachen', gibAlleSprachen())
       ->assign('oConfig_arr', $oConfig_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('emailblacklist.tpl');
