<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';

/**
 * Class IOMethods
 */
class IOMethods
{
    /**
     * @var IO
     */
    private $io;

    /**
     * IOMethods constructor.
     * @param IO $io
     * @throws Exception
     */
    public function __construct($io)
    {
        $this->io = $io;
    }

    /**
     * @return IO
     * @throws Exception
     */
    public function registerMethods(): IO
    {
        return $this->io->register('suggestions', [$this, 'suggestions'])
                        ->register('pushToBasket', [$this, 'pushToBasket'])
                        ->register('pushToComparelist', [$this, 'pushToComparelist'])
                        ->register('removeFromComparelist', [$this, 'removeFromComparelist'])
                        ->register('pushToWishlist', [$this, 'pushToWishlist'])
                        ->register('removeFromWishlist', [$this, 'removeFromWishlist'])
                        ->register('checkDependencies', [$this, 'checkDependencies'])
                        ->register('checkVarkombiDependencies', [$this, 'checkVarkombiDependencies'])
                        ->register('generateToken', [$this, 'generateToken'])
                        ->register('buildConfiguration', [$this, 'buildConfiguration'])
                        ->register('getBasketItems', [$this, 'getBasketItems'])
                        ->register('getCategoryMenu', [$this, 'getCategoryMenu'])
                        ->register('getRegionsByCountry', [$this, 'getRegionsByCountry'])
                        ->register('checkDeliveryCountry', [$this, 'checkDeliveryCountry'])
                        ->register('setSelectionWizardAnswers', [$this, 'setSelectionWizardAnswers'])
                        ->register('getCitiesByZip', [$this, 'getCitiesByZip']);
    }

    /**
     * @param string $keyword
     * @return array
     * @throws SmartyException
     */
    public function suggestions($keyword): array
    {
        $results = [];
        if (strlen($keyword) < 2) {
            return $results;
        }
        $smarty     = Shop::Smarty();
        $language   = Shop::getLanguage();
        $maxResults = ($cnt = Shop::getSettingValue(CONF_ARTIKELUEBERSICHT, 'suche_ajax_anzahl')) > 0
            ? $cnt
            : 10;
        $results    = Shop::Container()->getDB()->queryPrepared(
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
    public function getCitiesByZip($cityQuery, $country, $zip): array
    {
        $results = [];
        if (!empty($country) && !empty($zip)) {
            $cityQuery = '%' . StringHandler::filterXSS($cityQuery) . '%';
            $cities    = Shop::Container()->getDB()->queryPrepared(
                'SELECT cOrt
                    FROM tplz
                    WHERE cLandISO = :country
                        AND cPLZ = :zip
                        AND cOrt LIKE :cityQuery',
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
     * @param int|float    $amount
     * @param string|array $properties
     * @return IOResponse
     * @throws SmartyException
     */
    public function pushToBasket(int $kArtikel, $amount, $properties = ''): IOResponse
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

        $config      = Shopsetting::getInstance()->getAll();
        $smarty      = Shop::Smarty();
        $oResponse   = new stdClass();
        $objResponse = new IOResponse();
        if ($amount <= 0 || $kArtikel <= 0) {
            return $objResponse;
        }
        $Artikel = new Artikel();
        $Artikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
        // Falls der Artikel ein Variationskombikind ist, hole direkt seine Eigenschaften
        if ($Artikel->kEigenschaftKombi > 0 || $Artikel->nIstVater === 1) {
            // Variationskombi-Artikel
            $_POST['eigenschaftwert'] = $properties['eigenschaftwert'];
            $properties               = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($kArtikel);
        } elseif (isset($properties['eigenschaftwert']) && is_array($properties['eigenschaftwert'])) {
            // einfache Variation - keine Varkombi
            $_POST['eigenschaftwert'] = $properties['eigenschaftwert'];
            $properties               = ArtikelHelper::getSelectedPropertiesForArticle($kArtikel);
        }

        if ((int)$amount != $amount && $Artikel->cTeilbar !== 'Y') {
            $amount = max((int)$amount, 1);
        }
        // Prüfung
        $errors = WarenkorbHelper::addToCartCheck($Artikel, $amount, $properties);

        if (count($errors) > 0) {
            $localizedErrors = ArtikelHelper::getProductMessages($errors, true, $Artikel, $amount);

            $oResponse->nType  = 0;
            $oResponse->cLabel = Shop::Lang()->get('basket');
            $oResponse->cHints = StringHandler::utf8_convert_recursive($localizedErrors);
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
        $cart = \Session\Session::getCart();
        WarenkorbHelper::addVariationPictures($cart);
        /** @var Warenkorb $cart */
        $cart->fuegeEin($kArtikel, $amount, $properties)
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
        Kupon::reCheck();
        // Persistenter Warenkorb
        if (!isset($_POST['login'])) {
            WarenkorbPers::addToCheck($kArtikel, $amount, $properties);
        }
        $boxes         = Shop::Container()->getBoxService();
        $boxesToShow   = $boxes->render($boxes->buildList(Shop::getPageType()));
        $warensumme[0] = Preise::getLocalizedPriceString(
            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
        );
        $warensumme[1] = Preise::getLocalizedPriceString(
            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false)
        );
        $smarty->assign('Boxen', $boxesToShow)
               ->assign('WarenkorbWarensumme', $warensumme);

        $kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0)
            ? $_SESSION['Kunde']->kKundengruppe
            : \Session\Session::getCustomerGroup()->getID();
        $oXSelling     = ArtikelHelper::getXSelling($kArtikel, $Artikel->nIstVater > 0);

        $smarty->assign(
            'WarenkorbVersandkostenfreiHinweis',
            VersandartHelper::getShippingFreeString(
                VersandartHelper::getFreeShippingMinimum($kKundengruppe),
                $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
            )
        )
               ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
               ->assign('fAnzahl', $amount)
               ->assign('NettoPreise', \Session\Session::getCustomerGroup()->getIsMerchant())
               ->assign('Einstellungen', $config)
               ->assign('Xselling', $oXSelling)
               ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
               ->assign('oSpezialseiten_arr', Shop::Container()->getLinkService()->getSpecialPages())
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
            Kampagne::setCampaignAction(KAMPAGNE_DEF_WARENKORB, $kArtikel, $amount); // Warenkorb
        }

        if ($config['global']['global_warenkorb_weiterleitung'] === 'Y') {
            $oResponse->nType     = 1;
            $oResponse->cLocation = Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php');
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');
        }

        return $objResponse;
    }

    /**
     * @param int $kArtikel
     * @return IOResponse
     * @throws SmartyException
     */
    public function pushToComparelist(int $kArtikel): IOResponse
    {
        $conf        = Shopsetting::getInstance()->getAll();
        $oResponse   = new stdClass();
        $objResponse = new IOResponse();
        $smarty      = Shop::Smarty();

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

        $oResponse->cNotification = $smarty->assign('type', empty($error) ? 'info' : 'danger')
                                           ->assign('body', empty($error) ? $notice : $error)
                                           ->assign('buttons', $buttons)
                                           ->fetch('snippets/notification.tpl');

        $oResponse->cNavBadge = $smarty->assign('Einstellungen', $conf)
                                       ->fetch('layout/header_shop_nav_compare.tpl');

        foreach (Shop::Container()->getBoxService()->buildList() as $_position => $boxes) {
            /** @var \Boxes\Items\BoxInterface[] $boxes */
            if (!is_array($boxes)) {
                continue;
            }
            foreach ($boxes as $box) {
                if ($box->getType() === \Boxes\Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if (get_class($childBox) === \Boxes\Items\CompareList::class) {
                            $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $childBox);
                            $oResponse->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                }
                if (get_class($box) === \Boxes\Items\CompareList::class) {
                    $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $box);
                    $oResponse->cBoxContainer[$box->getID()] = $renderer->render();
                }
            }
        }

        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

        return $objResponse;
    }

    /**
     * @param int $kArtikel
     * @return IOResponse
     * @throws SmartyException
     */
    public function removeFromComparelist(int $kArtikel): IOResponse
    {
        $conf        = Shopsetting::getInstance()->getAll();
        $oResponse   = new stdClass();
        $objResponse = new IOResponse();
        $smarty      = Shop::Smarty();

        $_GET['Vergleichsliste'] = 1;
        $_GET['vlplo']           = $kArtikel;

        \Session\Session::getInstance()->setStandardSessionVars();
        $oResponse->nType     = 2;
        $oResponse->nCount    = count($_SESSION['Vergleichsliste']->oArtikel_arr);
        $oResponse->cTitle    = Shop::Lang()->get('compare');
        $oResponse->cNavBadge = $smarty->assign('Einstellungen', $conf)
                                       ->fetch('layout/header_shop_nav_compare.tpl');

        foreach (Shop::Container()->getBoxService()->buildList() as $_position => $boxes) {
            if (!is_array($boxes)) {
                continue;
            }
            /** @var \Boxes\Items\BoxInterface[] $boxes */
            foreach ($boxes as $box) {
                if ($box->getType() === \Boxes\Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if (get_class($childBox) === \Boxes\Items\CompareList::class) {
                            $smarty->assign('Einstellungen', $conf);
                            $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $childBox);

                            $oResponse->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                } elseif (get_class($box) === \Boxes\Items\CompareList::class) {
                    $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $box);
                    $oResponse->cBoxContainer[$box->getID()] = $renderer->render();
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
     * @throws SmartyException
     */
    public function pushToWishlist(int $kArtikel, $qty): IOResponse
    {
        $conf        = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_VERGLEICHSLISTE]);
        $oResponse   = new stdClass();
        $objResponse = new IOResponse();
        $qty         = (int)$qty === 0 ? 1 : (int)$qty;
        $smarty      = Shop::Smarty();
        if (\Session\Session::getCustomer()->getID() === 0) {
            $oResponse->nType     = 1;
            $oResponse->cLocation = Shop::Container()->getLinkService()->getStaticRoute('jtl.php') .
                '?a=' . $kArtikel .
                '&n=' . $qty .
                '&r=' . R_LOGIN_WUNSCHLISTE;
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
        $vals = Shop::Container()->getDB()->selectAll('teigenschaft', 'kArtikel', $kArtikel);
        if (!empty($vals) && !ArtikelHelper::isParent($kArtikel)) {
            // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
            // muss zum Artikel weitergeleitet werden um Variationen zu wählen
            $oResponse->nType     = 1;
            $oResponse->cLocation = (Shop::getURL() . '/?a=' . $kArtikel .
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
               ->assign('Einstellungen', $conf);

        $oResponse->cNotification = $smarty->fetch('snippets/notification.tpl');
        $oResponse->cNavBadge     = $smarty->fetch('layout/header_shop_nav_wish.tpl');
        foreach (Shop::Container()->getBoxService()->buildList() as $_position => $boxes) {
            if (!is_array($boxes)) {
                continue;
            }
            /** @var \Boxes\Items\BoxInterface[] $boxes */
            foreach ($boxes as $box) {
                if ($box->getType() === \Boxes\Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if (get_class($childBox) === \Boxes\Items\Wishlist::class) {
                            $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $childBox);
                            $oResponse->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                } elseif (get_class($box) === \Boxes\Items\Wishlist::class) {
                    $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $box);
                    $oResponse->cBoxContainer[$box->getID()] = $renderer->render();
                }
            }
        }

        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

        return $objResponse;
    }

    /**
     * @param int $kArtikel
     * @return IOResponse
     * @throws SmartyException
     */
    public function removeFromWishlist(int $kArtikel): IOResponse
    {
        $conf        = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_VERGLEICHSLISTE]);
        $oResponse   = new stdClass();
        $objResponse = new IOResponse();
        $smarty      = Shop::Smarty();

        $_GET['Wunschliste'] = 1;
        $_GET['wlplo']       = $kArtikel;

        \Session\Session::getInstance()->setStandardSessionVars();
        $oResponse->nType  = 2;
        $oResponse->nCount = count($_SESSION['Wunschliste']->CWunschlistePos_arr);
        $oResponse->cTitle = Shop::Lang()->get('goToWishlist');

        $oResponse->cNavBadge = $smarty->assign('Einstellungen', $conf)
                                       ->fetch('layout/header_shop_nav_wish.tpl');

        foreach (Shop::Container()->getBoxService()->buildList() as $_position => $boxes) {
            if (!is_array($boxes)) {
                continue;
            }
            /** @var \Boxes\Items\BoxInterface[] $boxes */
            foreach ($boxes as $box) {
                if ($box->getType() === \Boxes\Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if ($childBox->getType() === \Boxes\Items\Wishlist::class) {
                            $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $childBox);
                            $oResponse->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                } elseif (get_class($box) === \Boxes\Items\Wishlist::class) {
                    $renderer = new \Boxes\Renderer\DefaultRenderer($smarty, $box);
                    $oResponse->cBoxContainer[$box->getID()] = $renderer->render();
                }
            }
        }
        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

        return $objResponse;
    }

    /**
     * @param int $nTyp - 0 = Template, 1 = Object
     * @return IOResponse
     * @throws SmartyException
     */
    public function getBasketItems($nTyp = 0): IOResponse
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
        $cart        = \Session\Session::getCart();
        $oResponse   = new stdClass();
        $objResponse = new IOResponse();

        WarenkorbHelper::addVariationPictures($cart);
        switch ($nTyp) {
            default:
            case 0:
                $smarty        = Shop::Smarty();
                $kKundengruppe = \Session\Session::getCustomerGroup()->getID();
                $nAnzahl       = $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]);
                $cLand         = $_SESSION['cLieferlandISO'] ?? '';
                $cPLZ          = '*';

                if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
                    $cLand         = $_SESSION['Kunde']->cLand;
                    $cPLZ          = $_SESSION['Kunde']->cPLZ;
                }
                $error               = $smarty->getTemplateVars('fehler');
                $versandkostenfreiAb = VersandartHelper::getFreeShippingMinimum($kKundengruppe, $cLand);
                $smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
                       ->assign('Warensumme', $cart->gibGesamtsummeWaren())
                       ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
                       ->assign('Einstellungen', Shop::getSettings([CONF_GLOBAL]))
                       ->assign('WarenkorbArtikelPositionenanzahl', $nAnzahl)
                       ->assign('WarenkorbArtikelanzahl', $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]))
                       ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
                       ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
                       ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
                       ->assign('NettoPreise', \Session\Session::getCustomerGroup()->getIsMerchant())
                       ->assign('FavourableShipping', $cart->getFavourableShipping())
                       ->assign('WarenkorbVersandkostenfreiHinweis', VersandartHelper::getShippingFreeString(
                           $versandkostenfreiAb,
                           $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)
                       ))
                       ->assign('oSpezialseiten_arr', Shop::Container()->getLinkService()->getSpecialPages());

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
     * @throws SmartyException
     */
    public function buildConfiguration($aValues): IOResponse
    {
        $smarty          = Shop::Smarty();
        $oResponse       = new IOResponse();
        $Artikel         = new Artikel();
        $productID       = (int)($aValues['VariKindArtikel'] ?? $aValues['a']);
        $items           = $aValues['item'] ?? [];
        $quantities      = $aValues['quantity'] ?? [];
        $itemQuantities  = $aValues['item_quantity'] ?? [];
        $variationValues = $aValues['eigenschaftwert'] ?? [];
        $amount          = $aValues['anzahl'] ?? 1;
        $oKonfig         = ArtikelHelper::buildConfig(
            $productID,
            $amount,
            $variationValues,
            $items,
            $quantities,
            $itemQuantities
        );
        $net             = \Session\Session::getCustomerGroup()->getIsMerchant();
        $Artikel->fuelleArtikel($productID, null);
        $Artikel->Preise->cVKLocalized[$net] =
            Preise::getLocalizedPriceString($Artikel->Preise->fVK[$net] * $amount, null, true);

        $smarty->assign('oKonfig', $oKonfig)
               ->assign('NettoPreise', $net)
               ->assign('Artikel', $Artikel);
        $oKonfig->cTemplate = $smarty->fetch('productdetails/config_summary.tpl');

        $oResponse->script('this.response = ' . json_encode($oKonfig) . ';');

        return $oResponse;
    }

    /**
     * @param int   $productID
     * @param array|null $selectedVariationValues
     * @return null|stdClass
     */
    public function getArticleStockInfo(int $productID, $selectedVariationValues = null): ?stdClass
    {
        $result = (object)[
            'stock'  => false,
            'status' => 0,
            'text'   => '',
        ];

        if ($selectedVariationValues !== null) {
            $products = $this->getArticleByVariations($productID, $selectedVariationValues);

            if (count($products) === 1) {
                $productID = $products[0]->kArtikel;
            } else {
                return $result;
            }
        }

        if ($productID > 0) {
            $product = new Artikel();
            $options = (object)[
                'nMain'                     => 0,
                'nWarenlager'               => 0,
                'nVariationKombi'           => 0,
                'nVariationen'              => 0,
                'nKeinLagerbestandBeachten' => 1,
            ];

            $product->fuelleArtikel(
                $productID,
                $options,
                Kundengruppe::getCurrent(),
                Shop::getLanguageID()
            );

            $stockInfo = $product->getStockInfo();

            if ($stockInfo->notExists || !$stockInfo->inStock) {
                $result->stock = false;
                $result->text  = $stockInfo->notExists
                    ? Shop::Lang()->get('notAvailableInSelection')
                    : Shop::Lang()->get('ampelRot');
            } else {
                $result->stock = true;
                $result->text  = '';
            }

            $result->status = $product->Lageranzeige->nStatus;
        }

        return $result;
    }

    /**
     * @param array $aValues
     * @return IOResponse
     */
    public function checkDependencies($aValues): IOResponse
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
        $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen, \Session\Session::getCustomerGroup()->getID());
        $weightDiff   = 0;
        $newProductNr = '';
        foreach ($valueID_arr as $valueID) {
            $currentValue = new EigenschaftWert($valueID);
            $weightDiff   += $currentValue->fGewichtDiff;
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
        foreach ($valueID_arr as $kKey => $cVal) {
            if (!isset($nKeyValueVariation_arr[$kKey])) {
                unset($valueID_arr[$kKey]);
            }
        }

        $nNettoPreise = \Session\Session::getCustomerGroup()->getIsMerchant();
        $fVKNetto     = $oArtikel->gibPreis($fAnzahl, $valueID_arr, \Session\Session::getCustomerGroup()->getID());
        $fVK          = [
            TaxHelper::getGross($fVKNetto, $_SESSION['Steuersatz'][$oArtikel->kSteuerklasse]),
            $fVKNetto
        ];
        $cVKLocalized = [
            0 => Preise::getLocalizedPriceString($fVK[0]),
            1 => Preise::getLocalizedPriceString($fVK[1])
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
                $fStaffelVKNetto         = $oArtikel->gibPreis(
                    $nAnzahl,
                    $valueID_arr,
                    \Session\Session::getCustomerGroup()->getID()
                );
                $fStaffelVK[0][$nAnzahl] = TaxHelper::getGross(
                    $fStaffelVKNetto,
                    $_SESSION['Steuersatz'][$oArtikel->kSteuerklasse]
                );
                $fStaffelVK[1][$nAnzahl] = $fStaffelVKNetto;
                $cStaffelVK[0][$nAnzahl] = Preise::getLocalizedPriceString($fStaffelVK[0][$nAnzahl]);
                $cStaffelVK[1][$nAnzahl] = Preise::getLocalizedPriceString($fStaffelVK[1][$nAnzahl]);
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
     * @param array $aValues
     * @param int   $kEigenschaft
     * @param int   $kEigenschaftWert
     * @return IOResponse
     */
    public function checkVarkombiDependencies($aValues, $kEigenschaft = 0, $kEigenschaftWert = 0): IOResponse
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
                $products = $this->getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWert_arr);

                if (count($products) === 1 && $kArtikelKind !== (int)$products[0]->kArtikel) {
                    $oArtikelTMP                  = $products[0];
                    $oGesetzteEigeschaftWerte_arr = [];
                    foreach ($kFreifeldEigeschaftWert_arr as $cKey => $cValue) {
                        $oGesetzteEigeschaftWerte_arr[] = (object)[
                            'key'   => $cKey,
                            'value' => $cValue
                        ];
                    }
                    $cUrl = UrlHelper::buildURL($oArtikelTMP, URLART_ARTIKEL, true);
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
            $checkStockInfo      = count($kGesetzteEigeschaftWert_arr) > 0
                && (count($kGesetzteEigeschaftWert_arr) === count($nPossibleVariations) - 1);
            $stockInfo           = (object)[
                'stock'  => true,
                'status' => 2,
                'text'   => '',
            ];

            foreach ($oArtikel->Variationen as $variation) {
                if (in_array($variation->cTyp, ['FREITEXT', 'PFLICHTFREITEXT'])) {
                    $objResponse->jsfunc('$.evo.article().variationEnable', $variation->kEigenschaft, 0, $wrapper);
                } else {
                    foreach ($variation->Werte as $value) {
                        $stockInfo->stock = true;
                        $stockInfo->text  = '';

                        if (isset($nPossibleVariations[$value->kEigenschaft])
                            && in_array($value->kEigenschaftWert, $nPossibleVariations[$value->kEigenschaft])
                        ) {
                            $objResponse->jsfunc(
                                '$.evo.article().variationEnable',
                                $value->kEigenschaft,
                                $value->kEigenschaftWert,
                                $wrapper
                            );

                            if ($checkStockInfo
                                && !array_key_exists($value->kEigenschaft, $kGesetzteEigeschaftWert_arr)
                            ) {
                                $kGesetzteEigeschaftWert_arr[$value->kEigenschaft] = $value->kEigenschaftWert;

                                $products = $this->getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWert_arr);
                                if (count($products) === 1) {
                                    $stockInfo = $this->getArticleStockInfo((int)$products[0]->kArtikel);
                                }
                                unset($kGesetzteEigeschaftWert_arr[$value->kEigenschaft]);
                            }
                        } else {
                            $stockInfo->stock  = false;
                            $stockInfo->status = 0;
                            $stockInfo->text   = Shop::Lang()->get('notAvailableInSelection');
                        }
                        if ($value->notExists || !$value->inStock) {
                            $stockInfo->stock  = false;
                            $stockInfo->status = 0;
                            $stockInfo->text   = $value->notExists
                                ? Shop::Lang()->get('notAvailableInSelection')
                                : Shop::Lang()->get('ampelRot');
                        }
                        if (!$stockInfo->stock) {
                            $objResponse->jsfunc(
                                '$.evo.article().variationInfo',
                                $value->kEigenschaftWert,
                                $stockInfo->status,
                                $stockInfo->text,
                                $wrapper
                            );
                        }
                    }

                    if (isset($kGesetzteEigeschaftWert_arr[$variation->kEigenschaft])) {
                        $objResponse->jsfunc(
                            '$.evo.article().variationActive',
                            $variation->kEigenschaft,
                            addslashes($kGesetzteEigeschaftWert_arr[$variation->kEigenschaft]),
                            null,
                            $wrapper
                        );
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
     * @param int   $parentProductID
     * @param array $selectedVariationValues
     * @return array
     */
    public function getArticleByVariations(int $parentProductID, $selectedVariationValues): array
    {
        if (!is_array($selectedVariationValues) || count($selectedVariationValues) === 0) {
            return [];
        }

        $variationID    = 0;
        $variationValue = 0;

        if (count($selectedVariationValues) > 0) {
            $combinations = [];
            $i            = 0;
            foreach ($selectedVariationValues as $id => $value) {
                if (0 === $i++) {
                    $variationID    = $id;
                    $variationValue = $value;
                } else {
                    $combinations[] = "($id, $value)";
                }
            }
        } else {
            $combinations = null;
        }

        $combinationSQL = ($combinations !== null && count($combinations) > 0)
            ? 'EXISTS (
                     SELECT 1
                     FROM teigenschaftkombiwert innerKombiwert
                     WHERE (innerKombiwert.kEigenschaft, innerKombiwert.kEigenschaftWert) IN 
                     (' . implode(', ', $combinations) . ')
                        AND innerKombiwert.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                     GROUP BY innerKombiwert.kEigenschaftKombi
                     HAVING COUNT(innerKombiwert.kEigenschaftKombi) = ' . count($combinations) . '
                )
                AND '
            : '';

        return Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikel.kArtikel,
                tseo.kKey AS kSeoKey, COALESCE(tseo.cSeo, \'\') AS cSeo,
                tartikel.fLagerbestand, tartikel.cLagerBeachten, tartikel.cLagerKleinerNull
                FROM teigenschaftkombiwert
                INNER JOIN tartikel ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                LEFT JOIN tseo ON tseo.cKey = \'kArtikel\'
                                AND tseo.kKey = tartikel.kArtikel
                                AND tseo.kSprache = :languageID
                LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                                AND tartikelsichtbarkeit.kKundengruppe = :customergroupID
                WHERE ' . $combinationSQL . 'tartikel.kVaterArtikel = :parentProductID
                    AND teigenschaftkombiwert.kEigenschaft = :variationID
                    AND teigenschaftkombiwert.kEigenschaftWert = :variationValue
                    AND tartikelsichtbarkeit.kArtikel IS NULL',
            [
                'languageID'      => Shop::getLanguageID(),
                'customergroupID' => \Session\Session::getCustomerGroup()->getID(),
                'parentProductID' => $parentProductID,
                'variationID'     => $variationID,
                'variationValue'  => $variationValue,
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int $categoryId
     * @return IOResponse
     * @throws SmartyException
     */
    public function getCategoryMenu(int $categoryId): IOResponse
    {
        $smarty = Shop::Smarty();
        $auto   = $categoryId === 0;

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
    public function getRegionsByCountry(string $country): IOResponse
    {
        $response = new IOResponse();

        if (strlen($country) === 2) {
            $regions = Staat::getRegions($country);
            $response->script('this.response = ' . json_encode($regions) . ';');
        }

        return $response;
    }

    /**
     * @param string $country
     * @return IOResponse
     */
    public function checkDeliveryCountry(string $country): IOResponse
    {
        $response = new IOResponse();

        if (strlen($country) === 2) {
            $deliveryCountries = VersandartHelper::getPossibleShippingCountries(Session::getCustomerGroup()->getID(), false, false, [$country]);
            $response->script('this.response = ' . (count($deliveryCountries) === 1 ? 'true' : 'false') . ';');
        }

        return $response;
    }

    /**
     * @param string $cKey
     * @param int    $kKey
     * @param int    $kSprache
     * @param array  $kSelection_arr
     * @return IOResponse
     */
    public function setSelectionWizardAnswers($cKey, $kKey, $kSprache, $kSelection_arr): IOResponse
    {
        $smarty   = Shop::Smarty();
        $response = new IOResponse();
        $AWA      = AuswahlAssistent::startIfRequired($cKey, $kKey, $kSprache, $smarty, $kSelection_arr);

        if ($AWA !== null) {
            $oLastSelectedValue = $AWA->getLastSelectedValue();
            $NaviFilter         = $AWA->getNaviFilter();

            if (($oLastSelectedValue !== null && $oLastSelectedValue->nAnzahl === 1)
                || $AWA->getCurQuestion() === $AWA->getQuestionCount()
                || $AWA->getQuestion($AWA->getCurQuestion())->nTotalResultCount === 0) {
                $response->script("window.location.href='" . StringHandler::htmlentitydecode(
                    $NaviFilter->getFilterURL()->getURL()
                ) . "';");
            } else {
                $response->assign('selectionwizard', 'innerHTML', $AWA->fetchForm($smarty));
            }
        }

        return $response;
    }

    /**
     * @return IOResponse
     * @deprecated since 5.0.0
     */
    public function generateToken(): IOResponse
    {
        $objResponse             = new IOResponse();
        $cToken                  = gibToken();
        $cName                   = gibTokenName();
        $token_arr               = ['name' => $cName, 'token' => $cToken];
        $_SESSION['xcrsf_token'] = json_encode($token_arr);
        $objResponse->script("doXcsrfToken('" . $cName . "', '" . $cToken . "');");

        return $objResponse;
    }
}
