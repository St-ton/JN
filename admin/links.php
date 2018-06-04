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
if (isset($_POST['addlink']) && (int)$_POST['addlink'] > 0) {
    $step = 'neuer Link';
    if (!isset($link)) {
        $link = new stdClass();
    }
    $link->kLinkgruppe = (int)$_POST['addlink'];
}

if (isset($_POST['removefromlinkgroup'], $_POST['kLinkgruppe'])
    && (int)$_POST['removefromlinkgroup'] > 0
    && validateToken()
) {
    $linkID      = (int)$_POST['removefromlinkgroup'];
    $linkGroupID = (int)$_POST['kLinkgruppe'];
    $res         = Shop::Container()->getDB()->delete(
        'tlinkgroupassociations',
        ['linkGroupID', 'linkID'],
        [$linkGroupID, $linkID]
    );
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
    $kLink       = (int)$_POST['dellink'];
    $kLinkgruppe = (int)$_POST['kLinkgruppe'];
    $res         = removeLink($kLink, $kLinkgruppe);
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
    $kLinkgruppe = -1;
    if (isset($_POST['dellinkgruppe'])) {
        $kLinkgruppe = (int)$_POST['dellinkgruppe'];
    }
    if ((int)$_POST['kLinkgruppe'] > 0) {
        $kLinkgruppe = (int)$_POST['kLinkgruppe'];
    }

    $linkIDs = Shop::Container()->getDB()->selectAll('tlinkgroupassociations', 'linkGroupID', $kLinkgruppe);
    foreach ($linkIDs as $linkID) {
        removeLink($linkID->linkID);
    }
    Shop::Container()->getDB()->delete('tlinkgruppe', 'kLinkgruppe', $kLinkgruppe);
    Shop::Container()->getDB()->delete('tlinkgruppesprache', 'kLinkgruppe', $kLinkgruppe);
    $hinweis    .= 'Linkgruppe erfolgreich gel&ouml;scht!';
    $clearCache = true;
    $step       = 'uebersicht';
    $_POST      = [];
}

if (isset($_POST['delconfirmlinkgruppe']) && (int)$_POST['delconfirmlinkgruppe'] > 0 && validateToken()) {
    $step  = 'linkgruppe_loeschen_confirm';
    $links = Shop::Container()->getDB()->queryPrepared(
        'SELECT tlink.cName
            FROM tlink
            JOIN tlinkgroupassociations A
                ON tlink.kLink = A.linkID
            JOIN tlinkgroupassociations B
                ON A.linkID = B.linkID
            WHERE A.linkGroupID = :lgid
            GROUP BY A.linkID
            HAVING COUNT(A.linkID) > 1',
         ['lgid' => (int)$_POST['delconfirmlinkgruppe']],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('oLinkgruppe', holeLinkgruppe((int)$_POST['delconfirmlinkgruppe']))
           ->assign('affectedLinkNames', \Functional\map($links, function ($l) {
               return $l->cName;
           }));
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
            $kLink              = Shop::Container()->getDB()->insert('tlink', $link);
            $assoc              = new stdClass();
            $assoc->linkID      = $kLink;
            $assoc->linkGroupID = (int)$_POST['kLinkgruppe'];
            Shop::Container()->getDB()->insert('tlinkgroupassociations', $assoc);
            $hinweis .= 'Link wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kLink    = (int)$_POST['kLink'];
            $revision = new Revision();
            $revision->addRevision('link', (int)$_POST['kLink'], true);
            Shop::Container()->getDB()->update('tlink', 'kLink', $kLink, $link);
            $hinweis  .= "Der Link <strong>$link->cName</strong> wurde erfolgreich ge&auml;ndert.";
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
            Shop::Container()->getDB()->delete('tlinksprache', ['kLink', 'cISOSprache'], [$kLink, $sprache->cISO]);
            $linkSprache->cSeo = getSeo($linkSprache->cSeo);
            Shop::Container()->getDB()->insert('tlinksprache', $linkSprache);
            $oSpracheTMP = Shop::Container()->getDB()->select('tsprache', 'cISO ', $linkSprache->cISOSprache);
            if (isset($oSpracheTMP->kSprache) && $oSpracheTMP->kSprache > 0) {
                Shop::Container()->getDB()->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', (int)$linkSprache->kLink, (int)$oSpracheTMP->kSprache]
                );
                $oSeo           = new stdClass();
                $oSeo->cSeo     = checkSeo($linkSprache->cSeo);
                $oSeo->kKey     = $linkSprache->kLink;
                $oSeo->cKey     = 'kLink';
                $oSeo->kSprache = $oSpracheTMP->kSprache;
                Shop::Container()->getDB()->insert('tseo', $oSeo);
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
        $linkgruppe = Shop::Container()->getDB()->select('tlinkgruppe', 'kLinkgruppe', (int)$_POST['kLinkgruppe']);
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
    $link = Shop::Container()->getDB()->select('tlink', 'kLink', verifyGPCDataInteger('kLink'));
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
    $cDatei_arr = [];
    if (is_dir($cUploadVerzeichnis . $link->kLink)) {
        $DirHandle = opendir($cUploadVerzeichnis . $link->kLink);
        $shopURL   = Shop::getURL() . '/';
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $nImageGroesse_arr = calcRatio(PFAD_ROOT . '/' . PFAD_BILDER . PFAD_LINKBILDER . $link->kLink . '/' . $Datei,
                    160, 120);
                $oDatei            = new stdClass();
                $oDatei->cName     = substr($Datei, 0, strpos($Datei, '.'));
                $oDatei->cNameFull = $Datei;
                $oDatei->cURL      = '<img class="link_image" src="' .
                    $shopURL . PFAD_BILDER . PFAD_LINKBILDER . $link->kLink . '/' . $Datei . '" />';
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
        if (!isset($linkgruppe)) {
            $linkgruppe = new stdClass();
        }
        $linkgruppe->kLinkgruppe   = (int)$_POST['kLinkgruppe'];
        $linkgruppe->cName         = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $linkgruppe->cTemplatename = htmlspecialchars($_POST['cTemplatename'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);

        $kLinkgruppe = 0;
        if ((int)$_POST['kLinkgruppe'] === 0) {
            //einfuegen
            $kLinkgruppe = Shop::Container()->getDB()->insert('tlinkgruppe', $linkgruppe);
            $hinweis     .= 'Linkgruppe wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kLinkgruppe = (int)$_POST['kLinkgruppe'];
            Shop::Container()->getDB()->update('tlinkgruppe', 'kLinkgruppe', $kLinkgruppe, $linkgruppe);
            $hinweis .= "Die Linkgruppe <strong>$linkgruppe->cName</strong> wurde erfolgreich ge&auml;ndert.";
            $step    = 'uebersicht';
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
                $linkgruppeSprache->cName = htmlspecialchars($_POST['cName_' . $sprache->cISO],
                    ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            }

            Shop::Container()->getDB()->delete(
                'tlinkgruppesprache',
                ['kLinkgruppe', 'cISOSprache'],
                [$kLinkgruppe, $sprache->cISO]
            );
            Shop::Container()->getDB()->insert('tlinkgruppesprache', $linkgruppeSprache);
        }
    } else {
        $step   = 'neue Linkgruppe';
        $fehler = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
}
// Verschiebt einen Link in eine andere Linkgruppe
if (isset($_POST['aender_linkgruppe']) && (int)$_POST['aender_linkgruppe'] === 1 && validateToken()) {
    if ((int)$_POST['kLink'] > 0 && (int)$_POST['kLinkgruppe'] > 0 && (int)$_POST['kLinkgruppeAlt'] >= 0) {
        $oldLinkGroupID = (int)$_POST['kLinkgruppeAlt'];
        $newLinkGroupID = (int)$_POST['kLinkgruppe'];
        $oLink          = new \Link\Link(Shop::Container()->getDB());
        $oLink->load((int)$_POST['kLink']);
        if ($oLink->getID() > 0) {
            $oLinkgruppe = Shop::Container()->getDB()->select('tlinkgruppe', 'kLinkgruppe', $newLinkGroupID);
            if (isset($oLinkgruppe->kLinkgruppe) && $oLinkgruppe->kLinkgruppe > 0) {
                $exists = Shop::Container()->getDB()->select(
                    'tlinkgroupassociations',
                    ['linkGroupID', 'linkID'],
                    [$newLinkGroupID, $oLink->getID()]
                );
                if (empty($exists)) {
                    $upd              = new stdClass();
                    $upd->linkGroupID = $newLinkGroupID;
                    $rows             = Shop::Container()->getDB()->update(
                        'tlinkgroupassociations',
                        ['linkGroupID', 'linkID'],
                        [$oldLinkGroupID, $oLink->getID()],
                        $upd
                    );
                    if ($rows === 0) {
                        // previously unassigned link
                        $upd              = new stdClass();
                        $upd->linkGroupID = $newLinkGroupID;
                        $upd->linkID      = $oLink->getID();
                        $rows             = Shop::Container()->getDB()->insert(
                            'tlinkgroupassociations',
                            $upd
                        );
                    }
                    unset($upd->linkID);
                    updateChildLinkGroups($oLink, $oldLinkGroupID, $newLinkGroupID);
                    $hinweis    .= 'Sie haben den Link "' . $oLink->getName() . '" erfolgreich in die Linkgruppe "' .
                        $oLinkgruppe->cName . '" verschoben.';
                    $step       = 'uebersicht';
                    $clearCache = true;
                } else {
                    $fehler .= 'Fehler: Der Link konnte nicht verschoben werden. Er existiert bereits in der Zielgruppe.';
                }
            } else {
                $fehler .= 'Fehler: Es konnte keine Linkgruppe mit Ihrem Key gefunden werden.';
            }
        } else {
            $fehler .= 'Fehler: Es konnte kein Link mit Ihrem Key gefunden werden.';
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
    $link = new \Link\Link(Shop::Container()->getDB());
    $link->load((int)$_POST['kLink']);
    if ($link->getID() > 0) {
        $targetLinkGroupID = (int)$_POST['kLinkgruppe'];
        $oLinkgruppe       = Shop::Container()->getDB()->select('tlinkgruppe', 'kLinkgruppe', $targetLinkGroupID);
        if (isset($oLinkgruppe->kLinkgruppe) && $oLinkgruppe->kLinkgruppe > 0) {
            $exists = Shop::Container()->getDB()->select(
                'tlinkgroupassociations',
                ['linkID', 'linkGroupID'],
                [(int)$_POST['kLink'], $targetLinkGroupID]
            );
            if (empty($exists)) {
                $ins              = new stdClass();
                $ins->linkID      = $link->getID();
                $ins->linkGroupID = $targetLinkGroupID;
                Shop::Container()->getDB()->insert('tlinkgroupassociations', $ins);
                copyChildLinksToLinkGroup($link, $targetLinkGroupID);
                $hinweis .= 'Sie haben den Link "' . $link->getName() . '" erfolgreich in die Linkgruppe "' .
                    $oLinkgruppe->cName . '" kopiert.';
            } else {
                $fehler .= 'Fehler: Der Link konnte nicht kopiert werden. Er existiert bereits in der Zielgruppe.';
            }
            $step       = 'uebersicht';
            $clearCache = true;
        } else {
            $fehler .= 'Fehler: Es konnte keine Linkgruppe mit Ihrem Key gefunden werden.';
        }
    } else {
        $fehler .= 'Fehler: Es konnte kein Link mit Ihrem Key gefunden werden.';
    }
}
// Ordnet einen Link neu an
if (isset($_POST['aender_linkvater']) && (int)$_POST['aender_linkvater'] === 1 && validateToken()) {
    $success = false;
    if ((int)$_POST['kLink'] > 0 && (int)$_POST['kVaterLink'] >= 0 && (int)$_POST['kLinkgruppe'] > 0) {
        $kLink       = (int)$_POST['kLink'];
        $kVaterLink  = (int)$_POST['kVaterLink'];
        $kLinkgruppe = (int)$_POST['kLinkgruppe'];
        $oLink       = Shop::Container()->getDB()->select('tlink', 'kLink', $kLink);
        $oVaterLink  = Shop::Container()->getDB()->select('tlink', 'kLink', $kVaterLink);

        if (isset($oLink->kLink)
            && $oLink->kLink > 0
            && ((isset($oVaterLink->kLink) && $oVaterLink->kLink > 0) || $kVaterLink === 0)
        ) {
            $success         = true;
            $upd             = new stdClass();
            $upd->kVaterLink = $kVaterLink;
            Shop::Container()->getDB()->update('tlink', 'kLink', $kLink, $upd);
            $hinweis .= "Sie haben den Link '" . $oLink->cName . "' erfolgreich verschoben.";
            $step    = 'uebersicht';
        }
        $clearCache = true;
    }

    if (!$success) {
        $fehler .= 'Fehler: Link konnte nicht verschoben werden.';
    }
}
//clear cache
if ($clearCache === true) {
    Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
    Shop::Container()->getDB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
}
if ($step === 'uebersicht') {
    $db  = Shop::Container()->getDB();
    $lgl = new \Link\LinkGroupList($db);
    $lgl->loadAll();
    $linkGroups = $lgl->getLinkGroups()->filter(function (\Link\LinkGroupInterface $e) {
        return $e->isSpecial() === false;
    });
    foreach ($linkGroups as $linkGroup) {
        /** @var \Link\LinkGroupInterface $linkGroup */
        $filtered = build_navigation_subs_admin($linkGroup);
        $linkGroup->setLinks($filtered);
    }
    $unassigned = $db->query(
        'SELECT kLink 
            FROM tlink 
            WHERE kLink NOT IN (SELECT linkID FROM tlinkgroupassociations)',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (count($unassigned) > 0) {
        $languages     = $db->query('SELECT * FROM tsprache', \DB\ReturnType::ARRAY_OF_OBJECTS);
        $names         = [];
        $languageIDs   = [];
        $languageCodes = [];
        foreach ($languages as $language) {
            $langID                 = (int)$language->kSprache;
            $names[$langID]         = $language->cISO === 'ger' ? 'Links ohne Linkgruppe' : 'Links without link group';
            $languageIDs[]          = $langID;
            $languageCodes[$langID] = $language->cISO;
        }
        $ualg       = new \Link\LinkGroup($db);
        $ull        = new \Link\LinkList($db);
        $unassigned = \Functional\map($unassigned, function ($l) {
            return (int)$l->kLink;
        });
        $ualg->setLinks($ull->createLinks($unassigned));
        $ualg->setNames($names);
        $ualg->setLanguageCode($languageCodes);
        $ualg->setLanguageID($languageIDs);
        $ualg->setID(0);
        $ualg->setTemplate('');
        $linkGroups->push($ualg);
    }
    $assocCount             = Shop::Container()->getDB()->query(
        'SELECT tlink.kLink, COUNT(*) AS cnt 
            FROM tlink 
            JOIN tlinkgroupassociations
                ON tlinkgroupassociations.linkID = tlink.kLink
            GROUP BY tlink.kLink
            HAVING COUNT(*) > 1',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $linkGroupCountByLinkID = [];
    foreach ($assocCount as $item) {
        $linkGroupCountByLinkID[(int)$item->kLink] = (int)$item->cnt;
    }
    $smarty->assign('kPlugin', verifyGPCDataInteger('kPlugin'))
           ->assign('linkGroupCountByLinkID', $linkGroupCountByLinkID)
           ->assign('linkgruppen', $linkGroups);
}

if ($step === 'neue Linkgruppe') {
    $smarty->assign('sprachen', gibAlleSprachen());
}

if ($step === 'neuer Link') {
    $kundengruppen = Shop::Container()->getDB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2);
    $smarty->assign('Link', $link)
           ->assign('oSpezialseite_arr', holeSpezialseiten())
           ->assign('sprachen', gibAlleSprachen())
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($link));
}

//clear cache
if ($clearCache === true) {
    Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
    Shop::Container()->getDB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
}
$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('links.tpl');
