<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('SETTINGS_NAVIGATION_FILTER_VIEW', true, true);

$Einstellungen = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
$cHinweis      = '';
$cFehler       = '';

setzeSprache();

if (isset($_POST['speichern']) && FormHelper::validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_NAVIGATIONSFILTER, $_POST);
    Shop::Cache()->flushTags([CACHING_GROUP_CATEGORY]);
    if (is_array($_POST['nVon'])
        && is_array($_POST['nBis'])
        && count($_POST['nVon']) > 0
        && count($_POST['nBis']) > 0
    ) {
        // Tabelle leeren
        Shop::Container()->getDB()->query('TRUNCATE TABLE tpreisspannenfilter', \DB\ReturnType::AFFECTED_ROWS);

        foreach ($_POST['nVon'] as $i => $nVon) {
            $nVon = (float)$nVon;
            $nBis = (float)$_POST['nBis'][$i];

            if ($nVon >= 0 && $nBis >= 0) {
                Shop::Container()->getDB()->insert('tpreisspannenfilter', (object)['nVon' => $nVon, 'nBis' => $nBis]);
            }
        }
    }
}

$oConfig_arr = Shop::Container()->getDB()->selectAll(
    'teinstellungenconf',
    'kEinstellungenSektion',
    CONF_NAVIGATIONSFILTER,
    '*',
    'nSort'
);

foreach ($oConfig_arr as $oConfig) {
    if ($oConfig->cInputTyp === 'selectbox') {
        $oConfig->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig->kEinstellungenConf,
            '*',
            'nSort'
        );
    }
    $oSetValue = Shop::Container()->getDB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_NAVIGATIONSFILTER,
        'cName',
        $oConfig->cWertName
    );
    $oConfig->gesetzterWert = $oSetValue->cWert ?? null;
}

$oPreisspannenfilter_arr = Shop::Container()->getDB()->query(
    'SELECT * FROM tpreisspannenfilter',
    \DB\ReturnType::ARRAY_OF_OBJECTS
);

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oPreisspannenfilter_arr', $oPreisspannenfilter_arr)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('navigationsfilter.tpl');
