<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

$oAccount->permission('CONTENT_NEWS_SYSTEM_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';

$Einstellungen      = Shop::getSettings(array(CONF_NEWS));
$cHinweis           = '';
$cFehler            = '';
$step               = 'news_uebersicht';
$cUploadVerzeichnis = PFAD_ROOT . PFAD_NEWSBILDER;
$oNewsKategorie_arr = array();
$continueWith       = false;
setzeSprache();
$nAnzahlProSeite   = 15;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(3, $nAnzahlProSeite);

// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
$Sprachen     = gibAlleSprachen();
$oSpracheNews = Shop::Lang()->getIsoFromLangID($_SESSION['kSprache']);
if (!$oSpracheNews) {
    $oSpracheNews = Shop::DB()->query("SELECT cISO FROM tsprache WHERE kSprache = " . (int)$_SESSION['kSprache'], 1);
}
// News
if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) > 0 && validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_NEWS, $_POST, array(CACHING_GROUP_OPTION, CACHING_GROUP_NEWS));
    if (count($Sprachen) > 0) {
        // tnewsmonatspraefix loeschen
        Shop::DB()->query("TRUNCATE tnewsmonatspraefix", 3);

        foreach ($Sprachen as $oSpracheTMP) {
            $oNewsMonatsPraefix           = new stdClass();
            $oNewsMonatsPraefix->kSprache = $oSpracheTMP->kSprache;
            if (strlen($_POST['praefix_' . $oSpracheTMP->cISO]) > 0) {
                $oNewsMonatsPraefix->cPraefix = $_POST['praefix_' . $oSpracheTMP->cISO];
            } else {
                $oNewsMonatsPraefix->cPraefix = ($oSpracheTMP->cISO === 'ger') ?
                    'Newsuebersicht' :
                    'Newsoverview';
            }
            Shop::DB()->insert('tnewsmonatspraefix', $oNewsMonatsPraefix);
        }
    }
}

if (verifyGPCDataInteger('news') === 1 && validateToken()) {
    // Neue News erstellen
    if ((isset($_POST['erstellen']) && intval($_POST['erstellen']) === 1 && isset($_POST['news_erstellen'])) || (isset($_POST['news_erstellen']) && intval($_POST['news_erstellen']) === 1)) {
        $oNewsKategorie_arr = holeNewskategorie();
        // News erstellen, $oNewsKategorie_arr leer = Fehler ausgeben
        if (count($oNewsKategorie_arr) > 0) {
            $step = 'news_erstellen';
            $smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr);
        } else {
            $cFehler .= 'Fehler: Bitte legen Sie zuerst eine Newskategorie an.<br />';
            $step = 'news_uebersicht';
        }
    } elseif ((isset($_POST['erstellen']) && intval($_POST['erstellen']) === 1 && isset($_POST['news_kategorie_erstellen'])) ||
        (isset($_POST['news_kategorie_erstellen']) && intval($_POST['news_kategorie_erstellen']) === 1)) {
        $step = 'news_kategorie_erstellen';
    } elseif (verifyGPCDataInteger('nkedit') === 1) { // Newskommentar editieren
        if (verifyGPCDataInteger('kNews') > 0) {
            if (isset($_POST['newskommentarsavesubmit'])) {
                if (speicherNewsKommentar(verifyGPCDataInteger('kNewsKommentar'), $_POST)) {
                    $step = 'news_vorschau';
                    $cHinweis .= 'Der Newskommentar wurde erfolgreich editiert.<br />';

                    if (verifyGPCDataInteger('nFZ') === 1) {
                        header('Location: freischalten.php');
                        exit();
                    }
                } else {
                    $step = 'news_kommentar_editieren';
                    $cFehler .= 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben.<br />';
                    $oNewsKommentar                 = new stdClass();
                    $oNewsKommentar->kNewsKommentar = $_POST['kNewsKommentar'];
                    $oNewsKommentar->kNews          = $_POST['kNews'];
                    $oNewsKommentar->cName          = $_POST['cName'];
                    $oNewsKommentar->cKommentar     = $_POST['cKommentar'];
                    $smarty->assign('oNewsKommentar', $oNewsKommentar);
                }
            } else {
                $step = 'news_kommentar_editieren';
                $smarty->assign('oNewsKommentar', Shop::DB()->select('tnewskommentar', 'kNewsKommentar', verifyGPCDataInteger('kNewsKommentar')));
                if (verifyGPCDataInteger('nFZ') === 1) {
                    $smarty->assign('nFZ', 1);
                }
            }
        }
    } elseif (isset($_POST['news_speichern']) && intval($_POST['news_speichern']) === 1) { // News speichern
        $kKundengruppe_arr  = (isset($_POST['kKundengruppe']) ? $_POST['kKundengruppe'] : null);
        $kNewsKategorie_arr = (isset($_POST['kNewsKategorie']) ? $_POST['kNewsKategorie'] : null);
        $cBetreff           = $_POST['betreff'];
        $cSeo               = $_POST['seo'];
        $cText              = $_POST['text'];
        $cVorschauText      = $_POST['cVorschauText'];
        $nAktiv             = (int)$_POST['nAktiv'];
        $cMetaTitle         = $_POST['cMetaTitle'];
        $cMetaDescription   = $_POST['cMetaDescription'];
        $cMetaKeywords      = $_POST['cMetaKeywords'];
        $dGueltigVon        = $_POST['dGueltigVon'];
        $cPreviewImage      = $_POST['previewImage'];
        //$dGueltigBis      = $_POST['dGueltigBis'];

        $cPlausiValue_arr = pruefeNewsPost($cBetreff, $cText, $kKundengruppe_arr, $kNewsKategorie_arr);

        if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) === 0) {
            $oNews                   = new stdClass();
            $oNews->kSprache         = $_SESSION['kSprache'];
            $oNews->cKundengruppe    = ';' . implode(';', $kKundengruppe_arr) . ';';
            $oNews->cBetreff         = $cBetreff;
            $oNews->cText            = $cText;
            $oNews->cVorschauText    = $cVorschauText;
            $oNews->nAktiv           = $nAktiv;
            $oNews->cMetaTitle       = $cMetaTitle;
            $oNews->cMetaDescription = $cMetaDescription;
            $oNews->cMetaKeywords    = $cMetaKeywords;
            $oNews->dErstellt        = 'now()';
            $oNews->dGueltigVon      = convertDate($dGueltigVon);
            $oNews->cPreviewImage    = $cPreviewImage;

            $nNewsOld = 0;
            if (isset($_POST['news_edit_speichern']) && intval($_POST['news_edit_speichern']) === 1) {
                $nNewsOld = 1;
                $kNews    = (int)$_POST['kNews'];
                Shop::DB()->delete('tnews', 'kNews', $kNews);
                // tseo loeschen
                Shop::DB()->delete('tseo', array('cKey', 'kKey'), array('kNews', $kNews));
            }
            $oNews->cSeo = (strlen($cSeo) > 0) ? checkSeo(getSeo($cSeo)) : checkSeo(getSeo($cBetreff));
            if (isset($kNews) && $kNews > 0) {
                $oNews->kNews = $kNews;
                Shop::DB()->insert('tnews', $oNews);
            } else {
                $kNews = Shop::DB()->insert('tnews', $oNews);
            }
            $kNews = (int)$kNews;
            // Bilder hochladen
            if (!is_dir($cUploadVerzeichnis . $kNews)) {
                mkdir($cUploadVerzeichnis . $kNews);
            } else {
                $oAlteBilder_arr = holeNewsBilder($oNews->kNews, $cUploadVerzeichnis);
            }
            if (isset($_FILES['previewImage']['name']) && strlen($_FILES['previewImage']['name']) > 0) {
                $extension = substr(
                    $_FILES['previewImage']['type'],
                    strpos($_FILES['previewImage']['type'], '/') + 1,
                    strlen($_FILES['previewImage']['type'] - (strpos($_FILES['previewImage']['type'], '/'))) + 1
                );
                //not elegant, but since it's 99% jpg..
                if ($extension === 'jpe') {
                    $extension = 'jpg';
                }
                //check if preview exists and delete
                foreach ($oAlteBilder_arr as $oBild) {
                    if (strpos($oBild->cDatei, 'preview') !== false) {
                        loescheNewsBild($oBild->cName, $kNews, $cUploadVerzeichnis);
                    }
                }
                $cUploadDatei = $cUploadVerzeichnis . $kNews . '/preview.' . $extension;
                move_uploaded_file($_FILES['previewImage']['tmp_name'], $cUploadDatei);
                $oNews->cPreviewImage = PFAD_NEWSBILDER . $kNews . '/preview.' . $extension;
            }
            if (is_array($_FILES['Bilder']['name']) && count($_FILES['Bilder']['name']) > 0) {
                $nLetztesBild = gibLetzteBildNummer($kNews);
                $nZaehler     = 0;
                if ($nLetztesBild > 0) {
                    $nZaehler = $nLetztesBild;
                }

                for ($i = $nZaehler; $i < (count($_FILES['Bilder']['name']) + $nZaehler); $i++) {
                    $extension = substr(
                        $_FILES['Bilder']['type'][$i - $nZaehler],
                        strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/') + 1,
                        strlen($_FILES['Bilder']['type'][$i - $nZaehler] - (strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/'))) + 1
                    );
                    //not elegant, but since it's 99% jpg..
                    if ($extension === 'jpe') {
                        $extension = 'jpg';
                    }
                    //check if image exists and delete
                    foreach ($oAlteBilder_arr as $oBild) {
                        if (strpos($oBild->cDatei, 'Bild' . ($i + 1) . '.') !== false && $_FILES['Bilder']['name'][$i - $nZaehler] != '') {
                            loescheNewsBild($oBild->cName, $kNews, $cUploadVerzeichnis);
                        }
                    }
                    $cUploadDatei = $cUploadVerzeichnis . $kNews . '/Bild' . ($i + 1) . '.' . $extension;
                    move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $cUploadDatei);
                }
            }
            // Text parsen
            Shop::DB()->query(
                "UPDATE tnews
                    SET cText = '" . parseText($cText, $kNews) . "',
                        cVorschauText = '" . parseText($cVorschauText, $kNews) . "',
                        cPreviewImage = '" . $oNews->cPreviewImage . "'
                    WHERE kNews = " . $kNews, 3
            );
            Shop::DB()->delete('tseo', array('cKey', 'kKey', 'kSprache'), array('kNews', $kNews, (int)$_SESSION['kSprache']));
            // SEO tseo eintragen
            $oSeo           = new stdClass();
            $oSeo->cSeo     = $oNews->cSeo;
            $oSeo->cKey     = 'kNews';
            $oSeo->kKey     = $kNews;
            $oSeo->kSprache = $_SESSION['kSprache'];
            Shop::DB()->insert('tseo', $oSeo);
            // tnewskategorienews fuer aktuelle news loeschen
            Shop::DB()->delete('tnewskategorienews', 'kNews', $kNews);
            // tnewskategorienews eintragen
            foreach ($kNewsKategorie_arr as $kNewsKategorie) {
                $oNewsKategorieNews                 = new stdClass();
                $oNewsKategorieNews->kNews          = $kNews;
                $oNewsKategorieNews->kNewsKategorie = (int)$kNewsKategorie;

                Shop::DB()->insert('tnewskategorienews', $oNewsKategorieNews);
            }
            // tnewsmonatsuebersicht updaten
            if ($nAktiv == 1) {
                $oDatum = gibJahrMonatVonDateTime($oNews->dGueltigVon);
                $dMonat = $oDatum->Monat;
                $dJahr  = $oDatum->Jahr;

                $oNewsMonatsUebersicht = Shop::DB()->query(
                    "SELECT *
                        FROM tnewsmonatsuebersicht
                        WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                            AND nMonat = " . (int)$dMonat . "
                            AND nJahr = " . (int)$dJahr, 1
                );
                // Falls dies die erste News des Monats ist, neuen Eintrag in tnewsmonatsuebersicht, ansonsten updaten
                if (isset($oNewsMonatsUebersicht->kNewsMonatsUebersicht) && $oNewsMonatsUebersicht->kNewsMonatsUebersicht > 0) {
                    unset($oNewsMonatsPraefix);
                    $oNewsMonatsPraefix = Shop::DB()->query(
                        "SELECT cPraefix
                            FROM tnewsmonatspraefix
                            WHERE kSprache = " . (int)$_SESSION['kSprache'], 1
                    );

                    if (strlen($oNewsMonatsPraefix->cPraefix) === 0) {
                        $oNewsMonatsPraefix->cPraefix = 'Newsuebersicht';
                    }
                    Shop::DB()->delete('tseo', array('cKey', 'kKey', 'kSprache'), array('kNewsMonatsUebersicht', (int)$oNewsMonatsUebersicht->kNewsMonatsUebersicht, (int)$_SESSION['kSprache']));
                    // SEO tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = checkSeo(getSeo($oNewsMonatsPraefix->cPraefix . '-' . strval($dMonat) . '-' . $dJahr));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = $oNewsMonatsUebersicht->kNewsMonatsUebersicht;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    Shop::DB()->insert('tseo', $oSeo);
                } else {
                    $oNewsMonatsPraefix = new stdClass();
                    $oNewsMonatsPraefix = Shop::DB()->query(
                        "SELECT cPraefix
                            FROM tnewsmonatspraefix
                            WHERE kSprache = " . (int)$_SESSION['kSprache'], 1
                    );

                    if (strlen($oNewsMonatsPraefix->cPraefix) === 0) {
                        $oNewsMonatsPraefix->cPraefix = 'Newsuebersicht';
                    }

                    $oNewsMonatsUebersichtTMP           = new stdClass();
                    $oNewsMonatsUebersichtTMP->kSprache = (int)$_SESSION['kSprache'];
                    $oNewsMonatsUebersichtTMP->cName    = mappeDatumName(strval($dMonat), $dJahr, $oSpracheNews->cISO);
                    $oNewsMonatsUebersichtTMP->nMonat   = (int)$dMonat;
                    $oNewsMonatsUebersichtTMP->nJahr    = (int)$dJahr;

                    $kNewsMonatsUebersicht = Shop::DB()->insert('tnewsmonatsuebersicht', $oNewsMonatsUebersichtTMP);

                    Shop::DB()->delete('tseo', array('cKey', 'kKey', 'kSprache'), array('kNewsMonatsUebersicht', (int)$kNewsMonatsUebersicht, (int)$_SESSION['kSprache']));
                    // SEO tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = checkSeo(getSeo($oNewsMonatsPraefix->cPraefix . '-' . strval($dMonat) . '-' . $dJahr));
                    $oSeo->cKey     = 'kNewsMonatsUebersicht';
                    $oSeo->kKey     = (int)$kNewsMonatsUebersicht;
                    $oSeo->kSprache = (int)$_SESSION['kSprache'];
                    Shop::DB()->insert('tseo', $oSeo);
                }
            }
            $cHinweis .= 'Ihre News wurde erfolgreich gespeichert.<br />';
            if (isset($_POST['continue']) && $_POST['continue'] === '1') {
                $step         = 'news_editieren';
                $continueWith = (int)$kNews;
            }
        } else {
            $step               = 'news_editieren';
            $oNewsKategorie_arr = holeNewskategorie();
            $smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
                   ->assign('cPostVar_arr', $_POST)
                   ->assign('cPlausiValue_arr', $cPlausiValue_arr);
            $cFehler .= 'Fehler: Bitte f&uuml;llen Sie alle Pflichtfelder aus.<br />';
        }
    } elseif (isset($_POST['news_loeschen']) && intval($_POST['news_loeschen']) === 1) { // News loeschen
        if (is_array($_POST['kNews']) && count($_POST['kNews']) > 0) {
            foreach ($_POST['kNews'] as $kNews) {
                $kNews = intval($kNews);

                if ($kNews > 0) {
                    $oNewsTMP = Shop::DB()->query(
                        "SELECT dGueltigVon, nAktiv, kSprache
                            FROM tnews
                            WHERE kNews = " . $kNews, 1
                    );
                    Shop::DB()->delete('tnews', 'kNews', $kNews);
                    // Bilderverzeichnis loeschen
                    loescheNewsBilderDir($kNews, $cUploadVerzeichnis);
                    // Kommentare loeschen
                    Shop::DB()->delete('tnewskommentar', 'kNews', $kNews);
                    // tseo loeschen
                    Shop::DB()->delete('tseo', array('cKey', 'kKey'), array('kNews', $kNews));
                    // tnewskategorienews loeschen
                    Shop::DB()->delete('tnewskategorienews', 'kNews', $kNews);
                    // War das die letzte News fuer einen bestimmten Monat? => Falls ja, tnewsmonatsuebersicht Monat loeschen
                    $oDatum       = gibJahrMonatVonDateTime($oNewsTMP->dGueltigVon);
                    $kSpracheTMP  = (int)$oNewsTMP->kSprache;
                    $oNewsTMP_arr = Shop::DB()->query(
                        "SELECT kNews
                            FROM tnews
                            WHERE month(dGueltigVon) = " . $oDatum->Monat . "
                                AND year(dGueltigVon) = " . $oDatum->Jahr . "
                                AND kSprache = " . $kSpracheTMP, 2
                    );
                    if (is_array($oNewsTMP_arr) && count($oNewsTMP_arr) === 0) {
                        Shop::DB()->query(
                            "DELETE tnewsmonatsuebersicht, tseo FROM tnewsmonatsuebersicht
                                LEFT JOIN tseo ON tseo.cKey = 'kNewsMonatsUebersicht'
                                    AND tseo.kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                                    AND tseo.kSprache = tnewsmonatsuebersicht.kSprache
                                WHERE tnewsmonatsuebersicht.nMonat = " . $oDatum->Monat . "
                                    AND tnewsmonatsuebersicht.nJahr = " . $oDatum->Jahr . "
                                    AND tnewsmonatsuebersicht.kSprache = " . $kSpracheTMP, 4
                        );
                    }
                }
            }

            $cHinweis .= 'Ihre markierten News wurden erfolgreich gel&ouml;scht.<br />';
        } else {
            $cFehler .= 'Fehler: Sie m&uuml;ssen mindestens eine News ausgew&auml;hlt haben.<br />';
        }
    } elseif (isset($_POST['news_kategorie_speichern']) && intval($_POST['news_kategorie_speichern']) === 1) { //Newskategorie speichern
        $step             = 'news_uebersicht';
        $cName            = $_POST['cName'];
        $cSeo             = $_POST['cSeo'];
        $nSort            = $_POST['nSort'];
        $nAktiv           = $_POST['nAktiv'];
        $cMetaTitle       = $_POST['cMetaTitle'];
        $cMetaDescription = $_POST['cMetaDescription'];
        $cBeschreibung    = $_POST['cBeschreibung'];
        $cPlausiValue_arr = pruefeNewsKategorie($_POST['cName'], (isset($_POST['newskategorie_edit_speichern'])) ? intval($_POST['newskategorie_edit_speichern']) : 0);

        if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) === 0) {
            $kNewsKategorie = 0;

            if (isset($_POST['newskategorie_edit_speichern']) && isset($_POST['kNewsKategorie']) && intval($_POST['newskategorie_edit_speichern']) === 1 && intval($_POST['kNewsKategorie']) > 0) {
                $kNewsKategorie = (int)$_POST['kNewsKategorie'];

                Shop::DB()->delete('tnewskategorie', 'kNewsKategorie', $kNewsKategorie);
                // tseo loeschen
                Shop::DB()->delete('tseo', array('cKey', 'kKey'), array('kNewsKategorie', $kNewsKategorie));
            }
            $oNewsKategorie                        = new stdClass();
            $oNewsKategorie->kSprache              = (int)$_SESSION['kSprache'];
            $oNewsKategorie->cName                 = $cName;
            $oNewsKategorie->cBeschreibung         = $cBeschreibung;
            $oNewsKategorie->nSort                 = ((int)$nSort > -1) ? (int)$nSort : 0;
            $oNewsKategorie->nAktiv                = (int)$nAktiv;
            $oNewsKategorie->cMetaTitle            = $cMetaTitle;
            $oNewsKategorie->cMetaDescription      = $cMetaDescription;
            $oNewsKategorie->dLetzteAktualisierung = 'now()';
            $oNewsKategorie->cSeo                  = (strlen($cSeo) > 0) ?
                checkSeo(getSeo($cSeo)) :
                checkSeo(getSeo($cName));

            if ($kNewsKategorie > 0) {
                $oNewsKategorie->kNewsKategorie = $kNewsKategorie;
                Shop::DB()->insert('tnewskategorie', $oNewsKategorie);
            } else {
                $kNewsKategorie = Shop::DB()->insert('tnewskategorie', $oNewsKategorie);
            }
            Shop::DB()->delete('tseo', array('cKey', 'kKey', 'kSprache'), array('kNewsKategorie', (int)$kNewsKategorie, (int)$oNewsKategorie->kSprache));
            // SEO tseo eintragen
            $oSeo           = new stdClass();
            $oSeo->cSeo     = $oNewsKategorie->cSeo;
            $oSeo->cKey     = 'kNewsKategorie';
            $oSeo->kKey     = $kNewsKategorie;
            $oSeo->kSprache = $oNewsKategorie->kSprache;
            Shop::DB()->insert('tseo', $oSeo);

            $cHinweis .= 'Ihre Newskategorie "' . $cName . '" wurde erfolgreich eingetragen.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben.<br />';
            $step = 'news_kategorie_erstellen';
            $smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
                   ->assign('cPostVar_arr', $_POST);
        }
    } elseif (isset($_POST['news_kategorie_loeschen']) && intval($_POST['news_kategorie_loeschen']) === 1) { // Newskategorie loeschen
        $step = 'news_uebersicht';

        if (loescheNewsKategorie($_POST['kNewsKategorie'])) {
            $cHinweis .= 'Ihre markierten Newskategorien wurden erfolgreich gel&ouml;scht.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Newskategorie.<br />';
        }
    } elseif (isset($_GET['newskategorie_editieren']) && intval($_GET['newskategorie_editieren']) === 1) { // Newskategorie editieren
        if (isset($_GET['kNewsKategorie']) && intval($_GET['kNewsKategorie']) > 0) {
            $step = 'news_kategorie_erstellen';

            $oNewsKategorie = editiereNewskategorie($_GET['kNewsKategorie'], $_SESSION['kSprache']);

            if (isset($oNewsKategorie->kNewsKategorie) && intval($oNewsKategorie->kNewsKategorie) > 0) {
                $smarty->assign('oNewsKategorie', $oNewsKategorie);
            } else {
                $step = 'news_uebersicht';
                $cFehler .= 'Fehler: Die Newskategorie mit der ID "' . $kNewsKategorie . '" konnte nicht gefunden werden.<br />';
            }
        }
    } elseif (isset($_POST['newskommentar_freischalten']) && intval($_POST['newskommentar_freischalten']) && !isset($_POST['kommentareloeschenSubmit'])) { // Kommentare freischalten
        if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
            foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                $kNewsKommentar = (int)$kNewsKommentar;
                Shop::DB()->query(
                    "UPDATE tnewskommentar
                        SET nAktiv = 1
                        WHERE kNewsKommentar = " . $kNewsKommentar, 3
                );
            }

            $cHinweis .= 'Ihre markierten Newskommentare wurden erfolgreich freigeschaltet.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newskommentar.<br />';
        }
    } elseif (isset($_POST['newskommentar_freischalten']) && isset($_POST['kommentareloeschenSubmit'])) {
        if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
            foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                Shop::DB()->delete('tnewskommentar', 'kNewsKommentar', (int)$kNewsKommentar);
            }

            $cHinweis .= 'Ihre markierten Kommentare wurden erfolgreich gel&ouml;scht.<br />';
        } else {
            $cFehler .= 'Fehler: Sie m&uuml;ssen mindestens einen Kommentar markieren.<br />';
        }
    }
    if ((isset($_GET['news_editieren']) && intval($_GET['news_editieren']) === 1) || ($continueWith !== false && $continueWith > 0)) { // News editieren
        $oNewsKategorie_arr = holeNewskategorie();
        $kNews              = ($continueWith !== false && $continueWith > 0) ? $continueWith : (int)$_GET['kNews'];

        if ($kNews > 0 && count($oNewsKategorie_arr) > 0) {
            $smarty->assign('oNewsKategorie_arr', $oNewsKategorie_arr);
            $step  = 'news_editieren';
            $oNews = Shop::DB()->query(
                "SELECT DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y %H:%i') AS Datum, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de,
                    tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, tnews.cMetaTitle,
                    tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo, tnews.cPreviewImage
                    FROM tnews
                    LEFT JOIN tseo ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
                    WHERE kNews = " . $kNews, 1
            );

            if (!empty($oNews->kNews)) {
                $oNews->kKundengruppe_arr = gibKeyArrayFuerKeyString($oNews->cKundengruppe, ';');
                // Sollen einzelne Newsbilder geloescht werden?
                if (strlen(verifyGPDataString('delpic')) > 0) {
                    if (loescheNewsBild(verifyGPDataString('delpic'), $oNews->kNews, $cUploadVerzeichnis)) {
                        $cHinweis .= 'Ihr ausgew&auml;hltes Newsbild wurde erfolgreich gel&ouml;scht.';
                    } else {
                        $cFehler .= 'Fehler: Ihr ausgew&auml;hltes Newsbild konnte nicht gel&ouml;scht werden.';
                    }
                }
                // Hole Bilder
                if (is_dir($cUploadVerzeichnis . $oNews->kNews)) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($oNews->kNews, $cUploadVerzeichnis));
                }
                // NewskategorieNews
                $oNewsKategorieNews_arr = Shop::DB()->query(
                    "SELECT DISTINCT(kNewsKategorie)
                        FROM tnewskategorienews
                        WHERE kNews = " . (int)$oNews->kNews, 2
                );
                // Newskategorie
                $oNewsKategorie_arr = Shop::DB()->query(
                    "SELECT *, DATE_FORMAT(dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
                        FROM tnewskategorie
                        WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                            AND nAktiv = 1", 2
                );
                $smarty->assign('oNewsKategorieNews_arr', $oNewsKategorieNews_arr)
                       ->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
                       ->assign('oNews', $oNews);
            }
        } else {
            $cFehler .= 'Fehler: Bitte legen Sie zuerst eine Newskategorie an.<br />';
            $step = 'news_uebersicht';
        }
    }

    // News Vorschau
    if (verifyGPCDataInteger('nd') === 1 || $step === 'news_vorschau') {
        if (verifyGPCDataInteger('kNews')) {
            $step  = 'news_vorschau';
            $kNews = verifyGPCDataInteger('kNews');
            $oNews = Shop::DB()->query(
                "SELECT DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y %H:%i') AS Datum, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de,
                    tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, tnews.cMetaTitle,
                    tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo
                    FROM tnews
                    LEFT JOIN tseo ON tseo.cKey = 'kNews'
                        AND tseo.kKey = tnews.kNews
                        AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
                    WHERE kNews = " . $kNews, 1
            );

            if ($oNews->kNews > 0) {
                $oNews->kKundengruppe_arr = gibKeyArrayFuerKeyString($oNews->cKundengruppe, ';');

                if (is_dir($cUploadVerzeichnis . $oNews->kNews)) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($oNews->kNews, $cUploadVerzeichnis));
                }
                $smarty->assign('oNews', $oNews);
                // Kommentare loeschen
                if ((isset($_POST['kommentare_loeschen']) && intval($_POST['kommentare_loeschen']) === 1) || isset($_POST['kommentareloeschenSubmit'])) {
                    if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
                        foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                            Shop::DB()->delete('tnewskommentar', 'kNewsKommentar', (int)$kNewsKommentar);
                        }

                        $cHinweis .= "Ihre markierten Kommentare wurden erfolgreich gel&ouml;scht.<br />";
                    } else {
                        $cFehler .= "Fehler: Sie m&uuml;ssen mindestens einen Kommentar markieren.<br />";
                    }
                }
                // Newskommentare
                $oNewsKommentar_arr = Shop::DB()->query(
                    "SELECT tnewskommentar.*, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
                        tkunde.kKunde, tkunde.cVorname, tkunde.cNachname
                        FROM tnewskommentar
                        JOIN tnews ON tnews.kNews = tnewskommentar.kNews
                        LEFT JOIN tkunde ON tkunde.kKunde = tnewskommentar.kKunde
                        WHERE tnewskommentar.nAktiv = 1
                            AND tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
                            AND tnewskommentar.kNews = " . (int)$oNews->kNews, 2
                );

                if (is_array($oNewsKommentar_arr) && count($oNewsKommentar_arr) > 0) {
                    foreach ($oNewsKommentar_arr as $i => $oNewsKommentar) {
                        $oKunde = new Kunde($oNewsKommentar->kKunde);

                        $oNewsKommentar_arr[$i]->cNachname = $oKunde->cNachname;
                    }
                }

                $smarty->assign('oNewsKommentar_arr', $oNewsKommentar_arr);
            }
        }
    }
    Shop::Cache()->flushTags(array(CACHING_GROUP_NEWS));
}
// Hole News aus DB
if ($step === 'news_uebersicht') {
    $oNews_arr = Shop::DB()->query(
        "SELECT tnews.*, count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahl,
            DATE_FORMAT(tnews.dErstellt, '%d.%m.%Y %H:%i') AS Datum, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de
            FROM tnews
            LEFT JOIN tnewskommentar ON tnewskommentar.kNews = tnews.kNews
            WHERE tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
            GROUP BY tnews.kNews
            ORDER BY tnews.dErstellt DESC" . $oBlaetterNaviConf->cSQL2, 2
    );
    $oNewsAnzahl = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnews
            WHERE tnews.kSprache = " . (int)$_SESSION['kSprache'], 1
    );

    if (is_array($oNews_arr) && count($oNews_arr) > 0) {
        foreach ($oNews_arr as $i => $oNews) {
            $oNews_arr[$i]->cKundengruppe_arr = array();
            $kKundengruppe_arr                = array();
            $kKundengruppe_arr                = gibKeyArrayFuerKeyString($oNews->cKundengruppe, ';');

            foreach ($kKundengruppe_arr as $kKundengruppe) {
                if ($kKundengruppe == -1) {
                    $oNews_arr[$i]->cKundengruppe_arr[] = 'Alle';
                } else {
                    $oKundengruppe = Shop::DB()->query(
                        "SELECT cName
                            FROM tkundengruppe
                            WHERE kKundengruppe = " . (int)$kKundengruppe, 1
                    );

                    if (strlen($oKundengruppe->cName) > 0) {
                        $oNews_arr[$i]->cKundengruppe_arr[] = $oKundengruppe->cName;
                    }
                }
            }
            //add row "Kategorie" to news
            $oCategorytoNews_arr = Shop::DB()->query(
                "SELECT tnewskategorie.cName
                    FROM tnewskategorie
                    LEFT JOIN tnewskategorienews ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    WHERE tnewskategorienews.kNews = {$oNews->kNews} ORDER BY tnewskategorie.nSort", 2
            );
            $Kategoriearray = array();
            foreach ($oCategorytoNews_arr as $j => $KategorieAusgabe) {
                $Kategoriearray[] = $KategorieAusgabe->cName;
            }
            $oNews_arr[$i]->KategorieAusgabe = implode(',<br />', $Kategoriearray);
            // Limit News comments on aktiv comments
            $oNewsKommentarAktiv = Shop::DB()->query(
                "SELECT count(tnewskommentar.kNewsKommentar) AS nNewsKommentarAnzahlAktiv
                    FROM tnews
                    LEFT JOIN tnewskommentar ON tnewskommentar.kNews = tnews.kNews
                    WHERE tnewskommentar.nAktiv = 1 AND tnews.kNews = {$oNews->kNews}
                    AND tnews.kSprache = " . (int)$_SESSION['kSprache'], 1
            );
            $oNews_arr[$i]->nNewsKommentarAnzahl = $oNewsKommentarAktiv->nNewsKommentarAnzahlAktiv;
        }
    }
    // Newskommentare die auf eine Freischaltung warten
    $oNewsKommentar_arr = Shop::DB()->query(
        "SELECT tnewskommentar.*, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de,
            tkunde.kKunde, tkunde.cVorname, tkunde.cNachname, tnews.cBetreff
            FROM tnewskommentar
            JOIN tnews ON tnews.kNews = tnewskommentar.kNews
            LEFT JOIN tkunde ON tkunde.kKunde = tnewskommentar.kKunde
            WHERE tnewskommentar.nAktiv=0
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'] . $oBlaetterNaviConf->cSQL1, 2
    );
    $oNewsKommentarAnzahl = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewskommentar
            JOIN tnews ON tnews.kNews = tnewskommentar.kNews
            WHERE tnewskommentar.nAktiv = 0
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'], 1
    );
    if (is_array($oNewsKommentar_arr) && count($oNewsKommentar_arr) > 0) {
        foreach ($oNewsKommentar_arr as $i => $oNewsKommentar) {
            $oKunde = new Kunde($oNewsKommentar->kKunde);

            $oNewsKommentar_arr[$i]->cNachname = $oKunde->cNachname;
        }
    }
    $smarty->assign('oNews_arr', $oNews_arr)
           ->assign('oNewsKommentar_arr', $oNewsKommentar_arr);
    // Einstellungen
    $oConfig_arr = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenSektion = " . CONF_NEWS . "
            ORDER BY nSort", 2
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
                "SELECT *
                    FROM teinstellungenconfwerte
                    WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
                    ORDER BY nSort", 2
            );
        }
        $oSetValue = Shop::DB()->query(
            "SELECT cWert
                FROM teinstellungen
                WHERE kEinstellungenSektion = " . CONF_NEWS . "
                    AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
        );
        $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
    }

    // Praefix
    if (count($Sprachen) > 0) {
        $oNewsMonatsPraefix_arr = array();
        foreach ($Sprachen as $i => $oSprache) {
            $oNewsMonatsPraefix_arr[$i]                = new stdClass();
            $oNewsMonatsPraefix_arr[$i]->kSprache      = $oSprache->kSprache;
            $oNewsMonatsPraefix_arr[$i]->cNameEnglisch = $oSprache->cNameEnglisch;
            $oNewsMonatsPraefix_arr[$i]->cNameDeutsch  = $oSprache->cNameDeutsch;
            $oNewsMonatsPraefix_arr[$i]->cISOSprache   = $oSprache->cISO;
            $oNewsMonatsPraefix                        = Shop::DB()->query(
                "SELECT cPraefix
                    FROM tnewsmonatspraefix
                    WHERE kSprache = " . (int)$oSprache->kSprache, 1
            );

            $oNewsMonatsPraefix_arr[$i]->cPraefix = (isset($oNewsMonatsPraefix->cPraefix)) ? $oNewsMonatsPraefix->cPraefix : null;
        }
        $smarty->assign('oNewsMonatsPraefix_arr', $oNewsMonatsPraefix_arr);
    }
    // Newskategorie
    $oNewsKategorie_arr = Shop::DB()->query(
        "SELECT *, DATE_FORMAT(dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
            ORDER BY nSort DESC", 2
    );
    $oNewsKatsAnzahl = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewskategorie
            WHERE kSprache = " . (int)$_SESSION['kSprache'], 1
    );
    // Baue Blaetternavigation
    $oBlaetterNaviKommentar = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $oNewsKommentarAnzahl->nAnzahl, $nAnzahlProSeite);
    $oBlaetterNaviNews      = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite2, $oNewsAnzahl->nAnzahl, $nAnzahlProSeite);
    $oBlaetterNaviKats      = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite3, $oNewsKatsAnzahl->nAnzahl, $nAnzahlProSeite);

    $smarty->assign('oConfig_arr', $oConfig_arr)
           ->assign('oNewsKategorie_arr', $oNewsKategorie_arr)
           ->assign('oBlaetterNaviKommentar', $oBlaetterNaviKommentar)
           ->assign('oBlaetterNaviNews', $oBlaetterNaviNews)
           ->assign('oBlaetterNaviKats', $oBlaetterNaviKats);
}

$oKundengruppe_arr = Shop::DB()->query(
    "SELECT kKundengruppe, cName
        FROM tkundengruppe
        ORDER BY cStandard DESC", 2
);
$smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', $Sprachen)
       ->assign('kSprache', (int)$_SESSION['kSprache'])
       ->assign('shopURL', Shop::getURL())
       ->display('news.tpl');
