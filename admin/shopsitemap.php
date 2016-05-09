<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);

$Einstellungen = Shop::getSettings(array(CONF_SITEMAP));

$cHinweis = '';
$cFehler  = '';

setzeSprache();

if (isset($_POST['speichern']) && validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_SITEMAP, $_POST);
    if (isset($_POST['nVon']) && is_array($_POST['nVon']) && count($_POST['nVon']) > 0 && is_array($_POST['nBis']) && count($_POST['nBis']) > 0) {
        // Tabelle leeren
        Shop::DB()->query("TRUNCATE TABLE tpreisspannenfilter", 3);

        for ($i = 0; $i < 10; $i++) {
            // Neue Werte in die DB einfuegen
            if (intval($_POST['nVon'][$i]) >= 0 && intval($_POST['nBis'][$i]) > 0) {
                unset($oPreisspannenfilter);
                $oPreisspannenfilter       = new stdClass();
                $oPreisspannenfilter->nVon = intval($_POST['nVon'][$i]);
                $oPreisspannenfilter->nBis = intval($_POST['nBis'][$i]);

                Shop::DB()->insert('tpreisspannenfilter', $oPreisspannenfilter);
            }
        }
    }
}

$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenSektion = " . CONF_SITEMAP . "
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
            WHERE kEinstellungenSektion = " . CONF_SITEMAP . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );
    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('Sprachen', gibAlleSprachen())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('shopsitemap.tpl');
