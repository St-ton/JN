<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';

$io = IO::getInstance();

$io->register('suggestions')
   ->register('pushToBasket')
   ->register('pushToComparelist')
   ->register('removeFromComparelist')
   ->register('pushToWishlist')
   ->register('removeFromWishlist')
   ->register('checkDependencies')
   ->register('checkVarkombiDependencies')
   ->register('generateToken')
   ->register('buildConfiguration')
   ->register('getBasketItems')
   ->register('getCategoryMenu')
   ->register('getRegionsByCountry')
   ->register('setSelectionWizardAnswers')
   ->register('getCitiesByZip');

/**
 * @param string $keyword
 * @return array
 */
function suggestions($keyword)
{
    $results = [];
    if (strlen($keyword) < 2) {
        return $results;
    }
    $Einstellungen = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);
    $smarty        = Shop::Smarty();
    $language      = Shop::getLanguage();
    $maxResults    = ((int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl'] > 0)
        ? (int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl']
        : 10;
    $results       = Shop::Container()->getDB()->queryPrepared(
        "SELECT cSuche AS keyword, nAnzahlTreffer AS quantity
          FROM tsuchanfrage
          WHERE SOUNDEX(cSuche) LIKE CONCAT(TRIM(TRAILING '0' FROM SOUNDEX(:keyword)), '%')
              AND nAktiv = 1
              AND kSprache = :lang
        ORDER BY CASE
            WHEN cSuche = :keyword THEN 0
            WHEN cSuche LIKE CONCAT(:keyword, '%') THEN 1
            WHEN cSuche LIKE CONCAT('%', :keyword, '%') THEN 2
            ELSE 99
            END, nAnzahlGesuche DESC, cSuche
        LIMIT :maxres",
        [
            'keyword' => $keyword,
            'maxres'  => $maxResults,
            'lang'    => $language
        ],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($results as $result) {
        $result->suggestion = $smarty->assign('result', $result)->fetch('snippets/suggestion.tpl');
    }

    return $results;
}

/**
 * @param string $cityQuery
 * @param string $country
 * @param string $zip
 * @return array
 */
function getCitiesByZip($cityQuery, $country, $zip)
{
    $results = [];
    if (!empty($country) && !empty($zip)) {
        $cityQuery = "%" . StringHandler::filterXSS($cityQuery) . "%";
        $cities    = Shop::Container()->getDB()->queryPrepared(
            "SELECT cOrt
                FROM tplz
                WHERE cLandISO = :country
                    AND cPLZ = :zip
                    AND cOrt LIKE :cityQuery",
            ['country' => $country, 'zip' => $zip, 'cityQuery' => $cityQuery],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($cities as $result) {
            $results[] = $result->cOrt;
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
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

    $smarty      = Shop::Smarty();
    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $GLOBALS['oSprache'] = Sprache::getInstance();

    $kArtikel = (int)$kArtikel;
    if ($anzahl <= 0 || $kArtikel <= 0) {
        return $objResponse;
    }
    $Artikel = new Artikel();
    $Artikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
    // Falls der Artikel ein Variationskombikind ist, hole direkt seine Eigenschaften
    if ($Artikel->kEigenschaftKombi > 0 || $Artikel->nIstVater === 1) {
        // Variationskombi-Artikel
        $_POST['eigenschaftwert'] = $oEigenschaftwerte_arr['eigenschaftwert'];
        $oEigenschaftwerte_arr    = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($kArtikel);
    } elseif (isset($oEigenschaftwerte_arr['eigenschaftwert']) && is_array($oEigenschaftwerte_arr['eigenschaftwert'])) {
        // einfache Variation - keine Varkombi
        $_POST['eigenschaftwert'] = $oEigenschaftwerte_arr['eigenschaftwert'];
        $oEigenschaftwerte_arr    = ArtikelHelper::getSelectedPropertiesForArticle($kArtikel);
    }

    if ((int)$anzahl != $anzahl && $Artikel->cTeilbar !== 'Y') {
        $anzahl = max((int)$anzahl, 1);
    }
    // Prüfung
    $errors = WarenkorbHelper::addToCartCheck($Artikel, $anzahl, $oEigenschaftwerte_arr);

    if (count($errors) > 0) {
        $localizedErrors = baueArtikelhinweise($errors, true, $Artikel, $anzahl);

        $oResponse->nType  = 0;
        $oResponse->cLabel = Shop::Lang()->get('basket');
        $oResponse->cHints = utf8_convert_recursive($localizedErrors);
        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

        return $objResponse;
    }
    $cart = Session::Cart();
    WarenkorbHelper::addVariationPictures($cart);
    /** @var Warenkorb $cart */
    $cart->fuegeEin($kArtikel, $anzahl, $oEigenschaftwerte_arr)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
         ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);

    unset(
        $_SESSION['VersandKupon'],
        $_SESSION['NeukundenKupon'],
        $_SESSION['Versandart'],
        $_SESSION['Zahlungsart'],
        $_SESSION['TrustedShops']
    );
    // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb,
    // dann verwerfen und neu anlegen
    altenKuponNeuBerechnen();
    setzeLinks();
    // Persistenter Warenkorb
    if (!isset($_POST['login'])) {
        fuegeEinInWarenkorbPers($kArtikel, $anzahl, $oEigenschaftwerte_arr);
    }
    $boxes         = Boxen::getInstance();
    $pageType      = Shop::getPageType();
    $boxesToShow   = $boxes->build($pageType, true)->render();
    $warensumme[0] = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true));
    $warensumme[1] = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false));
    $smarty->assign('Boxen', $boxesToShow)
           ->assign('WarenkorbWarensumme', $warensumme);

    $kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0)
        ? $_SESSION['Kunde']->kKundengruppe
        : Session::CustomerGroup()->getID();
    $oXSelling     = gibArtikelXSelling($kArtikel, $Artikel->nIstVater > 0);

    $smarty->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString(
        gibVersandkostenfreiAb($kKundengruppe),
        $cart->gibGesamtsummeWarenExt(
            [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_KUPON, C_WARENKORBPOS_TYP_NEUKUNDENKUPON],
            true
        )))
           ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
           ->assign('fAnzahl', $anzahl)
           ->assign('NettoPreise', Session::CustomerGroup()->getIsMerchant())
           ->assign('Einstellungen', Shop::getSettings([CONF_GLOBAL]))
           ->assign('Xselling', $oXSelling)
           ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
           ->assign('Steuerpositionen', $cart->gibSteuerpositionen());

    $oResponse->nType           = 2;
    $oResponse->cWarenkorbText  = lang_warenkorb_warenkorbEnthaeltXArtikel($cart);
    $oResponse->cWarenkorbLabel = lang_warenkorb_warenkorbLabel($cart);
    $oResponse->cPopup          = $smarty->fetch('productdetails/pushed.tpl');
    $oResponse->cWarenkorbMini  = $smarty->fetch('basket/cart_dropdown.tpl');
    $oResponse->oArtikel        = $Artikel;
    $oResponse->cNotification   = Shop::Lang()->get('basketAllAdded', 'messages');

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');
    // Kampagne
    if (isset($_SESSION['Kampagnenbesucher'])) {
        setzeKampagnenVorgang(KAMPAGNE_DEF_WARENKORB, $kArtikel, $anzahl); // Warenkorb
    }

    if ($GLOBALS['GlobaleEinstellungen']['global']['global_warenkorb_weiterleitung'] === 'Y') {
        $linkHelper           = LinkHelper::getInstance();
        $oResponse->nType     = 1;
        $oResponse->cLocation = $linkHelper->getStaticRoute('warenkorb.php');
        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');
    }

    return $objResponse;
}

/**
 * @param int $kArtikel
 * @return IOResponse
 */
function pushToComparelist($kArtikel)
{
    $Einstellungen = Shop::getSettings([CONF_VERGLEICHSLISTE]);
    $kArtikel      = (int)$kArtikel;
    $oResponse     = new stdClass();
    $objResponse   = new IOResponse();

    $_POST['Vergleichsliste'] = 1;
    $_POST['a']               = $kArtikel;

    WarenkorbHelper::checkAdditions();
    $error             = Shop::Smarty()->getTemplateVars('fehler');
    $notice            = Shop::Smarty()->getTemplateVars('hinweis');
    $oResponse->nType  = 2;
    $oResponse->nCount = count($_SESSION['Vergleichsliste']->oArtikel_arr);
    $oResponse->cTitle = Shop::Lang()->get('compare');
    $buttons           = [
        (object)[
            'href'    => '#',
            'fa'      => 'fa fa-arrow-circle-right',
            'title'   => Shop::Lang()->get('continueShopping', 'checkout'),
            'primary' => true,
            'dismiss' => 'modal'
        ]
    ];

    if ($oResponse->nCount > 1) {
        array_unshift($buttons, (object)[
            'href'  => 'vergleichsliste.php',
            'fa'    => 'fa-tasks',
            'title' => Shop::Lang()->get('compare')
        ]);
    }

    $oResponse->cNotification = Shop::Smarty()
            ->assign('type', empty($error) ? 'info' : 'danger')
            ->assign('body', empty($error) ? $notice : $error)
            ->assign('buttons', $buttons)
            ->fetch('snippets/notification.tpl');

    $oResponse->cNavBadge = Shop::Smarty()
            ->assign('Einstellungen', $Einstellungen)
            ->fetch('layout/header_shop_nav_compare.tpl');

    $boxes    = Boxen::getInstance();
    $allBoxes = $boxes->build();
    foreach ($allBoxes->boxes as $_position => $_boxes) {
        if (!is_array($_boxes)) {
            continue;
        }
        foreach ($_boxes as $_box) {
            if (isset($_box->bContainer) && $_box->bContainer === true) {
                foreach ($_box->oContainer_arr as $_cbox) {
                    if ($_cbox->kBoxvorlage === BOX_VERGLEICHSLISTE) {
                        $oBox = $boxes->prepareBox(BOX_VERGLEICHSLISTE, $boxes->holeBox($_cbox->kBox));
                        $oResponse->cBoxContainer[$_cbox->kBox] = Shop::Smarty()
                            ->assign('Einstellungen', $Einstellungen)
                            ->assign('oBox', $oBox)
                            ->fetch('boxes/box_comparelist.tpl');
                    }
                }
            }
            if ($_box->kBoxvorlage === BOX_VERGLEICHSLISTE) {
                $oBox = $boxes->prepareBox(BOX_VERGLEICHSLISTE, $boxes->holeBox($_box->kBox));
                $oResponse->cBoxContainer[$_box->kBox] = Shop::Smarty()
                    ->assign('Einstellungen', $Einstellungen)
                    ->assign('oBox', $oBox)
                    ->fetch('boxes/box_comparelist.tpl');
            }
        }
    }

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param int $kArtikel
 * @return IOResponse
 */
function removeFromComparelist($kArtikel)
{
    $kArtikel      = (int)$kArtikel;
    $Einstellungen = Shopsetting::getInstance()->getAll();
    $oResponse     = new stdClass();
    $objResponse   = new IOResponse();
    $smarty        = Shop::Smarty();

    $_GET['Vergleichsliste'] = 1;
    $_GET['vlplo']           = $kArtikel;

    Session::getInstance()->setStandardSessionVars();
    $oResponse->nType     = 2;
    $oResponse->nCount    = count($_SESSION['Vergleichsliste']->oArtikel_arr);
    $oResponse->cTitle    = Shop::Lang()->get('compare');
    $oResponse->cNavBadge = $smarty->assign('Einstellungen', $Einstellungen)
                                   ->fetch('layout/header_shop_nav_compare.tpl');

    $boxes    = Boxen::getInstance();
    $allBoxes = $boxes->build();
    foreach ($allBoxes->boxes as $_position => $_boxes) {
        if (!is_array($_boxes)) {
            continue;
        }
        foreach ($_boxes as $_box) {
            if (isset($_box->bContainer) && $_box->bContainer === true) {
                foreach ($_box->oContainer_arr as $_cbox) {
                    if ($_cbox->kBoxvorlage === BOX_VERGLEICHSLISTE) {
                        $oBox = $boxes->prepareBox(BOX_VERGLEICHSLISTE, $boxes->holeBox($_cbox->kBox));
                        $smarty->assign('Einstellungen', $Einstellungen)
                               ->assign('oBox', $oBox);

                        $oResponse->cBoxContainer[$_cbox->kBox] = $smarty->fetch('boxes/box_comparelist.tpl');
                    }
                }
            }
            if ($_box->kBoxvorlage === BOX_VERGLEICHSLISTE) {
                $oBox = $boxes->prepareBox(BOX_VERGLEICHSLISTE, $boxes->holeBox($_box->kBox));
                $smarty->assign('Einstellungen', $Einstellungen)
                       ->assign('oBox', $oBox);
                $oResponse->cBoxContainer[$_box->kBox] = $smarty->fetch('boxes/box_comparelist.tpl');
            }
        }
    }

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param int $kArtikel
 * @param int $qty
 * @return IOResponse
 */
function pushToWishlist($kArtikel, $qty)
{
    $Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_VERGLEICHSLISTE]);
    $kArtikel      = (int)$kArtikel;
    $oResponse     = new stdClass();
    $objResponse   = new IOResponse();
    $qty           = (int)$qty === 0 ? 1 : (int)$qty;
    $smarty        = Shop::Smarty();
    if (empty($customerID = Session::Customer()->getID())) {
        $linkHelper           = LinkHelper::getInstance();
        $oResponse->nType     = 1;
        $oResponse->cLocation = $linkHelper->getStaticRoute('jtl.php') .
            '?a=' . $kArtikel .
            '&n=' . $qty .
            '&r=' . R_LOGIN_WUNSCHLISTE;
        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

        return $objResponse;
    }
    $vals = Shop::Container()->getDB()->selectAll('teigenschaft', 'kArtikel', $kArtikel);
    if (!ArtikelHelper::isParent($kArtikel) && !empty($vals)) {
        // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
        // muss zum Artikel weitergeleitet werden um Variationen zu wählen
        $oResponse->nType     = 1;
        $oResponse->cLocation =(Shop::getURL() . '/?a=' . $kArtikel .
                '&n=' . $qty .
                '&r=' . R_VARWAEHLEN);
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
    }

    $_POST['Wunschliste'] = 1;
    $_POST['a']           = $kArtikel;
    $_POST['n']           = (int)$qty;

    WarenkorbHelper::checkAdditions();
    $error             = $smarty->getTemplateVars('fehler');
    $notice            = $smarty->getTemplateVars('hinweis');
    $oResponse->nType  = 2;
    $oResponse->nCount = count($_SESSION['Wunschliste']->CWunschlistePos_arr);
    $oResponse->cTitle = Shop::Lang()->get('goToWishlist');
    $buttons           = [
        (object)[
            'href'    => '#',
            'fa'      => 'fa fa-arrow-circle-right',
            'title'   => Shop::Lang()->get('continueShopping', 'checkout'),
            'primary' => true,
            'dismiss' => 'modal'
        ]
    ];

    if ($oResponse->nCount > 1) {
        array_unshift($buttons, (object)[
            'href'  => 'wunschliste.php',
            'fa'    => 'fa-tasks',
            'title' => Shop::Lang()->get('goToWishlist')
        ]);
    }
    $smarty->assign('type', empty($error) ? 'info' : 'danger')
           ->assign('body', empty($error) ? $notice : $error)
           ->assign('buttons', $buttons)
           ->assign('Einstellungen', $Einstellungen);

    $oResponse->cNotification = $smarty->fetch('snippets/notification.tpl');
    $oResponse->cNavBadge     = $smarty->fetch('layout/header_shop_nav_wish.tpl');

    $boxes    = Boxen::getInstance();
    $allBoxes = $boxes->build();
    foreach ($allBoxes->boxes as $_position => $_boxes) {
        if (!is_array($_boxes)) {
            continue;
        }
        foreach ($_boxes as $_box) {
            if (isset($_box->bContainer) && $_box->bContainer === true) {
                foreach ($_box->oContainer_arr as $_cbox) {
                    if ($_cbox->kBoxvorlage === BOX_WUNSCHLISTE) {
                        $oBox = $boxes->prepareBox(BOX_WUNSCHLISTE, $boxes->holeBox($_cbox->kBox));
                        $smarty->assign('oBox', $oBox);

                        $oResponse->cBoxContainer[$_cbox->kBox] = $smarty->fetch('boxes/box_wishlist.tpl');
                    }
                }
            }
            if ($_box->kBoxvorlage === BOX_WUNSCHLISTE) {
                $oBox = $boxes->prepareBox(BOX_WUNSCHLISTE, $boxes->holeBox($_box->kBox));
                $smarty->assign('oBox', $oBox);

                $oResponse->cBoxContainer[$_box->kBox] = $smarty->fetch('boxes/box_wishlist.tpl');
            }
        }
    }

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param int $kArtikel
 * @return IOResponse
 */
function removeFromWishlist($kArtikel)
{
    $kArtikel      = (int)$kArtikel;
    $Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_VERGLEICHSLISTE]);
    $oResponse     = new stdClass();
    $objResponse   = new IOResponse();
    $smarty        = Shop::Smarty();

    $_GET['Wunschliste'] = 1;
    $_GET['wlplo']       = $kArtikel;

    Session::getInstance()->setStandardSessionVars();
    $oResponse->nType     = 2;
    $oResponse->nCount    = count($_SESSION['Wunschliste']->CWunschlistePos_arr);
    $oResponse->cTitle    = Shop::Lang()->get('goToWishlist');

    $oResponse->cNavBadge = $smarty->assign('Einstellungen', $Einstellungen)
                                   ->fetch('layout/header_shop_nav_wish.tpl');

    $boxes    = Boxen::getInstance();
    $allBoxes = $boxes->build();
    foreach ($allBoxes->boxes as $_position => $_boxes) {
        if (!is_array($_boxes)) {
            continue;
        }
        foreach ($_boxes as $_box) {
            if (isset($_box->bContainer) && $_box->bContainer === true) {
                foreach ($_box->oContainer_arr as $_cbox) {
                    if ($_cbox->kBoxvorlage === BOX_WUNSCHLISTE) {
                        $oBox = $boxes->prepareBox(BOX_WUNSCHLISTE, $boxes->holeBox($_cbox->kBox));
                        $smarty->assign('Einstellungen', $Einstellungen)
                               ->assign('oBox', $oBox);
                        $oResponse->cBoxContainer[$_cbox->kBox] = $smarty->fetch('boxes/box_wishlist.tpl');
                    }
                }
            }
            if ($_box->kBoxvorlage === BOX_WUNSCHLISTE) {
                $oBox = $boxes->prepareBox(BOX_WUNSCHLISTE, $boxes->holeBox($_box->kBox));
                $smarty->assign('Einstellungen', $Einstellungen)
                       ->assign('oBox', $oBox);
                $oResponse->cBoxContainer[$_box->kBox] = $smarty->fetch('boxes/box_wishlist.tpl');
            }
        }
    }
    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param int $nTyp - 0 = Template, 1 = Object
 * @return IOResponse
 */
function getBasketItems($nTyp)
{
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

    $smarty      = Shop::Smarty();
    $cart        = Session::Cart();
    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $GLOBALS['oSprache'] = Sprache::getInstance();
    WarenkorbHelper::addVariationPictures($cart);

    switch ((int)$nTyp) {
        default:
        case 0:
            $kKundengruppe = Session::CustomerGroup()->getID();
            $nAnzahl       = $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]);
            $cLand         = $_SESSION['cLieferlandISO'] ?? '';
            $cPLZ          = '*';

            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
                $cLand         = $_SESSION['Kunde']->cLand;
                $cPLZ          = $_SESSION['Kunde']->cPLZ;
            }

            $versandkostenfreiAb = gibVersandkostenfreiAb($kKundengruppe, $cLand);
            $smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
                   ->assign('Warensumme', $cart->gibGesamtsummeWaren())
                   ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
                   ->assign('Einstellungen', Shop::getSettings([CONF_GLOBAL]))
                   ->assign('WarenkorbArtikelPositionenanzahl', $nAnzahl)
                   ->assign('WarenkorbArtikelanzahl', $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]))
                   ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
                   ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
                   ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
                   ->assign('NettoPreise', Session::CustomerGroup()->getIsMerchant())
                   ->assign('FavourableShipping', $cart->getFavourableShipping())
                   ->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString($versandkostenfreiAb,
                       $cart->gibGesamtsummeWarenExt(
                           [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_KUPON, C_WARENKORBPOS_TYP_NEUKUNDENKUPON],
                           true)
                   ))
                   ->assign('oSpezialseiten_arr', LinkHelper::getInstance()->getSpecialPages());

            VersandartHelper::getShippingCosts($cLand, $cPLZ, $error);
            $oResponse->cTemplate = $smarty->fetch('basket/cart_dropdown_label.tpl');
            break;

        case 1:
            $oResponse->cItems = $cart->PositionenArr;
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
    $smarty          = Shop::Smarty();
    $oResponse       = new IOResponse();
    $Artikel         = new Artikel();
    $articleId       = intval($aValues['VariKindArtikel'] ?? $aValues['a']);
    $items           = $aValues['item'] ?? [];
    $quantities      = $aValues['quantity'] ?? [];
    $variationValues = $aValues['eigenschaftwert'] ?? [];
    $oKonfig         = buildConfig($articleId, $aValues['anzahl'], $variationValues, $items, $quantities, []);
    $net             = Session::CustomerGroup()->getIsMerchant();
    $Artikel->fuelleArtikel($articleId, null);
    $Artikel->Preise->cVKLocalized[$net]
        = gibPreisStringLocalized($Artikel->Preise->fVK[$net] * $aValues['anzahl'], 0, true);

    $smarty->assign('oKonfig', $oKonfig)
           ->assign('NettoPreise', $net)
           ->assign('Artikel', $Artikel);
    $oKonfig->cTemplate = $smarty->fetch('productdetails/config_summary.tpl');

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

    if ($oTMPArtikel !== null && $oTMPArtikel->kArtikel > 0) {
        $oTestArtikel                                = new Artikel();
        $oArtikelOptionen                            = new stdClass();
        $oArtikelOptionen->nMain                     = 0;
        $oArtikelOptionen->nWarenlager               = 0;
        $oArtikelOptionen->nVariationKombi           = 0;
        $oArtikelOptionen->nKeinLagerbestandBeachten = 1;

        $oTestArtikel->fuelleArtikel(
            $oTMPArtikel->kArtikel,
            $oArtikelOptionen,
            Kundengruppe::getCurrent(),
            Shop::getLanguage()
        );

        return (object)[
            'stock'  => $oTestArtikel->aufLagerSichtbarkeit(),
            'status' => $oTestArtikel->Lageranzeige->nStatus,
            'text'   => $oTestArtikel->Lageranzeige->AmpelText
        ];
    }

    return null;
}

/**
 * @param array $aValues
 * @return IOResponse
 */
function checkDependencies($aValues)
{
    $objResponse   = new IOResponse();
    $kVaterArtikel = (int)$aValues['a'];
    $fAnzahl       = (float)$aValues['anzahl'];
    $valueID_arr   = array_filter((array)$aValues['eigenschaftwert']);
    $wrapper       = isset($aValues['wrapper']) ? StringHandler::filterXSS($aValues['wrapper']) : '';

    if ($kVaterArtikel <= 0) {
        return $objResponse;
    }
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
    $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen, Session::CustomerGroup()->getID());
    $weightDiff   = 0;
    $newProductNr = '';
    foreach ($valueID_arr as $valueID) {
        $currentValue = new EigenschaftWert($valueID);
        $weightDiff  += $currentValue->fGewichtDiff;
        $newProductNr = (!empty($currentValue->cArtNr) && $oArtikel->cArtNr !== $currentValue->cArtNr)
            ? $currentValue->cArtNr
            : $oArtikel->cArtNr;
    }
    $weightTotal        = Trennzeichen::getUnit(
        JTL_SEPARATOR_WEIGHT,
        Shop::getLanguage(),
        $oArtikel->fGewicht + $weightDiff
    );
    $weightArticleTotal = Trennzeichen::getUnit(
        JTL_SEPARATOR_WEIGHT,
        Shop::getLanguage(),
        $oArtikel->fArtikelgewicht + $weightDiff
    );
    $cUnitWeightLabel   = Shop::Lang()->get('weightUnit');

    // Alle Variationen ohne Freifeld
    $nKeyValueVariation_arr = $oArtikel->keyValueVariations($oArtikel->VariationenOhneFreifeld);

    // Freifeldpositionen gesondert zwischenspeichern
    foreach ($valueID_arr as $kKey => $cVal) {
        if (!isset($nKeyValueVariation_arr[$kKey])) {
            unset($valueID_arr[$kKey]);
            $kFreifeldEigeschaftWert_arr[$kKey] = $cVal;
        }
    }

    $nNettoPreise = Session::CustomerGroup()->getIsMerchant();
    $fVKNetto     = $oArtikel->gibPreis($fAnzahl, $valueID_arr, Session::CustomerGroup()->getID());
    $fVK          = [
        berechneBrutto($fVKNetto, $_SESSION['Steuersatz'][$oArtikel->kSteuerklasse]),
        $fVKNetto
    ];
    $cVKLocalized = [
        0 => gibPreisStringLocalized($fVK[0]),
        1 => gibPreisStringLocalized($fVK[1])
    ];
    $cPriceLabel  = '';
    if (isset($oArtikel->nVariationAnzahl) && $oArtikel->nVariationAnzahl > 0) {
        $cPriceLabel = $oArtikel->nVariationOhneFreifeldAnzahl === count($valueID_arr)
            ? Shop::Lang()->get('priceAsConfigured', 'productDetails')
            : Shop::Lang()->get('priceStarting');
    }

    $objResponse->jsfunc(
        '$.evo.article().setPrice',
        $fVK[$nNettoPreise],
        $cVKLocalized[$nNettoPreise],
        $cPriceLabel,
        $wrapper
    );
    $objResponse->jsfunc('$.evo.article().setArticleWeight', [
        [$oArtikel->fGewicht, $weightTotal . ' ' . $cUnitWeightLabel],
        [$oArtikel->fArtikelgewicht, $weightArticleTotal . ' ' . $cUnitWeightLabel],
    ], $wrapper);

    if (!empty($oArtikel->staffelPreis_arr)) {
        $fStaffelVK = [0 => [], 1 => []];
        $cStaffelVK = [0 => [], 1 => []];
        foreach ($oArtikel->staffelPreis_arr as $staffelPreis) {
            $nAnzahl                 = &$staffelPreis['nAnzahl'];
            $fStaffelVKNetto         = $oArtikel->gibPreis($nAnzahl, $valueID_arr, Session::CustomerGroup()->getID());
            $fStaffelVK[0][$nAnzahl] = berechneBrutto(
                $fStaffelVKNetto,
                $_SESSION['Steuersatz'][$oArtikel->kSteuerklasse]
            );
            $fStaffelVK[1][$nAnzahl] = $fStaffelVKNetto;
            $cStaffelVK[0][$nAnzahl] = gibPreisStringLocalized($fStaffelVK[0][$nAnzahl]);
            $cStaffelVK[1][$nAnzahl] = gibPreisStringLocalized($fStaffelVK[1][$nAnzahl]);
        }

        $objResponse->jsfunc(
            '$.evo.article().setStaffelPrice',
            $fStaffelVK[$nNettoPreise],
            $cStaffelVK[$nNettoPreise],
            $wrapper
        );
    }

    if ($oArtikel->cVPE === 'Y'
        && $oArtikel->fVPEWert > 0
        && $oArtikel->cVPEEinheit
        && !empty($oArtikel->Preise)
    ) {
        $oArtikel->baueVPE($fVKNetto);
        $fStaffelVPE = [0 => [], 1 => []];
        $cStaffelVPE = [0 => [], 1 => []];
        foreach ($oArtikel->staffelPreis_arr as $key => $staffelPreis) {
            $nAnzahl                  = &$staffelPreis['nAnzahl'];
            $fStaffelVPE[0][$nAnzahl] = $oArtikel->fStaffelpreisVPE_arr[$key][0];
            $fStaffelVPE[1][$nAnzahl] = $oArtikel->fStaffelpreisVPE_arr[$key][1];
            $cStaffelVPE[0][$nAnzahl] = $staffelPreis['cBasePriceLocalized'][0];
            $cStaffelVPE[1][$nAnzahl] = $staffelPreis['cBasePriceLocalized'][1];
        }

        $objResponse->jsfunc(
            '$.evo.article().setVPEPrice',
            $oArtikel->cLocalizedVPE[$nNettoPreise],
            $fStaffelVPE[$nNettoPreise],
            $cStaffelVPE[$nNettoPreise],
            $wrapper
        );
    }

    if (!empty($newProductNr)) {
        $objResponse->jsfunc('$.evo.article().setProductNumber', $newProductNr, $wrapper);
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
    $kEigenschaft                = (int)$kEigenschaft;
    $kEigenschaftWert            = (int)$kEigenschaftWert;
    $oArtikel                    = null;
    $objResponse                 = new IOResponse();
    $kVaterArtikel               = (int)$aValues['a'];
    $kArtikelKind                = isset($aValues['VariKindArtikel']) ? (int)$aValues['VariKindArtikel'] : 0;
    $idx                         = isset($aValues['eigenschaftwert']) ? (array)$aValues['eigenschaftwert'] : [];
    $kFreifeldEigeschaftWert_arr = [];
    $kGesetzteEigeschaftWert_arr = array_filter($idx);
    $wrapper                     = isset($aValues['wrapper']) ? StringHandler::filterXSS($aValues['wrapper']) : '';

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
        $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen);

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
            $objResponse->jsfunc('$.evo.article().variationResetAll', $wrapper);

            $kGesetzteEigeschaftWert_arr = [$kEigenschaft => $kEigenschaftWert];
            $nInvalidVariations          = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, true);

            // Auswählter EigenschaftWert ist ebenfalls nicht vorhanden
            if (in_array($kEigenschaftWert, $nInvalidVariations[$kEigenschaft])) {
                $kGesetzteEigeschaftWert_arr = [];

                // Wir befinden uns im Kind-Artikel -> Weiterleitung auf Vater-Artikel
                if ($kArtikelKind > 0) {
                    $objResponse->jsfunc(
                        '$.evo.article().setArticleContent',
                        $oArtikel->kArtikel,
                        0,
                        $oArtikel->cURL,
                        [],
                        $wrapper
                    );

                    return $objResponse;
                }
            }
        }

        // Alle EigenschaftWerte vorhanden, Kind-Artikel ermitteln
        if (count($kGesetzteEigeschaftWert_arr) >= $oArtikel->nVariationOhneFreifeldAnzahl) {
            $oArtikelTMP = getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWert_arr);

            if ($oArtikelTMP !== null && $kArtikelKind !== (int)$oArtikelTMP->kArtikel) {
                $oGesetzteEigeschaftWerte_arr = [];
                foreach ($kFreifeldEigeschaftWert_arr as $cKey => $cValue) {
                    $oGesetzteEigeschaftWerte_arr[] = (object)[
                        'key'   => $cKey,
                        'value' => $cValue
                    ];
                }
                $cUrl = baueURL($oArtikelTMP, URLART_ARTIKEL, 0, empty($oArtikelTMP->kSeoKey), true);
                $objResponse->jsfunc(
                    '$.evo.article().setArticleContent',
                    $kVaterArtikel,
                    $oArtikelTMP->kArtikel,
                    $cUrl,
                    $oGesetzteEigeschaftWerte_arr,
                    $wrapper
                );

                executeHook(HOOK_TOOLSAJAXSERVER_PAGE_TAUSCHEVARIATIONKOMBI, [
                    'objResponse' => &$objResponse,
                    'oArtikel'    => &$oArtikel,
                    'bIO'         => true
                ]);

                return $objResponse;
            }
        }

        $objResponse->jsfunc('$.evo.article().variationDisableAll', $wrapper);

        $nPossibleVariations = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, false);

        foreach ($nPossibleVariations as $k => $values) {
            foreach ($values as $v) {
                $objResponse->jsfunc('$.evo.article().variationEnable', $k, $v, $wrapper);
            }
        }

        foreach ($kGesetzteEigeschaftWert_arr as $key => $value) {
            $escaped = addslashes($value);
            $objResponse->jsfunc('$.evo.article().variationActive', $key, $escaped, null, $wrapper);
        }

        foreach ($nInvalidVariations as $k => $values) {
            foreach ($values as $v) {
                $text = Shop::Lang()->get('notAvailableInSelection');
                $objResponse->jsfunc('$.evo.article().variationInfo', $v, -1, $text);
            }
        }

        $kNichtGesetzteEigenschaft_arr = array_values(
            array_diff(
                array_keys($nKeyValueVariation_arr),
                array_keys($kGesetzteEigeschaftWert_arr)
            )
        );
        $kZuletztGesetzteEigenschaft = $kEigenschaft;
        if (count($kNichtGesetzteEigenschaft_arr) <= 1) {
            foreach ($nKeyValueVariation_arr as $kEigenschaft => $kEigenschaftWert) {
                $kVerfuegbareEigenschaftWert_arr = $nKeyValueVariation_arr[$kEigenschaft];
                $kMoeglicheEigeschaftWert_arr    = $kGesetzteEigeschaftWert_arr;

                foreach ($kVerfuegbareEigenschaftWert_arr as $kVerfuegbareEigenschaftWert) {
                    //nur für noch auswählbare Varkombis Lagerbestand holen und Infos setzen
                    if (in_array($kEigenschaft, $kNichtGesetzteEigenschaft_arr) || $kZuletztGesetzteEigenschaft === 0) {
                        $kMoeglicheEigeschaftWert_arr[$kEigenschaft] = $kVerfuegbareEigenschaftWert;
                        $oKindArtikel = getArticleStockInfo(
                            $kVaterArtikel,
                            $kMoeglicheEigeschaftWert_arr
                        );

                        if ($oKindArtikel !== null
                            && $oKindArtikel->status == 0
                            && !in_array($kVerfuegbareEigenschaftWert, $kGesetzteEigeschaftWert_arr)
                        ) {
                            $objResponse->jsfunc(
                                '$.evo.article().variationInfo',
                                $kVerfuegbareEigenschaftWert,
                                $oKindArtikel->status,
                                $oKindArtikel->text
                            );
                        }
                    }
                }
            }
        }
    } else {
        $objResponse->jsfunc('$.evo.error', 'Article not found', $kVaterArtikel);
    }
    $objResponse->jsfunc("$.evo.article().variationRefreshAll", $wrapper);

    return $objResponse;
}

/**
 * @param int   $kArtikel
 * @param array $kVariationKombi_arr
 * @return stdClass|null
 */
function getArticleByVariations($kArtikel, $kVariationKombi_arr)
{
    if (!is_array($kVariationKombi_arr) || count($kVariationKombi_arr) === 0) {
        return null;
    }
    $kArtikel        = (int)$kArtikel;
    $attributes      = [];
    $attributeValues = [];
    foreach ($kVariationKombi_arr as $i => $kVariationKombi) {
        $attributes[]      = (int)$i;
        $attributeValues[] = (int)$kVariationKombi;
    }

    return  Shop::Container()->getDB()->query(
        "SELECT a.kArtikel, tseo.kKey AS kSeoKey, IF (tseo.cSeo IS NULL, a.cSeo, tseo.cSeo) AS cSeo, 
            a.fLagerbestand, a.cLagerBeachten, a.cLagerKleinerNull
            FROM teigenschaftkombiwert
            JOIN tartikel a 
                ON a.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
            LEFT JOIN tseo 
                ON tseo.cKey = 'kArtikel' 
                AND tseo.kKey = a.kArtikel 
                AND tseo.kSprache = " . Shop::getLanguageID() .  "
            LEFT JOIN tartikelsichtbarkeit 
                ON a.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . Session::CustomerGroup()->getID() . "
        WHERE teigenschaftkombiwert.kEigenschaft IN (" . implode(',', $attributes) . ")
            AND teigenschaftkombiwert.kEigenschaftWert IN (" . implode(',', $attributeValues) . ")
            AND tartikelsichtbarkeit.kArtikel IS NULL
            AND a.kVaterArtikel = " . $kArtikel . "
        GROUP BY a.kArtikel
        HAVING count(*) = " . count($kVariationKombi_arr),
        \DB\ReturnType::SINGLE_OBJECT
    );
}

/**
 * @return IOResponse
 */
function generateToken()
{
    $objResponse             = new IOResponse();
    $cToken                  = gibToken();
    $cName                   = gibTokenName();
    $token_arr               = ['name' => $cName, 'token' => $cToken];
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
    $smarty     = Shop::Smarty();
    $categoryId = (int)$categoryId;
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

    $result = (object)['current' => $category, 'items' => $categories];

    $smarty->assign('result', $result)
           ->assign('nSeitenTyp', 0);

    $response->script('this.response = ' . json_encode($smarty->fetch('snippets/categories_offcanvas.tpl')) . ';');

    return $response;
}

/**
 * @param string $country
 * @return IOResponse
 */
function getRegionsByCountry($country)
{
    $response = new IOResponse();

    if (strlen($country) === 2) {
        $regions = Staat::getRegions($country);
        $regions = utf8_convert_recursive($regions);
        $response->script("this.response = " . json_encode($regions) . ";");
    }

    return $response;
}

/**
 * @param string $cKey
 * @param int $kKey
 * @param int $kSprache
 * @param array $kSelection_arr
 * @return IOResponse
 */
function setSelectionWizardAnswers($cKey, $kKey, $kSprache, $kSelection_arr)
{
    $smarty   = Shop::Smarty();
    $response = new IOResponse();
    $AWA      = AuswahlAssistent::startIfRequired($cKey, $kKey, $kSprache, $smarty, $kSelection_arr);

    if ($AWA !== null) {
        $oLastSelectedValue = $AWA->getLastSelectedValue();
        $NaviFilter         = $AWA->getNaviFilter();

        if (($oLastSelectedValue !== null && $oLastSelectedValue->nAnzahl === 1)
            || $AWA->getCurQuestion() === $AWA->getQuestionCount()
            || $AWA->getQuestion($AWA->getCurQuestion())->nTotalResultCount === 0)
        {
            $response->script("window.location.href='" . StringHandler::htmlentitydecode(
                $NaviFilter->getFilterURL()->getURL()
                ) . "';");
        } else {
            $response->assign('selectionwizard', 'innerHTML', $AWA->fetchForm($smarty));
        }
    }

    return $response;
}
