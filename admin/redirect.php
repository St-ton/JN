<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('REDIRECT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$aData           = (isset($_POST['aData'])) ? $_POST['aData'] : null;
$oRedirect       = new Redirect();
$urls            = array();
$cHinweis        = '';
$cFehler         = '';
$cParams_arr     = array(
    'cSortierFeld'     => 'nCount',
    'cSortierung'      => 'DESC',
    'nAnzahlProSeite'  => 50,
    'bUmgeleiteteUrls' => 0,
    'cSuchbegriff'     => ''
);

switch ($aData['action']) {
    case 'search':
        $ret = array(
            'article'      => getArticleList($aData['search'], array('cLimit' => 10, 'return' => 'object')),
            'category'     => getCategoryList($aData['search'], array('cLimit' => 10, 'return' => 'object')),
            'manufacturer' => getManufacturerList($aData['search'], array('cLimit' => 10, 'return' => 'object')),
        );
        exit(json_encode($ret));
        break;
    case 'check_url':
        $shopURL = Shop::getURL();
        $check   = (($aData['url'] != '' && $oRedirect->isAvailable($shopURL . $aData['url'])) ? '1' : '0');
        exit($check);
        break;
    case 'save' :
        if (validateToken()) {
            $shopURL   = Shop::getURL();
            $kRedirect = array_keys($aData['redirect']);
            for ($i = 0; $i < count($kRedirect); $i++) {
                $cToUrl = $aData['redirect'][$kRedirect[$i]]['url'];
                $oItem  = new Redirect($kRedirect[$i]);
                if (!empty($cToUrl)) {
                    $urls[$oItem->kRedirect] = $cToUrl;
                }
                if ($oItem->kRedirect > 0) {
                    $oItem->cToUrl = $cToUrl;
                    if ($oRedirect->isAvailable($shopURL . $cToUrl)) {
                        Shop::DB()->update('tredirect', 'kRedirect', $oItem->kRedirect, $oItem);
                    } else {
                        $cFehler .= "&Auml;nderungen konnten nicht gespeichert werden, da die weiterzuleitende URL {$cToUrl} nicht erreichbar ist.<br />";
                    }
                }
            }
            $cHinweis = 'Daten wurden erfolgreich aktualisiert.';
        }
        break;
    case 'delete':
        if (validateToken()) {
            $kRedirect = array_keys($aData['redirect']);
            for ($i = 0; $i < count($kRedirect); $i++) {
                if ($aData['redirect'][$kRedirect[$i]]['active'] == 1) {
                    $oRedirect->delete(intval($kRedirect[$i]));
                }
            };
        }
        break;
    case 'delete_all':
        if (validateToken()) {
            $oRedirect->deleteAll();
        }
        break;
    case 'new':
        if ($oRedirect->saveExt($_POST['cSource'], $_POST['cDestiny'])) {
            $cHinweis = 'Ihre Weiterleitung wurde erfolgreich gespeichert';
        } else {
            $cFehler = 'Fehler: Bitte pr&uuml;fen Sie Ihre Eingaben';
            $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST));
        }
        break;
    case 'csvimport':
        if (is_uploaded_file($_FILES['cFile']['tmp_name'])) {
            $cFile = PFAD_ROOT . PFAD_EXPORT . md5($_FILES['cFile']['name'] . time());
            if (move_uploaded_file($_FILES['cFile']['tmp_name'], $cFile)) {
                $cError_arr = $oRedirect->doImport($cFile);
                if (count($cError_arr) === 0) {
                    $cHinweis = 'Der Import wurde erfolgreich durchgef&uuml;hrt';
                } else {
                    @unlink($cFile);
                    $cFehler = 'Fehler: Der Import konnte nicht durchgef&uuml;hrt werden. Bitte pr&uuml;fen Sie die CSV Datei<br /><br />' . implode('<br />', $cError_arr);
                }
            }
        }
        break;
}

foreach ($cParams_arr as $cKey => $cVal) {
    if (isset($_POST[$cKey]) && !empty($_POST[$cKey])) {
        $cParams_arr[$cKey] = $_POST[$cKey];
        $smarty->assign($cKey, $_POST[$cKey]);
    } else {
        $smarty->assign($cKey, $cVal);
    }
}

$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(1, $cParams_arr['nAnzahlProSeite']);
$cParams           = '';
foreach ($cParams_arr as $key => $val) {
    $cParams .= $key . '=' . $val . '&';
}
$oRedirect_arr = $oRedirect->getList($oBlaetterNaviConf->cLimit1, $cParams_arr['nAnzahlProSeite'], $cParams_arr['bUmgeleiteteUrls'], $cParams_arr['cSortierFeld'], $cParams_arr['cSortierung'], $cParams_arr['cSuchbegriff']);
$oBlaetterNavi = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $oRedirect->getCount($cParams_arr['bUmgeleiteteUrls'], $cParams_arr['cSuchbegriff']), $cParams_arr['nAnzahlProSeite']);
if (!empty($oRedirect_arr) && !empty($urls)) {
    foreach ($oRedirect_arr as &$kRedirect) {
        if (array_key_exists($kRedirect->kRedirect, $urls)) {
            $kRedirect->cToUrl = $urls[$kRedirect->kRedirect];
        } elseif (array_key_exists('url', $_POST)) {
            //            $kRedirect->cToUrl = '';
        }
    }
    unset($urls);
}

$smarty->assign('aData', $aData)
       ->assign('cParams', $cParams)
       ->assign('oBlaetterNavi', $oBlaetterNavi)
       ->assign('oRedirect_arr', $oRedirect_arr)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->display('redirect.tpl');
