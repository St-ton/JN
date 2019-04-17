<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\IO;

use Exception;
use JTL\Alert\Alert;
use JTL\Boxes\Renderer\DefaultRenderer;
use JTL\Boxes\Type;
use JTL\Catalog\Product\Artikel;
use JTL\Boxes\Items\BoxInterface;
use JTL\Boxes\Items\CompareList;
use JTL\Boxes\Items\Wishlist;
use JTL\Cart\Warenkorb;
use JTL\Cart\WarenkorbPers;
use JTL\DB\ReturnType;
use JTL\Catalog\Product\EigenschaftWert;
use JTL\Extensions\AuswahlAssistent;
use JTL\Helpers\Cart;
use JTL\Helpers\Product;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Helpers\Tax;
use JTL\Helpers\URL;
use JTL\Kampagne;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Customer\Kundengruppe;
use JTL\Checkout\Kupon;
use JTL\Catalog\Product\Preise;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Staat;
use JTL\Catalog\Trennzeichen;
use SmartyException;
use stdClass;
use JTL\Catalog\Wishlist\Wunschliste;

require_once \PFAD_ROOT . \PFAD_INCLUDES . 'artikel_inc.php';

/**
 * Class IOMethods
 * @package JTL\IO
 */
class IOMethods
{
    /**
     * @var IO
     */
    private $io;

    /**
     * IOMethods constructor.
     *
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
                        ->register('updateWishlistDropdown', [$this, 'updateWishlistDropdown'])
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
        if (\mb_strlen($keyword) < 2) {
            return $results;
        }
        $smarty     = Shop::Smarty();
        $language   = Shop::getLanguage();
        $maxResults = ($cnt = Shop::getSettingValue(\CONF_ARTIKELUEBERSICHT, 'suche_ajax_anzahl')) > 0
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
            ReturnType::ARRAY_OF_OBJECTS
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
            $cityQuery = '%' . Text::filterXSS($cityQuery) . '%';
            $cities    = Shop::Container()->getDB()->queryPrepared(
                'SELECT cOrt
                    FROM tplz
                    WHERE cLandISO = :country
                        AND cPLZ = :zip
                        AND cOrt LIKE :cityQuery',
                ['country' => $country, 'zip' => $zip, 'cityQuery' => $cityQuery],
                ReturnType::ARRAY_OF_OBJECTS
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
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';

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
            $properties               = Product::getSelectedPropertiesForVarCombiArticle($kArtikel);
        } elseif (isset($properties['eigenschaftwert']) && \is_array($properties['eigenschaftwert'])) {
            // einfache Variation - keine Varkombi
            $_POST['eigenschaftwert'] = $properties['eigenschaftwert'];
            $properties               = Product::getSelectedPropertiesForArticle($kArtikel);
        }

        if ((int)$amount != $amount && $Artikel->cTeilbar !== 'Y') {
            $amount = \max((int)$amount, 1);
        }
        // Prüfung
        $errors = Cart::addToCartCheck($Artikel, $amount, $properties);

        if (\count($errors) > 0) {
            $localizedErrors = Product::getProductMessages($errors, true, $Artikel, $amount);

            $oResponse->nType  = 0;
            $oResponse->cLabel = Shop::Lang()->get('basket');
            $oResponse->cHints = Text::utf8_convert_recursive($localizedErrors);
            $objResponse->script('this.response = ' . \json_encode($oResponse) . ';');

            return $objResponse;
        }
        $cart = Frontend::getCart();
        Cart::addVariationPictures($cart);
        /** @var Warenkorb $cart */
        $cart->fuegeEin($kArtikel, $amount, $properties)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDPOS)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZAHLUNGSART)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
             ->loescheSpezialPos(\C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);

        unset(
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Versandart'],
            $_SESSION['Zahlungsart']
        );
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb,
        // dann verwerfen und neu anlegen
        Kupon::reCheck();
        // Persistenter Warenkorb
        if (!isset($_POST['login'])) {
            WarenkorbPers::addToCheck($kArtikel, $amount, $properties);
        }
        $pageType      = Shop::getPageType();
        $boxes         = Shop::Container()->getBoxService();
        $boxesToShow   = $boxes->render($boxes->buildList($pageType), $pageType);
        $warensumme[0] = Preise::getLocalizedPriceString(
            $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
        );
        $warensumme[1] = Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL]));
        $smarty->assign('Boxen', $boxesToShow)
               ->assign('WarenkorbWarensumme', $warensumme);

        $kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0)
            ? $_SESSION['Kunde']->kKundengruppe
            : Frontend::getCustomerGroup()->getID();
        $oXSelling     = Product::getXSelling($kArtikel, $Artikel->nIstVater > 0);

        $smarty->assign(
            'WarenkorbVersandkostenfreiHinweis',
            ShippingMethod::getShippingFreeString(
                ShippingMethod::getFreeShippingMinimum($kKundengruppe),
                $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
            )
        )
               ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
               ->assign('fAnzahl', $amount)
               ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
               ->assign('Einstellungen', $config)
               ->assign('Xselling', $oXSelling)
               ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
               ->assign('oSpezialseiten_arr', Shop::Container()->getLinkService()->getSpecialPages())
               ->assign('Steuerpositionen', $cart->gibSteuerpositionen());

        $oResponse->nType           = 2;
        $oResponse->cWarenkorbText  = \lang_warenkorb_warenkorbEnthaeltXArtikel($cart);
        $oResponse->cWarenkorbLabel = \lang_warenkorb_warenkorbLabel($cart);
        $oResponse->cPopup          = $smarty->fetch('productdetails/pushed.tpl');
        $oResponse->cWarenkorbMini  = $smarty->fetch('basket/cart_dropdown.tpl');
        $oResponse->oArtikel        = $Artikel;
        $oResponse->cNotification   = Shop::Lang()->get('basketAllAdded', 'messages');

        $objResponse->script('this.response = ' . \json_encode($oResponse) . ';');
        // Kampagne
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Kampagne::setCampaignAction(\KAMPAGNE_DEF_WARENKORB, $kArtikel, $amount); // Warenkorb
        }

        if ($config['global']['global_warenkorb_weiterleitung'] === 'Y') {
            $oResponse->nType     = 1;
            $oResponse->cLocation = Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php');
            $objResponse->script('this.response = ' . \json_encode($oResponse) . ';');
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
        $response    = new stdClass();
        $objResponse = new IOResponse();
        $smarty      = Shop::Smarty();

        $_POST['Vergleichsliste'] = 1;
        $_POST['a']               = $kArtikel;

        Cart::checkAdditions();
        $response->nType  = 2;
        $response->nCount = \count($_SESSION['Vergleichsliste']->oArtikel_arr);
        $response->cTitle = Shop::Lang()->get('compare');
        $buttons          = [
            (object)[
                'href'    => '#',
                'fa'      => 'fa fa-arrow-circle-right',
                'title'   => Shop::Lang()->get('continueShopping', 'checkout'),
                'primary' => true,
                'dismiss' => 'modal'
            ]
        ];

        if ($response->nCount > 1) {
            \array_unshift($buttons, (object)[
                'href'  => 'vergleichsliste.php',
                'fa'    => 'fa-tasks',
                'title' => Shop::Lang()->get('compare')
            ]);
        }
        $alerts  = Shop::Container()->getAlertService();
        $content = $smarty->assign('alertList', $alerts)
                          ->fetch('snippets/alert_list.tpl');

        $response->cNotification = $smarty
            ->assign(
                'type',
                $alerts->alertTypeExists(Alert::TYPE_ERROR) ? 'danger' : 'info'
            )
            ->assign('body', $content)
            ->assign('buttons', $buttons)
            ->fetch('snippets/notification.tpl');

        $response->cNavBadge   = $smarty->assign('Einstellungen', $conf)
                                        ->fetch('layout/header_shop_nav_compare.tpl');
        $response->navDropdown = $smarty->fetch('snippets/comparelist_dropdown.tpl');

        foreach (Shop::Container()->getBoxService()->buildList() as $boxes) {
            /** @var BoxInterface[] $boxes */
            if (!\is_array($boxes)) {
                continue;
            }
            foreach ($boxes as $box) {
                if ($box->getType() === Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if (\get_class($childBox) === CompareList::class) {
                            $renderer = new DefaultRenderer($smarty, $childBox);

                            $response->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                }
                if (\get_class($box) === CompareList::class) {
                    $renderer = new DefaultRenderer($smarty, $box);

                    $response->cBoxContainer[$box->getID()] = $renderer->render();
                }
            }
        }

        $objResponse->script('this.response = ' . \json_encode($response) . ';');

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
        $response    = new stdClass();
        $objResponse = new IOResponse();
        $smarty      = Shop::Smarty();

        $_GET['Vergleichsliste'] = 1;
        $_GET['vlplo']           = $kArtikel;

        Frontend::getInstance()->setStandardSessionVars();
        $response->nType       = 2;
        $response->nCount      = isset($_SESSION['Vergleichsliste']->oArtikel_arr) ?
            \count($_SESSION['Vergleichsliste']->oArtikel_arr) : 0;
        $response->cTitle      = Shop::Lang()->get('compare');
        $response->cNavBadge   = $smarty->assign('Einstellungen', $conf)
                                        ->fetch('layout/header_shop_nav_compare.tpl');
        $response->navDropdown = $smarty->fetch('snippets/comparelist_dropdown.tpl');

        foreach (Shop::Container()->getBoxService()->buildList() as $boxes) {
            if (!\is_array($boxes)) {
                continue;
            }
            /** @var BoxInterface[] $boxes */
            foreach ($boxes as $box) {
                if ($box->getType() === Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if (\get_class($childBox) === CompareList::class) {
                            $smarty->assign('Einstellungen', $conf);
                            $renderer = new DefaultRenderer($smarty, $childBox);

                            $response->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                } elseif (\get_class($box) === CompareList::class) {
                    $renderer = new DefaultRenderer($smarty, $box);

                    $response->cBoxContainer[$box->getID()] = $renderer->render();
                }
            }
        }

        $objResponse->script('this.response = ' . \json_encode($response) . ';');

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
        $conf        = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_VERGLEICHSLISTE]);
        $response    = new stdClass();
        $objResponse = new IOResponse();
        $qty         = (int)$qty === 0 ? 1 : (int)$qty;
        $smarty      = Shop::Smarty();
        if (Frontend::getCustomer()->getID() === 0) {
            $response->nType     = 1;
            $response->cLocation = Shop::Container()->getLinkService()->getStaticRoute('jtl.php') .
                '?a=' . $kArtikel .
                '&n=' . $qty .
                '&r=' . \R_LOGIN_WUNSCHLISTE;
            $objResponse->script('this.response = ' . \json_encode($response) . ';');

            return $objResponse;
        }
        $vals = Shop::Container()->getDB()->selectAll('teigenschaft', 'kArtikel', $kArtikel);
        if (!empty($vals) && !Product::isParent($kArtikel)) {
            // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
            // muss zum Artikel weitergeleitet werden um Variationen zu wählen
            $response->nType     = 1;
            $response->cLocation = (Shop::getURL() . '/?a=' . $kArtikel .
                '&n=' . $qty .
                '&r=' . \R_VARWAEHLEN);
            $objResponse->script('this.response = ' . \json_encode($response) . ';');

            return $objResponse;
        }

        $_POST['Wunschliste'] = 1;
        $_POST['a']           = $kArtikel;
        $_POST['n']           = (int)$qty;

        Cart::checkAdditions();

        $response->nType  = 2;
        $response->nCount = \count($_SESSION['Wunschliste']->CWunschlistePos_arr);
        $response->cTitle = Shop::Lang()->get('goToWishlist');
        $buttons          = [
            (object)[
                'href'    => '#',
                'fa'      => 'fa fa-arrow-circle-right',
                'title'   => Shop::Lang()->get('continueShopping', 'checkout'),
                'primary' => true,
                'dismiss' => 'modal'
            ]
        ];

        if ($response->nCount > 1) {
            \array_unshift($buttons, (object)[
                'href'  => 'wunschliste.php',
                'fa'    => 'fa-tasks',
                'title' => Shop::Lang()->get('goToWishlist')
            ]);
        }
        $alerts = Shop::Container()->getAlertService();
        $body   = $smarty->assign('alertList', $alerts)
                         ->fetch('snippets/alert_list.tpl');

        $smarty->assign('type', $alerts->alertTypeExists(Alert::TYPE_ERROR) ? 'danger' : 'info')
               ->assign('body', $body)
               ->assign('buttons', $buttons)
               ->assign('Einstellungen', $conf);

        $response->cNotification = $smarty->fetch('snippets/notification.tpl');
        $response->cNavBadge     = $smarty->fetch('layout/header_shop_nav_wish.tpl');
        foreach (Shop::Container()->getBoxService()->buildList() as $boxes) {
            if (!\is_array($boxes)) {
                continue;
            }
            /** @var BoxInterface[] $boxes */
            foreach ($boxes as $box) {
                if ($box->getType() === Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if (\get_class($childBox) === Wishlist::class) {
                            $renderer                                    = new DefaultRenderer($smarty, $childBox);
                            $response->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                } elseif (\get_class($box) === Wishlist::class) {
                    $renderer                               = new DefaultRenderer($smarty, $box);
                    $response->cBoxContainer[$box->getID()] = $renderer->render();
                }
            }
        }

        $objResponse->script('this.response = ' . \json_encode($response) . ';');

        if ($conf['global']['global_wunschliste_weiterleitung'] === 'Y') {
            $response->nType     = 1;
            $response->cLocation = Shop::Container()->getLinkService()->getStaticRoute('wunschliste.php');
            $objResponse->script('this.response = ' . \json_encode($response) . ';');
        }

        return $objResponse;
    }

    /**
     * @param int $kArtikel
     * @return IOResponse
     * @throws SmartyException
     */
    public function removeFromWishlist(int $kArtikel): IOResponse
    {
        $conf        = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_VERGLEICHSLISTE]);
        $response    = new stdClass();
        $objResponse = new IOResponse();
        $smarty      = Shop::Smarty();

        $_GET['Wunschliste'] = 1;
        $_GET['wlplo']       = $kArtikel;

        Frontend::getInstance()->setStandardSessionVars();
        $response->nType  = 2;
        $response->nCount = \count($_SESSION['Wunschliste']->CWunschlistePos_arr);
        $response->cTitle = Shop::Lang()->get('goToWishlist');

        $response->cNavBadge = $smarty->assign('Einstellungen', $conf)
                                       ->fetch('layout/header_shop_nav_wish.tpl');

        foreach (Shop::Container()->getBoxService()->buildList() as $boxes) {
            if (!\is_array($boxes)) {
                continue;
            }
            /** @var BoxInterface[] $boxes */
            foreach ($boxes as $box) {
                if ($box->getType() === Type::CONTAINER) {
                    foreach ($box->getChildren() as $childBox) {
                        if ($childBox->getType() === Wishlist::class) {
                            $renderer                                    = new DefaultRenderer($smarty, $childBox);
                            $response->cBoxContainer[$childBox->getID()] = $renderer->render();
                        }
                    }
                } elseif (\get_class($box) === Wishlist::class) {
                    $renderer                               = new DefaultRenderer($smarty, $box);
                    $response->cBoxContainer[$box->getID()] = $renderer->render();
                }
            }
        }
        $objResponse->script('this.response = ' . \json_encode($response) . ';');

        return $objResponse;
    }

    /**
     * @return IOResponse
     * @throws SmartyException
     */
    public function updateWishlistDropdown(): IOResponse
    {
        $response    = new stdClass();
        $objResponse = new IOResponse();
        $smarty      = Shop::Smarty();

        $smarty->assign('wishlists', Wunschliste::getWishlists());

        $response->content         = $smarty->fetch('snippets/wishlist_dropdown.tpl');
        $response->currentPosCount = count(Frontend::getWishList()->CWunschlistePos_arr);

        $objResponse->script('this.response = ' . \json_encode($response) . ';');

        return $objResponse;
    }

    /**
     * @param int $nTyp - 0 = Template, 1 = Object
     * @return IOResponse
     * @throws SmartyException
     */
    public function getBasketItems($nTyp = 0): IOResponse
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';
        $cart        = Frontend::getCart();
        $response    = new stdClass();
        $objResponse = new IOResponse();

        Cart::addVariationPictures($cart);
        switch ($nTyp) {
            default:
            case 0:
                $smarty        = Shop::Smarty();
                $kKundengruppe = Frontend::getCustomerGroup()->getID();
                $nAnzahl       = $cart->gibAnzahlPositionenExt([\C_WARENKORBPOS_TYP_ARTIKEL]);
                $cLand         = $_SESSION['cLieferlandISO'] ?? '';
                $cPLZ          = '*';

                if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                    $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
                    $cLand         = $_SESSION['Kunde']->cLand;
                    $cPLZ          = $_SESSION['Kunde']->cPLZ;
                }
                $error               = $smarty->getTemplateVars('fehler');
                $versandkostenfreiAb = ShippingMethod::getFreeShippingMinimum($kKundengruppe, $cLand);
                $smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
                       ->assign('Warensumme', $cart->gibGesamtsummeWaren())
                       ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
                       ->assign('Einstellungen', Shop::getSettings([\CONF_GLOBAL]))
                       ->assign('WarenkorbArtikelPositionenanzahl', $nAnzahl)
                       ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
                       ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
                       ->assign('Warenkorbtext', \lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
                       ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
                       ->assign('FavourableShipping', $cart->getFavourableShipping())
                       ->assign('WarenkorbVersandkostenfreiHinweis', ShippingMethod::getShippingFreeString(
                           $versandkostenfreiAb,
                           $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
                       ))
                       ->assign('oSpezialseiten_arr', Shop::Container()->getLinkService()->getSpecialPages());

                ShippingMethod::getShippingCosts($cLand, $cPLZ, $error);
                $response->cTemplate = $smarty->fetch('basket/cart_dropdown_label.tpl');
                break;

            case 1:
                $response->cItems = $cart->PositionenArr;
                break;
        }

        $objResponse->script('this.response = ' . \json_encode($response) . ';');

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
        $response        = new IOResponse();
        $Artikel         = new Artikel();
        $productID       = (int)($aValues['VariKindArtikel'] ?? $aValues['a']);
        $items           = $aValues['item'] ?? [];
        $quantities      = $aValues['quantity'] ?? [];
        $itemQuantities  = $aValues['item_quantity'] ?? [];
        $variationValues = $aValues['eigenschaftwert'] ?? [];
        $amount          = $aValues['anzahl'] ?? 1;
        $oKonfig         = Product::buildConfig(
            $productID,
            $amount,
            $variationValues,
            $items,
            $quantities,
            $itemQuantities,
            true
        );
        $net             = Frontend::getCustomerGroup()->getIsMerchant();
        $Artikel->fuelleArtikel($productID);
        $fVKNetto                      = $Artikel->gibPreis($amount, [], Frontend::getCustomerGroup()->getID());
        $fVK                           = [
            Tax::getGross($fVKNetto, $_SESSION['Steuersatz'][$Artikel->kSteuerklasse]),
            $fVKNetto
        ];
        $Artikel->Preise->cVKLocalized = [
            0 => Preise::getLocalizedPriceString($fVK[0]),
            1 => Preise::getLocalizedPriceString($fVK[1])
        ];

        $smarty->assign('oKonfig', $oKonfig)
               ->assign('NettoPreise', $net)
               ->assign('Artikel', $Artikel);
        $oKonfig->cTemplate = $smarty->fetch('productdetails/config_summary.tpl');

        $response->script('this.response = ' . \json_encode($oKonfig) . ';');

        return $response;
    }

    /**
     * @param int        $productID
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

            if (\count($products) === 1) {
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
        $valueIDs      = \array_filter((array)$aValues['eigenschaftwert']);
        $wrapper       = isset($aValues['wrapper']) ? Text::filterXSS($aValues['wrapper']) : '';

        if ($kVaterArtikel <= 0) {
            return $objResponse;
        }
        $options                            = new stdClass();
        $options->nMerkmale                 = 0;
        $options->nAttribute                = 0;
        $options->nArtikelAttribute         = 0;
        $options->nMedienDatei              = 0;
        $options->nVariationKombi           = 1;
        $options->nKeinLagerbestandBeachten = 1;
        $options->nKonfig                   = 0;
        $options->nDownload                 = 0;
        $options->nMain                     = 1;
        $options->nWarenlager               = 1;
        $product                            = new Artikel();
        $product->fuelleArtikel($kVaterArtikel, $options, Frontend::getCustomerGroup()->getID());
        $weightDiff   = 0;
        $newProductNr = '';

        // Alle Variationen ohne Freifeld
        $keyValueVariations = $product->keyValueVariations($product->VariationenOhneFreifeld);
        foreach ($valueIDs as $kKey => $cVal) {
            if (!isset($keyValueVariations[$kKey])) {
                unset($valueIDs[$kKey]);
            }
        }

        foreach ($valueIDs as $valueID) {
            $currentValue = new EigenschaftWert((int)$valueID);
            $weightDiff  += $currentValue->fGewichtDiff;
            $newProductNr = (!empty($currentValue->cArtNr) && $product->cArtNr !== $currentValue->cArtNr)
                ? $currentValue->cArtNr
                : $product->cArtNr;
        }
        $weightTotal        = Trennzeichen::getUnit(
            \JTL_SEPARATOR_WEIGHT,
            Shop::getLanguage(),
            $product->fGewicht + $weightDiff
        );
        $weightArticleTotal = Trennzeichen::getUnit(
            \JTL_SEPARATOR_WEIGHT,
            Shop::getLanguage(),
            $product->fArtikelgewicht + $weightDiff
        );
        $cUnitWeightLabel   = Shop::Lang()->get('weightUnit');

        $nNettoPreise = Frontend::getCustomerGroup()->getIsMerchant();
        $fVKNetto     = $product->gibPreis($fAnzahl, $valueIDs, Frontend::getCustomerGroup()->getID());
        $fVK          = [
            Tax::getGross($fVKNetto, $_SESSION['Steuersatz'][$product->kSteuerklasse]),
            $fVKNetto
        ];
        $cVKLocalized = [
            0 => Preise::getLocalizedPriceString($fVK[0]),
            1 => Preise::getLocalizedPriceString($fVK[1])
        ];
        $cPriceLabel  = '';
        if (isset($product->nVariationAnzahl) && $product->nVariationAnzahl > 0) {
            $cPriceLabel = $product->nVariationOhneFreifeldAnzahl === \count($valueIDs)
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
            [$product->fGewicht, $weightTotal . ' ' . $cUnitWeightLabel],
            [$product->fArtikelgewicht, $weightArticleTotal . ' ' . $cUnitWeightLabel],
        ], $wrapper);

        if (!empty($product->staffelPreis_arr)) {
            $fStaffelVK = [0 => [], 1 => []];
            $cStaffelVK = [0 => [], 1 => []];
            foreach ($product->staffelPreis_arr as $staffelPreis) {
                $nAnzahl                 = &$staffelPreis['nAnzahl'];
                $fStaffelVKNetto         = $product->gibPreis(
                    $nAnzahl,
                    $valueIDs,
                    Frontend::getCustomerGroup()->getID()
                );
                $fStaffelVK[0][$nAnzahl] = Tax::getGross(
                    $fStaffelVKNetto,
                    $_SESSION['Steuersatz'][$product->kSteuerklasse]
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

        if ($product->cVPE === 'Y'
            && $product->fVPEWert > 0
            && $product->cVPEEinheit
            && !empty($product->Preise)
        ) {
            $product->baueVPE($fVKNetto);
            $fStaffelVPE = [0 => [], 1 => []];
            $cStaffelVPE = [0 => [], 1 => []];
            foreach ($product->staffelPreis_arr as $key => $staffelPreis) {
                $nAnzahl                  = &$staffelPreis['nAnzahl'];
                $fStaffelVPE[0][$nAnzahl] = $product->fStaffelpreisVPE_arr[$key][0];
                $fStaffelVPE[1][$nAnzahl] = $product->fStaffelpreisVPE_arr[$key][1];
                $cStaffelVPE[0][$nAnzahl] = $staffelPreis['cBasePriceLocalized'][0];
                $cStaffelVPE[1][$nAnzahl] = $staffelPreis['cBasePriceLocalized'][1];
            }

            $objResponse->jsfunc(
                '$.evo.article().setVPEPrice',
                $product->cLocalizedVPE[$nNettoPreise],
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
        $kEigenschaft             = (int)$kEigenschaft;
        $kEigenschaftWert         = (int)$kEigenschaftWert;
        $oArtikel                 = null;
        $objResponse              = new IOResponse();
        $kVaterArtikel            = (int)$aValues['a'];
        $kArtikelKind             = isset($aValues['VariKindArtikel']) ? (int)$aValues['VariKindArtikel'] : 0;
        $idx                      = isset($aValues['eigenschaftwert']) ? (array)$aValues['eigenschaftwert'] : [];
        $kFreifeldEigeschaftWerte = [];
        $kGesetzteEigeschaftWerte = \array_filter($idx);
        $wrapper                  = isset($aValues['wrapper']) ? Text::filterXSS($aValues['wrapper']) : '';

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
            $keyValueVariations = $oArtikel->keyValueVariations($oArtikel->VariationenOhneFreifeld);
            // Freifeldpositionen gesondert zwischenspeichern
            foreach ($kGesetzteEigeschaftWerte as $kKey => $cVal) {
                if (!isset($keyValueVariations[$kKey])) {
                    unset($kGesetzteEigeschaftWerte[$kKey]);
                    $kFreifeldEigeschaftWerte[$kKey] = $cVal;
                }
            }
            $hasInvalidSelection = false;
            $invalidVariations   = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWerte, true);
            foreach ($kGesetzteEigeschaftWerte as $kKey => $kValue) {
                if (isset($invalidVariations[$kKey]) && \in_array($kValue, $invalidVariations[$kKey])) {
                    $hasInvalidSelection = true;
                    break;
                }
            }
            // Auswahl zurücksetzen sobald eine nicht vorhandene Variation ausgewählt wurde.
            if ($hasInvalidSelection) {
                $objResponse->jsfunc('$.evo.article().variationResetAll', $wrapper);

                $kGesetzteEigeschaftWerte = [$kEigenschaft => $kEigenschaftWert];
                $invalidVariations        = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWerte, true);

                // Auswählter EigenschaftWert ist ebenfalls nicht vorhanden
                if (\in_array($kEigenschaftWert, $invalidVariations[$kEigenschaft])) {
                    $kGesetzteEigeschaftWerte = [];

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
            if (\count($kGesetzteEigeschaftWerte) >= $oArtikel->nVariationOhneFreifeldAnzahl) {
                $products = $this->getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWerte);
                if (\count($products) === 1 && $kArtikelKind !== (int)$products[0]->kArtikel) {
                    $oArtikelTMP             = $products[0];
                    $gesetzteEigeschaftWerte = [];
                    foreach ($kFreifeldEigeschaftWerte as $cKey => $cValue) {
                        $gesetzteEigeschaftWerte[] = (object)[
                            'key'   => $cKey,
                            'value' => $cValue
                        ];
                    }
                    $cUrl = URL::buildURL($oArtikelTMP, \URLART_ARTIKEL, true);
                    $objResponse->jsfunc(
                        '$.evo.article().setArticleContent',
                        $kVaterArtikel,
                        $oArtikelTMP->kArtikel,
                        $cUrl,
                        $gesetzteEigeschaftWerte,
                        $wrapper
                    );

                    \executeHook(\HOOK_TOOLSAJAXSERVER_PAGE_TAUSCHEVARIATIONKOMBI, [
                        'objResponse' => &$objResponse,
                        'oArtikel'    => &$oArtikel,
                        'bIO'         => true
                    ]);

                    return $objResponse;
                }
            }

            $objResponse->jsfunc('$.evo.article().variationDisableAll', $wrapper);
            $nPossibleVariations = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWerte);
            $checkStockInfo      = \count($kGesetzteEigeschaftWerte) > 0
                && (\count($kGesetzteEigeschaftWerte) === \count($nPossibleVariations) - 1);
            $stockInfo           = (object)[
                'stock'  => true,
                'status' => 2,
                'text'   => '',
            ];
            foreach ($oArtikel->Variationen as $variation) {
                if (\in_array($variation->cTyp, ['FREITEXT', 'PFLICHTFREITEXT'])) {
                    $objResponse->jsfunc('$.evo.article().variationEnable', $variation->kEigenschaft, 0, $wrapper);
                } else {
                    foreach ($variation->Werte as $value) {
                        $stockInfo->stock = true;
                        $stockInfo->text  = '';

                        if (isset($nPossibleVariations[$value->kEigenschaft])
                            && \in_array($value->kEigenschaftWert, $nPossibleVariations[$value->kEigenschaft])
                        ) {
                            $objResponse->jsfunc(
                                '$.evo.article().variationEnable',
                                $value->kEigenschaft,
                                $value->kEigenschaftWert,
                                $wrapper
                            );

                            if ($checkStockInfo
                                && !\array_key_exists($value->kEigenschaft, $kGesetzteEigeschaftWerte)
                            ) {
                                $kGesetzteEigeschaftWerte[$value->kEigenschaft] = $value->kEigenschaftWert;

                                $products = $this->getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWerte);
                                if (\count($products) === 1) {
                                    $stockInfo = $this->getArticleStockInfo((int)$products[0]->kArtikel);
                                }
                                unset($kGesetzteEigeschaftWerte[$value->kEigenschaft]);
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

                    if (isset($kGesetzteEigeschaftWerte[$variation->kEigenschaft])) {
                        $objResponse->jsfunc(
                            '$.evo.article().variationActive',
                            $variation->kEigenschaft,
                            \addslashes($kGesetzteEigeschaftWerte[$variation->kEigenschaft]),
                            null,
                            $wrapper
                        );
                    }
                }
            }
        } else {
            $objResponse->jsfunc('$.evo.error', 'Article not found', $kVaterArtikel);
        }
        $objResponse->jsfunc('$.evo.article().variationRefreshAll', $wrapper);

        return $objResponse;
    }

    /**
     * @param int   $parentProductID
     * @param array $selectedVariationValues
     * @return array
     */
    public function getArticleByVariations(int $parentProductID, $selectedVariationValues): array
    {
        if (!\is_array($selectedVariationValues) || \count($selectedVariationValues) === 0) {
            return [];
        }

        $variationID    = 0;
        $variationValue = 0;

        if (\count($selectedVariationValues) > 0) {
            $combinations = [];
            $i            = 0;
            foreach ($selectedVariationValues as $id => $value) {
                if ($i++ === 0) {
                    $variationID    = $id;
                    $variationValue = $value;
                } else {
                    $combinations[] = "($id, $value)";
                }
            }
        } else {
            $combinations = null;
        }

        $combinationSQL = ($combinations !== null && \count($combinations) > 0)
            ? 'EXISTS (
                     SELECT 1
                     FROM teigenschaftkombiwert innerKombiwert
                     WHERE (innerKombiwert.kEigenschaft, innerKombiwert.kEigenschaftWert) IN 
                     (' . \implode(', ', $combinations) . ')
                        AND innerKombiwert.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                     GROUP BY innerKombiwert.kEigenschaftKombi
                     HAVING COUNT(innerKombiwert.kEigenschaftKombi) = ' . \count($combinations) . '
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
                'customergroupID' => Frontend::getCustomerGroup()->getID(),
                'parentProductID' => $parentProductID,
                'variationID'     => $variationID,
                'variationValue'  => $variationValue,
            ],
            ReturnType::ARRAY_OF_OBJECTS
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

        if ($auto && \count($categories) === 0) {
            $category   = new Kategorie($category->kOberKategorie);
            $categories = $list->holUnterkategorien($category->kKategorie, 0, 0);
        }

        $result = (object)['current' => $category, 'items' => $categories];

        $smarty->assign('result', $result)
               ->assign('nSeitenTyp', 0);

        $response->script('this.response = ' . \json_encode($smarty->fetch('snippets/categories_offcanvas.tpl')) . ';');

        return $response;
    }

    /**
     * @param string $country
     * @return IOResponse
     */
    public function getRegionsByCountry(string $country): IOResponse
    {
        $response = new IOResponse();

        if (\mb_strlen($country) === 2) {
            $regions = Staat::getRegions($country);
            $response->script('this.response = ' . \json_encode($regions) . ';');
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

        if (\mb_strlen($country) === 2) {
            $deliveryCountries = ShippingMethod::getPossibleShippingCountries(
                Frontend::getCustomerGroup()->getID(),
                false,
                false,
                [$country]
            );
            $response->script('this.response = ' . (\count($deliveryCountries) === 1 ? 'true' : 'false') . ';');
        }

        return $response;
    }

    /**
     * @param string $cKey
     * @param int    $kKey
     * @param int    $kSprache
     * @param array  $selection
     * @return IOResponse
     */
    public function setSelectionWizardAnswers($cKey, $kKey, $kSprache, $selection): IOResponse
    {
        $smarty   = Shop::Smarty();
        $response = new IOResponse();
        $AWA      = AuswahlAssistent::startIfRequired($cKey, $kKey, $kSprache, $smarty, $selection);

        if ($AWA !== null) {
            $oLastSelectedValue = $AWA->getLastSelectedValue();
            $NaviFilter         = $AWA->getNaviFilter();

            if (($oLastSelectedValue !== null && $oLastSelectedValue->nAnzahl === 1)
                || $AWA->getCurQuestion() === $AWA->getQuestionCount()
                || $AWA->getQuestion($AWA->getCurQuestion())->nTotalResultCount === 0) {
                $response->script("window.location.href='" . Text::htmlentitydecode(
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
        $cToken                  = \gibToken();
        $cName                   = \gibTokenName();
        $_SESSION['xcrsf_token'] = \json_encode(['name' => $cName, 'token' => $cToken]);
        $objResponse->script("doXcsrfToken('" . $cName . "', '" . $cToken . "');");

        return $objResponse;
    }
}
