<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SITEMAP_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$cHinweis      = '';
$cFehler       = '';
setzeSprache();

if (isset($_POST['speichern']) && FormHelper::validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_SITEMAP, $_POST);
    if (isset($_POST['nVon'])
        && is_array($_POST['nVon'])
        && is_array($_POST['nBis'])
        && count($_POST['nVon']) > 0
        && count($_POST['nBis']) > 0
    ) {
        Shop::Container()->getDB()->query('TRUNCATE TABLE tpreisspannenfilter', \DB\ReturnType::AFFECTED_ROWS);
        for ($i = 0; $i < 10; $i++) {
            if ((int)$_POST['nVon'][$i] >= 0 && (int)$_POST['nBis'][$i] > 0) {
                $oPreisspannenfilter       = new stdClass();
                $oPreisspannenfilter->nVon = (int)$_POST['nVon'][$i];
                $oPreisspannenfilter->nBis = (int)$_POST['nBis'][$i];

                Shop::Container()->getDB()->insert('tpreisspannenfilter', $oPreisspannenfilter);
            }
        }
    }
}

$smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_SITEMAP))
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('shopsitemap.tpl');
