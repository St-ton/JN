<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Alert\Alert;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Catalog\Product\Preisverlauf;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Product as ProductHelper;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ProductController
 * @package JTL\Router\Controller
 */
class ProductController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();
        $this->currentProduct = new Artikel();
        $this->currentProduct->fuelleArtikel(
            $this->state->productID,
            Artikel::getDetailOptions(),
            $this->customerGroupID,
            $this->state->languageID
        );
        if ($this->state->childProductID > 0) {
            $child = (new Artikel())->fuelleArtikel(
                $this->state->childProductID,
                Artikel::getDetailOptions(),
                $this->customerGroupID,
                $this->state->languageID
            );
            if ($child === null || $child->kArtikel <= 0) {
                return false;
            }
        }

        return $this->currentProduct->kArtikel > 0 && $this->currentProduct->kArtikel === $this->state->productID;
    }

    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        $productID   = (int)($args['id'] ?? 0);
        $productName = $args['name'] ?? null;
        if ($productID < 1 && $productName === null) {
            return $this->state;
        }
        $languageID = $this->parseLanguageFromArgs($args, $this->languageID ?? Shop::getLanguageID());

        $seo = $productID > 0
            ? $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND kKey = :kid
                      AND kSprache = :lid',
                ['key' => 'kArtikel', 'kid' => $productID, 'lid' => $languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key AND cSeo = :seo',
                ['key' => 'kArtikel', 'seo' => $productName]
            );
        if ($seo === null) {
            return $this->handleSeoError($productID, $languageID);
        }
        $slug          = $seo->cSeo;
        $seo->kKey     = (int)$seo->kKey;
        $seo->kSprache = (int)$seo->kSprache;

        return $this->updateState($seo, $slug);
    }

    /**
     * @param int $productID
     * @param int $languageID
     * @return State
     */
    private function handleSeoError(int $productID, int $languageID): State
    {
        if ($productID > 0) {
            $exists = $this->db->getSingleObject(
                'SELECT kArtikel
                    FROM tartikel
                    WHERE kArtikel = :pid',
                ['pid' => $productID]
            );
            if ($exists !== null) {
                $seo           = new stdClass();
                $seo->cSeo     = '';
                $seo->cKey     = 'kArtikel';
                $seo->kKey     = $productID;
                $seo->kSprache = $languageID;

                return $this->updateState($seo, $seo->cSeo);
            }
        }
        $this->state->is404 = true;

        return $this->updateProductFilter();
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        if (isset($args['id']) || isset($args['name'])) {
            $this->getStateFromSlug($args);
            if (!$this->init()) {
                return $this->notFoundResponse($request, $args, $smarty);
            }
        }
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_ARTIKEL);
        global $AktuellerArtikel;
        $priceHistory = null;
        $rated        = false;
        $nonAllowed   = [];
        $shopURL      = Shop::getURL() . '/';
        $valid        = Form::validateToken();
        if ($productNote = ProductHelper::mapErrorCode(
            Request::verifyGPDataString('cHinweis'),
            ((float)Request::getVar('fB', 0) > 0) ? (float)$_GET['fB'] : 0.0
        )) {
            $this->alertService->addNotice($productNote, 'productNote', ['showInAlertListTemplate' => false]);
        }
        if ($productError = ProductHelper::mapErrorCode(Request::verifyGPDataString('cFehler'))) {
            $this->alertService->addError($productError, 'productError');
        }
        if ($valid && isset($_POST['a'])
            && Request::verifyGPCDataInt('addproductbundle') === 1
            && ProductHelper::addProductBundleToCart(Request::verifyGPCDataInt('a'))
        ) {
            $this->alertService->addNotice(Shop::Lang()->get('basketAllAdded', 'messages'), 'allAdded');
            $this->state->productID = Request::postInt('aBundle');
        }
        // Warenkorbmatrix Anzeigen auf Artikel Attribut pruefen und falls vorhanden setzen
        if (isset($this->currentProduct->FunktionsAttribute['warenkorbmatrixanzeigen'])
            && \mb_strlen($this->currentProduct->FunktionsAttribute['warenkorbmatrixanzeigen']) > 0
        ) {
            $this->config['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] =
                $this->currentProduct->FunktionsAttribute['warenkorbmatrixanzeigen'];
        }
        // Warenkorbmatrix Anzeigeformat auf Artikel Attribut pruefen und falls vorhanden setzen
        if (isset($this->currentProduct->FunktionsAttribute['warenkorbmatrixanzeigeformat'])
            && \mb_strlen($this->currentProduct->FunktionsAttribute['warenkorbmatrixanzeigeformat']) > 0
        ) {
            $this->config['artikeldetails']['artikeldetails_warenkorbmatrix_anzeigeformat'] =
                $this->currentProduct->FunktionsAttribute['warenkorbmatrixanzeigeformat'];
        }
        $similarProducts = (int)$this->config['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0
            ? $this->currentProduct->holeAehnlicheArtikel()
            : [];
        if ($this->state->childProductID > 0) {
            $child = new Artikel();
            $child->fuelleArtikel(
                $this->state->childProductID,
                Artikel::getDetailOptions(),
                $this->customerGroupID,
                $this->languageID
            );
            $child->verfuegbarkeitsBenachrichtigung = ProductHelper::showAvailabilityForm(
                $child,
                $this->config['artikeldetails']['benachrichtigung_nutzen']
            );

            $this->currentProduct = ProductHelper::combineParentAndChild($this->currentProduct, $child);
            $this->canonicalURL   = $this->currentProduct->baueVariKombiKindCanonicalURL(
                $this->currentProduct,
                $this->config['artikeldetails']['artikeldetails_canonicalurl_varkombikind'] !== 'N'
            );
        }
        if ($this->config['preisverlauf']['preisverlauf_anzeigen'] === 'Y'
            && Frontend::getCustomerGroup()->mayViewPrices()
        ) {
            $this->state->productID = $this->state->childProductID > 0
                ? $this->state->childProductID
                : $this->currentProduct->kArtikel;
            $priceHistory           = new Preisverlauf();
            $priceHistory           = $priceHistory->gibPreisverlauf(
                $this->state->productID,
                $this->currentProduct->Preise->kKundengruppe,
                (int)$this->config['preisverlauf']['preisverlauf_anzahl_monate']
            );
        }
        // Canonical bei non SEO Shops oder wenn SEO kein Ergebnis geliefert hat
        if (empty($this->canonicalURL)) {
            $this->canonicalURL = $shopURL . $this->currentProduct->cSeo;
        }
        $this->currentProduct->berechneSieSparenX((int)$this->config['artikeldetails']['sie_sparen_x_anzeigen']);

        $messages = ProductHelper::getProductMessages();
        if ($this->config['artikeldetails']['artikeldetails_fragezumprodukt_anzeigen'] !== 'N') {
            $this->smarty->assign('Anfrage', ProductHelper::getProductQuestionFormDefaults());
        }
        if ($this->config['artikeldetails']['benachrichtigung_nutzen'] !== 'N') {
            $this->smarty->assign('Benachrichtigung', ProductHelper::getAvailabilityFormDefaults());
        }
        if ($valid && Request::postInt('fragezumprodukt') === 1) {
            $messages = ProductHelper::checkProductQuestion(
                $messages,
                $this->config['artikeldetails'],
                $this->currentProduct
            );
        } elseif ($valid && Request::postInt('benachrichtigung_verfuegbarkeit') === 1) {
            $messages = ProductHelper::checkAvailabilityMessage($messages, $this->config['artikeldetails']);
        }
        foreach ($messages as $productNoticeKey => $productNotice) {
            $this->alertService->addDanger($productNotice, 'productNotice' . $productNoticeKey);
        }
        $this->currentCategory    = new Kategorie(
            $this->currentProduct->gibKategorie($this->customerGroupID),
            $this->languageID,
            $this->customerGroupID
        );
        $this->expandedCategories = new KategorieListe();
        $this->expandedCategories->getOpenCategories($this->currentCategory);
        $ratingPage   = Request::verifyGPCDataInt('btgseite');
        $ratingStars  = Request::verifyGPCDataInt('btgsterne');
        $sorting      = Request::verifyGPCDataInt('sortierreihenfolge');
        $showRatings  = Request::verifyGPCDataInt('bewertung_anzeigen');
        $allLanguages = Request::verifyGPCDataInt('moreRating');
        if ($ratingPage === 0) {
            $ratingPage = 1;
        }
        if ($this->currentProduct->Bewertungen === null || $ratingStars > 0) {
            $this->currentProduct->holeBewertung(
                -1,
                $ratingPage,
                $ratingStars,
                $this->config['bewertung']['bewertung_freischalten'],
                $sorting,
                $this->config['bewertung']['bewertung_alle_sprachen'] === 'Y'
            );
            $this->currentProduct->holehilfreichsteBewertung();
        }

        if (Frontend::getCustomer()->getID() > 0) {
            $rated = ProductHelper::getRatedByCurrentCustomer(
                $this->currentProduct->kArtikel,
                $this->currentProduct->kVaterArtikel
            );
        }

        $this->currentProduct->Bewertungen->Sortierung = $sorting;

        $ratingsCount = $ratingStars === 0
            ? $this->currentProduct->Bewertungen->nAnzahlSprache
            : $this->currentProduct->Bewertungen->nSterne_arr[5 - $ratingStars];
        $ratingNav    = ProductHelper::getRatingNavigation(
            $ratingPage,
            $ratingStars,
            $ratingsCount,
            $this->config['bewertung']['bewertung_anzahlseite']
        );
        if (Request::hasGPCData('ek')) {
            ProductHelper::getEditConfigMode(Request::verifyGPCDataInt('ek'), $this->smarty);
            $this->smarty->assign(
                'voucherPrice',
                Tax::getGross(
                    Frontend::getCart()->PositionenArr[Request::verifyGPCDataInt('ek')]->fPreis,
                    Tax::getSalesTax($this->currentProduct->kSteuerklasse)
                )
            );
        }
        foreach ($this->currentProduct->Variationen as $Variation) {
            if (!$Variation->Werte || $Variation->cTyp === 'FREIFELD' || $Variation->cTyp === 'PFLICHT-FREIFELD') {
                continue;
            }
            foreach ($Variation->Werte as $value) {
                $nonAllowed[$value->kEigenschaftWert] =
                    ProductHelper::getNonAllowedAttributeValues($value->kEigenschaftWert);
            }
        }
        $child  = $this->currentProduct->kVariKindArtikel ?? 0;
        $parent = $this->currentProduct->kArtikel;
        $nav    = $this->config['artikeldetails']['artikeldetails_navi_blaettern'] === 'Y'
            ? ProductHelper::getProductNavigation($parent, $this->currentCategory->getID() ?? 0)
            : null;
        if ($child === 0 && $this->currentProduct->nIstVater === 0 && Upload::checkLicense()) {
            $maxSize = Upload::uploadMax();
            $this->smarty->assign('nMaxUploadSize', $maxSize)
                ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
                ->assign('oUploadSchema_arr', Upload::gibArtikelUploads($parent));
        }

        $this->smarty->assign('showMatrix', $this->currentProduct->showMatrix())
            ->assign('arNichtErlaubteEigenschaftswerte', $nonAllowed)
            ->assign('oAehnlicheArtikel_arr', $similarProducts)
            ->assign('UVPlocalized', $this->currentProduct->cUVPLocalized)
            ->assign('UVPBruttolocalized', Preise::getLocalizedPriceString($this->currentProduct->fUVPBrutto))
            ->assign('Artikel', $this->currentProduct)
            ->assign('Xselling', $child > 0
                ? ProductHelper::getXSelling($child, null, $this->config['artikeldetails'])
                : ProductHelper::buildXSellersFromIDs($this->currentProduct->similarProducts, $parent))
            ->assign('Artikelhinweise', $messages)
            ->assign(
                'verfuegbarkeitsBenachrichtigung',
                ProductHelper::showAvailabilityForm(
                    $this->currentProduct,
                    $this->config['artikeldetails']['benachrichtigung_nutzen']
                )
            )
            ->assign('BlaetterNavi', $ratingNav)
            ->assign('BewertungsTabAnzeigen', (int)($ratingPage > 0
                || $ratingStars > 0
                || $showRatings > 0
                || $allLanguages > 0))
            ->assign('alertNote', $this->alertService->alertTypeExists(Alert::TYPE_NOTE))
            ->assign('bewertungSterneSelected', $ratingStars)
            ->assign('bPreisverlauf', \is_array($priceHistory) && \count($priceHistory) > 1)
            ->assign('preisverlaufData', $priceHistory)
            ->assign('NavigationBlaettern', $nav)
            ->assign('bereitsBewertet', $rated)
            ->assignDeprecated('PFAD_MEDIAFILES', $shopURL . \PFAD_MEDIAFILES, '5.0.0')
            ->assignDeprecated('PFAD_BILDER', \PFAD_BILDER, '5.0.0')
            ->assignDeprecated('FKT_ATTRIBUT_ATTRIBUTEANHAENGEN', \FKT_ATTRIBUT_ATTRIBUTEANHAENGEN, '5.0.0')
            ->assignDeprecated('FKT_ATTRIBUT_WARENKORBMATRIX', \FKT_ATTRIBUT_WARENKORBMATRIX, '5.0.0')
            ->assignDeprecated('FKT_ATTRIBUT_INHALT', \FKT_ATTRIBUT_INHALT, '5.0.0')
            ->assignDeprecated('FKT_ATTRIBUT_MAXBESTELLMENGE', \FKT_ATTRIBUT_MAXBESTELLMENGE, '5.0.0')
            ->assignDeprecated('KONFIG_ITEM_TYP_ARTIKEL', \KONFIG_ITEM_TYP_ARTIKEL, '5.0.0')
            ->assignDeprecated('KONFIG_ITEM_TYP_SPEZIAL', \KONFIG_ITEM_TYP_SPEZIAL, '5.0.0')
            ->assignDeprecated('KONFIG_ANZEIGE_TYP_CHECKBOX', \KONFIG_ANZEIGE_TYP_CHECKBOX, '5.0.0')
            ->assignDeprecated('KONFIG_ANZEIGE_TYP_RADIO', \KONFIG_ANZEIGE_TYP_RADIO, '5.0.0')
            ->assignDeprecated('KONFIG_ANZEIGE_TYP_DROPDOWN', \KONFIG_ANZEIGE_TYP_DROPDOWN, '5.0.0')
            ->assignDeprecated('KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI', \KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI, '5.0.0');

        $this->assignPagination();
        $AktuellerArtikel = $this->currentProduct; // @todo
        $this->preRender();

        \executeHook(\HOOK_ARTIKEL_PAGE, ['oArtikel' => $this->currentProduct]);

        if (Request::isAjaxRequest()) {
            $this->smarty->assign('listStyle', Text::filterXSS($_GET['isListStyle'] ?? ''));
        }

        return $this->smarty->getResponse('productdetails/index.tpl');
    }

    /**
     * @return void
     */
    protected function assignPagination(): void
    {
        $ratings = $this->currentProduct->Bewertungen->oBewertung_arr;
        if ((int)($this->currentProduct->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich ?? 0) > 0) {
            $ratings = \array_filter(
                $this->currentProduct->Bewertungen->oBewertung_arr,
                function ($rating) {
                    return (int)$this->currentProduct->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung
                        !== (int)$rating->kBewertung;
                }
            );
        }
        $pagination = new Pagination('ratings');
        $pagination->setItemArray($ratings)
            ->setItemsPerPageOptions([(int)$this->config['bewertung']['bewertung_anzahlseite']])
            ->setDefaultItemsPerPage($this->config['bewertung']['bewertung_anzahlseite'])
            ->setSortByOptions([
                ['dDatum', Shop::Lang()->get('paginationOrderByDate')],
                ['nSterne', Shop::Lang()->get('paginationOrderByRating')],
                ['nHilfreich', Shop::Lang()->get('paginationOrderUsefulness')]
            ])
            ->setDefaultSortByDir((int)$this->config['bewertung']['bewertung_sortierung'])
            ->setSortFunction(function ($a, $b) use ($pagination) {
                $sortBy  = $pagination->getSortByCol();
                $sortDir = $pagination->getSortDirSQL() === 0 ? +1 : -1;
                $valueA  = \is_string($a->$sortBy) ? \mb_convert_case($a->$sortBy, \MB_CASE_LOWER) : $a->$sortBy;
                $valueB  = \is_string($b->$sortBy) ? \mb_convert_case($b->$sortBy, \MB_CASE_LOWER) : $b->$sortBy;

                if ($b->kSprache === $this->languageID && $a->kSprache !== $this->languageID) {
                    return +1;
                }
                if ($a->kSprache === $this->languageID && $b->kSprache !== $this->languageID) {
                    return -1;
                }
                if ($valueA === $valueB) {
                    return 0;
                }
                if ($valueA < $valueB) {
                    return -$sortDir;
                }
                return +$sortDir;
            })
            ->assemble();

        $this->smarty->assign('ratingPagination', $pagination);
    }
}
