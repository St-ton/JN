<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    /**
     * @param string    $cKey
     * @param int       $kKey
     * @param int       $kSprache
     * @param JTLSmarty $smarty
     * @param array     $Einstellungen
     * @return bool
     */
    function starteAuswahlAssistent($cKey, $kKey, $kSprache, &$smarty, $Einstellungen)
    {
        $kMerkmalWert           = null;
        $kAuswahlAssistentFrage = null;
        $nFrage                 = null;
        $kKategorie             = null;
        if (class_exists('AuswahlAssistent') && $Einstellungen['auswahlassistent_nutzen'] === 'Y') {
            // Work Around falls schon einmal der Auswahlassistent durchlaufen wurde
            if (isset($GLOBALS['NaviFilter']) && function_exists('gibAnzahlFilter')) {
                if (gibAnzahlFilter($GLOBALS['NaviFilter']) > 0) {
                    return false;
                }
            }
            if (strlen($cKey) > 0 && intval($kKey) > 0 && intval($kSprache) > 0) {
                $Einstellungen = Shop::getSettings(array(CONF_GLOBAL, CONF_RSS, CONF_ARTIKELUEBERSICHT, CONF_AUSWAHLASSISTENT));

                if (isset($_GET['aaParams']) && strlen($_GET['aaParams']) > 0) {
                    // a href geklickt
                    extract(extractAAURL($_GET['aaParams']));
                    setSelectionWizardAnswer($kMerkmalWert, $kAuswahlAssistentFrage, $nFrage, $kKategorie);
                } elseif (isset($_POST['aaParams']) && intval($_POST['aaParams']) === 1) {
                    // Selectbox geklickt
                    $kMerkmalWert           = StringHandler::filterXSS($_POST['kMerkmalWert']);
                    $kAuswahlAssistentFrage = StringHandler::filterXSS($_POST['kAuswahlAssistentFrage']);
                    $nFrage                 = StringHandler::filterXSS($_POST['nFrage']);
                    $kKategorie             = StringHandler::filterXSS($_POST['kKategorie']);
                    setSelectionWizardAnswer($kMerkmalWert, $kAuswahlAssistentFrage, $nFrage, $kKategorie);
                } elseif (isset($_GET['aaReset']) && strlen($_GET['aaReset']) > 0) {
                    // Antwort resetten
                    extract(extractAAURL($_GET['aaReset']));
                    resetSelectionWizard($nFrage, $kKategorie);
                } else {
                    unset($_SESSION['AuswahlAssistent']);
                    $oAuswahlAssistent = AuswahlAssistent::getGroupsByLocation($cKey, $kKey, $kSprache);
                    if (!isset($_SESSION['AuswahlAssistent']) || !is_object($_SESSION['AuswahlAssistent'])) {
                        $_SESSION['AuswahlAssistent'] = new stdClass();
                    }
                    $_SESSION['AuswahlAssistent']->nFrage               = 0;
                    $_SESSION['AuswahlAssistent']->oAuswahl_arr         = array();
                    $_SESSION['AuswahlAssistent']->kMerkmalGesetzt_arr  = array();
                    $_SESSION['AuswahlAssistent']->oAuswahlAssistent    = $oAuswahlAssistent;
                    $_SESSION['AuswahlAssistent']->oAuswahlAssistentOrt = AuswahlAssistentOrt::getLocation($cKey, $kKey, $_SESSION['kSprache']);
                    if ($_SESSION['AuswahlAssistent']->oAuswahlAssistentOrt !== null) {
                        if (!isset($bMerkmalFilterVorhanden)) {
                            $bMerkmalFilterVorhanden = null;
                        }
                        if ($cKey == AUSWAHLASSISTENT_ORT_KATEGORIE && intval($kKey) > 0) {
                            filterSelectionWizard($GLOBALS['oSuchergebnisse']->MerkmalFilter, $bMerkmalFilterVorhanden);
                        } else {
                            require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';

                            $bMerkmalFilterVorhanden = false;
                            if (!isset($NaviFilter)) {
                                $NaviFilter = new stdClass();
                            }
                            if (!isset($FilterSQL)) {
                                $FilterSQL = bauFilterSQL($NaviFilter);
                            }
                            if (!isset($AktuelleKategorie)) {
                                $AktuelleKategorie = null;
                            }
                            if (!isset($oSuchergebnisse)) {
                                $oSuchergebnisse = new stdClass();
                            }
                            $oSuchergebnisse->MerkmalFilter = gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $AktuelleKategorie, true);
                            filterSelectionWizard($oSuchergebnisse->MerkmalFilter, $bMerkmalFilterVorhanden);
                        }
                    }
                }
                $cRequestURI = $_SERVER['REQUEST_URI'];
                if (strpos($cRequestURI, '?') !== false) {
                    $cRequestURI .= '&';
                } else {
                    $cRequestURI .= '?';
                }
                $smarty->assign('oAuswahlAssistent', $_SESSION['AuswahlAssistent']->oAuswahlAssistent)
                       ->assign('Einstellungen', $Einstellungen)
                       ->assign('cRequestURI', $cRequestURI);
            }
        }
    }

    /**
     * @param int    $kKategorie
     * @param object $NaviFilter
     * @param object $FilterSQL
     * @param object $oSuchergebnisse
     * @param int    $nArtikelProSeite
     * @param int    $nLimitN
     */
    function baueFilterSelectionWizard($kKategorie, &$NaviFilter, &$FilterSQL, &$oSuchergebnisse, &$nArtikelProSeite, &$nLimitN)
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
        if (isset($_SESSION['AuswahlAssistent']->oAuswahl_arr)) {
            foreach ($_SESSION['AuswahlAssistent']->oAuswahl_arr as $i => $oAuswahl) {
                $_POST['mf' . ($i + 1)] = $oAuswahl->kMerkmalWert;
            }
        }
        $kKategorie     = (int)$kKategorie;
        $cParameter_arr = array();
        if ($kKategorie > 0) {
            $cParameter_arr['kKategorie'] = $kKategorie;
        } else {
            $cParameter_arr['kMerkmalWert'] = (isset($_SESSION['AuswahlAssistent']->oAuswahl_arr[0]->kMerkmalWert)) ?
                $_SESSION['AuswahlAssistent']->oAuswahl_arr[0]->kMerkmalWert :
                null;
        }
        if (!isset($NaviFilter)) {
            $NaviFilter = new stdClass();
        }
        if (!isset($FilterSQL)) {
            $FilterSQL = new stdClass();
        }
        if (!isset($oSuchergebnisse)) {
            $oSuchergebnisse = new stdClass();
        }
        $NaviFilter->oSprache_arr            = new stdClass();
        $NaviFilter->oSprache_arr            = $_SESSION['Sprachen'];
        $cParameter_arr['MerkmalFilter_arr'] = setzeMerkmalFilter();
        $NaviFilter                          = Shop::buildNaviFilter($cParameter_arr, $NaviFilter);
        $FilterSQL->oMerkmalFilterSQL        = gibMerkmalFilterSQL($NaviFilter);
        $FilterSQL->oKategorieFilterSQL      = gibKategorieFilterSQL($NaviFilter);
        $AktuelleKategorie                   = new Kategorie($kKategorie);
        $oSuchergebnisse->MerkmalFilter      = gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, $AktuelleKategorie, true);

        $nLimitN = ($NaviFilter->nSeite - 1) * $nArtikelProSeite;
    }

    /**
     * @param array $oMerkmalFilter_arr
     * @param bool  $bMerkmalFilterVorhanden
     */
    function filterSelectionWizard($oMerkmalFilter_arr, &$bMerkmalFilterVorhanden)
    {
        // Naechste Antwortmoeglichkeiten in Abhaengigkeit der vorher ausgewaehlten
        foreach ($oMerkmalFilter_arr as $MerkmalFilter) {
            if (!isset($bFragenEnde)) {
                $bFragenEnde = false;
            }
            if (!$bFragenEnde && isset($_SESSION['AuswahlAssistent']->kMerkmalGesetzt_arr) && !in_array($MerkmalFilter->kMerkmal, $_SESSION['AuswahlAssistent']->kMerkmalGesetzt_arr) &&
                isset($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->kMerkmal) &&
                $MerkmalFilter->kMerkmal == $_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->kMerkmal
            ) {
                if (isset($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr)) {
                    $kMerkmalWertDrin_arr = array();
                    foreach ($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr as $i => $oMerkmalWertAlle) {
                        foreach ($MerkmalFilter->oMerkmalWerte_arr as $oMerkmalWertMoeglich) {
                            if ($oMerkmalWertMoeglich->kMerkmalWert == $oMerkmalWertAlle->kMerkmalWert) {
                                $_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr[$i]->nAnzahl = $oMerkmalWertMoeglich->nAnzahl;
                                $kMerkmalWertDrin_arr[]                                                                                                                                      = $oMerkmalWertMoeglich->kMerkmalWert;
                            }
                        }
                    }

                    foreach ($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr as $i => $oMerkmalWertAlle) {
                        if (!in_array($oMerkmalWertAlle->kMerkmalWert, $kMerkmalWertDrin_arr)) {
                            unset($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr[$i]);
                        }
                    }

                    $_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr =
                        array_merge($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr);

                    if (count($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$_SESSION['AuswahlAssistent']->nFrage]->oMerkmal->oMerkmalWert_arr) > 0) {
                        $bMerkmalFilterVorhanden = true;
                    }
                }
            }
        }
    }

    /**
     * @param int    $kMerkmalWert
     * @param int    $nFrage
     * @param int    $kKategorie
     * @param bool   $bFragenEnde
     * @param object $oSuchergebnisse
     * @param object $NaviFilter
     * @param bool   $bMerkmalFilterVorhanden
     */
    function processSelectionWizard($kMerkmalWert, $nFrage, $kKategorie, &$bFragenEnde, &$oSuchergebnisse, &$NaviFilter, &$bMerkmalFilterVorhanden)
    {
        if (isset($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$nFrage]->oMerkmal->oMerkmalWert_arr)) {
            foreach ($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$nFrage]->oMerkmal->oMerkmalWert_arr as $oMerkmalWert) {
                if ($oMerkmalWert->kMerkmalWert == $kMerkmalWert) {
                    $_SESSION['AuswahlAssistent']->oAuswahl_arr[$nFrage] = $oMerkmalWert;
                }

                if (!in_array($oMerkmalWert->kMerkmal, $_SESSION['AuswahlAssistent']->kMerkmalGesetzt_arr)) {
                    $_SESSION['AuswahlAssistent']->kMerkmalGesetzt_arr[$nFrage] = $oMerkmalWert->kMerkmal;
                }
            }
        }
        if (isset($_SESSION['AuswahlAssistent'])) {
            $_SESSION['AuswahlAssistent']->nFrage = $nFrage + 1;
        }
        if (!isset($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr) ||
            count($_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr) == $_SESSION['AuswahlAssistent']->nFrage) {
            $bFragenEnde = true;
        }
        // Filter
        $FilterSQL        = null;
        $nArtikelProSeite = (isset($_SESSION['ArtikelProSeite'])) ? (int)$_SESSION['ArtikelProSeite'] : 0;
        if ($nArtikelProSeite === 0) {
            $nArtikelProSeite = 20;
        }
        $nLimitN = null;
        baueFilterSelectionWizard($kKategorie, $NaviFilter, $FilterSQL, $oSuchergebnisse, $nArtikelProSeite, $nLimitN);
        filterSelectionWizard($oSuchergebnisse->MerkmalFilter, $bMerkmalFilterVorhanden);
        // Artikelanzahl nach Filterung
        baueArtikelAnzahl($FilterSQL, $oSuchergebnisse, $nArtikelProSeite, $nLimitN);
    }

    /**
     * @param int $kMerkmalWert
     * @param int $kAuswahlAssistentFrage
     * @param int $nFrage
     * @param int $kKategorie
     */
    function setSelectionWizardAnswer($kMerkmalWert, $kAuswahlAssistentFrage, $nFrage, $kKategorie)
    {
        global $smarty;

        $bMerkmalFilterVorhanden = false;
        $bFragenEnde             = false;
        $oSuchergebnisse         = null;
        $NaviFilter              = null;
        processSelectionWizard($kMerkmalWert, $nFrage, $kKategorie, $bFragenEnde, $oSuchergebnisse, $NaviFilter, $bMerkmalFilterVorhanden);

        if (!$bFragenEnde && $bMerkmalFilterVorhanden && $oSuchergebnisse->GesamtanzahlArtikel > 1) {
            $smarty->assign('NaviFilter', $NaviFilter);
        } elseif (!$bFragenEnde || $oSuchergebnisse->GesamtanzahlArtikel == 1 || !$bMerkmalFilterVorhanden) { // Abbruch
            if (!$kKategorie) {
                unset($_POST['mf1']);
            }
            $cParameter_arr['MerkmalFilter_arr'] = setzeMerkmalFilter();
            $NaviFilter                          = Shop::buildNaviFilter($cParameter_arr);
            header('Location: ' . StringHandler::htmlentitydecode(gibNaviURL($NaviFilter, true, null)));
            exit();
        }
    }

    /**
     * @param int $nFrage
     * @param int $kKategorie
     */
    function resetSelectionWizard($nFrage, $kKategorie)
    {
        global $smarty, $bMerkmalFilterVorhanden;

        $_SESSION['AuswahlAssistent']->nFrage            = $nFrage;
        $_SESSION['AuswahlAssistent']->oAuswahlAssistent = AuswahlAssistent::getGroupsByLocation(
            $_SESSION['AuswahlAssistent']->oAuswahlAssistentOrt->cKey,
            $_SESSION['AuswahlAssistent']->oAuswahlAssistentOrt->kKey,
            $_SESSION['kSprache']
        );
        // Bereits ausgewaehlte Antworten loeschen
        foreach ($_SESSION['AuswahlAssistent']->oAuswahl_arr as $i => $oAuswahl) {
            if ($i >= $nFrage) {
                unset($_SESSION['AuswahlAssistent']->oAuswahl_arr[$i]);
                unset($_SESSION['AuswahlAssistent']->kMerkmalGesetzt_arr[$i]);
            }
        }
        // Filter
        $NaviFilter       = null;
        $FilterSQL        = null;
        $oSuchergebnisse  = null;
        $nArtikelProSeite = null;
        $nLimitN          = null;
        baueFilterSelectionWizard($kKategorie, $NaviFilter, $FilterSQL, $oSuchergebnisse, $nArtikelProSeite, $nLimitN);
        filterSelectionWizard($oSuchergebnisse->MerkmalFilter, $bMerkmalFilterVorhanden);

        $smarty->assign('NaviFilter', $NaviFilter);
    }

    /**
     * @param string $aaParams
     * @return array
     */
    function extractAAURL($aaParams)
    {
        $cParams         = base64_decode($aaParams);
        $cParams_arr     = explode(';', $cParams);
        $cParamAssoc_arr = array();

        if (count($cParams_arr) > 1) {
            foreach ($cParams_arr as $cParams) {
                if (strlen($cParams) > 0) {
                    $cParamTMP_arr                      = explode('=', $cParams);
                    $cParamAssoc_arr[$cParamTMP_arr[0]] = $cParamTMP_arr[1];
                }
            }
        }

        return $cParamAssoc_arr;
    }
}
