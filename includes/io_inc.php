<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES  . 'class.JTL-Shop.Warenkorb.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';

$io = new IO();

$io->register('suggestions')
    ->register('pushToBasket')
    ->register('checkDependencies')
    ->register('checkVarkombiDependencies')
    ->register('generateToken')
    ->register('buildConfiguration')
    ->register('getBasketItems')
    ->register('getCategoryMenu')
    ->register('getRegionsByCountry');

/**
 * @param string $keyword
 * @return array
 */
function suggestions($keyword)
{
    global $Einstellungen, $smarty;

    $results    = array();
    $language   = Shop::getLanguage();
    $maxResults = (intval($Einstellungen['artikeluebersicht']['suche_ajax_anzahl']) > 0) ?
        intval($Einstellungen['artikeluebersicht']['suche_ajax_anzahl']) :
        10;
    if (strlen($keyword) >= 2) {
        $results = Shop::DB()->query("
          SELECT cSuche AS keyword, nAnzahlTreffer as quantity
            FROM tsuchanfrage
            WHERE SOUNDEX(cSuche) LIKE CONCAT(TRIM(TRAILING '0' FROM SOUNDEX('" . $keyword . "')), '%')
                AND nAktiv = 1
                AND kSprache = " . $language . "
            ORDER BY nAnzahlGesuche DESC, cSuche
            LIMIT " . $maxResults, 2);

        if (is_array($results) && count($results) > 0) {
            foreach ($results as &$result) {
                $result->suggestion = utf8_encode($smarty->assign('result', $result)->fetch('snippets/suggestion.tpl'));
            }
        }
    }

    return $results;
}

/**
 * @param int          $kArtikel
 * @param int|float    $anzahl
 * @param string|array $oEigenschaftwerte_arr
 * @return IOResponse
 */
function pushToBasket($kArtikel, $anzahl, $oEigenschaftwerte_arr = '')
{
    global $Einstellungen, $Kunde, $smarty;

    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Sprache.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'boxen.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $GLOBALS['oSprache'] = Sprache::getInstance();

    $kArtikel = intval($kArtikel);
    if ($anzahl > 0 && $kArtikel > 0) {
        $Artikel                             = new Artikel();
        $oArtikelOptionen                    = new stdClass();
        $oArtikelOptionen->nMerkmale         = 1;
        $oArtikelOptionen->nAttribute        = 1;
        $oArtikelOptionen->nArtikelAttribute = 1;
        $oArtikelOptionen->nDownload         = 1;
        $Artikel->fuelleArtikel($kArtikel, $oArtikelOptionen);

        // Falls der Artikel ein Variationskombikind ist, hole direkt seine Eigenschaften
        if (isset($Artikel->kEigenschaftKombi) && $Artikel->kEigenschaftKombi > 0) {
            $oEigenschaftwerte_arr = gibVarKombiEigenschaftsWerte($Artikel->kArtikel);
        }

        if (intval($anzahl) != $anzahl && $Artikel->cTeilbar !== 'Y') {
            $anzahl = max(intval($anzahl), 1);
        }

        // Prüfung
        $errors = pruefeFuegeEinInWarenkorb($Artikel, $anzahl, $oEigenschaftwerte_arr);

        if (count($errors) > 0) {
            $localizedErrors = baueArtikelhinweise($errors, true, $Artikel, $anzahl);

            $oResponse->nType  = 0;
            $oResponse->cLabel = Shop::Lang()->get('basket', 'global');
            $oResponse->cHints = utf8_convert_recursive($localizedErrors);
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
        $_SESSION['Warenkorb']->fuegeEin($kArtikel, $anzahl, $oEigenschaftwerte_arr)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);

        unset($_SESSION['VersandKupon']);
        unset($_SESSION['NeukundenKupon']);
        unset($_SESSION['Versandart']);
        unset($_SESSION['Zahlungsart']);
        unset($_SESSION['TrustedShops']);
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb,
        // dann verwerfen und neu anlegen
        altenKuponNeuBerechnen();

        setzeLinks();
        // Persistenter Warenkorb
        if (!isset($_POST['login'])) {
            fuegeEinInWarenkorbPers($kArtikel, $anzahl, $oEigenschaftwerte_arr);
        }
        $boxes         = Boxen::getInstance();
        $pageType      = (Shop::getPageType() !== null) ? Shop::getPageType() : PAGE_UNBEKANNT;
        $boxesToShow   = $boxes->build($pageType, true)->render();
        $warensumme[0] = gibPreisStringLocalized($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), true));
        $warensumme[1] = gibPreisStringLocalized($_SESSION['Warenkorb']->gibGesamtsummeWarenExt(array(C_WARENKORBPOS_TYP_ARTIKEL), false));
        $smarty->assign('Boxen', $boxesToShow)
               ->assign('WarenkorbWarensumme', $warensumme);

        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
            $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        }
        $oXSelling = gibArtikelXSelling($kArtikel);

        $smarty->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString(gibVersandkostenfreiAb($kKundengruppe), $_SESSION['Warenkorb']->gibGesamtsummeWaren(true, true)))
               ->assign('WarenkorbVersandkostenfreiLaenderHinweis', baueVersandkostenfreiLaenderString(gibVersandkostenfreiAb($kKundengruppe)))
               ->assign('zuletztInWarenkorbGelegterArtikel', $Artikel)
               ->assign('fAnzahl', $anzahl)
               ->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise)
               ->assign('Einstellungen', $Einstellungen)
               ->assign('Xselling', $oXSelling);

        $oResponse->nType           = 2;
        $oResponse->cWarenkorbText  = utf8_encode(lang_warenkorb_warenkorbEnthaeltXArtikel($_SESSION['Warenkorb']));
        $oResponse->cWarenkorbLabel = utf8_encode(lang_warenkorb_warenkorbLabel($_SESSION['Warenkorb']));
        $oResponse->cPopup          = utf8_encode($smarty->fetch('productdetails/pushed.tpl'));
        $oResponse->cWarenkorbMini  = utf8_encode($smarty->fetch('basket/cart_dropdown.tpl'));
        $oResponse->oArtikel        = utf8_convert_recursive($Artikel, true);
        $oResponse->cNotification   = utf8_encode(Shop::Lang()->get('basketAllAdded', 'messages'));

        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');
        // Kampagne
        if (isset($_SESSION['Kampagnenbesucher'])) {
            setzeKampagnenVorgang(KAMPAGNE_DEF_WARENKORB, $kArtikel, $anzahl); // Warenkorb
        }

        if ($GLOBALS['GlobaleEinstellungen']['global']['global_warenkorb_weiterleitung'] === 'Y') {
            $oResponse->nType     = 1;
            $oResponse->cLocation = 'warenkorb.php';
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
    }

    return $objResponse;
}

/**
 * @param int $nTyp - 0 = Template, 1 = Object
 * @return IOResponse
 */
function getBasketItems($nTyp)
{
    global $Einstellungen, $smarty;

    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $GLOBALS['oSprache'] = Sprache::getInstance();

    switch (intval($nTyp)) {
        default:
        case 0:
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
            $nAnzahl       = $_SESSION['Warenkorb']->gibAnzahlPositionenExt(array(C_WARENKORBPOS_TYP_ARTIKEL));
            $cLand         = isset($_SESSION['cLieferlandISO']) ? $_SESSION['cLieferlandISO'] : '';
            $cPLZ          = '*';

            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
                $cLand         = $_SESSION['Kunde']->cLand;
                $cPLZ          = $_SESSION['Kunde']->cPLZ;
            }

            $versandkostenfreiAb = gibVersandkostenfreiAb($kKundengruppe, $cLand);

            $smarty->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
                ->assign('Warensumme', $_SESSION['Warenkorb']->gibGesamtsummeWaren())
                ->assign('Steuerpositionen', $_SESSION['Warenkorb']->gibSteuerpositionen())
                ->assign('Einstellungen', $Einstellungen)
                ->assign('WarenkorbArtikelPositionenanzahl', $nAnzahl)
                ->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise)
                ->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString($versandkostenfreiAb, $_SESSION['Warenkorb']->gibGesamtsummeWaren(true, true)))
                ->assign('WarenkorbVersandkostenfreiLaenderHinweis', baueVersandkostenfreiLaenderString($versandkostenfreiAb));

            VersandartHelper::getShippingCosts($cLand, $cPLZ, $error);
            $oResponse->cTemplate = utf8_encode($smarty->fetch('basket/cart_dropdown_label.tpl'));
            break;

        case 1:
            $oResponse->cItems = utf8_convert_recursive($_SESSION['Warenkorb']->PositionenArr);
            break;
    }

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param array $aValues
 * @return IOResponse
 */
function buildConfiguration($aValues)
{
    global $smarty;

    $oResponse = new IOResponse();

    $articleId = isset($aValues['VariKindArtikel'])
        ? intval($aValues['VariKindArtikel'])
        : intval($aValues['a']);

    $items           = isset($aValues['item']) ? $aValues['item'] : array();
    $quantities      = isset($aValues['quantity']) ? $aValues['quantity'] : array();
    $variationValues = isset($aValues['eigenschaftwert']) ? $aValues['eigenschaftwert'] : array();

    $oKonfig = buildConfig($articleId, $aValues['anzahl'], $variationValues, $items, $quantities, array());

    $smarty->assign('oKonfig', $oKonfig);
    $oKonfig->cTemplate = utf8_encode(
        $smarty->fetch('productdetails/config_summary.tpl')
    );

    $oResponse->script('this.response = ' . json_encode($oKonfig) . ';');

    return $oResponse;
}

/**
 * @param int   $kArtikel
 * @param array $kEigenschaftWert_arr
 * @return null|object
 */
function getArticleStockInfo($kArtikel, $kEigenschaftWert_arr)
{
    $oTMPArtikel = getArticleByVariations($kArtikel, $kEigenschaftWert_arr);

    if (isset($oTMPArtikel->kArtikel) && $oTMPArtikel->kArtikel > 0) {
        $oTestArtikel                                = new Artikel();
        $oArtikelOptionen                            = new stdClass();
        $oArtikelOptionen->nMain                     = 0;
        $oArtikelOptionen->nWarenlager               = 0;
        $oArtikelOptionen->nVariationKombi           = 0;
        $oArtikelOptionen->nKeinLagerbestandBeachten = 1;

        $oTestArtikel->fuelleArtikel($oTMPArtikel->kArtikel, $oArtikelOptionen, Kundengruppe::getCurrent(), $_SESSION['kSprache']);
        $oTestArtikel->Lageranzeige->AmpelText = utf8_encode($oTestArtikel->Lageranzeige->AmpelText);

        return (object) [
            'stock'  => $oTestArtikel->aufLagerSichtbarkeit(),
            'status' => $oTestArtikel->Lageranzeige->nStatus,
            'text'   => $oTestArtikel->Lageranzeige->AmpelText
        ];
    }

    return;
}

/**
 * @param array $aValues
 * @return IOResponse
 */
function checkDependencies($aValues)
{
    $objResponse   = new IOResponse();
    $kVaterArtikel = intval($aValues['a']);
    $fAnzahl       = floatval($aValues['anzahl']);
    $valueID_arr   = array_filter((array) $aValues['eigenschaftwert']);

    if ($kVaterArtikel > 0) {
        $oArtikelOptionen                            = new stdClass();
        $oArtikelOptionen->nMerkmale                 = 0;
        $oArtikelOptionen->nAttribute                = 0;
        $oArtikelOptionen->nArtikelAttribute         = 0;
        $oArtikelOptionen->nMedienDatei              = 0;
        $oArtikelOptionen->nVariationKombi           = 1;
        $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
        $oArtikelOptionen->nKonfig                   = 0;
        $oArtikelOptionen->nDownload                 = 0;
        $oArtikelOptionen->nMain                     = 1;
        $oArtikelOptionen->nWarenlager               = 1;
        $oArtikel                                    = new Artikel();
        $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen, Kundengruppe::getCurrent(), $_SESSION['kSprache']);
        $weightDiff = 0;
        foreach ($valueID_arr as $valueID) {
            $currentValue  = new EigenschaftWert($valueID);
            $weightDiff   += $currentValue->fGewichtDiff;
        }
        $weightTotal      = Trennzeichen::getUnit(JTLSEPARATER_WEIGHT, $_SESSION['kSprache'], $oArtikel->fGewicht + $weightDiff);
        $cUnitWeightLabel = Shop::Lang()->get('weightUnit', 'global');

        // Alle Variationen ohne Freifeld
        $nKeyValueVariation_arr = $oArtikel->keyValueVariations($oArtikel->VariationenOhneFreifeld);

        // Freifeldpositionen gesondert zwischenspeichern
        foreach ($valueID_arr as $kKey => $cVal) {
            if (!isset($nKeyValueVariation_arr[$kKey])) {
                unset($valueID_arr[$kKey]);
                $kFreifeldEigeschaftWert_arr[$kKey] = $cVal;
            }
        }

        $nNettoPreise = $_SESSION['Kundengruppe']->nNettoPreise;
        $fVKNetto     = $oArtikel->gibPreis($fAnzahl, $valueID_arr, Kundengruppe::getCurrent());

        $fVK = [
            berechneBrutto($fVKNetto, $_SESSION['Steuersatz'][$oArtikel->kSteuerklasse]),
            $fVKNetto
        ];

        $cVKLocalized = [
            0 => gibPreisStringLocalized($fVK[0]),
            1 => gibPreisStringLocalized($fVK[1])
        ];

        $cPriceLabel = $oArtikel->nVariationOhneFreifeldAnzahl === count($valueID_arr) ? Shop::Lang()->get('priceAsConfigured', 'productDetails') : Shop::Lang()->get('priceStarting', 'global');

        $objResponse->jsfunc('$.evo.article().setPrice', $fVK[$nNettoPreise], $cVKLocalized[$nNettoPreise], $cPriceLabel);
        $objResponse->jsfunc('$.evo.article().setUnitWeight', $oArtikel->fGewicht, $weightTotal . ' ' . $cUnitWeightLabel);
    }

    return $objResponse;
}

/**
 * @param array      $aValues
 * @param int        $kEigenschaft
 * @param int        $kEigenschaftWert
 * @return IOResponse
 */
function checkVarkombiDependencies($aValues, $kEigenschaft = 0, $kEigenschaftWert = 0)
{
    $oArtikel                    = null;
    $objResponse                 = new IOResponse();
    $kVaterArtikel               = intval($aValues['a']);
    $kArtikelKind                = isset($aValues['VariKindArtikel']) ? intval($aValues['VariKindArtikel']) : 0;
    $kFreifeldEigeschaftWert_arr = array();
    $kGesetzteEigeschaftWert_arr = array_filter((array) $aValues['eigenschaftwert']);

    if ($kVaterArtikel > 0) {
        $oArtikelOptionen                            = new stdClass();
        $oArtikelOptionen->nMerkmale                 = 0;
        $oArtikelOptionen->nAttribute                = 0;
        $oArtikelOptionen->nArtikelAttribute         = 0;
        $oArtikelOptionen->nMedienDatei              = 0;
        $oArtikelOptionen->nVariationKombi           = 1;
        $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
        $oArtikelOptionen->nKonfig                   = 0;
        $oArtikelOptionen->nDownload                 = 0;
        $oArtikelOptionen->nMain                     = 1;
        $oArtikelOptionen->nWarenlager               = 1;
        $oArtikel                                    = new Artikel();
        $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen, Kundengruppe::getCurrent(), $_SESSION['kSprache']);

        // Alle Variationen ohne Freifeld
        $nKeyValueVariation_arr = $oArtikel->keyValueVariations($oArtikel->VariationenOhneFreifeld);

        // Freifeldpositionen gesondert zwischenspeichern
        foreach ($kGesetzteEigeschaftWert_arr as $kKey => $cVal) {
            if (!isset($nKeyValueVariation_arr[$kKey])) {
                unset($kGesetzteEigeschaftWert_arr[$kKey]);
                $kFreifeldEigeschaftWert_arr[$kKey] = $cVal;
            }
        }

        $bHasInvalidSelection = false;
        $nInvalidVariations   = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, true);

        foreach ($kGesetzteEigeschaftWert_arr as $kKey => $kValue) {
            if (isset($nInvalidVariations[$kKey]) && in_array($kValue, $nInvalidVariations[$kKey])) {
                $bHasInvalidSelection = true;
                break;
            }
        }

        // Auswahl zurücksetzen sobald eine nicht vorhandene Variation ausgewählt wurde.
        if ($bHasInvalidSelection) {
            $objResponse->jsfunc('$.evo.article().variationResetAll');

            $kGesetzteEigeschaftWert_arr = array($kEigenschaft => $kEigenschaftWert);
            $nInvalidVariations          = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, true);

            // Auswählter EigenschaftWert ist ebenfalls nicht vorhanden
            if (in_array($kEigenschaftWert, $nInvalidVariations[$kEigenschaft])) {
                $kGesetzteEigeschaftWert_arr = [];

                // Wir befinden uns im Kind-Artikel -> Weiterleitung auf Vater-Artikel
                if ($kArtikelKind > 0) {
                    $objResponse->jsfunc('$.evo.article().setArticleContent', $oArtikel->Artikel, 0, $oArtikel->cURL, []);

                    return $objResponse;
                }
            }
        }

        // Alle EigenschaftWerte vorhanden, Kind-Artikel ermitteln
        if (count($kGesetzteEigeschaftWert_arr) >= $oArtikel->nVariationOhneFreifeldAnzahl) {
            $oArtikelTMP = getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWert_arr);

            if ($kArtikelKind != $oArtikelTMP->kArtikel) {
                $oGesetzteEigeschaftWerte_arr = [];
                foreach ($kFreifeldEigeschaftWert_arr as $cKey => $cValue) {
                    $oGesetzteEigeschaftWerte_arr[] = (object) [
                        'key'   => $cKey,
                        'value' => $cValue
                    ];
                }
                $cUrl = baueURL($oArtikelTMP, URLART_ARTIKEL, 0, false, true);
                $objResponse->jsfunc('$.evo.article().setArticleContent', $kVaterArtikel, $oArtikelTMP->kArtikel, $cUrl, $oGesetzteEigeschaftWerte_arr);

                executeHook(HOOK_TOOLSAJAXSERVER_PAGE_TAUSCHEVARIATIONKOMBI, array('objResponse' => &$objResponse, 'oArtikel' => &$oArtikel, 'bIO' => true));

                return $objResponse;
            }
        }

        $objResponse->jsfunc('$.evo.article().variationDisableAll');

        $nPossibleVariations = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, false);
        //$nInvalidVariations  = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, true);

        foreach ($nPossibleVariations as $k => $values) {
            foreach ($values as $v) {
                $objResponse->jsfunc('$.evo.article().variationEnable', $k, $v);
            }
        }

        foreach ($kGesetzteEigeschaftWert_arr as $key => $value) {
            $escaped = addslashes($value);
            $objResponse->jsfunc('$.evo.article().variationActive', $key, $escaped);
        }

        foreach ($nInvalidVariations as $k => $values) {
            foreach ($values as $v) {
                $text = utf8_encode(Shop::Lang()->get('notAvailableInSelection'));
                $objResponse->jsfunc('$.evo.article().variationInfo', $v, -1, $text);
            }
        }

        $kNichtGesetzteEigenschaft_arr = array_values(array_diff(array_keys($nKeyValueVariation_arr), array_keys($kGesetzteEigeschaftWert_arr)));

        if (count($kNichtGesetzteEigenschaft_arr) <= 1) {
            foreach ($nKeyValueVariation_arr as $kEigenschaft => $kEigenschaftWert) {
                $kVerfuegbareEigenschaftWert_arr = $nKeyValueVariation_arr[$kEigenschaft];
                $kMoeglicheEigeschaftWert_arr    = $kGesetzteEigeschaftWert_arr;

                foreach ($kVerfuegbareEigenschaftWert_arr as $kVerfuegbareEigenschaftWert) {
                    $kMoeglicheEigeschaftWert_arr[$kEigenschaft] = $kVerfuegbareEigenschaftWert;
                    $oKindArtikel                                = getArticleStockInfo($kVaterArtikel, $kMoeglicheEigeschaftWert_arr);

                    if ($oKindArtikel !== null && $oKindArtikel->status == 0) {
                        if (!in_array($kVerfuegbareEigenschaftWert, $kGesetzteEigeschaftWert_arr)) {
                            $objResponse->jsfunc('$.evo.article().variationInfo', $kVerfuegbareEigenschaftWert, $oKindArtikel->status, $oKindArtikel->text);
                        }
                    }
                }
            }
        }
    } else {
        $objResponse->jsfunc('$.evo.error', 'Article not found', $kVaterArtikel);
    }

    return $objResponse;
}

/**
 * @param int   $kArtikel
 * @param array $kVariationKombi_arr
 * @return mixed
 */
function getArticleByVariations($kArtikel, $kVariationKombi_arr)
{
    $cSQL1 = '';
    $cSQL2 = '';
    if (is_array($kVariationKombi_arr) && count($kVariationKombi_arr) > 0) {
        $j = 0;
        foreach ($kVariationKombi_arr as $i => $kVariationKombi) {
            if ($j > 0) {
                $cSQL1 .= ',' . $i;
                $cSQL2 .= ',' . $kVariationKombi;
            } else {
                $cSQL1 .= $i;
                $cSQL2 .= $kVariationKombi;
            }

            $j++;
        }
    }

    $kSprache    = Shop::getLanguage();
    $oArtikelTMP = Shop::DB()->query(
        "SELECT a.kArtikel, IF (tseo.cSeo IS NULL, a.cSeo, tseo.cSeo) AS cSeo, a.fLagerbestand, a.cLagerBeachten, a.cLagerKleinerNull
            FROM teigenschaftkombiwert
            JOIN tartikel a ON a.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
            LEFT JOIN tseo ON tseo.cKey = 'kArtikel' AND tseo.kKey = a.kArtikel AND tseo.kSprache = " . $kSprache .  "
            LEFT JOIN tartikelsichtbarkeit ON a.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . $_SESSION['Kundengruppe']->kKundengruppe . "
        WHERE teigenschaftkombiwert.kEigenschaft IN (" . $cSQL1 . ")
            AND teigenschaftkombiwert.kEigenschaftWert IN (" . $cSQL2 . ")
            AND tartikelsichtbarkeit.kArtikel IS NULL
            AND a.kVaterArtikel = " . $kArtikel . "
        GROUP BY a.kArtikel
        HAVING count(*) = " . count($kVariationKombi_arr), 1
    );

    return $oArtikelTMP;
}

/**
 * @return IOResponse
 */
function generateToken()
{
    $objResponse             = new IOResponse();
    $cToken                  = gibToken();
    $cName                   = gibTokenName();
    $token_arr               = array('name' => $cName, 'token' => $cToken);
    $_SESSION['xcrsf_token'] = json_encode($token_arr);
    $objResponse->script("doXcsrfToken('" . $cName . "', '" . $cToken . "');");

    return $objResponse;
}

/**
 * @param int $categoryId
 * @return IOResponse
 */
function getCategoryMenu($categoryId)
{
    global $smarty;

    $categoryId = (int) $categoryId;
    $auto       = $categoryId === 0;

    if ($auto) {
        $categoryId = Shop::$kKategorie;
    }

    $response   = new IOResponse();
    $list       = new KategorieListe();
    $category   = new Kategorie($categoryId);
    $categories = $list->holUnterkategorien($category->kKategorie, 0, 0);

    if ($auto && count($categories) === 0) {
        $category   = new Kategorie($category->kOberKategorie);
        $categories = $list->holUnterkategorien($category->kKategorie, 0, 0);
    }

    $result = (object) ['current' => $category, 'items' => $categories];

    $smarty->assign('result', $result)
           ->assign('nSeitenTyp', 0);
    $template = utf8_encode($smarty->fetch('snippets/categories_offcanvas.tpl'));

    $response->script('this.response = ' . json_encode($template) . ';');

    return $response;
}

/**
 * @param string $country
 * @return IOResponse
 */
function getRegionsByCountry($country)
{
    $response = new IOResponse();

    if (strlen($country) == 2) {
        $regions = Staat::getRegions($country);
        $regions = utf8_convert_recursive($regions);
        $response->script("this.response = " . json_encode($regions) . ";");
    }

    return $response;
}
