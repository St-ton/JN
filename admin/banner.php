<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('DISPLAY_BANNER_VIEW', true, true);
/** @global \Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'banner_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
$cFehler  = '';
$cHinweis = '';
$cAction  = (isset($_REQUEST['action']) && FormHelper::validateToken()) ? $_REQUEST['action'] : 'view';

if (!empty($_POST) && (isset($_POST['cName']) || isset($_POST['kImageMap'])) && FormHelper::validateToken()) {
    $cPlausi_arr = [];
    $oBanner     = new ImageMap();
    $kImageMap   = (isset($_POST['kImageMap']) ? (int)$_POST['kImageMap'] : null);
    $cName       = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    if (strlen($cName) === 0) {
        $cPlausi_arr['cName'] = 1;
    }
    $cBannerPath = (isset($_POST['cPath']) && $_POST['cPath'] !== '' ? $_POST['cPath'] : null);
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
    if (isset($_POST['vDatum']) && $_POST['vDatum'] !== '') {
        try {
            $vDatum = new DateTime($_POST['vDatum']);
            $vDatum = $vDatum->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $cPlausi_arr['vDatum'] = 1;
        }
    }
    if (isset($_POST['bDatum']) && $_POST['bDatum'] !== '') {
        try {
            $bDatum = new DateTime($_POST['bDatum']);
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
        $kSprache      = (int)$_POST['kSprache'];
        $kKundengruppe = (int)$_POST['kKundengruppe'];
        $nSeite        = (int)$_POST['nSeitenTyp'];
        $cKey          = $_POST['cKey'];
        $cKeyValue     = '';
        $cValue        = '';

        if ($nSeite === PAGE_ARTIKEL) {
            $cKey      = 'kArtikel';
            $cKeyValue = 'article_key';
            $cValue    = $_POST[$cKeyValue] ?? null;
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
            $cValue    = $_POST[$cKeyValue] ?? null;
        } elseif ($nSeite === PAGE_EIGENE) {
            $cKey      = 'kLink';
            $cKeyValue = 'link_key';
            $cValue    = $_POST[$cKeyValue] ?? null;
        }

        Shop::Container()->getDB()->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $kImageMap]);
        // save extensionpoint
        $oExtension                = new stdClass();
        $oExtension->kSprache      = $kSprache;
        $oExtension->kKundengruppe = $kKundengruppe;
        $oExtension->nSeite        = $nSeite;
        $oExtension->cKey          = $cKey;
        $oExtension->cValue        = $cValue;
        $oExtension->cClass        = 'ImageMap';
        $oExtension->kInitial      = $kImageMap;

        $ins = Shop::Container()->getDB()->insert('textensionpoint', $oExtension);
        // saved?
        if ($kImageMap && $ins > 0) {
            $cAction  = 'view';
            $cHinweis = 'Banner wurde erfolgreich gespeichert.';
        } else {
            $cFehler = 'Banner konnte nicht angelegt werden.';
        }
    } else {
        $cFehler = 'Bitte füllen Sie alle Pflichtfelder die mit einem * marktiert sind aus';
        $smarty->assign('cPlausi_arr', $cPlausi_arr)
               ->assign('cName', $_POST['cName'] ?? null)
               ->assign('vDatum', $_POST['vDatum'] ?? null)
               ->assign('bDatum', $_POST['bDatum'] ?? null)
               ->assign('kSprache', $_POST['kSprache'] ?? null)
               ->assign('kKundengruppe', $_POST['kKundengruppe'] ?? null)
               ->assign('nSeitenTyp', $_POST['nSeitenTyp'] ?? null)
               ->assign('cKey', $_POST['cKey'] ?? null)
               ->assign('categories_key', $_POST['categories_key'] ?? null)
               ->assign('attribute_key', $_POST['attribute_key'] ?? null)
               ->assign('tag_key', $_POST['tag_key'] ?? null)
               ->assign('manufacturer_key', $_POST['manufacturer_key'] ?? null)
               ->assign('keycSuche', $_POST['keycSuche'] ?? null);
    }
}
switch ($cAction) {
    case 'area':
        $id      = (int)$_POST['id'];
        $oBanner = holeBanner($id, false);
        if (!is_object($oBanner)) {
            $cFehler = 'Banner wurde nicht gefunden';
            $cAction = 'view';
            break;
        }

        $smarty->assign('oBanner', $oBanner)
               ->assign('cBannerLocation', Shop::getURL() . '/' . PFAD_BILDER_BANNER);
        break;

    case 'edit':
        $id = isset($_POST['id'])
            ? (int)$_POST['id']
            : (int)$_POST['kImageMap'];
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
        $smarty->assign('oBanner', $oBanner ?? null)
               ->assign('oSprachen_arr', $oSprachen_arr)
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('cBannerLocation', PFAD_BILDER_BANNER)
               ->assign('nMaxFileSize', $nMaxFileSize)
               ->assign('cBannerFile_arr', holeBannerDateien());
        break;

    case 'delete':
        $id  = (int)$_POST['id'];
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
