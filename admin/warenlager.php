<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('WAREHOUSE_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cStep    = 'uebersicht';
$cHinweis = '';
$cFehler  = '';
$cAction  = (isset($_POST['a']) && FormHelper::validateToken()) ? $_POST['a'] : null;

switch ($cAction) {
    case 'update':
        Shop::Container()->getDB()->query('UPDATE twarenlager SET nAktiv = 0', \DB\ReturnType::AFFECTED_ROWS);
        if (isset($_REQUEST['kWarenlager']) && is_array($_REQUEST['kWarenlager']) && count($_REQUEST['kWarenlager']) > 0) {
            $wl = [];
            foreach ($_REQUEST['kWarenlager'] as $_wl) {
                $wl[] = (int)$_wl;
            }
            Shop::Container()->getDB()->query(
                'UPDATE twarenlager SET nAktiv = 1 WHERE kWarenlager IN (' . implode(', ', $wl) . ')',
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
        if (is_array($_REQUEST['cNameSprache']) && count($_REQUEST['cNameSprache']) > 0) {
            foreach ($_REQUEST['cNameSprache'] as $kWarenlager => $cSpracheAssoc_arr) {
                Shop::Container()->getDB()->delete('twarenlagersprache', 'kWarenlager', (int)$kWarenlager);

                foreach ($cSpracheAssoc_arr as $kSprache => $cName) {
                    if (strlen(trim($cName)) > 1) {
                        $oObj              = new stdClass();
                        $oObj->kWarenlager = (int)$kWarenlager;
                        $oObj->kSprache    = (int)$kSprache;
                        $oObj->cName       = htmlspecialchars(trim($cName), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);

                        Shop::Container()->getDB()->insert('twarenlagersprache', $oObj);
                    }
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE]);
        $cHinweis = 'Ihre Warenlager wurden erfolgreich aktualisiert';
        break;
    default:
        break;
}

if ($cStep === 'uebersicht') {
    $smarty->assign('oWarenlager_arr', Warenlager::getAll(false, true))
           ->assign('oSprache_arr', Sprache::getAllLanguages());
}

$smarty->assign('cStep', $cStep)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->display('warenlager.tpl');
