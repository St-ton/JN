<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.PlausiCMS.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Link.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'links_inc.php';

$hinweis            = '';
$fehler             = '';
$step               = 'uebersicht';
$link               = null;
$cUploadVerzeichnis = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
$clearCache         = false;
$continue           = true;

if (isset($_POST['addlink']) && intval($_POST['addlink']) > 0) {
    $step = 'neuer Link';
    if (!isset($link)) {
        $link = new stdClass();
    }
    $link->kLinkgruppe = (int)$_POST['addlink'];
}

if (isset($_POST['dellink']) && intval($_POST['dellink']) > 0 && validateToken()) {
    $kLink = (int)$_POST['dellink'];
    removeLink($kLink);
    $hinweis .= 'Link erfolgreich gel&ouml;scht!';
    $clearCache = true;
}

if (isset($_POST['loesch_linkgruppe']) && intval($_POST['loesch_linkgruppe']) === 1 && validateToken()) {
    if (isset($_POST['loeschConfirmJaSubmit'])) {
        $step = 'loesch_linkgruppe';
    } else {
        $step  = 'uebersicht';
        $_POST = array();
    }
}

if ((isset($_POST['dellinkgruppe']) && intval($_POST['dellinkgruppe']) > 0  && validateToken()) || $step === 'loesch_linkgruppe') {
    $step = 'uebersicht';

    $kLinkgruppe = -1;
    if (isset($_POST['dellinkgruppe'])) {
        $kLinkgruppe = (int)$_POST['dellinkgruppe'];
    }
    if (intval($_POST['kLinkgruppe']) > 0) {
        $kLinkgruppe = (int)$_POST['kLinkgruppe'];
    }

    Shop::DB()->delete('tlinkgruppe', 'kLinkgruppe', $kLinkgruppe);
    Shop::DB()->delete('tlinkgruppesprache', 'kLinkgruppe', $kLinkgruppe);
    $links = Shop::DB()->query("SELECT kLink FROM tlink WHERE kLinkgruppe = " . $kLinkgruppe, 2);
    foreach ($links as $link) {
        $oLink = new Link($link->kLink, null, true);
        $oLink->delete(false);
    }
    Shop::DB()->delete('tlink', 'kLinkgruppe', $kLinkgruppe);
    $hinweis .= 'Linkgruppe erfolgreich gel&ouml;scht!';
    $clearCache = true;
    $step       = 'uebersicht';
    $_POST      = array();
}

if (isset($_POST['delconfirmlinkgruppe']) && intval($_POST['delconfirmlinkgruppe']) > 0 && validateToken()) {
    $step = 'linkgruppe_loeschen_confirm';
    $smarty->assign('oLinkgruppe', holeLinkgruppe((int)$_POST['delconfirmlinkgruppe']));
}

if (isset($_POST['neu_link']) && intval($_POST['neu_link']) === 1 && validateToken()) {
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST);
    $oPlausiCMS->doPlausi('lnk');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        if (!isset($link)) {
            $link = new stdClass();
        }
        $link->kLink              = (int)$_POST['kLink'];
        $link->kLinkgruppe        = (int)$_POST['kLinkgruppe'];
        $link->kPlugin            = (int)$_POST['kPlugin'];
        $link->cName              = $_POST['cName'];
        $link->nLinkart           = (int)$_POST['nLinkart'];
        $link->cURL               = (isset($_POST['cURL'])) ? $_POST['cURL'] : null;
        $link->nSort              = !empty($_POST['nSort']) ? $_POST['nSort'] : 0;
        $link->bSSL               = (int)$_POST['bSSL'];
        $link->cSichtbarNachLogin = 'N';
        $link->cNoFollow          = 'N';
        $link->cIdentifier        = $_POST['cIdentifier'];
        $link->bIsFluid           = (isset($_POST['bIsFluid']) && $_POST['bIsFluid'] === '1') ? 1 : 0;
        if (isset($_POST['cKundengruppen']) && is_array($_POST['cKundengruppen']) && count($_POST['cKundengruppen']) > 0) {
            $link->cKundengruppen = implode(';', $_POST['cKundengruppen']) . ';';
        }
        if (is_array($_POST['cKundengruppen']) && in_array('-1', $_POST['cKundengruppen'])) {
            $link->cKundengruppen = 'NULL';
        }
        if (isset($_POST['cSichtbarNachLogin']) && $_POST['cSichtbarNachLogin'] === 'Y') {
            $link->cSichtbarNachLogin = 'Y';
        }
        if (isset($_POST['cNoFollow']) && $_POST['cNoFollow'] === 'Y') {
            $link->cNoFollow = 'Y';
        }
        if ($link->nLinkart > 2 && isset($_POST['nSpezialseite']) && intval($_POST['nSpezialseite']) > 0) {
            $link->nLinkart = (int)$_POST['nSpezialseite'];
            $link->cURL     = '';
        }
        $clearCache = true;
        $kLink      = 0;
        if (intval($_POST['kLink']) === 0) {
            //einfuegen
            $kLink = Shop::DB()->insert('tlink', $link);
            $hinweis .= 'Link wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kLink = intval($_POST['kLink']);
            //clear page cache
            if (Shop::Cache()->isPageCacheEnabled()) {
                $_smarty = new JTLSmarty(true, false, true, 'cache');
                $_smarty->setCachingParams(true)->clearCache(null, 'jtlc|page|link|lid' . $_POST['kLink']);
            }
            Shop::DB()->update('tlink', 'kLink', $kLink, $link);
            $hinweis .= "Der Link <strong>$link->cName</strong> wurde erfolgreich ge&auml;ndert.";
            $step     = 'uebersicht';
            $continue = (isset($_POST['continue']) && $_POST['continue'] === '1');
        }
        // Bilder hochladen
        if (!is_dir($cUploadVerzeichnis . $kLink)) {
            mkdir($cUploadVerzeichnis . $kLink);
        }

        if (is_array($_FILES['Bilder']['name']) && count($_FILES['Bilder']['name']) > 0) {
            $nLetztesBild = gibLetzteBildNummer($kLink);
            $nZaehler     = 0;
            if ($nLetztesBild > 0) {
                $nZaehler = $nLetztesBild;
            }
            $imageCount = (count($_FILES['Bilder']['name']) + $nZaehler);
            for ($i = $nZaehler; $i < $imageCount; $i++) {
                if ($_FILES['Bilder']['size'][$i - $nZaehler] <= 2097152) {
                    $cUploadDatei = $cUploadVerzeichnis . $kLink . '/Bild' . ($i + 1) . '.' .
                        substr(
                            $_FILES['Bilder']['type'][$i - $nZaehler],
                            strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/') + 1,
                            strlen($_FILES['Bilder']['type'][$i - $nZaehler] - (strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/'))) + 1
                        );
                    move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $cUploadDatei);
                }
            }
        }

        $sprachen = gibAlleSprachen();
        if (!isset($linkSprache)) {
            $linkSprache = new stdClass();
        }
        $linkSprache->kLink = $kLink;
        foreach ($sprachen as $sprache) {
            $linkSprache->cISOSprache = $sprache->cISO;
            $linkSprache->cName       = $link->cName;
            $linkSprache->cTitle      = '';
            $linkSprache->cContent    = '';
            if (!empty($_POST['cName_' . $sprache->cISO])) {
                $linkSprache->cName = $_POST['cName_' . $sprache->cISO];
            }
            if (!empty($_POST['cTitle_' . $sprache->cISO])) {
                $linkSprache->cTitle = $_POST['cTitle_' . $sprache->cISO];
            }
            if (!empty($_POST['cContent_' . $sprache->cISO])) {
                $linkSprache->cContent = parseText($_POST['cContent_' . $sprache->cISO], $kLink);
            }
            $linkSprache->cSeo = $linkSprache->cName;
            if (!empty($_POST['cSeo_' . $sprache->cISO])) {
                $linkSprache->cSeo = $_POST['cSeo_' . $sprache->cISO];
            }
            $linkSprache->cMetaTitle = $linkSprache->cTitle;
            if (isset($_POST['cMetaTitle_' . $sprache->cISO])) {
                $linkSprache->cMetaTitle = $_POST['cMetaTitle_' . $sprache->cISO];
            }
            $linkSprache->cMetaKeywords    = $_POST['cMetaKeywords_' . $sprache->cISO];
            $linkSprache->cMetaDescription = $_POST['cMetaDescription_' . $sprache->cISO];
            Shop::DB()->delete('tlinksprache', array('kLink', 'cISOSprache'), array($kLink, $sprache->cISO));
            $linkSprache->cSeo = getSeo($linkSprache->cSeo);
            Shop::DB()->insert('tlinksprache', $linkSprache);

            $oSpracheTMP = Shop::DB()->query("SELECT kSprache FROM tsprache WHERE cISO = '" . $linkSprache->cISOSprache . "'", 1);
            if ($oSpracheTMP->kSprache > 0) {
                Shop::DB()->delete('tseo', array('cKey', 'kKey', 'kSprache'),  array('kLink', (int)$linkSprache->kLink, (int)$oSpracheTMP->kSprache));
                $oSeo           = new stdClass();
                $oSeo->cSeo     = checkSeo($linkSprache->cSeo);
                $oSeo->kKey     = $linkSprache->kLink;
                $oSeo->cKey     = 'kLink';
                $oSeo->kSprache = $oSpracheTMP->kSprache;
                Shop::DB()->insert('tseo', $oSeo);
            }
        }
    } else {
        $step = 'neuer Link';
        if (!isset($link)) {
            $link = new stdClass();
        }
        $link->kLinkgruppe = intval($_POST['kLinkgruppe']);
        $fehler            = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
} elseif (((isset($_POST['neuelinkgruppe']) && intval($_POST['neuelinkgruppe']) === 1) || (isset($_POST['kLinkgruppe']) && intval($_POST['kLinkgruppe']) > 0)) && validateToken()) {
    $step = 'neue Linkgruppe';

    if (isset($_POST['kLinkgruppe']) && intval($_POST['kLinkgruppe']) > 0) {
        $linkgruppe = Shop::DB()->query("SELECT * FROM tlinkgruppe WHERE kLinkgruppe = " . intval($_POST['kLinkgruppe']), 1);
        $smarty->assign('Linkgruppe', $linkgruppe)
               ->assign('Linkgruppenname', getLinkgruppeNames($linkgruppe->kLinkgruppe));
    }
}

if ($continue && ((isset($_POST['kLink']) && intval($_POST['kLink']) > 0) || (isset($_GET['kLink']) && intval($_GET['kLink']) && isset($_GET['delpic']))) && validateToken()) {
    $step = 'neuer Link';
    $link = Shop::DB()->query("SELECT * FROM tlink WHERE kLink=" . verifyGPCDataInteger('kLink'), 1);
    $smarty->assign('Link', $link)
           ->assign('Linkname', getLinkVar($link->kLink, 'cName'))
           ->assign('Linkseo', getLinkVar($link->kLink, 'cSeo'))
           ->assign('Linktitle', getLinkVar($link->kLink, 'cTitle'))
           ->assign('Linkcontent', getLinkVar($link->kLink, 'cContent'))
           ->assign('Linkmetatitle', getLinkVar($link->kLink, 'cMetaTitle'))
           ->assign('Linkmetakeys', getLinkVar($link->kLink, 'cMetaKeywords'))
           ->assign('Linkmetadesc', getLinkVar($link->kLink, 'cMetaDescription'));
    // Bild loeschen?
    if (verifyGPCDataInteger('delpic') === 1) {
        @unlink($cUploadVerzeichnis . $link->kLink . '/' . verifyGPDataString('cName'));
    }
    // Hohle Bilder
    $cDatei_arr = array();
    if (is_dir($cUploadVerzeichnis . $link->kLink)) {
        $DirHandle = opendir($cUploadVerzeichnis . $link->kLink);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $nImageGroesse_arr = calcRatio(PFAD_ROOT . '/' . PFAD_BILDER . PFAD_LINKBILDER . $link->kLink . '/' . $Datei, 160, 120);
                $oDatei            = new stdClass();
                $oDatei->cName     = substr($Datei, 0, strpos($Datei, '.'));
                $oDatei->cNameFull = $Datei;
                $oDatei->cURL      = '<img class="link_image" src="' . Shop::getURL() . '/' . PFAD_BILDER . PFAD_LINKBILDER . $link->kLink . '/' . $Datei . '" />';
                $oDatei->nBild     = intval(substr(str_replace('Bild', '', $Datei), 0, strpos(str_replace('Bild', '', $Datei), '.')));
                $cDatei_arr[]      = $oDatei;
            }
        }
        usort($cDatei_arr, 'cmp_obj');
        $smarty->assign('cDatei_arr', $cDatei_arr);
    }
}

if (isset($_POST['neu_linkgruppe']) && intval($_POST['neu_linkgruppe']) === 1 && validateToken()) {
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST);
    $oPlausiCMS->doPlausi('grp');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        if (!isset($linkgruppe)) {
            $linkgruppe = new stdClass();
        }
        $linkgruppe->kLinkgruppe   = (int)$_POST['kLinkgruppe'];
        $linkgruppe->cName         = $_POST['cName'];
        $linkgruppe->cTemplatename = $_POST['cTemplatename'];

        $kLinkgruppe = 0;
        if (intval($_POST['kLinkgruppe']) === 0) {
            //einf?gen
            $kLinkgruppe = Shop::DB()->insert('tlinkgruppe', $linkgruppe);
            $hinweis .= 'Linkgruppe wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kLinkgruppe = intval($_POST['kLinkgruppe']);
            Shop::DB()->update('tlinkgruppe', 'kLinkgruppe', $kLinkgruppe, $linkgruppe);
            $hinweis .= "Die Linkgruppe <strong>$linkgruppe->cName</strong> wurde erfolgreich ge&auml;ndert.";
            $step = 'uebersicht';
        }
        $clearCache = true;
        $sprachen   = gibAlleSprachen();
        if (!isset($linkgruppeSprache)) {
            $linkgruppeSprache = new stdClass();
        }
        $linkgruppeSprache->kLinkgruppe = $kLinkgruppe;
        foreach ($sprachen as $sprache) {
            $linkgruppeSprache->cISOSprache = $sprache->cISO;
            $linkgruppeSprache->cName       = $linkgruppe->cName;
            if ($_POST['cName_' . $sprache->cISO]) {
                $linkgruppeSprache->cName = $_POST['cName_' . $sprache->cISO];
            }

            Shop::DB()->delete('tlinkgruppesprache', array('kLinkgruppe', 'cISOSprache'), array($kLinkgruppe, $sprache->cISO));
            Shop::DB()->insert('tlinkgruppesprache', $linkgruppeSprache);
        }
    } else {
        $step   = 'neue Linkgruppe';
        $fehler = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
}
// Verschiebt einen Link in eine andere Linkgruppe
if (isset($_POST['aender_linkgruppe']) && intval($_POST['aender_linkgruppe']) === 1 && validateToken()) {
    if (intval($_POST['kLink']) > 0 && intval($_POST['kLinkgruppe']) > 0) {
        $oLink = new Link((int)$_POST['kLink'], null, true);

        if ($oLink->getLink() > 0) {
            $oLinkgruppe = Shop::DB()->query(
                "SELECT kLinkgruppe, cName
                    FROM tlinkgruppe
                    WHERE kLinkgruppe = " . (int)$_POST['kLinkgruppe'], 1
            );

            if ($oLinkgruppe->kLinkgruppe > 0) {
                $oLink->setLinkgruppe($_POST['kLinkgruppe'])
                      ->setVaterLink(0)
                      ->update();
                // Kinder auch umziehen
                if (isset($oLink->oSub_arr) && count($oLink->oSub_arr) > 0) {
                    aenderLinkgruppeRek($oLink->oSub_arr, $_POST['kLinkgruppe']);
                }
                $hinweis .= 'Sie haben den Link "' . $oLink->cName . '" erfolgreich in die Linkgruppe "' . $oLinkgruppe->cName . '" verschoben.';
                $step       = 'uebersicht';
                $clearCache = true;
            } else {
                $fehler .= 'Fehler: Es konnte keine Linkgruppe mit Ihrem Key gefunden werden.';
            }
        } else {
            $fehler .= 'Fehler: Es konnte kein Link mit Ihrem Key gefunden werden.';
        }
    }
}
// Ordnet einen Link neu an
if (isset($_POST['aender_linkvater']) && intval($_POST['aender_linkvater']) === 1 && validateToken()) {
    $success = false;
    if (intval($_POST['kLink']) > 0 && intval($_POST['kVaterLink']) >= 0) {
        $kLink      = (int)$_POST['kLink'];
        $kVaterLink = (int)$_POST['kVaterLink'];
        $oLink      = Shop::DB()->query("SELECT kLink, cName FROM tlink WHERE kLink = " . $kLink, 1);
        $oVaterLink = Shop::DB()->query("SELECT kLink, cName FROM tlink WHERE kLink = " . $kVaterLink, 1);

        if (isset($oLink->kLink) && $oLink->kLink > 0 && ((isset($oVaterLink->kLink) && $oVaterLink->kLink > 0) || $kVaterLink == 0)) {
            $success = true;
            Shop::DB()->query("UPDATE tlink SET kVaterLink = " . $kVaterLink . " WHERE kLink = " . $kLink, 4);
            $hinweis .= "Sie haben den Link '" . $oLink->cName . "' erfolgreich verschoben.";
            $step = 'uebersicht';
        }
        $clearCache = true;
    }

    if (!$success) {
        $fehler .= 'Fehler: Link konnte nicht verschoben werden.';
    }
}

if ($step === 'uebersicht') {
    $linkgruppen = Shop::DB()->query("SELECT * FROM tlinkgruppe", 2);
    $lCount      = count($linkgruppen);
    for ($i = 0; $i < $lCount; $i++) {
        $linkgruppen[$i]->links_nh = Shop::DB()->query("SELECT * FROM tlink WHERE kLinkgruppe = " . (int)$linkgruppen[$i]->kLinkgruppe . " ORDER BY nSort, cName", 2);
        $linkgruppen[$i]->links    = build_navigation_subs_admin($linkgruppen[$i]->links_nh);
    }

    $smarty->assign('kPlugin', verifyGPCDataInteger('kPlugin'))
           ->assign('linkgruppen', $linkgruppen);
}

if ($step === 'neue Linkgruppe') {
    $smarty->assign('sprachen', gibAlleSprachen());
}

if ($step === 'neuer Link') {
    $kundengruppen = Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2);
    $smarty->assign('Link', $link)
           ->assign('oSpezialseite_arr', holeSpezialseiten())
           ->assign('sprachen', gibAlleSprachen())
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($link));
}

//clear page cache
if ($clearCache === true) {
    Shop::Cache()->flushTags(array(CACHING_GROUP_CORE));
    Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
    if (Shop::Cache()->isPageCacheEnabled()) {
        $_smarty = new JTLSmarty(true, false, true, 'cache');
        $_smarty->setCachingParams(true)->clearCache(null, 'jtlc|page');
    }
}
$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('links.tpl');
