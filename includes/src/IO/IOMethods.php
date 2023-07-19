<?php declare(strict_types=1);

namespace JTL\IO;

use Exception;
use JTL\Alert\Alert;
use JTL\Boxes\Factory;
use JTL\Boxes\Renderer\DefaultRenderer;
use JTL\Campaign;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\EigenschaftWert;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Separator;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\Checkout\Kupon;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Extensions\Config\Configurator;
use JTL\Extensions\Config\Item;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Product;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Router\Controller\ReviewController;
use JTL\Router\Route;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use SmartyException;
use stdClass;
use function Functional\filter;
use function Functional\flatten;
use function Functional\pluck;

/**
 * Class IOMethods
 * @package JTL\IO
 */
class IOMethods
{
    /**
     * IOMethods constructor.
     * @param IO               $io
     * @param DbInterface|null $db
     */
    public function __construct(private IO $io, private ?DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
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
            ->register('buildConfiguration', [$this, 'buildConfiguration'])
            ->register('getBasketItems', [$this, 'getBasketItems'])
            ->register('getCategoryMenu', [$this, 'getCategoryMenu'])
            ->register('getRegionsByCountry', [$this, 'getRegionsByCountry'])
            ->register('checkDeliveryCountry', [$this, 'checkDeliveryCountry'])
            ->register('setSelectionWizardAnswers', [$this, 'setSelectionWizardAnswers'])
            ->register('getCitiesByZip', [$this, 'getCitiesByZip'])
            ->register('getOpcDraftsHtml', [$this, 'getOpcDraftsHtml'])
            ->register('setWishlistVisibility', [$this, 'setWishlistVisibility'])
            ->register('updateWishlistItem', [$this, 'updateWishlistItem'])
            ->register('updateReviewHelpful', [$this, 'updateReviewHelpful']);
    }

    /**
     * @param string $keyword
     * @return array
     * @throws SmartyException
     */
    public function suggestions(string $keyword): array
    {
        $results = [];
        if (\mb_strlen($keyword) < 2) {
            return $results;
        }
        $smarty     = Shop::Smarty();
        $language   = Shop::getLanguageID();
        $maxResults = ($cnt = Shop::getSettingValue(\CONF_ARTIKELUEBERSICHT, 'suche_ajax_anzahl')) > 0
            ? $cnt
            : 10;
        $results    = $this->db->getObjects(
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
            ]
        );
        $smarty->assign('shopURL', Shop::getURL());
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
        if (empty($country) || empty($zip)) {
            return [];
        }

        return pluck(
            $this->db->getObjects(
                'SELECT cOrt
                    FROM tplz
                    WHERE cLandISO = :country
                        AND cPLZ = :zip
                        AND cOrt LIKE :cityQuery',
                ['country' => $country, 'zip' => $zip, 'cityQuery' => '%' . $cityQuery . '%']
            ),
            'cOrt'
        );
    }

    /**
     * @param int          $productID
     * @param int|float    $amount
     * @param string|array $properties
     * @return IOResponse
     * @throws SmartyException
     */
    public function pushToBasket(int $productID, $amount, $properties = ''): IOResponse
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';
        $config     = Shopsetting::getInstance()->getAll();
        $smarty     = Shop::Smarty();
        $response   = new stdClass();
        $ioResponse = new IOResponse();
        $token      = $properties['jtl_token'];
        if ($amount <= 0 || $productID <= 0) {
            return $ioResponse;
        }
        $product               = new Artikel($this->db);
        $options               = Artikel::getDefaultOptions();
        $options->nStueckliste = 1;
        $product->fuelleArtikel($productID, $options);
        // Falls der Artikel ein Variationskombikind ist, hole direkt seine Eigenschaften
        if ($product->kEigenschaftKombi > 0 || $product->nIstVater === 1) {
            // Variationskombi-Artikel
            $_POST['eigenschaftwert'] = $properties['eigenschaftwert'];
            $properties               = Product::getSelectedPropertiesForVarCombiArticle($productID);
        } elseif (GeneralObject::isCountable('eigenschaftwert', $properties)) {
            // einfache Variation - keine Varkombi
            $_POST['eigenschaftwert'] = $properties['eigenschaftwert'];
            $properties               = Product::getSelectedPropertiesForArticle($productID);
        }

        if ((int)$amount != $amount && $product->cTeilbar !== 'Y') {
            $amount = \max((int)$amount, 1);
        }
        $errors = CartHelper::addToCartCheck($product, $amount, $properties, 2, $token);
        if (\count($errors) > 0) {
            $localizedErrors = Product::getProductMessages($errors, true, $product, $amount);

            $response->nType  = 0;
            $response->cLabel = Shop::Lang()->get('basket');
            $response->cHints = Text::utf8_convert_recursive($localizedErrors);
            $ioResponse->assignVar('response', $response);

            return $ioResponse;
        }
        $cart = Frontend::getCart();
        CartHelper::addVariationPictures($cart);
        $cart->fuegeEin($productID, $amount, $properties)
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
            PersistentCart::getInstance(Frontend::getCustomer()->getID())->check($productID, $amount, $properties);
        }
        $pageType    = Shop::getPageType();
        $boxes       = Shop::Container()->getBoxService();
        $boxesToShow = $boxes->render($boxes->buildList($pageType), $pageType);
        $xSelling    = Product::getXSelling($productID, $product->nIstVater > 0);
        $sum         = [
            Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)),
            Preise::getLocalizedPriceString($cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL]))
        ];

        $customerGroupID = Frontend::getCustomer()->getGroupID();

        $smarty->assign('Boxen', $boxesToShow)
            ->assign('WarenkorbWarensumme', $sum)
            ->assign(
                'WarenkorbVersandkostenfreiHinweis',
                ShippingMethod::getShippingFreeString(
                    ShippingMethod::getFreeShippingMinimum($customerGroupID),
                    $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true, true),
                    $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], false, true)
                )
            )
            ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
            ->assign('fAnzahl', $amount)
            ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
            ->assign('Einstellungen', $config)
            ->assign('Xselling', $xSelling)
            ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
            ->assign('oSpezialseiten_arr', Shop::Container()->getLinkService()->getSpecialPages())
            ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
            ->assign('favourableShippingString', $cart->favourableShippingString);

        $response->nType           = 2;
        $response->cWarenkorbText  = \lang_warenkorb_warenkorbEnthaeltXArtikel($cart);
        $response->cWarenkorbLabel = Shop::Lang()->get('cartSumLabel', 'checkout', Preise::getLocalizedPriceString(
            $cart->gibGesamtsummeWarenExt(
                [\C_WARENKORBPOS_TYP_ARTIKEL],
                !Frontend::getCustomerGroup()->isMerchant()
            )
        ));
        $response->cPopup          = $smarty->fetch('productdetails/pushed.tpl');
        $response->cWarenkorbMini  = $smarty->fetch('basket/cart_dropdown.tpl');
        $response->oArtikel        = $product;
        $response->cNotification   = Shop::Lang()->get('basketAllAdded', 'messages');

        $ioResponse->assignVar('response', $response);
        Campaign::setCampaignAction(\KAMPAGNE_DEF_WARENKORB, $productID, $amount);
        if ($config['global']['global_warenkorb_weiterleitung'] === 'Y') {
            $response->nType     = 1;
            $response->cLocation = Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php');
            $ioResponse->assignVar('response', $response);
        }

        return $ioResponse;
    }

    /**
     * @param int $productID
     * @return IOResponse
     * @throws SmartyException
     */
    public function pushToComparelist(int $productID): IOResponse
    {
        $conf       = Shopsetting::getInstance()->getAll();
        $response   = new stdClass();
        $ioResponse = new IOResponse();
        $smarty     = Shop::Smarty();

        $_POST['Vergleichsliste'] = 1;
        $_POST['a']               = $productID;

        CartHelper::checkAdditions();
        $response->nType  = 2;
        $response->nCount = \count($_SESSION['Vergleichsliste']->oArtikel_arr ?? []);
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
                'href'  => Shop::Container()->getLinkService()->getStaticRoute('vergleichsliste.php'),
                'fa'    => 'fa-tasks',
                'title' => Shop::Lang()->get('compare')
            ]);
        }
        $alerts  = Shop::Container()->getAlertService();
        $content = $smarty->assign('alertList', $alerts)
            ->assign('Einstellungen', $conf)
            ->fetch('snippets/alert_list.tpl');

        $response->cNotification = $smarty
            ->assign('type', $alerts->alertTypeExists(Alert::TYPE_ERROR) ? 'danger' : 'info')
            ->assign('body', $content)
            ->assign('buttons', $buttons)
            ->fetch('snippets/notification.tpl');

        $response->cNavBadge     = $smarty->fetch('layout/header_shop_nav_compare.tpl');
        $response->navDropdown   = $smarty->fetch('snippets/comparelist_dropdown.tpl');
        $response->cBoxContainer = [];
        foreach ($this->forceRenderBoxes(\BOX_VERGLEICHSLISTE, $conf, $smarty) as $id => $html) {
            $response->cBoxContainer[$id] = $html;
        }
        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }

    /**
     * @param int $productID
     * @return IOResponse
     * @throws SmartyException
     */
    public function removeFromComparelist(int $productID): IOResponse
    {
        $conf       = Shopsetting::getInstance()->getAll();
        $response   = new stdClass();
        $ioResponse = new IOResponse();
        $smarty     = Shop::Smarty();

        $_GET['Vergleichsliste'] = 1;
        $_GET['vlplo']           = $productID;

        Frontend::getInstance()->setStandardSessionVars();
        $response->nType     = 2;
        $response->productID = $productID;
        $response->nCount    = \count(Frontend::get('Vergleichsliste')->oArtikel_arr ?? []);
        $response->cTitle    = Shop::Lang()->get('compare');
        $response->cNavBadge = $smarty->assign('Einstellungen', $conf)
            ->fetch('layout/header_shop_nav_compare.tpl');

        $response->navDropdown   = $smarty->fetch('snippets/comparelist_dropdown.tpl');
        $response->cBoxContainer = [];

        foreach ($this->forceRenderBoxes(\BOX_VERGLEICHSLISTE, $conf, $smarty) as $id => $html) {
            $response->cBoxContainer[$id] = $html;
        }
        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }

    /**
     * @param int       $type
     * @param array     $conf
     * @param JTLSmarty $smarty
     * @return array
     */
    private function forceRenderBoxes(int $type, array $conf, JTLSmarty $smarty): array
    {
        $res      = [];
        $boxData  = $this->db->getObjects(
            'SELECT *, 0 AS nSort, \'\' AS pageIDs, \'\' AS pageVisibilities,
                       GROUP_CONCAT(tboxensichtbar.nSort) AS sortBypageIDs,
                       GROUP_CONCAT(tboxensichtbar.kSeite) AS pageIDs,
                       GROUP_CONCAT(tboxensichtbar.bAktiv) AS pageVisibilities
                FROM tboxen
                LEFT JOIN tboxensichtbar
                    ON tboxen.kBox = tboxensichtbar.kBox
                LEFT JOIN tboxvorlage
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxen.kBoxvorlage = :type
                GROUP BY tboxen.kBox',
            ['type' => $type]
        );
        $factory  = new Factory($conf);
        $renderer = new DefaultRenderer($smarty);
        foreach ($boxData as $item) {
            $box = $factory->getBoxByBaseType($type);
            $box->map([$item]);
            $box->setFilter([]);
            $box->setShow(true);
            $renderer->setBox($box);
            $res[$box->getID()] = $renderer->render();
        }

        return $res;
    }

    /**
     * @param int       $productID
     * @param float|int $qty
     * @param array     $data
     * @return IOResponse
     * @throws SmartyException
     */
    public function pushToWishlist(int $productID, $qty, array $data): IOResponse
    {
        $_POST      = $data;
        $conf       = Shopsetting::getInstance()->getAll();
        $response   = new stdClass();
        $ioResponse = new IOResponse();
        $qty        = empty($qty) ? 1 : $qty;
        $smarty     = Shop::Smarty();
        if (Frontend::getCustomer()->getID() === 0) {
            $response->nType     = 1;
            $response->cLocation = Shop::Container()->getLinkService()->getStaticRoute('jtl.php')
                . '?a=' . $productID
                . '&n=' . $qty
                . '&r=' . \R_LOGIN_WUNSCHLISTE;
            $ioResponse->assignVar('response', $response);

            return $ioResponse;
        }
        $vals = $this->db->selectAll('teigenschaft', 'kArtikel', $productID);
        if (!empty($vals) && empty($_POST['eigenschaftwert']) && !Product::isParent($productID)) {
            // Falls die Wunschliste aus der Artikelübersicht ausgewählt wurde,
            // muss zum Artikel weitergeleitet werden um Variationen zu wählen
            $response->nType     = 1;
            $response->cLocation = (Shop::getURL() . '/?a=' . $productID
                . '&n=' . $qty
                . '&r=' . \R_VARWAEHLEN);
            $ioResponse->assignVar('response', $response);

            return $ioResponse;
        }

        $_POST['Wunschliste'] = 1;
        $_POST['a']           = $productID;
        $_POST['n']           = $qty;

        CartHelper::checkAdditions();

        foreach (Frontend::getWishList()->getItems() as $wlPos) {
            if ($wlPos->getProductID() === $productID) {
                $response->wlPosAdd = $wlPos->getID();
            }
        }
        $response->nType     = 2;
        $response->nCount    = \count(Frontend::getWishList()->getItems());
        $response->productID = $productID;
        $response->cTitle    = Shop::Lang()->get('goToWishlist');
        $buttons             = [(object)[
            'href'    => '#',
            'fa'      => 'fa fa-arrow-circle-right',
            'title'   => Shop::Lang()->get('continueShopping', 'checkout'),
            'primary' => true,
            'dismiss' => 'modal'
        ]];

        if ($response->nCount > 1) {
            \array_unshift($buttons, (object)[
                'href'  => Shop::Container()->getLinkService()->getStaticRoute('wunschliste.php'),
                'fa'    => 'fa-tasks',
                'title' => Shop::Lang()->get('goToWishlist')
            ]);
        }
        $alerts = Shop::Container()->getAlertService();
        $body   = $smarty->assign('alertList', $alerts)
            ->assign('Einstellungen', $conf)
            ->fetch('snippets/alert_list.tpl');

        $smarty->assign('type', $alerts->alertTypeExists(Alert::TYPE_ERROR) ? 'danger' : 'info')
            ->assign('body', $body)
            ->assign('buttons', $buttons);

        $response->cNotification = $smarty->fetch('snippets/notification.tpl');
        $response->cNavBadge     = $smarty->fetch('layout/header_shop_nav_wish.tpl');
        $response->cBoxContainer = [];
        foreach ($this->forceRenderBoxes(\BOX_WUNSCHLISTE, $conf, $smarty) as $id => $html) {
            $response->cBoxContainer[$id] = $html;
        }
        $ioResponse->assignVar('response', $response);

        if ($conf['global']['global_wunschliste_weiterleitung'] === 'Y') {
            $response->nType     = 1;
            $response->cLocation = Shop::Container()->getLinkService()->getStaticRoute('wunschliste.php');
            $ioResponse->assignVar('response', $response);
        }

        return $ioResponse;
    }

    /**
     * @param int $productID
     * @return IOResponse
     * @throws SmartyException
     */
    public function removeFromWishlist(int $productID): IOResponse
    {
        $conf       = Shopsetting::getInstance()->getAll();
        $response   = new stdClass();
        $ioResponse = new IOResponse();
        $smarty     = Shop::Smarty();

        $_GET['Wunschliste'] = 1;
        $_GET['wlplo']       = $productID;

        Frontend::getInstance()->setStandardSessionVars();
        $response->nType         = 2;
        $response->wlPosRemove   = $productID;
        $response->nCount        = \count(Frontend::getWishList()->getItems());
        $response->cTitle        = Shop::Lang()->get('goToWishlist');
        $response->cBoxContainer = [];
        $response->cNavBadge     = $smarty->assign('Einstellungen', $conf)
            ->fetch('layout/header_shop_nav_wish.tpl');

        foreach ($this->forceRenderBoxes(\BOX_WUNSCHLISTE, $conf, $smarty) as $id => $html) {
            $response->cBoxContainer[$id] = $html;
        }
        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }

    /**
     * @return IOResponse
     * @throws SmartyException
     */
    public function updateWishlistDropdown(): IOResponse
    {
        $response   = new stdClass();
        $ioResponse = new IOResponse();
        $smarty     = Shop::Smarty();

        $response->content         = $smarty->assign('wishlists', Wishlist::getWishlists())
            ->fetch('snippets/wishlist_dropdown.tpl');
        $response->currentPosCount = \count(Frontend::getWishList()->getItems());

        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }

    /**
     * @param int $type - 0 = Template, 1 = Object
     * @return IOResponse
     * @throws SmartyException
     */
    public function getBasketItems(int $type = 0): IOResponse
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'sprachfunktionen.php';
        $cart       = Frontend::getCart();
        $response   = new stdClass();
        $ioResponse = new IOResponse();

        CartHelper::addVariationPictures($cart);
        switch ($type) {
            default:
            case 0:
                $smarty          = Shop::Smarty();
                $customerGroupID = Frontend::getCustomerGroup()->getID();
                $customer        = Frontend::getCustomer();
                $qty             = $cart->gibAnzahlPositionenExt([\C_WARENKORBPOS_TYP_ARTIKEL]);
                $country         = $_SESSION['cLieferlandISO'] ?? '';
                $plz             = '*';
                $error           = $smarty->getTemplateVars('fehler') ?? '';
                if ($customer->getID() > 0) {
                    $customerGroupID = $customer->getGroupID();
                    $country         = $customer->cLand;
                    $plz             = $customer->cPLZ;
                }

                $shippingFreeMin = ShippingMethod::getFreeShippingMinimum($customerGroupID, $country);
                $cartValueGros   = $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true, true, $country);
                $cartValueNet    = $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], false, true, $country);

                $smarty->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
                    ->assign('Warensumme', $cart->gibGesamtsummeWaren())
                    ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
                    ->assign('Einstellungen', Shop::getSettings([\CONF_GLOBAL, \CONF_BILDER]))
                    ->assign('WarenkorbArtikelPositionenanzahl', $qty)
                    ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
                    ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
                    ->assign('Warenkorbtext', \lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
                    ->assign('NettoPreise', Frontend::getCustomerGroup()->getIsMerchant())
                    ->assign('FavourableShipping', $cart->getFavourableShipping(
                        $shippingFreeMin !== 0
                        && ShippingMethod::getShippingFreeDifference(
                            $shippingFreeMin,
                            $cartValueGros,
                            $cartValueNet
                        ) <= 0
                            ? (int)$shippingFreeMin->kVersandart
                            : null
                    ))
                    ->assign('WarenkorbVersandkostenfreiHinweis', ShippingMethod::getShippingFreeString(
                        $shippingFreeMin,
                        $cartValueGros,
                        $cartValueNet
                    ))
                    ->assign('oSpezialseiten_arr', Shop::Container()->getLinkService()->getSpecialPages())
                    ->assign('favourableShippingString', $cart->favourableShippingString);

                ShippingMethod::getShippingCosts($country, $plz, $error);
                $response->cTemplate = $smarty->fetch('basket/cart_dropdown_label.tpl');
                break;

            case 1:
                $response->cItems = $cart->PositionenArr;
                break;
        }

        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }

    /**
     * @param array $aValues
     * @return IOResponse
     * @throws SmartyException
     */
    public function buildConfiguration(array $aValues): IOResponse
    {
        $_POST['jtl_token'] = $aValues['jtl_token'];
        $smarty             = Shop::Smarty();
        $response           = new IOResponse();
        $product            = new Artikel($this->db);
        $productID          = (int)($aValues['VariKindArtikel'] ?? $aValues['a']);
        $items              = $aValues['item'] ?? [];
        $quantities         = $aValues['quantity'] ?? [];
        $itemQuantities     = $aValues['item_quantity'] ?? [];
        $variationValues    = $aValues['eigenschaftwert'] ?? [];
        $amount             = $aValues['anzahl'] ?? 1;
        $invalidGroups      = [];
        $configItems        = [];
        $customerGroupID    = Frontend::getCustomerGroup()->getID();
        $languageID         = Shop::getLanguageID();
        $config             = Product::buildConfig(
            $productID,
            $amount,
            $variationValues,
            $items,
            $quantities,
            $itemQuantities,
            true
        );
        if ($config === null) {
            return $response;
        }
        $net                   = Frontend::getCustomerGroup()->getIsMerchant();
        $options               = Artikel::getDefaultOptions();
        $options->nVariationen = 1;
        $product->fuelleArtikel($productID, $options, $customerGroupID, $languageID);
        $fVKNetto                      = $product->gibPreis($amount, [], $customerGroupID);
        $fVK                           = [
            Tax::getGross($fVKNetto, $_SESSION['Steuersatz'][$product->kSteuerklasse]),
            $fVKNetto
        ];
        $product->Preise->cVKLocalized = [
            0 => Preise::getLocalizedPriceString($fVK[0]),
            1 => Preise::getLocalizedPriceString($fVK[1])
        ];

        $configGroups      = $items;
        $configGroupCounts = $quantities;
        $configItemCounts  = $itemQuantities;
        foreach ($configGroups as $itemList) {
            foreach ($itemList ?? [] as $configItemID) {
                $configItemID = (int)$configItemID;
                // Falls ungültig, ignorieren
                if ($configItemID <= 0) {
                    continue;
                }
                $configItem          = new Item($configItemID, $languageID, $customerGroupID);
                $configItem->fAnzahl = (float)($configItemCounts[$configItemID]
                    ?? $configGroupCounts[$configItem->getKonfiggruppe()] ?? $configItem->getInitial());
                if ($configItemCounts && isset($configItemCounts[$configItem->getKonfigitem()])) {
                    $configItem->fAnzahl = (float)$configItemCounts[$configItem->getKonfigitem()];
                }
                if ($configItem->fAnzahl < 1) {
                    $configItem->fAnzahl = 1;
                }
                $count                 = \max($amount, 1);
                $configItem->fAnzahlWK = $configItem->fAnzahl;
                if (!$configItem->ignoreMultiplier()) {
                    $configItem->fAnzahlWK *= $count;
                }
                $configItems[] = $configItem;
                // Alle Artikel können in den WK gelegt werden?
                if ($configItem->getPosTyp() !== \KONFIG_ITEM_TYP_ARTIKEL) {
                    continue;
                }
                // Varikombi
                $configItem->oEigenschaftwerte_arr = [];
                /** @var Artikel $tmpProduct */
                $tmpProduct = $configItem->getArtikel();
                if ($tmpProduct !== null
                    && $tmpProduct->kVaterArtikel > 0
                    && isset($tmpProduct->kEigenschaftKombi)
                    && $tmpProduct->kEigenschaftKombi > 0
                ) {
                    $configItem->oEigenschaftwerte_arr =
                        Product::getVarCombiAttributeValues($tmpProduct->kArtikel, false);
                }
                $tmpProduct->isKonfigItem = true;
                $redirectParam            = CartHelper::addToCartCheck(
                    $tmpProduct,
                    $configItem->fAnzahlWK,
                    $configItem->oEigenschaftwerte_arr
                );
                if (\count($redirectParam) > 0) {
                    $productMessages = Product::getProductMessages(
                        $redirectParam,
                        true,
                        $configItem->getArtikel(),
                        $configItem->fAnzahlWK,
                        $configItem->getKonfigitem()
                    );

                    $itemErrors[$configItem->getKonfigitem()] = (object)[
                        'message' => $productMessages[0],
                        'group'   => $configItem->getKonfiggruppe()
                    ];
                    $invalidGroups[]                          = $configItem->getKonfiggruppe();
                }
            }
        }
        $errors                     = Configurator::validateCart($productID, $configItems ?? []);
        $config->invalidGroups      = \array_values(\array_unique(\array_merge(
            $invalidGroups,
            \array_keys(\is_array($errors) ? $errors : [])
        )));
        $config->errorMessages      = $itemErrors ?? [];
        $config->valid              = empty($config->invalidGroups) && empty($config->errorMessages);
        $cartHelperErrors           = CartHelper::addToCartCheck(
            $product,
            1,
            Product::getSelectedPropertiesForArticle($productID, false)
        );
        $config->variationsSelected = $product->kVaterArtikel > 0 || !\in_array(
            \R_VARWAEHLEN,
            $cartHelperErrors,
            true
        );
        $config->inStock            = !\in_array(\R_LAGER, $cartHelperErrors, true);
        $smarty->assign('oKonfig', $config)
            ->assign('NettoPreise', $net)
            ->assign('Artikel', $product);
        $config->cTemplate = $smarty->fetch('productdetails/config_summary.tpl');

        $response->assignVar('response', $config);

        return $response;
    }

    /**
     * @param int        $productID
     * @param array|null $selectedVariationValues
     * @return stdClass
     */
    public function getArticleStockInfo(int $productID, array $selectedVariationValues = null): stdClass
    {
        $result = (object)[
            'stock'  => false,
            'status' => 0,
            'text'   => '',
        ];

        if ($selectedVariationValues !== null) {
            $products = $this->getArticleByVariations($productID, $selectedVariationValues);
            if (\count($products) === 1) {
                $productID = (int)$products[0]->kArtikel;
            } else {
                return $result;
            }
        }

        if ($productID <= 0) {
            return $result;
        }
        $product                            = new Artikel($this->db);
        $options                            = Artikel::getDefaultOptions();
        $options->nKeinLagerbestandBeachten = 1;

        $product->fuelleArtikel(
            $productID,
            $options,
            CustomerGroup::getCurrent(),
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

        return $result;
    }

    /**
     * @param array $values
     * @return IOResponse
     */
    public function checkDependencies(array $values): IOResponse
    {
        $ioResponse = new IOResponse();
        $checkBulk  = isset($values['VariKindArtikel']);
        $parentID   = $checkBulk ? (int)$values['VariKindArtikel'] : (int)$values['a'];
        if ($parentID <= 0) {
            return $ioResponse;
        }
        $amount   = (float)($values['anzahl'] ?? 0);
        $valueIDs = \array_filter((array)$values['eigenschaftwert']);
        $wrapper  = isset($values['wrapper']) ? Text::filterXSS($values['wrapper']) : '';

        $options                            = new stdClass();
        $options->nKeinLagerbestandBeachten = 1;
        $options->nMain                     = 1;
        $options->nWarenlager               = 1;
        $options->nVariationen              = 1;
        $product                            = new Artikel($this->db);
        $product->fuelleArtikel($parentID, $checkBulk ? null : $options, Frontend::getCustomerGroup()->getID());
        $weightDiff   = 0;
        $newProductNr = '';

        $response         = new stdClass();
        $response->check  = Wishlist::checkVariOnList($parentID, $valueIDs);
        $response->itemID = $parentID;

        $ioResponse->assignVar('response', $response);

        // Alle Variationen ohne Freifeld
        $keyValueVariations = $product->keyValueVariations($product->VariationenOhneFreifeld);
        foreach ($valueIDs as $index => $value) {
            if (isset($keyValueVariations[$index])) {
                $ioResponse->callEvoProductFunction(
                    'variationActive',
                    $index,
                    \addslashes($value),
                    null,
                    $wrapper
                );
            } else {
                unset($valueIDs[$index]);
            }
        }

        foreach ($valueIDs as $valueID) {
            $currentValue = new EigenschaftWert((int)$valueID);
            $weightDiff  += $currentValue->fGewichtDiff;
            $newProductNr = (!empty($currentValue->cArtNr) && $product->cArtNr !== $currentValue->cArtNr)
                ? $currentValue->cArtNr
                : $product->cArtNr;
        }
        $weightTotal        = Separator::getUnit(
            \JTL_SEPARATOR_WEIGHT,
            Shop::getLanguageID(),
            $product->fGewicht + $weightDiff
        );
        $weightProductTotal = Separator::getUnit(
            \JTL_SEPARATOR_WEIGHT,
            Shop::getLanguageID(),
            $product->fArtikelgewicht + $weightDiff
        );
        $unitWeightLabel    = Shop::Lang()->get('weightUnit');
        $currency           = Frontend::getCurrency();
        $isNet              = Frontend::getCustomerGroup()->getIsMerchant();
        $fVKNetto           = $product->gibPreis($amount, $valueIDs, Frontend::getCustomerGroup()->getID());
        $fVK                = [
            Tax::getGross($fVKNetto, $_SESSION['Steuersatz'][$product->kSteuerklasse]),
            $fVKNetto
        ];
        $cVKLocalized       = [
            0 => Preise::getLocalizedPriceString($fVK[0], $currency),
            1 => Preise::getLocalizedPriceString($fVK[1], $currency)
        ];
        $priceLabel         = '';
        if (isset($product->nVariationAnzahl) && $product->nVariationAnzahl > 0) {
            $priceLabel = $product->nVariationOhneFreifeldAnzahl === \count($valueIDs)
                ? Shop::Lang()->get('priceAsConfigured', 'productDetails')
                : Shop::Lang()->get('priceStarting');
        }
        if (!$product->bHasKonfig) {
            $ioResponse->callEvoProductFunction(
                'setPrice',
                $fVK[$isNet],
                $cVKLocalized[$isNet],
                $priceLabel,
                $wrapper
            );
        }
        $ioResponse->callEvoProductFunction(
            'setArticleWeight',
            [
                [$product->fGewicht, $weightTotal . ' ' . $unitWeightLabel],
                [$product->fArtikelgewicht, $weightProductTotal . ' ' . $unitWeightLabel],
            ],
            $wrapper
        );

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
                $cStaffelVK[0][$nAnzahl] = Preise::getLocalizedPriceString($fStaffelVK[0][$nAnzahl], $currency);
                $cStaffelVK[1][$nAnzahl] = Preise::getLocalizedPriceString($fStaffelVK[1][$nAnzahl], $currency);
            }

            $ioResponse->callEvoProductFunction(
                'setStaffelPrice',
                $fStaffelVK[$isNet],
                $cStaffelVK[$isNet],
                $wrapper
            );
        }

        if ($product->cVPE === 'Y' && $product->fVPEWert > 0 && $product->cVPEEinheit && !empty($product->Preise)) {
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

            $ioResponse->callEvoProductFunction(
                'setVPEPrice',
                $product->cLocalizedVPE[$isNet],
                $fStaffelVPE[$isNet],
                $cStaffelVPE[$isNet],
                $wrapper
            );
        }

        if (!empty($newProductNr)) {
            $ioResponse->callEvoProductFunction('setProductNumber', $newProductNr, $wrapper);
        }

        return $ioResponse;
    }

    /**
     * @param array      $values
     * @param int|string $propertyID
     * @param int|string $propertyValueID
     * @return IOResponse
     */
    public function checkVarkombiDependencies(array $values, $propertyID = 0, $propertyValueID = 0): IOResponse
    {
        $propertyID      = (int)$propertyID;
        $propertyValueID = (int)$propertyValueID;
        $ioResponse      = new IOResponse();
        $parentProductID = (int)$values['a'];
        $childProductID  = isset($values['VariKindArtikel']) ? (int)$values['VariKindArtikel'] : 0;
        $idx             = isset($values['eigenschaftwert']) ? (array)$values['eigenschaftwert'] : [];
        $freetextValues  = [];
        $set             = \array_filter($idx);
        $layout          = isset($values['layout']) ? Text::filterXSS($values['layout']) : '';
        $wrapper         = isset($values['wrapper']) ? Text::filterXSS($values['wrapper']) : '';
        if ($parentProductID <= 0) {
            throw new Exception('Product not found ' . $parentProductID);
        }
        $options                            = new stdClass();
        $options->nKeinLagerbestandBeachten = 1;
        $options->nMain                     = 1;
        $options->nWarenlager               = 1;
        $options->nVariationen              = 1;
        $product                            = new Artikel($this->db);
        $product->fuelleArtikel($parentProductID, $options);
        // Alle Variationen ohne Freifeld
        $keyValueVariations = $product->keyValueVariations($product->VariationenOhneFreifeld);
        // Freifeldpositionen gesondert zwischenspeichern
        foreach ($set as $kKey => $cVal) {
            if (!isset($keyValueVariations[$kKey])) {
                unset($set[$kKey]);
                $freetextValues[$kKey] = $cVal;
            }
        }
        $hasInvalidSelection = false;
        $invalidVariations   = $product->getVariationsBySelection($set, true);
        foreach ($set as $kKey => $kValue) {
            if (isset($invalidVariations[$kKey]) && \in_array($kValue, $invalidVariations[$kKey])) {
                $hasInvalidSelection = true;
                break;
            }
        }
        // Auswahl zurücksetzen sobald eine nicht vorhandene Variation ausgewählt wurde.
        if ($hasInvalidSelection) {
            $ioResponse->callEvoProductFunction('variationResetAll', $wrapper);
            $set               = [$propertyID => $propertyValueID];
            $invalidVariations = $product->getVariationsBySelection($set, true);
            // Auswählter EigenschaftWert ist ebenfalls nicht vorhanden
            if (isset($invalidVariations[$propertyID])
                && \in_array($propertyValueID, $invalidVariations[$propertyID])
            ) {
                $set = [];
                // Wir befinden uns im Kind-Artikel -> Weiterleitung auf Vater-Artikel
                if ($childProductID > 0) {
                    $ioResponse->callEvoProductFunction(
                        'setArticleContent',
                        $product->kArtikel,
                        0,
                        $product->cURL,
                        [],
                        $wrapper
                    );

                    return $ioResponse;
                }
            }
        }
        // Alle EigenschaftWerte vorhanden, Kind-Artikel ermitteln
        if (\count($set) >= $product->nVariationOhneFreifeldAnzahl) {
            $products = $this->getArticleByVariations($parentProductID, $set);
            if (\count($products) === 1 && $childProductID !== (int)$products[0]->kArtikel) {
                $tmpProduct              = $products[0];
                $gesetzteEigeschaftWerte = [];
                foreach ($freetextValues as $cKey => $cValue) {
                    $gesetzteEigeschaftWerte[] = (object)[
                        'key'   => $cKey,
                        'value' => $cValue
                    ];
                }
                $childHasOPCContent = $this->db->getSingleInt(
                    "SELECT COUNT(kPage) AS count
                    FROM topcpage
                    WHERE cPageId LIKE '%\"type\":\"product\"%'
                        AND (
                            cPageId LIKE CONCAT('%\"id\":', :id,'%')
                            OR cPageId LIKE CONCAT('%\"id\":', :last_id,'%')
                            OR cPageId LIKE CONCAT('%\"id\":', :father_id,'%'))",
                    'count',
                    [
                        'id' => (int)$tmpProduct->kArtikel,
                        'last_id' => $childProductID,
                        'father_id' => $parentProductID
                    ]
                ) > 0;
                if ($layout === 'gallery' || $childHasOPCContent) {
                    $ioResponse->callEvoProductFunction(
                        'redirectToArticle',
                        $parentProductID,
                        $tmpProduct->kArtikel,
                        URL::buildURL($tmpProduct, \URLART_ARTIKEL, true),
                        $gesetzteEigeschaftWerte,
                        $wrapper
                    );
                } else {
                    $ioResponse->callEvoProductFunction(
                        'setArticleContent',
                        $parentProductID,
                        $tmpProduct->kArtikel,
                        URL::buildURL($tmpProduct, \URLART_ARTIKEL, true),
                        $gesetzteEigeschaftWerte,
                        $wrapper
                    );
                }
                \executeHook(\HOOK_TOOLSAJAXSERVER_PAGE_TAUSCHEVARIATIONKOMBI, [
                    'objResponse' => &$ioResponse,
                    'oArtikel'    => &$product,
                    'bIO'         => true
                ]);

                return $ioResponse;
            }
        }

        $ioResponse->callEvoProductFunction('variationDisableAll', $wrapper);
        $possibleVariations = $product->getVariationsBySelection($set);
        $checkStockInfo     = \count($set) > 0 && (\count($set) === \count($possibleVariations) - 1);
        $stockInfo          = (object)[
            'stock'  => true,
            'status' => 2,
            'text'   => '',
        ];
        foreach ($product->Variationen as $variation) {
            if (\in_array($variation->cTyp, ['FREITEXT', 'PFLICHTFREITEXT'])) {
                $ioResponse->callEvoProductFunction('variationEnable', $variation->kEigenschaft, 0, $wrapper);
            } else {
                $ioResponse->callEvoProductFunction('showGalleryVariation', $variation->kEigenschaft, $wrapper);
                foreach ($variation->Werte as $value) {
                    $id               = $value->kEigenschaft;
                    $stockInfo->stock = true;
                    $stockInfo->text  = '';

                    if (isset($possibleVariations[$id])
                        && \in_array($value->kEigenschaftWert, $possibleVariations[$id])
                    ) {
                        $ioResponse->callEvoProductFunction(
                            'variationEnable',
                            $id,
                            $value->kEigenschaftWert,
                            $wrapper
                        );

                        if ($checkStockInfo && !\array_key_exists($id, $set)) {
                            $set[$id] = $value->kEigenschaftWert;

                            $products = $this->getArticleByVariations($parentProductID, $set);
                            if (\count($products) === 1) {
                                $stockInfo = $this->getArticleStockInfo((int)$products[0]->kArtikel);
                            }
                            unset($set[$id]);
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
                        $ioResponse->callEvoProductFunction(
                            'variationInfo',
                            $value->kEigenschaftWert,
                            $stockInfo->status,
                            $stockInfo->text,
                            $value->notExists,
                            $wrapper
                        );
                    }
                }

                if (isset($set[$variation->kEigenschaft])) {
                    $ioResponse->callEvoProductFunction(
                        'variationActive',
                        $variation->kEigenschaft,
                        \addslashes($set[$variation->kEigenschaft]),
                        null,
                        $wrapper
                    );
                }
            }
        }
        $ioResponse->callEvoProductFunction('variationRefreshAll', $wrapper);

        return $ioResponse;
    }

    /**
     * @param int   $parentProductID
     * @param array $selectedVariationValues
     * @return stdClass[]
     */
    public function getArticleByVariations(int $parentProductID, array $selectedVariationValues): array
    {
        if (\count($selectedVariationValues) === 0) {
            return [];
        }
        $variationID    = 0;
        $variationValue = 0;
        $combinations   = [];
        $i              = 0;
        foreach ($selectedVariationValues as $id => $value) {
            $id    = (int)$id;
            $value = (int)$value;
            if ($i++ === 0) {
                $variationID    = $id;
                $variationValue = $value;
            } else {
                $combinations[] = '(' . $id . ', ' . $value . ')';
            }
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

        return $this->db->getObjects(
            'SELECT tartikel.kArtikel,
                tseo.kKey AS kSeoKey, COALESCE(tseo.cSeo, \'\') AS cSeo,
                tartikel.fLagerbestand, tartikel.cLagerBeachten, tartikel.cLagerKleinerNull
                FROM teigenschaftkombiwert
                INNER JOIN tartikel
                    ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                LEFT JOIN tseo
                    ON tseo.cKey = \'kArtikel\'
                    AND tseo.kKey = tartikel.kArtikel
                    AND tseo.kSprache = :languageID
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
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
            ]
        );
    }

    /**
     * @param int $categoryID
     * @return IOResponse
     * @throws SmartyException
     */
    public function getCategoryMenu(int $categoryID): IOResponse
    {
        $smarty = Shop::Smarty();
        $auto   = $categoryID === 0;
        if ($auto) {
            $categoryID = Shop::$kKategorie;
        }
        $response   = new IOResponse();
        $list       = new KategorieListe();
        $category   = new Kategorie($categoryID, 0, 0, false, $this->db);
        $categories = $list->getChildCategories($category->getParentID(), 0, 0);
        if ($auto && \count($categories) === 0) {
            $category   = new Kategorie($category->getParentID(), 0, 0, false, $this->db);
            $categories = $list->getChildCategories($category->getParentID(), 0, 0);
        }

        $smarty->assign('result', (object)['current' => $category, 'items' => $categories])
            ->assign('nSeitenTyp', 0);

        $response->assignVar('response', $smarty->fetch('snippets/categories_offcanvas.tpl'));

        return $response;
    }

    /**
     * @param string $iso
     * @return IOResponse
     */
    public function getRegionsByCountry(string $iso): IOResponse
    {
        $ioResponse = new IOResponse();
        if (\mb_strlen($iso) === 2) {
            $country = Shop::Container()->getCountryService()->getCountry($iso);
            if ($country === null) {
                return $ioResponse;
            }
            $data           = new stdClass();
            $data->states   = $country->getStates();
            $data->required = $country->isRequireStateDefinition()
                || Shop::getSettingValue(\CONF_KUNDEN, 'kundenregistrierung_abfragen_bundesland') === 'Y';
            $ioResponse->assignVar('response', $data);
        }

        return $ioResponse;
    }

    /**
     * @param string $country
     * @return IOResponse
     */
    public function checkDeliveryCountry(string $country): IOResponse
    {
        $response = new IOResponse();
        if (\mb_strlen($country) !== 2) {
            return $response;
        }
        $deliveryCountries = ShippingMethod::getPossibleShippingCountries(
            Frontend::getCustomerGroup()->getID(),
            false,
            false,
            [$country]
        );
        $response->assignVar('response', \count($deliveryCountries) === 1);

        return $response;
    }

    /**
     * @param string $keyName
     * @param int    $id
     * @param int    $languageID
     * @param array  $selection
     * @return IOResponse
     */
    public function setSelectionWizardAnswers(string $keyName, int $id, int $languageID, array $selection): IOResponse
    {
        $smarty     = Shop::Smarty();
        $ioResponse = new IOResponse();
        $wizard     = Wizard::startIfRequired($keyName, $id, $languageID, $smarty, $selection);
        if ($wizard === null) {
            return $ioResponse;
        }
        $lastSelectedValue = $wizard->getLastSelectedValue();
        $productFilter     = $wizard->getNaviFilter();
        if (($lastSelectedValue !== null && $lastSelectedValue->getCount() === 1)
            || $wizard->getCurQuestion() === $wizard->getQuestionCount()
            || $wizard->getQuestion($wizard->getCurQuestion())->nTotalResultCount === 0
        ) {
            $ioResponse->setClientRedirect($productFilter->getFilterURL()->getURL());
        } else {
            $ioResponse->assignDom('selectionwizard', 'innerHTML', $wizard->fetchForm($smarty));
        }

        return $ioResponse;
    }

    /**
     * @param string $curPageID
     * @param string $adminSessionToken
     * @param array  $languages
     * @param array  $currentLanguage
     * @return IOResponse
     * @throws SmartyException|Exception
     */
    public function getOpcDraftsHtml(
        string $curPageID,
        string $adminSessionToken,
        array  $languages,
        array  $currentLanguage
    ): IOResponse {
        foreach ($languages as $i => $lang) {
            $languages[$i] = (object)$lang;
        }
        $opcPageService   = Shop::Container()->getOPCPageService();
        $response         = new IOResponse();
        $publicDraftkey   = $opcPageService->getPublicPage($curPageID)?->getKey() ?? 0;
        $newDraftListHtml = Shop::Smarty()
            ->assign('pageDrafts', $opcPageService->getDrafts($curPageID))
            ->assign('ShopURL', Shop::getURL())
            ->assign('adminSessionToken', $adminSessionToken)
            ->assign('languages', $languages)
            ->assign('currentLanguage', (object)$currentLanguage)
            ->assign('opcPageService', $opcPageService)
            ->assign('publicDraftKey', $publicDraftkey)
            ->assign('opcStartUrl', Shop::getAdminURL() . '/' . Route::OPC)
            ->fetch(\PFAD_ROOT . \PFAD_ADMIN . 'opc/tpl/draftlist.tpl');

        $response->assignDom('opc-draft-list', 'innerHTML', $newDraftListHtml);

        return $response;
    }

    /**
     * @param int    $wlID
     * @param bool   $state
     * @param string $token
     * @return IOResponse
     */
    public function setWishlistVisibility(int $wlID, bool $state, string $token): IOResponse
    {
        $ioResponse = new IOResponse();
        $wl         = Wishlist::instanceByID($wlID);
        if ($wl->isSelfControlled() === false) {
            return $ioResponse;
        }
        if (Form::validateToken($token)) {
            $wl->setVisibility($state);
        }
        $response        = new stdClass();
        $response->wlID  = $wlID;
        $response->state = $state;
        $response->url   = $wl->getURL();

        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }

    /**
     * @param int   $wlID
     * @param array $formData
     * @return IOResponse
     */
    public function updateWishlistItem(int $wlID, array $formData): IOResponse
    {
        $wl = Wishlist::instanceByID($wlID);
        if ($wl->isSelfControlled() === true && Form::validateToken($formData['jtl_token'])) {
            Wishlist::update($wlID, $formData);
        }

        $ioResponse     = new IOResponse();
        $response       = new stdClass();
        $response->wlID = $wlID;

        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }

    /**
     * @param array $formData
     * @return IOResponse
     * @throws Exception
     * @todo this is currently more of a hack
     */
    public function updateReviewHelpful(array $formData): IOResponse
    {
        $_POST      = $formData;
        $controller = new ReviewController(
            $this->db,
            Shop::Container()->getCache(),
            new State(),
            Shopsetting::getInstance()->getAll(),
            Shop::Container()->getAlertService()
        );
        if (Form::validateToken()) {
            $controller->updateWasHelpful(
                (int)($formData['a'] ?? 0),
                Frontend::getCustomer()->getID(),
                (int)($formData['btgseite'] ?? 0),
                (int)($formData['btgsterne'] ?? 0)
            );
        }
        $ioResponse       = new IOResponse();
        $response         = new stdClass();
        $response->review = flatten(filter(
            (new Artikel($this->db))
                ->fuelleArtikel((int)($formData['a'] ?? 0), Artikel::getDetailOptions())?->Bewertungen->oBewertung_arr,
            static function ($e) use ($formData): bool {
                return (int)$e->kBewertung === (int)$formData['reviewID'];
            }
        ))[0];

        $ioResponse->assignVar('response', $response);

        return $ioResponse;
    }
}
