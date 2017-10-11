<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * @deprecated since 4.06
 * TODO remove image prices in version 4.06+
 */
require_once __DIR__ . '/includes/admininclude.php';

/**
 * Preisanzeige Einstellungen holen
 *
 * @return array|mixed
 * @former holePreisanzeigeEinstellungen
 */
function getPriceDisplayConfig()
{
    $oPreisanzeigeConfTMP_arr = [];
    $oPreisanzeigeConf_arr    = Shop::DB()->selectAll(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_PREISANZEIGE,
        '*',
        'cName'
    );
    $cMapping_arr             = [
        'preisanzeige_preisgrafik_artikeldetails_anzeigen'    => 'Artikeldetails',
        'preisanzeige_preisgrafik_artikeluebersicht_anzeigen' => 'Artikeluebersicht',
        'preisanzeige_preisgrafik_boxen_anzeigen'             => 'Boxen',
        'preisanzeige_preisgrafik_startseite_anzeigen'        => 'Startseite',

        'preisanzeige_groesse_artikeldetails'                 => 'Artikeldetails',
        'preisanzeige_groesse_artikeluebersicht'              => 'Artikeluebersicht',
        'preisanzeige_groesse_boxen'                          => 'Boxen',
        'preisanzeige_groesse_startseite'                     => 'Startseite',

        'preisanzeige_farbe_artikeldetails'                   => 'Artikeldetails',
        'preisanzeige_farbe_artikeluebersicht'                => 'Artikeluebersicht',
        'preisanzeige_farbe_boxen'                            => 'Boxen',
        'preisanzeige_farbe_startseite'                       => 'Startseite',

        'preisanzeige_schriftart_artikeldetails'              => 'Artikeldetails',
        'preisanzeige_schriftart_artikeluebersicht'           => 'Artikeluebersicht',
        'preisanzeige_schriftart_boxen'                       => 'Boxen',
        'preisanzeige_schriftart_startseite'                  => 'Startseite'
    ];
    // Mapping
    if (is_array($oPreisanzeigeConf_arr) && count($oPreisanzeigeConf_arr) > 0) {
        foreach ($oPreisanzeigeConf_arr as $z => $oPreisanzeigeConf) {
            foreach ($cMapping_arr as $i => $cMapping) {
                if ($oPreisanzeigeConf->cName == $i) {
                    $oPreisanzeigeConfTMP_arr[$cMapping][] = $oPreisanzeigeConf;
                }
            }
        }
    } else {
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_preisgrafik_artikeldetails_anzeigen', 'N', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_preisgrafik_artikeluebersicht_anzeigen', 'N', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_preisgrafik_boxen_anzeigen', 'N', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_preisgrafik_startseite_anzeigen', 'N', NULL)", 4);

        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_groesse_artikeldetails', '18', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_groesse_artikeluebersicht', '18', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_groesse_boxen', '18', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_groesse_startseite', '18', NULL)", 4);

        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_farbe_artikeldetails', '#000000', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_farbe_artikeluebersicht', '#000000', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_farbe_boxen', '#000000', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_farbe_startseite', '#000000', NULL)", 4);

        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_schriftart_artikeldetails', 'GeosansLight.ttf', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_schriftart_artikeluebersicht', 'GeosansLight.ttf', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_schriftart_boxen', 'GeosansLight.ttf', NULL)", 4);
        Shop::DB()->query("INSERT INTO teinstellungen (kEinstellungenSektion, cName, cWert, cModulId)
                            VALUES(" . CONF_PREISANZEIGE . ", 'preisanzeige_schriftart_startseite', 'GeosansLight.ttf', NULL)", 4);

        $oPreisanzeigeConf_arr = Shop::DB()->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_PREISANZEIGE, '*', 'cName ASC');
        foreach ($oPreisanzeigeConf_arr as $z => $oPreisanzeigeConf) {
            foreach ($cMapping_arr as $i => $cMapping) {
                if ($oPreisanzeigeConf->cName == $i) {
                    $oPreisanzeigeConfTMP_arr[$cMapping][] = $oPreisanzeigeConf;
                }
            }
        }
    }
    $oPreisanzeigeConf_arr = $oPreisanzeigeConfTMP_arr;

    return $oPreisanzeigeConf_arr;
}

$oAccount->permission('DISPLAY_PRICECHART_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis              = '';
$cFehler               = '';
$oPreisanzeigeConf_arr = getPriceDisplayConfig();
// Update Preisanzeige
if (isset($_POST['update'])
    && (int)$_POST['update'] === 1
    && is_array($oPreisanzeigeConf_arr)
    && validateToken()
    && count($oPreisanzeigeConf_arr) > 0
) {
    foreach ($oPreisanzeigeConf_arr as $oPreisanzeigeConf) {
        $upd = new stdClass();
        if (isset($oPreisanzeigeConf[0]->cName, $_POST[$oPreisanzeigeConf[0]->cName])) {
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[0]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[0]->cName], $upd);
        }
        if (isset($oPreisanzeigeConf[1]->cName, $_POST[$oPreisanzeigeConf[1]->cName])) {
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[1]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[1]->cName], $upd);
        }
        if (isset($oPreisanzeigeConf[2]->cName, $_POST[$oPreisanzeigeConf[2]->cName])) {
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[2]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[2]->cName], $upd);
        }
        if (isset($oPreisanzeigeConf[3]->cName, $_POST[$oPreisanzeigeConf[3]->cName])) {
            $upd = new stdClass();
            $upd->cWert = StringHandler::htmlentities(StringHandler::filterXSS($_POST[$oPreisanzeigeConf[3]->cName]));
            Shop::DB()->update('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_PREISANZEIGE, $oPreisanzeigeConf[3]->cName], $upd);
        }
    }

    unset($GLOBALS['Einstellungen']['preisverlauf']);
    $oPreisanzeigeConf_arr = getPriceDisplayConfig();
    $cHinweis .= 'Ihre Einstellungen wurde erfolgreich gespeichert.';

    Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION]);
}
// Hole Fonts
$cFont_arr = [];
$dir       = PFAD_ROOT . PFAD_FONTS;
if (is_dir($dir)) {
    $dir_handle = opendir($dir);
    while (false !== ($file = readdir($dir_handle))) {
        if ($file !== '..' && $file !== '.' && $file[0] !== '.') {
            $cFont_arr[] = $file;
        }
    }
    closedir($dir_handle);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('cFont_arr', $cFont_arr)
       ->assign('oPreisanzeigeConf_arr', $oPreisanzeigeConf_arr)
       ->assign('cSektion_arr', array_keys($oPreisanzeigeConf_arr))
       ->display('preisanzeige.tpl');
