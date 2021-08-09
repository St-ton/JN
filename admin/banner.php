<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('DISPLAY_BANNER_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
$cFehler  = '';
$cHinweis = '';
$cAction  = (isset($_REQUEST['action']) && validateToken()) ? $_REQUEST['action'] : 'view';
$postData = StringHandler::filterXSS($_POST);

if (!empty($postData) && (isset($postData['cName']) || isset($postData['kImageMap'])) && validateToken()) {
    $cPlausi_arr = [];
    $oBanner     = new ImageMap();
    $kImageMap   = (isset($postData['kImageMap']) ? (int)$postData['kImageMap'] : null);
    $cName       = htmlspecialchars($postData['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    if (strlen($cName) === 0) {
        $cPlausi_arr['cName'] = 1;
    }
    $cBannerPath = (isset($postData['cPath']) && $postData['cPath'] !== '' ? $postData['cPath'] : null);
    if (isset($_FILES['oFile'])
        && $_FILES['oFile']['error'] === UPLOAD_ERR_OK
        && move_uploaded_file($_FILES['oFile']['tmp_name'], PFAD_ROOT . PFAD_BILDER_BANNER . $_FILES['oFile']['name'])
    ) {
        $cBannerPath = $_FILES['oFile']['name'];
    }
    if ($cBannerPath === null) {
        $cPlausi_arr['oFile'] = 1;
    }
    $vDatum = null;
    $bDatum = null;
    if (isset($postData['vDatum']) && $postData['vDatum'] !== '') {
        try {
            $vDatum = new DateTime($postData['vDatum']);
            $vDatum = $vDatum->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $cPlausi_arr['vDatum'] = 1;
        }
    }
    if (isset($postData['bDatum']) && $postData['bDatum'] !== '') {
        try {
            $bDatum = new DateTime($postData['bDatum']);
            $bDatum = $bDatum->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $cPlausi_arr['bDatum'] = 1;
        }
    }
    if ($bDatum !== null && $bDatum < $vDatum) {
        $cPlausi_arr['bDatum'] = 2;
    }
    if (strlen($cBannerPath) === 0) {
        $cPlausi_arr['cBannerPath'] = 1;
    }
    if (count($cPlausi_arr) === 0) {
        if ($kImageMap === null || $kImageMap === 0) {
            $kImageMap = $oBanner->save($cName, $cBannerPath, $vDatum, $bDatum);
        } else {
            $oBanner->update($kImageMap, $cName, $cBannerPath, $vDatum, $bDatum);
        }
        // extensionpoint
        $kSprache      = (int)$postData['kSprache'];
        $kKundengruppe = (int)$postData['kKundengruppe'];
        $nSeite        = (int)$postData['nSeitenTyp'];
        $cKey          = $postData['cKey'];
        $cKeyValue     = '';
        $cValue        = '';

        if ($nSeite === PAGE_ARTIKEL) {
            $cKey      = 'kArtikel';
            $cKeyValue = 'article_key';
            $cValue    = isset($postData[$cKeyValue]) ? $postData[$cKeyValue] : null;
        } elseif ($nSeite === PAGE_ARTIKELLISTE) {
            // data mapping
            $aFilter_arr = [
                'kTag'         => 'tag_key',
                'kMerkmalWert' => 'attribute_key',
                'kKategorie'   => 'categories_key',
                'kHersteller'  => 'manufacturer_key',
                'cSuche'       => 'keycSuche'
            ];
            $cKeyValue = $aFilter_arr[$cKey];
            $cValue    = isset($postData[$cKeyValue]) ? $postData[$cKeyValue] : null;
        } elseif ($nSeite === PAGE_EIGENE) {
            $cKey      = 'kLink';
            $cKeyValue = 'link_key';
            $cValue    = isset($postData[$cKeyValue]) ? $postData[$cKeyValue] : null;
        }

        Shop::DB()->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $kImageMap]);
        // save extensionpoint
        $oExtension                = new stdClass();
        $oExtension->kSprache      = $kSprache;
        $oExtension->kKundengruppe = $kKundengruppe;
        $oExtension->nSeite        = $nSeite;
        $oExtension->cKey          = $cKey;
        $oExtension->cValue        = $cValue;
        $oExtension->cClass        = 'ImageMap';
        $oExtension->kInitial      = $kImageMap;

        $ins = Shop::DB()->insert('textensionpoint', $oExtension);
        // saved?
        if ($kImageMap && (int)$ins > 0) {
            $cAction  = 'view';
            $cHinweis = 'Banner wurde erfolgreich gespeichert.';
        } else {
            $cFehler = 'Banner konnte nicht angelegt werden.';
        }
    } else {
        $cFehler = 'Bitte f&uuml;llen Sie alle Pflichtfelder die mit einem * marktiert sind aus';
        $smarty->assign('cPlausi_arr', $cPlausi_arr)
               ->assign('cName', isset($postData['cName']) ? $postData['cName'] : null)
               ->assign('vDatum', isset($postData['vDatum']) ? $postData['vDatum'] : null)
               ->assign('bDatum', isset($postData['bDatum']) ? $postData['bDatum'] : null)
               ->assign('kSprache', isset($postData['kSprache']) ? $postData['kSprache'] : null)
               ->assign('kKundengruppe', isset($postData['kKundengruppe']) ? $postData['kKundengruppe'] : null)
               ->assign('nSeitenTyp', isset($postData['nSeitenTyp']) ? $postData['nSeitenTyp'] : null)
               ->assign('cKey', isset($postData['cKey']) ? $postData['cKey'] : null)
               ->assign('categories_key', isset($postData['categories_key']) ? $postData['categories_key'] : null)
               ->assign('attribute_key', isset($postData['attribute_key']) ? $postData['attribute_key'] : null)
               ->assign('tag_key', isset($postData['tag_key']) ? $postData['tag_key'] : null)
               ->assign('manufacturer_key', isset($postData['manufacturer_key']) ? $postData['manufacturer_key'] : null)
               ->assign('keycSuche', isset($postData['keycSuche']) ? $postData['keycSuche'] : null);
    }
}
switch ($cAction) {
    case 'area':
        $id      = (int)$postData['id'];
        $oBanner = holeBanner($id, false); //do not fill with complete article object to avoid utf8 errors on json_encode
        if (!is_object($oBanner)) {
            $cFehler = 'Banner wurde nicht gefunden';
            $cAction = 'view';
            break;
        }
        $oBanner->cTitel = utf8_encode($oBanner->cTitel);
        foreach ($oBanner->oArea_arr as &$oArea) {
            $oArea->cTitel        = utf8_encode($oArea->cTitel);
            $oArea->cUrl          = utf8_encode($oArea->cUrl);
            $oArea->cBeschreibung = utf8_encode($oArea->cBeschreibung);
            $oArea->cStyle        = utf8_encode($oArea->cStyle);
        }
        $smarty->assign('oBanner', $oBanner)
               ->assign('cBannerLocation', Shop::getURL() . '/' . PFAD_BILDER_BANNER);
        break;

    case 'edit':
        $id = isset($postData['id'])
            ? (int)$postData['id']
            : (int)$postData['kImageMap'];
        $oBanner       = holeBanner($id);
        $oExtension    = holeExtension($id);
        $oSprache      = Sprache::getInstance(false);
        $oSprachen_arr = $oSprache->gibInstallierteSprachen();
        $nMaxFileSize  = getMaxFileSize(ini_get('upload_max_filesize'));

        $smarty->assign('oExtension', $oExtension)
               ->assign('cBannerFile_arr', holeBannerDateien())
               ->assign('oSprachen_arr', $oSprachen_arr)
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('nMaxFileSize', $nMaxFileSize)
               ->assign('oBanner', $oBanner);

        if (!is_object($oBanner)) {
            $cFehler = 'Banner wurde nicht gefunden.';
            $cAction = 'view';
        }
        break;

    case 'new':
        $oSprache      = Sprache::getInstance(false);
        $oSprachen_arr = $oSprache->gibInstallierteSprachen();
        $nMaxFileSize  = getMaxFileSize(ini_get('upload_max_filesize'));
        $smarty->assign('oBanner', (isset($oBanner) ? $oBanner : null))
               ->assign('oSprachen_arr', $oSprachen_arr)
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('cBannerLocation', PFAD_BILDER_BANNER)
               ->assign('nMaxFileSize', $nMaxFileSize)
               ->assign('cBannerFile_arr', holeBannerDateien());
        break;

    case 'delete':
        $id  = (int)$postData['id'];
        $bOk = entferneBanner($id);
        if ($bOk) {
            $cHinweis = 'Erfolgreich entfernt.';
        } else {
            $cFehler = 'Banner konnte nicht entfernt werden.';
        }
        break;

    default:
        break;
}

$smarty->assign('cFehler', $cFehler)
       ->assign('cHinweis', $cHinweis)
       ->assign('cAction', $cAction)
       ->assign('oBanner_arr', holeAlleBanner())
       ->display('banner.tpl');
