<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'links_inc.php';
/** @global JTLSmarty $smarty */
$hinweis            = '';
$fehler             = '';
$step               = 'uebersicht';
$link               = null;
$cUploadVerzeichnis = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
$clearCache         = false;
$continue           = true;
$db                 = Shop::Container()->getDB();
$cache              = Shop::Container()->getCache();
$linkAdmin          = new \Link\Admin\LinkAdmin($db, $cache);

if (isset($_POST['addlink']) && (int)$_POST['addlink'] > 0) {
    $step = 'neuer Link';
    $link = new \Link\Link($db);
    $link->setLinkGroupID((int)$_POST['addlink']);
    $link->setLinkGroups([(int)$_POST['addlink']]);
}

if (isset($_POST['removefromlinkgroup'], $_POST['kLinkgruppe'])
    && (int)$_POST['removefromlinkgroup'] > 0
    && validateToken()
) {
    $res = $linkAdmin->removeLinkFromLinkGroup((int)$_POST['removefromlinkgroup'], (int)$_POST['kLinkgruppe']);
    if ($res > 0) {
        $hinweis .= 'Link erfolgreich aus Linkgruppe entfernt.';
    } else {
        $fehler .= 'Link konnte nicht aus Linkgruppe entfernt werden.';
    }
    unset($_POST['kLinkgruppe']);
    $step       = 'uebersicht';
    $clearCache = true;
}

if (isset($_POST['dellink']) && (int)$_POST['dellink'] > 0 && validateToken()) {
    $res = $linkAdmin->deleteLink((int)$_POST['dellink'], (int)$_POST['kLinkgruppe']);
    if ($res > 0) {
        $hinweis .= 'Link erfolgreich gel&ouml;scht!';
    } else {
        $fehler .= 'Link konnte nicht gel&ouml;scht werden.';
    }
    $clearCache = true;
    $step       = 'uebersicht';
    $_POST      = [];
}

if (isset($_POST['loesch_linkgruppe']) && (int)$_POST['loesch_linkgruppe'] === 1 && validateToken()) {
    if (isset($_POST['loeschConfirmJaSubmit'])) {
        $step = 'loesch_linkgruppe';
    } else {
        $step  = 'uebersicht';
        $_POST = [];
    }
}

if (((isset($_POST['dellinkgruppe']) && (int)$_POST['dellinkgruppe'] > 0)
        || $step === 'loesch_linkgruppe')
    && validateToken()
) {
    $step        = 'uebersicht';
    $linkGroupID = 0;
    if (isset($_POST['dellinkgruppe'])) {
        $linkGroupID = (int)$_POST['dellinkgruppe'];
    }
    if ((int)$_POST['kLinkgruppe'] > 0) {
        $linkGroupID = (int)$_POST['kLinkgruppe'];
    }
    if ($linkAdmin->deleteLinkGroup($linkGroupID) > 0) {
        $hinweis    .= 'Linkgruppe erfolgreich gel&ouml;scht!';
        $clearCache = true;
        $step       = 'uebersicht';
        $_POST      = [];
    } else {
        $fehler .= 'Linkgruppe konnte nicht gel&ouml;scht werden.';
    }
}

if (isset($_POST['delconfirmlinkgruppe']) && (int)$_POST['delconfirmlinkgruppe'] > 0 && validateToken()) {
    $step = 'linkgruppe_loeschen_confirm';

    $smarty->assign('oLinkgruppe', holeLinkgruppe((int)$_POST['delconfirmlinkgruppe']))
           ->assign('affectedLinkNames', $linkAdmin->getPreDeletionLinks((int)$_POST['delconfirmlinkgruppe'], true));
}

if (isset($_POST['neu_link']) && (int)$_POST['neu_link'] === 1 && validateToken()) {
    $sprachen    = gibAlleSprachen();
    $hasHTML_arr = [];

    foreach ($sprachen as $sprache) {
        $hasHTML_arr[] = 'cContent_' . $sprache->cISO;
    }
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST, $hasHTML_arr, true);
    $oPlausiCMS->doPlausi('lnk');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        $link                     = new stdClass();
        $link->kLink              = (int)$_POST['kLink'];
        $link->kPlugin            = (int)$_POST['kPlugin'];
        $link->cName              = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $link->nLinkart           = (int)$_POST['nLinkart'];
        $link->cURL               = $_POST['cURL'] ?? null;
        $link->nSort              = !empty($_POST['nSort']) ? $_POST['nSort'] : 0;
        $link->bSSL               = (int)$_POST['bSSL'];
        $link->bIsActive          = 1;
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
        if (isset($_POST['bIsActive']) && (int)$_POST['bIsActive'] !== 1) {
            $link->bIsActive = 0;
        }
        if (isset($_POST['cSichtbarNachLogin']) && $_POST['cSichtbarNachLogin'] === 'Y') {
            $link->cSichtbarNachLogin = 'Y';
        }
        if (isset($_POST['cNoFollow']) && $_POST['cNoFollow'] === 'Y') {
            $link->cNoFollow = 'Y';
        }
        if ($link->nLinkart > 2 && isset($_POST['nSpezialseite']) && (int)$_POST['nSpezialseite'] > 0) {
            $link->nLinkart = (int)$_POST['nSpezialseite'];
            $link->cURL     = '';
        }
        $clearCache = true;
        $kLink      = 0;
        if ((int)$_POST['kLink'] === 0) {
            //einfuegen
            $kLink              = $db->insert('tlink', $link);
            $assoc              = new stdClass();
            $assoc->linkID      = $kLink;
            $assoc->linkGroupID = (int)$_POST['kLinkgruppe'];
            $db->insert('tlinkgroupassociations', $assoc);
            $hinweis .= 'Link wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kLink    = (int)$_POST['kLink'];
            $revision = new Revision();
            $revision->addRevision('link', (int)$_POST['kLink'], true);
            $db->update('tlink', 'kLink', $kLink, $link);
            $hinweis .= "Der Link <strong>$link->cName</strong> wurde erfolgreich ge&auml;ndert.";
        }
        $step     = 'uebersicht';
        $continue = (isset($_POST['continue']) && $_POST['continue'] === '1');
        if ($continue) {
            $step           = 'neuer link';
            $_POST['kLink'] = $kLink;
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
            for ($i = $nZaehler; $i < $imageCount; ++$i) {
                if (!empty($_FILES['Bilder']['size'][$i - $nZaehler])
                    && $_FILES['Bilder']['error'][$i - $nZaehler] === UPLOAD_ERR_OK
                ) {
                    $type         = $_FILES['Bilder']['type'][$i - $nZaehler];
                    $cUploadDatei = $cUploadVerzeichnis . $kLink . '/Bild' . ($i + 1) . '.' .
                        substr(
                            $type,
                            strpos($type, '/') + 1,
                            strlen($type) - strpos($type, '/') + 1
                        );
                    move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $cUploadDatei);
                }
            }
        }
        $linkSprache        = new stdClass();
        $linkSprache->kLink = $kLink;
        foreach ($sprachen as $sprache) {
            $linkSprache->cISOSprache = $sprache->cISO;
            $linkSprache->cName       = $link->cName;
            $linkSprache->cTitle      = '';
            $linkSprache->cContent    = '';
            if (!empty($_POST['cName_' . $sprache->cISO])) {
                $linkSprache->cName = htmlspecialchars($_POST['cName_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401,
                    JTL_CHARSET);
            }
            if (!empty($_POST['cTitle_' . $sprache->cISO])) {
                $linkSprache->cTitle = htmlspecialchars($_POST['cTitle_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401,
                    JTL_CHARSET);
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
                $linkSprache->cMetaTitle = htmlspecialchars($_POST['cMetaTitle_' . $sprache->cISO],
                    ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            }
            $linkSprache->cMetaKeywords    = htmlspecialchars($_POST['cMetaKeywords_' . $sprache->cISO],
                ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $linkSprache->cMetaDescription = htmlspecialchars($_POST['cMetaDescription_' . $sprache->cISO],
                ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $db->delete('tlinksprache', ['kLink', 'cISOSprache'], [$kLink, $sprache->cISO]);
            $linkSprache->cSeo = getSeo($linkSprache->cSeo);
            $db->insert('tlinksprache', $linkSprache);
            $oSpracheTMP = $db->select('tsprache', 'cISO ', $linkSprache->cISOSprache);
            if (isset($oSpracheTMP->kSprache) && $oSpracheTMP->kSprache > 0) {
                $db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', (int)$linkSprache->kLink, (int)$oSpracheTMP->kSprache]
                );
                $oSeo           = new stdClass();
                $oSeo->cSeo     = checkSeo($linkSprache->cSeo);
                $oSeo->kKey     = $linkSprache->kLink;
                $oSeo->cKey     = 'kLink';
                $oSeo->kSprache = $oSpracheTMP->kSprache;
                $db->insert('tseo', $oSeo);
            }
        }
    } else {
        $step              = 'neuer Link';
        $link              = new stdClass();
        $link->kLinkgruppe = (int)$_POST['kLinkgruppe'];
        $fehler            = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
} elseif (((isset($_POST['neuelinkgruppe']) && (int)$_POST['neuelinkgruppe'] === 1)
        || (isset($_POST['kLinkgruppe']) && (int)$_POST['kLinkgruppe'] > 0))
    && validateToken()
) {
    $step = 'neue Linkgruppe';
    if (isset($_POST['kLinkgruppe']) && (int)$_POST['kLinkgruppe'] > 0) {
        $linkgruppe = $db->select('tlinkgruppe', 'kLinkgruppe', (int)$_POST['kLinkgruppe']);
        $smarty->assign('Linkgruppe', $linkgruppe)
               ->assign('Linkgruppenname', getLinkgruppeNames($linkgruppe->kLinkgruppe));
    }
}

if ($continue
    && ((isset($_POST['kLink']) && (int)$_POST['kLink'] > 0)
        || (isset($_GET['kLink']) && (int)$_GET['kLink'] && isset($_GET['delpic'])))
    && validateToken()
) {
    $step = 'neuer Link';
    $link = (new \Link\Link($db))->load(verifyGPCDataInteger('kLink'));
    $smarty->assign('Link', $link);
    // Bild loeschen?
    if (verifyGPCDataInteger('delpic') === 1) {
        @unlink($cUploadVerzeichnis . $link->getID() . '/' . verifyGPDataString('cName'));
    }
    $cDatei_arr = [];
    if (is_dir($cUploadVerzeichnis . $link->getID())) {
        $DirHandle = opendir($cUploadVerzeichnis . $link->getID());
        $shopURL   = Shop::getURL() . '/';
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $nImageGroesse_arr = calcRatio(
                    PFAD_ROOT . '/' . PFAD_BILDER . PFAD_LINKBILDER . $link->getID() . '/' . $Datei,
                    160,
                    120
                );
                $oDatei            = new stdClass();
                $oDatei->cName     = substr($Datei, 0, strpos($Datei, '.'));
                $oDatei->cNameFull = $Datei;
                $oDatei->cURL      = '<img class="link_image" src="' .
                    $shopURL . PFAD_BILDER . PFAD_LINKBILDER . $link->getID() . '/' . $Datei . '" />';
                $oDatei->nBild     = (int)substr(str_replace('Bild', '', $Datei), 0,
                    strpos(str_replace('Bild', '', $Datei), '.'));
                $cDatei_arr[]      = $oDatei;
            }
        }
        usort($cDatei_arr, 'cmp_obj');
        $smarty->assign('cDatei_arr', $cDatei_arr);
    }
}

if (isset($_POST['neu_linkgruppe']) && (int)$_POST['neu_linkgruppe'] === 1 && validateToken()) {
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST);
    $oPlausiCMS->doPlausi('grp');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        $kLinkgruppe = 0;
        if ((int)$_POST['kLinkgruppe'] === 0) {
            $linkAdmin->createOrUpdateLinkGroup(0, $_POST);
            $hinweis .= 'Linkgruppe wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            $linkgruppe = $linkAdmin->createOrUpdateLinkGroup((int)$_POST['kLinkgruppe'], $_POST);
            $hinweis    .= "Die Linkgruppe <strong>$linkgruppe->cName</strong> wurde erfolgreich ge&auml;ndert.";
            $step       = 'uebersicht';
        }
        $clearCache = true;
    } else {
        $step   = 'neue Linkgruppe';
        $fehler = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
}
// Verschiebt einen Link in eine andere Linkgruppe
if (isset($_POST['aender_linkgruppe']) && (int)$_POST['aender_linkgruppe'] === 1 && validateToken()) {
    if ((int)$_POST['kLink'] > 0 && (int)$_POST['kLinkgruppe'] > 0 && (int)$_POST['kLinkgruppeAlt'] >= -1) {
        $res = $linkAdmin->updateLinkGroup(
            (int)$_POST['kLink'],
            (int)$_POST['kLinkgruppeAlt'],
            (int)$_POST['kLinkgruppe']
        );
        if ($res === \Link\Admin\LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
            $fehler .= 'Fehler: Der Link konnte nicht verschoben werden, da er bereits in der Zielgruppe existiert.';
        } elseif ($res === \Link\Admin\LinkAdmin::ERROR_LINK_NOT_FOUND) {
            $fehler .= 'Fehler: Es konnte kein Link mit Ihrem Key gefunden werden.';
        } elseif ($res === \Link\Admin\LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
            $fehler .= 'Fehler: Es konnte keine Linkgruppe mit Ihrem Key gefunden werden.';
        } elseif ($res instanceof \Link\LinkInterface) {
            $hinweis    .= 'Sie haben den Link "' . $link->getName() . '" erfolgreich verschoben.';
            $step       = 'uebersicht';
            $clearCache = true;
        } else {
            $fehler .= 'Ein unbekannter Fehler ist aufgetreten.';
        }
    }
    $step = 'uebersicht';
}
if (isset($_POST['kopiere_in_linkgruppe'])
    && (int)$_POST['kopiere_in_linkgruppe'] === 1
    && (int)$_POST['kLink'] > 0
    && (int)$_POST['kLinkgruppe'] > 0
    && validateToken()
) {
    $res = $linkAdmin->copyLinkToLinkGroup((int)$_POST['kLink'], (int)$_POST['kLinkgruppe']);
    if ($res === \Link\Admin\LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
        $fehler .= 'Fehler: Der Link konnte nicht kopiert werden, da er bereits in der Zielgruppe existiert.';
    } elseif ($res === \Link\Admin\LinkAdmin::ERROR_LINK_NOT_FOUND) {
        $fehler .= 'Fehler: Es konnte kein Link mit Ihrem Key gefunden werden.';
    } elseif ($res === \Link\Admin\LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
        $fehler .= 'Fehler: Es konnte keine Linkgruppe mit Ihrem Key gefunden werden.';
    } elseif ($res instanceof \Link\LinkInterface) {
        $hinweis    .= 'Sie haben den Link "' . $link->getName() . '" erfolgreich kopiert.';
        $step       = 'uebersicht';
        $clearCache = true;
    } else {
        $fehler .= 'Ein unbekannter Fehler ist aufgetreten.';
    }
}
// Ordnet einen Link neu an
if (isset($_POST['aender_linkvater']) && (int)$_POST['aender_linkvater'] === 1 && validateToken()) {
    if ((int)$_POST['kLink'] > 0
        && (int)$_POST['kVaterLink'] >= 0
        && (int)$_POST['kLinkgruppe'] > 0
        && ($oLink = $linkAdmin->updateParentID((int)$_POST['kLink'], (int)$_POST['kVaterLink'])) !== false
    ) {
        $hinweis    .= "Sie haben den Link '" . $oLink->cName . "' erfolgreich verschoben.";
        $step       = 'uebersicht';
        $clearCache = true;
    } else {
        $fehler .= 'Fehler: Link konnte nicht verschoben werden.';
    }
}
if ($clearCache === true) {
    $cache->flushTags([CACHING_GROUP_CORE]);
    $db->query('UPDATE tglobals SET dLetzteAenderung = now()', \DB\ReturnType::DEFAULT);
}
if ($step === 'uebersicht') {
    $smarty->assign('kPlugin', verifyGPCDataInteger('kPlugin'))
           ->assign('linkGroupCountByLinkID', $linkAdmin->getLinkGroupCountForLinkIDs())
           ->assign('linkgruppen', $linkAdmin->getLinkGroups());
}
if ($step === 'neuer Link') {
    $kundengruppen = $db->query('SELECT * FROM tkundengruppe ORDER BY cName', \DB\ReturnType::ARRAY_OF_OBJECTS);
    $smarty->assign('Link', $link)
           ->assign('oSpezialseite_arr', holeSpezialseiten())
           ->assign('sprachen', gibAlleSprachen())
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($link));
}
$smarty->assign('step', $step)
       ->assign('sprachen', gibAlleSprachen())
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->assign('linkAdmin', $linkAdmin)
       ->display('links.tpl');
