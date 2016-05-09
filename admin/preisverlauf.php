<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('MODULE_PRICECHART_VIEW', true, true);

$cHinweis = '';
$cfehler  = '';

if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1) {
    $cHinweis .= saveAdminSectionSettings(CONF_PREISVERLAUF, $_POST);
}

$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenSektion = " . CONF_PREISVERLAUF . "
        ORDER BY nSort", 2
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconfwerte
                WHERE kEinstellungenConf = " . $oConfig_arr[$i]->kEinstellungenConf . "
                ORDER BY nSort", 2
        );
    }

    $oSetValue = Shop::DB()->query(
        "SELECT cWert
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . CONF_PREISVERLAUF . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert) ? $oSetValue->cWert : null);
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cfehler)
       ->display('preisverlauf.tpl');

/**
 * @param $cFarbCode
 * @return string
 */
function checkeFarbCode($cFarbCode)
{
    if (preg_match('/#[A-Fa-f0-9]{6}/', $cFarbCode) == 1) {
        return $cFarbCode;
    } else {
        $GLOBALS['cfehler'] = 'Bitte den Farbcode in folgender Schreibweise angeben: z.b. #FFFFFF';

        return '#000000';
    }
}
