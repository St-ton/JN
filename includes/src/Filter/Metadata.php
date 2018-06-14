<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use DB\ReturnType;
use function Functional\group;
use function Functional\map;
use function Functional\reduce_left;
use function Functional\reindex;
use Tightenco\Collect\Support\Collection;

/**
 * Class Metadata
 */
class Metadata implements MetadataInterface
{
    use \MagicCompatibilityTrait;

    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * @var array
     */
    private $conf;

    /**
     * @var string
     */
    private $breadCrumb = '';

    /**
     * @var string
     */
    private $metaTitle = '';

    /**
     * @var string
     */
    private $metaDescription = '';

    /**
     * @var string
     */
    private $metaKeywords = '';

    /**
     * @var \Kategorie
     */
    private $category;

    /**
     * @var \Hersteller
     */
    private $manufacturer;

    /**
     * @var \MerkmalWert
     */
    private $attributeValue;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $imageURL = BILD_KEIN_KATEGORIEBILD_VORHANDEN;

    /**
     * @var array
     */
    private static $mapping = [
        'cMetaTitle'       => 'MetaTitle',
        'cMetaDescription' => 'MetaDescription',
        'cMetaKeywords'    => 'MetaKeywords',
        'cName'            => 'Name',
        'oHersteller'      => 'Manufacturer',
        'cBildURL'         => 'ImageURL',
        'oMerkmalWert'     => 'AttributeValue',
        'oKategorie'       => 'Category',
        'cBrotNavi'        => 'BreadCrumb'
    ];

    /**
     * Metadata constructor.
     * @param ProductFilter $navigationsfilter
     */
    public function __construct(ProductFilter $navigationsfilter)
    {
        $this->productFilter = $navigationsfilter;
        $this->conf          = $navigationsfilter->getConfig();
    }

    /**
     * @inheritdoc
     */
    public function getBreadCrumb(): string
    {
        return $this->breadCrumb;
    }

    /**
     * @inheritdoc
     */
    public function setBreadCrumb(string $breadCrumb): MetadataInterface
    {
        $this->breadCrumb = $breadCrumb;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    /**
     * @inheritdoc
     */
    public function setMetaTitle($metaTitle): MetadataInterface
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @inheritdoc
     */
    public function setMetaDescription($metaDescription): MetadataInterface
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @inheritdoc
     */
    public function setMetaKeywords($metaKeywords): MetadataInterface
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @inheritdoc
     */
    public function setCategory(\Kategorie $category): MetadataInterface
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @inheritdoc
     */
    public function setManufacturer(\Hersteller $manufacturer): MetadataInterface
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeValue(\MerkmalWert $attributeValue): MetadataInterface
    {
        $this->attributeValue = $attributeValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): MetadataInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getImageURL(): string
    {
        return $this->imageURL;
    }

    /**
     * @inheritdoc
     */
    public function setImageURL(string $imageURL): MetadataInterface
    {
        $this->imageURL = $imageURL;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasData(): bool
    {
        return !empty($this->imageURL) || !empty($this->name);
    }

    /**
     * @inheritdoc
     */
    public static function getGlobalMetaData(): array
    {
        return \Shop::Container()->getCache()->get('jtl_glob_meta', function ($cache, $id, &$content, &$tags) {
            $globalTmp = \Shop::Container()->getDB()->query(
                'SELECT cName, kSprache, cWertName 
                    FROM tglobalemetaangaben ORDER BY kSprache',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $content   = map(group($globalTmp, function ($g) {
                return $g->kSprache;
            }), function ($item) {
                return reduce_left($item, function ($value, $index, $collection, $reduction) {
                    $reduction->{$value->cName} = $value->cWertName;

                    return $reduction;
                }, new \stdClass());
            });
            $tags      = [CACHING_GROUP_CORE];

            return true;
        });
    }

    /**
     * @inheritdoc
     */
    public static function getExcludes(): array
    {
        return \Shop::Container()->getCache()->get('jtl_glob_excl', function ($cache, $id, &$content, &$tags) {
            $keyWords = \Shop::Container()->getDB()->query(
                'SELECT * 
                    FROM texcludekeywords 
                    ORDER BY cISOSprache',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $content  = reindex($keyWords, function ($e) {
                return $e->cISOSprache;
            });
            $tags     = [CACHING_GROUP_OPTION];

            return true;
        });
    }

    /**
     * @inheritdoc
     */
    public static function getFilteredString($cString, array $oExcludesKeywords_arr): string
    {
        return str_replace(array_map(
            function ($k) {
                return ' ' . $k . ' ';
            },
            $oExcludesKeywords_arr
        ), ' ', $cString);
    }

    /**
     * @inheritdoc
     */
    public static function getSearchSpecialConfigMapping(array $config): array
    {
        $mapping = [];
        foreach ($config as $key => $oSuchspecialEinstellung) {
            switch ($key) {
                case 'suchspecials_sortierung_bestseller':
                    $mapping[SEARCHSPECIALS_BESTSELLER] = $oSuchspecialEinstellung;
                    break;
                case 'suchspecials_sortierung_sonderangebote':
                    $mapping[SEARCHSPECIALS_SPECIALOFFERS] = $oSuchspecialEinstellung;
                    break;
                case 'suchspecials_sortierung_neuimsortiment':
                    $mapping[SEARCHSPECIALS_NEWPRODUCTS] = $oSuchspecialEinstellung;
                    break;
                case 'suchspecials_sortierung_topangebote':
                    $mapping[SEARCHSPECIALS_TOPOFFERS] = $oSuchspecialEinstellung;
                    break;
                case 'suchspecials_sortierung_inkuerzeverfuegbar':
                    $mapping[SEARCHSPECIALS_UPCOMINGPRODUCTS] = $oSuchspecialEinstellung;
                    break;
                case 'suchspecials_sortierung_topbewertet':
                    $mapping[SEARCHSPECIALS_TOPREVIEWS] = $oSuchspecialEinstellung;
                    break;
                default:
                    break;
            }
        }

        return $mapping;
    }

    /**
     * @inheritdoc
     */
    public function getNavigationInfo($currentCategory = null, $openCategories = null): MetadataInterface
    {
        if ($currentCategory !== null && $this->productFilter->hasCategory()) {
            $this->category = $currentCategory;

            if ($this->conf['navigationsfilter']['kategorie_bild_anzeigen'] === 'Y') {
                $this->name = $this->category->getName();
            } elseif ($this->conf['navigationsfilter']['kategorie_bild_anzeigen'] === 'BT') {
                $this->name     = $this->category->getName();
                $this->imageURL = $this->category->getKategorieBild();
            } elseif ($this->conf['navigationsfilter']['kategorie_bild_anzeigen'] === 'B') {
                $this->imageURL = $currentCategory->getKategorieBild();
            }
            $this->breadCrumb = createNavigation('PRODUKTE', $openCategories);
        } elseif ($this->productFilter->hasManufacturer()) {
            $this->manufacturer = new \Hersteller($this->productFilter->getManufacturer()->getValue());

            if ($this->conf['navigationsfilter']['hersteller_bild_anzeigen'] === 'Y') {
                $this->name = $this->manufacturer->getName();
            } elseif ($this->conf['navigationsfilter']['hersteller_bild_anzeigen'] === 'BT') {
                $this->name     = $this->manufacturer->getName();
                $this->imageURL = $this->manufacturer->cBildpfadNormal;
            } elseif ($this->conf['navigationsfilter']['hersteller_bild_anzeigen'] === 'B') {
                $this->imageURL = $this->manufacturer->cBildpfadNormal;
            }
            if ($this->manufacturer !== null) {
                $this->setMetaTitle($this->manufacturer->cMetaTitle)
                     ->setMetaDescription($this->manufacturer->cMetaDescription)
                     ->setMetaKeywords($this->manufacturer->cMetaKeywords);
            }
            $this->breadCrumb = createNavigation(
                '',
                '',
                0,
                $this->getBreadCrumbName(),
                $this->productFilter->getFilterURL()->getURL()
            );
        } elseif ($this->productFilter->hasAttributeValue()) {
            $this->attributeValue = new \MerkmalWert($this->productFilter->getAttributeValue()->getValue());
            if ($this->conf['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'Y') {
                $this->setName($this->attributeValue->cWert);
            } elseif ($this->conf['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'BT') {
                $this->setName($this->attributeValue->cWert)
                     ->setImageURL($this->attributeValue->cBildpfadNormal);
            } elseif ($this->conf['navigationsfilter']['merkmalwert_bild_anzeigen'] === 'B') {
                $this->setImageURL($this->attributeValue->cBildpfadNormal);
            }
            if ($this->attributeValue !== null) {
                $this->setMetaTitle($this->attributeValue->cMetaTitle)
                     ->setMetaDescription($this->attributeValue->cMetaDescription)
                     ->setMetaKeywords($this->attributeValue->cMetaKeywords);
            }
            $this->breadCrumb = createNavigation(
                '',
                '',
                0,
                $this->getBreadCrumbName(),
                $this->productFilter->getFilterURL()->getURL()
            );
        } elseif ($this->productFilter->hasTag()
            || $this->productFilter->hasSearchSpecial()
            || $this->productFilter->hasSearch()
        ) {
            $this->breadCrumb = createNavigation(
                '',
                '',
                0,
                $this->getBreadCrumbName(),
                $this->productFilter->getFilterURL()->getURL()
            );
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function generateMetaDescription(
        array $products,
        ProductFilterSearchResultsInterface $searchResults,
        array $globalMeta,
        $category = null
    ): string {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETADESCRIPTION);
        $maxLength = !empty($this->conf['metaangaben']['global_meta_maxlaenge_description'])
            ? (int)$this->conf['metaangaben']['global_meta_maxlaenge_description']
            : 0;
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (!empty($this->metaDescription)) {
            return prepareMeta(
                strip_tags($this->metaDescription),
                null,
                $maxLength
            );
        }
        // Kategorieattribut?
        $cKatDescription = '';
        $languageID      = $this->productFilter->getLanguageID();
        if ($this->productFilter->hasCategory()) {
            $category = $category ?? new \Kategorie($this->productFilter->getCategory()->getValue());
            if (!empty($category->cMetaDescription)) {
                // meta description via new method
                return prepareMeta(
                    strip_tags($category->cMetaDescription),
                    null,
                    $maxLength
                );
            }
            if (!empty($category->categoryAttributes['meta_description']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut eine Meta Description gesetzt?
                return prepareMeta(
                    strip_tags($category->categoryAttributes['meta_description']->cWert),
                    null,
                    $maxLength
                );
            }
            if (!empty($category->KategorieAttribute['meta_description'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                return prepareMeta(
                    strip_tags($category->KategorieAttribute['meta_description']),
                    null,
                    $maxLength
                );
            }
            // Hat die aktuelle Kategorie eine Beschreibung?
            if (!empty($category->cBeschreibung)) {
                $cKatDescription = strip_tags(str_replace(['<br>', '<br />'], [' ', ' '], $category->cBeschreibung));
            } elseif ($category->bUnterKategorien) {
                // Hat die aktuelle Kategorie Unterkategorien?
                $categoryListe = new \KategorieListe();
                $categoryListe->getAllCategoriesOnLevel($category->kKategorie);

                if (!empty($categoryListe->elemente) && count($categoryListe->elemente) > 0) {
                    foreach ($categoryListe->elemente as $i => $oUnterkat) {
                        if (!empty($oUnterkat->cName)) {
                            $cKatDescription .= $i > 0
                                ? ', ' . strip_tags($oUnterkat->cName)
                                : strip_tags($oUnterkat->cName);
                        }
                    }
                }
            }

            if (strlen($cKatDescription) > 1) {
                $cKatDescription  = str_replace('"', '', $cKatDescription);
                $cKatDescription  = \StringHandler::htmlentitydecode($cKatDescription, ENT_NOQUOTES);
                $cMetaDescription = !empty($globalMeta[$languageID]->Meta_Description_Praefix)
                    ? trim(
                        strip_tags($globalMeta[$languageID]->Meta_Description_Praefix) .
                        ' ' .
                        $cKatDescription
                    )
                    : trim($cKatDescription);
                // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
                if ($searchResults->getPages()->AktuelleSeite > 1
                    && $searchResults->getOffsetStart() > 0
                    && $searchResults->getOffsetEnd() > 0
                ) {
                    $cMetaDescription .= ', ' . \Shop::Lang()->get('products') .
                        " {$searchResults->getOffsetStart()} - {$searchResults->getOffsetEnd()}";
                }

                return prepareMeta($cMetaDescription, null, $maxLength);
            }
        }
        // Keine eingestellten Metas vorhanden => generiere Standard Metas
        $cMetaDescription = '';
        if (is_array($products) && count($products) > 0) {
            shuffle($products);
            $nCount       = min(12, count($products));
            $cArtikelName = '';
            for ($i = 0; $i < $nCount; ++$i) {
                $cArtikelName .= $i > 0
                    ? ' - ' . $products[$i]->cName
                    : $products[$i]->cName;
            }
            $cArtikelName = str_replace('"', '', $cArtikelName);
            $cArtikelName = \StringHandler::htmlentitydecode($cArtikelName, ENT_NOQUOTES);

            $cMetaDescription = !empty($globalMeta[$languageID]->Meta_Description_Praefix)
                ? $this->getMetaStart($searchResults) .
                ': ' .
                $globalMeta[$languageID]->Meta_Description_Praefix .
                ' ' . $cArtikelName
                : $this->getMetaStart($searchResults) . ': ' . $cArtikelName;
            // Seitenzahl anhaengen ab Seite 2 (Doppelte Meta-Descriptions vermeiden, #5992)
            if ($searchResults->getPages()->AktuelleSeite > 1
                && $searchResults->getOffsetStart() > 0
                && $searchResults->getOffsetEnd() > 0
            ) {
                $cMetaDescription .= ', ' . \Shop::Lang()->get('products') . ' ' .
                    $searchResults->getOffsetStart() . ' - ' . $searchResults->getOffsetEnd();
            }
        }

        return prepareMeta(strip_tags($cMetaDescription), null, $maxLength);
    }

    /**
     * @inheritdoc
     */
    public function generateMetaKeywords($products, $category = null): string
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETAKEYWORDS);
        // Prüfen ob bereits eingestellte Metas gesetzt sind
        if (!empty($this->metaKeywords)) {
            return strip_tags($this->metaKeywords);
        }
        // Kategorieattribut?
        $cKatKeywords = '';
        if ($this->productFilter->hasCategory()) {
            $category = $category ?? new \Kategorie($this->productFilter->getCategory()->getValue());
            if (!empty($category->cMetaKeywords)) {
                // meta keywords via new method
                return strip_tags($category->cMetaKeywords);
            }
            if (!empty($category->categoryAttributes['meta_keywords']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Keywords gesetzt?
                return strip_tags($category->categoryAttributes['meta_keywords']->cWert);
            }
            if (!empty($category->KategorieAttribute['meta_keywords'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */

                return strip_tags($category->KategorieAttribute['meta_keywords']);
            }
        }
        // Keine eingestellten Metas vorhanden => baue Standard Metas
        $cMetaKeywords = '';
        if (is_array($products) && count($products) > 0) {
            shuffle($products); // Shuffle alle Artikel
            $nCount                = min(6, count($products));
            $cArtikelName          = '';
            $excludes              = self::getExcludes();
            $oExcludesKeywords_arr = isset($excludes[$_SESSION['cISOSprache']]->cKeywords)
                ? explode(' ', $excludes[$_SESSION['cISOSprache']]->cKeywords)
                : [];
            for ($i = 0; $i < $nCount; ++$i) {
                $cExcArtikelName = self::getFilteredString(
                    $products[$i]->cName,
                    $oExcludesKeywords_arr
                ); // Filter nicht erlaubte Keywords
                if (strpos($cExcArtikelName, ' ') !== false) {
                    // Wenn der Dateiname aus mehreren Wörtern besteht
                    $cSubNameTMP_arr = explode(' ', $cExcArtikelName);
                    $cSubName        = '';
                    if (is_array($cSubNameTMP_arr) && count($cSubNameTMP_arr) > 0) {
                        foreach ($cSubNameTMP_arr as $j => $cSubNameTMP) {
                            if (strlen($cSubNameTMP) > 2) {
                                $cSubNameTMP = str_replace(',', '', $cSubNameTMP);
                                $cSubName    .= $j > 0
                                    ? ', ' . $cSubNameTMP
                                    : $cSubNameTMP;
                            }
                        }
                    }
                    $cArtikelName .= $cSubName;
                } elseif ($i > 0) {
                    $cArtikelName .= ', ' . $products[$i]->cName;
                } else {
                    $cArtikelName .= $products[$i]->cName;
                }
            }
            $cMetaKeywords = $cArtikelName;
            // Prüfe doppelte Einträge und lösche diese
            $cMetaKeywordsUnique_arr = [];
            $cMeta_arr               = explode(', ', $cMetaKeywords);
            if (is_array($cMeta_arr) && count($cMeta_arr) > 1) {
                foreach ($cMeta_arr as $cMeta) {
                    if (!in_array($cMeta, $cMetaKeywordsUnique_arr, true)) {
                        $cMetaKeywordsUnique_arr[] = $cMeta;
                    }
                }
                $cMetaKeywords = implode(', ', $cMetaKeywordsUnique_arr);
            }
        } elseif (!empty($category->kKategorie)) {
            // Hat die aktuelle Kategorie Unterkategorien?
            if ($category->bUnterKategorien) {
                $categoryListe = new \KategorieListe();
                $categoryListe->getAllCategoriesOnLevel($category->kKategorie);
                if (!empty($categoryListe->elemente) && count($categoryListe->elemente) > 0) {
                    foreach ($categoryListe->elemente as $i => $oUnterkat) {
                        if (!empty($oUnterkat->cName)) {
                            $cKatKeywords .= $i > 0
                                ? ', ' . $oUnterkat->cName
                                : $oUnterkat->cName;
                        }
                    }
                }
            } elseif (!empty($category->cBeschreibung)) { // Hat die aktuelle Kategorie eine Beschreibung?
                $cKatKeywords = $category->cBeschreibung;
            }
            $cKatKeywords  = str_replace('"', '', $cKatKeywords);
            $cMetaKeywords = $cKatKeywords;

            return strip_tags($cMetaKeywords);
        }

        return strip_tags(\StringHandler::htmlentitydecode(str_replace('"', '', $cMetaKeywords), ENT_NOQUOTES));
    }

    /**
     * @inheritdoc
     */
    public function generateMetaTitle($searchResults, $globalMeta, $category = null): string
    {
        executeHook(HOOK_FILTER_INC_GIBNAVIMETATITLE);
        $languageID = $this->productFilter->getLanguageID();
        $append     = $this->conf['metaangaben']['global_meta_title_anhaengen'] === 'Y';
        // Pruefen ob bereits eingestellte Metas gesetzt sind
        if (!empty($this->metaTitle)) {
            $metaTitle = strip_tags($this->metaTitle);
            // Globalen Meta Title anhaengen
            if ($append === true && !empty($globalMeta[$languageID]->Title)) {
                return $this->truncateMetaTitle(
                    $metaTitle . ' ' .
                    $globalMeta[$languageID]->Title
                );
            }

            return $this->truncateMetaTitle($metaTitle);
        }
        // Set Default Titles
        $cMetaTitle = $this->getMetaStart($searchResults);
        $cMetaTitle = str_replace('"', "'", $cMetaTitle);
        $cMetaTitle = \StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
        // Kategorieattribute koennen Standard-Titles ueberschreiben
        if ($this->productFilter->hasCategory()) {
            $category = $category ?? new \Kategorie($this->productFilter->getCategory()->getValue());
            if (!empty($category->cTitleTag)) {
                // meta title via new method
                $cMetaTitle = strip_tags($category->cTitleTag);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = \StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($category->categoryAttributes['meta_title']->cWert)) {
                // Hat die aktuelle Kategorie als Kategorieattribut einen Meta Title gesetzt?
                $cMetaTitle = strip_tags($category->categoryAttributes['meta_title']->cWert);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = \StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            } elseif (!empty($category->KategorieAttribute['meta_title'])) {
                /** @deprecated since 4.05 - this is for compatibilty only! */
                $cMetaTitle = strip_tags($category->KategorieAttribute['meta_title']);
                $cMetaTitle = str_replace('"', "'", $cMetaTitle);
                $cMetaTitle = \StringHandler::htmlentitydecode($cMetaTitle, ENT_NOQUOTES);
            }
        }
        // Seitenzahl anhaengen ab Seite 2 (Doppelte Titles vermeiden, #5992)
        if ($searchResults->getPages()->AktuelleSeite > 1) {
            $cMetaTitle .= ', ' . \Shop::Lang()->get('page') . ' ' .
                $searchResults->getPages()->AktuelleSeite;
        }
        // Globalen Meta Title ueberall anhaengen
        if ($append === true && !empty($globalMeta[$languageID]->Title)) {
            $cMetaTitle .= ' - ' . $globalMeta[$languageID]->Title;
        }
        // @todo: temp. fix to avoid destroyed header
        $cMetaTitle = str_replace(['<', '>'], ['&lt;', '&gt;'], $cMetaTitle);

        return $this->truncateMetaTitle($cMetaTitle);
    }

    /**
     * Erstellt für die NaviMetas die gesetzten Mainwords + Filter und stellt diese vor jedem Meta an.
     *
     * @param ProductFilterSearchResultsInterface $searchResults
     * @return string
     */
    public function getMetaStart($searchResults): string
    {
        $parts = new Collection();
        // MerkmalWert
        if ($this->productFilter->hasAttributeValue()) {
            $parts->push($this->productFilter->getAttributeValue()->getName());
        } elseif ($this->productFilter->hasCategory()) { // Kategorie
            $parts->push($this->productFilter->getCategory()->getName());
        } elseif ($this->productFilter->hasManufacturer()) { // Hersteller
            $parts->push($this->productFilter->getManufacturer()->getName());
        } elseif ($this->productFilter->hasTag()) { // Tag
            $parts->push($this->productFilter->getTag()->getName());
        } elseif ($this->productFilter->hasSearch()) { // Suchbegriff
            $parts->push($this->productFilter->getSearch()->getName());
        } elseif ($this->productFilter->hasSearchQuery()) { // Suchbegriff
            $parts->push($this->productFilter->getSearchQuery()->getName());
        } elseif ($this->productFilter->hasSearchSpecial()) { // Suchspecial
            $parts->push($this->productFilter->getSearchSpecial()->getName());
        }
        // Kategoriefilter
        if ($this->productFilter->hasCategoryFilter()) {
            $parts->push($this->productFilter->getCategoryFilter()->getName());
        }
        // Herstellerfilter
        if ($this->productFilter->hasManufacturerFilter()) {
            $parts->push($this->productFilter->getManufacturerFilter()->getName());
        }
        // Tagfilter
        if ($this->productFilter->hasTagFilter()
            && ($name = $this->productFilter->getTagFilter(0)->getName()) !== null
        ) {
            $parts->push($name);
        }
        // Suchbegrifffilter
        $parts = $parts->merge(collect($this->productFilter->getSearchFilter())
            ->map(function (FilterInterface $filter, $key) {
                return $filter->getName();
            })
            ->reject(function ($name) {
                return $name === null;
            })
        );
        // Suchspecialfilter
        if ($this->productFilter->hasSearchSpecialFilter()) {
            switch ($this->productFilter->getSearchSpecialFilter()->getValue()) {
                case SEARCHSPECIALS_BESTSELLER:
                    $parts->push(\Shop::Lang()->get('bestsellers'));
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    $parts->push(\Shop::Lang()->get('specialOffers'));
                    break;

                case SEARCHSPECIALS_NEWPRODUCTS:
                    $parts->push(\Shop::Lang()->get('newProducts'));
                    break;

                case SEARCHSPECIALS_TOPOFFERS:
                    $parts->push(\Shop::Lang()->get('topOffers'));
                    break;

                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $parts->push(\Shop::Lang()->get('upcomingProducts'));
                    break;

                case SEARCHSPECIALS_TOPREVIEWS:
                    $parts->push(\Shop::Lang()->get('topReviews'));
                    break;

                default:
                    break;
            }
        }
        // MerkmalWertfilter
        $parts = $parts->merge(collect($this->productFilter->getAttributeFilter())
            ->map(function (FilterInterface $filter, $key) {
                return $filter->getName();
            })
            ->reject(function ($name) {
                return $name === null;
            })
        );

        return $parts->implode(' ');
    }

    /**
     * @inheritdoc
     */
    public function truncateMetaTitle($cTitle): string
    {
        return ($length = (int)$this->conf['metaangaben']['global_meta_maxlaenge_title']) > 0
            ? substr($cTitle, 0, $length)
            : $cTitle;
    }

    /**
     * @inheritdoc
     */
    public function getHeader(): string
    {
        if ($this->productFilter->hasCategory()) {
            $this->breadCrumb = $this->productFilter->getCategory()->getName();

            return $this->breadCrumb ?? '';
        }
        if ($this->productFilter->hasManufacturer()) {
            $this->breadCrumb = $this->productFilter->getManufacturer()->getName();

            return \Shop::Lang()->get('productsFrom') . ' ' . $this->breadCrumb;
        }
        if ($this->productFilter->hasAttributeValue()) {
            $this->breadCrumb = $this->productFilter->getAttributeValue()->getName();

            return \Shop::Lang()->get('productsWith') . ' ' . $this->breadCrumb;
        }
        if ($this->productFilter->hasTag()) {
            $this->breadCrumb = $this->productFilter->getTag()->getName();

            return \Shop::Lang()->get('showAllProductsTaggedWith') . ' ' . $this->breadCrumb;
        }
        if ($this->productFilter->hasSearchSpecial()) {
            $this->breadCrumb = $this->productFilter->getSearchSpecial()->getName();

            return $this->breadCrumb ?? '';
        }
        if ($this->productFilter->hasSearch()) {
            $this->breadCrumb = $this->productFilter->getSearch()->getName();
        } elseif ($this->productFilter->getSearchQuery()->isInitialized()) {
            $this->breadCrumb = $this->productFilter->getSearchQuery()->getName();
        }
        if (!empty($this->productFilter->getSearch()->getName())
            || !empty($this->productFilter->getSearchQuery()->getName())
        ) {
            return \Shop::Lang()->get('for') . ' ' . $this->breadCrumb;
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function getBreadCrumbName()
    {
        return $this->breadCrumb;
    }

    /**
     * @inheritdoc
     */
    public function buildPageNavigation($bSeo, $oSeitenzahlen, $nMaxAnzeige = 7, $cFilterShopURL = ''): array
    {
        if (strlen($cFilterShopURL) > 0) {
            $bSeo = false;
        }
        $cURL        = '';
        $oSeite_arr  = [];
        $nVon        = 0; // Die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
        $nBis        = 0; // Begrenzt durch $nMaxAnzeige.
        $naviURL     = $this->productFilter->getFilterURL()->getURL();
        $bSeo        = $bSeo && strpos($naviURL, '?') === false;
        $nMaxAnzeige = (int)$nMaxAnzeige;
        if (isset($oSeitenzahlen->MaxSeiten, $oSeitenzahlen->AktuelleSeite)
            && $oSeitenzahlen->MaxSeiten > 0
            && $oSeitenzahlen->AktuelleSeite > 0
        ) {
            $oSeitenzahlen->AktuelleSeite = (int)$oSeitenzahlen->AktuelleSeite;
            $nMax                         = (int)floor($nMaxAnzeige / 2);
            if ($oSeitenzahlen->MaxSeiten > $nMaxAnzeige) {
                if ($oSeitenzahlen->AktuelleSeite - $nMax >= 1) {
                    $nDiff = 0;
                    $nVon  = $oSeitenzahlen->AktuelleSeite - $nMax;
                } else {
                    $nVon  = 1;
                    $nDiff = $nMax - $oSeitenzahlen->AktuelleSeite + 1;
                }
                if ($oSeitenzahlen->AktuelleSeite + $nMax + $nDiff <= $oSeitenzahlen->MaxSeiten) {
                    $nBis = $oSeitenzahlen->AktuelleSeite + $nMax + $nDiff;
                } else {
                    $nDiff = $oSeitenzahlen->AktuelleSeite + $nMax - $oSeitenzahlen->MaxSeiten;
                    if ($nDiff === 0) {
                        $nVon -= ($nMaxAnzeige - ($nMax + 1));
                    } elseif ($nDiff > 0) {
                        $nVon = $oSeitenzahlen->AktuelleSeite - $nMax - $nDiff;
                    }
                    $nBis = (int)$oSeitenzahlen->MaxSeiten;
                }
                // Laufe alle Seiten durch und baue URLs + Seitenzahl
                for ($i = $nVon; $i <= $nBis; ++$i) {
                    $oSeite         = new \stdClass();
                    $oSeite->nSeite = $i;
                    if ($i === $oSeitenzahlen->AktuelleSeite) {
                        $oSeite->cURL = '';
                    } else {
                        if ($oSeite->nSeite === 1) {
                            $oSeite->cURL = $naviURL . $cFilterShopURL;
                        } else {
                            if ($bSeo) {
                                $cURL         = $naviURL;
                                $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                                    ? $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL
                                    : $cURL . SEP_SEITE . $oSeite->nSeite;
                            } else {
                                $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            }
                        }
                    }
                    $oSeite_arr[] = $oSeite;
                }
            } else {
                // Laufe alle Seiten durch und baue URLs + Seitenzahl
                for ($i = 0; $i < $oSeitenzahlen->MaxSeiten; ++$i) {
                    $oSeite         = new \stdClass();
                    $oSeite->nSeite = $i + 1;

                    if ($i + 1 === $oSeitenzahlen->AktuelleSeite) {
                        $oSeite->cURL = '';
                    } else {
                        if ($oSeite->nSeite === 1) {
                            $oSeite->cURL = $naviURL . $cFilterShopURL;
                        } else {
                            if ($bSeo) {
                                $cURL         = $naviURL;
                                $oSeite->cURL = strpos(basename($cURL), 'index.php') !== false
                                    ? $cURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL
                                    : $cURL . SEP_SEITE . $oSeite->nSeite;
                            } else {
                                $oSeite->cURL = $naviURL . '&amp;seite=' . $oSeite->nSeite . $cFilterShopURL;
                            }
                        }
                    }
                    $oSeite_arr[] = $oSeite;
                }
            }
            // Baue Zurück-URL
            $oSeite_arr['zurueck']       = new \stdClass();
            $oSeite_arr['zurueck']->nBTN = 1;
            if ($oSeitenzahlen->AktuelleSeite > 1) {
                $oSeite_arr['zurueck']->nSeite = (int)$oSeitenzahlen->AktuelleSeite - 1;
                if ($oSeite_arr['zurueck']->nSeite === 1) {
                    $oSeite_arr['zurueck']->cURL = $naviURL . $cFilterShopURL;
                } else {
                    if ($bSeo) {
                        $cURL = $naviURL;
                        if (strpos(basename($cURL), 'index.php') !== false) {
                            $oSeite_arr['zurueck']->cURL = $cURL . '&amp;seite=' .
                                $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                        } else {
                            $oSeite_arr['zurueck']->cURL = $cURL . SEP_SEITE .
                                $oSeite_arr['zurueck']->nSeite;
                        }
                    } else {
                        $oSeite_arr['zurueck']->cURL = $naviURL . '&amp;seite=' .
                            $oSeite_arr['zurueck']->nSeite . $cFilterShopURL;
                    }
                }
            }
            // Baue Vor-URL
            $oSeite_arr['vor']       = new \stdClass();
            $oSeite_arr['vor']->nBTN = 1;
            if ($oSeitenzahlen->AktuelleSeite < $oSeitenzahlen->maxSeite) {
                $oSeite_arr['vor']->nSeite = $oSeitenzahlen->AktuelleSeite + 1;
                if ($bSeo) {
                    $cURL = $naviURL;
                    if (strpos(basename($cURL), 'index.php') !== false) {
                        $oSeite_arr['vor']->cURL = $cURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
                    } else {
                        $oSeite_arr['vor']->cURL = $cURL . SEP_SEITE . $oSeite_arr['vor']->nSeite;
                    }
                } else {
                    $oSeite_arr['vor']->cURL = $naviURL . '&amp;seite=' . $oSeite_arr['vor']->nSeite . $cFilterShopURL;
                }
            }
        }

        return $oSeite_arr;
    }

    /**
     * @inheritdoc
     */
    public function getExtendedView($nDarstellung = 0): \stdClass
    {
        if (!isset($_SESSION['oErweiterteDarstellung'])) {
            $nStdDarstellung                                    = 0;
            $_SESSION['oErweiterteDarstellung']                 = new \stdClass();
            $_SESSION['oErweiterteDarstellung']->cURL_arr       = [];
            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;

            if ($this->productFilter->hasCategory()) {
                $category = new \Kategorie($this->productFilter->getCategory()->getValue());
                if (!empty($category->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG])) {
                    $nStdDarstellung = (int)$category->categoryFunctionAttributes[KAT_ATTRIBUT_DARSTELLUNG];
                }
            }
            if ($nDarstellung === 0
                && isset($this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'])
                && (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
            ) {
                $nStdDarstellung = (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
            }
            if ($nStdDarstellung > 0) {
                switch ($nStdDarstellung) {
                    case ERWDARSTELLUNG_ANSICHT_LISTE:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] !== 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_GALERIE:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_GALERIE;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] !== 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                        }
                        break;
                    case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_MOSAIK;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                        }
                        break;
                    default: // when given invalid option from wawi attribute
                        $nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                        if (isset($this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht']) &&
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'] > 0
                        ) { // fallback to configured default
                            $nDarstellung = (int)$this->conf['artikeluebersicht']['artikeluebersicht_erw_darstellung_stdansicht'];
                        }
                        $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
                        if (isset($_SESSION['ArtikelProSeite'])) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                        } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                            $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                                (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                        }
                        break;
                }
            } else {
                // Std ist Listendarstellung
                $_SESSION['oErweiterteDarstellung']->nDarstellung = ERWDARSTELLUNG_ANSICHT_LISTE;
                if (isset($_SESSION['ArtikelProSeite'])) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
                } elseif ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] !== 0) {
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                        (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                }
            }
        }
        if ($nDarstellung > 0) {
            $_SESSION['oErweiterteDarstellung']->nDarstellung = $nDarstellung;
            switch ($_SESSION['oErweiterteDarstellung']->nDarstellung) {
                case ERWDARSTELLUNG_ANSICHT_LISTE:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung1'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_MOSAIK:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung3'];
                    }
                    break;
                case ERWDARSTELLUNG_ANSICHT_GALERIE:
                default:
                    $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = ERWDARSTELLUNG_ANSICHT_ANZAHL_STD;
                    if ((int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'] > 0) {
                        $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel =
                            (int)$this->conf['artikeluebersicht']['artikeluebersicht_anzahl_darstellung2'];
                    }
                    break;
            }

            if (isset($_SESSION['ArtikelProSeite'])) {
                $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel = $_SESSION['ArtikelProSeite'];
            }
        }
        if (isset($_SESSION['oErweiterteDarstellung'])) {
            $naviURL = $this->productFilter->getFilterURL()->getURL();
            $naviURL .= strpos($naviURL, '?') === false ? '?ed=' : '&amp;ed=';

            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_LISTE]   = $naviURL .
                ERWDARSTELLUNG_ANSICHT_LISTE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_GALERIE] = $naviURL .
                ERWDARSTELLUNG_ANSICHT_GALERIE;
            $_SESSION['oErweiterteDarstellung']->cURL_arr[ERWDARSTELLUNG_ANSICHT_MOSAIK]  = $naviURL .
                ERWDARSTELLUNG_ANSICHT_MOSAIK;
        }

        return $_SESSION['oErweiterteDarstellung'];
    }

    /**
     * @inheritdoc
     */
    public function getSortingOptions($bExtendedJTLSearch = false): array
    {
        $sortingOptions = [];
        $search         = [];
        if ($bExtendedJTLSearch !== false) {
            static $names = [
                'suche_sortierprio_name',
                'suche_sortierprio_name_ab',
                'suche_sortierprio_preis',
                'suche_sortierprio_preis_ab'
            ];
            static $values = [
                SEARCH_SORT_NAME_ASC,
                SEARCH_SORT_NAME_DESC,
                SEARCH_SORT_PRICE_ASC,
                SEARCH_SORT_PRICE_DESC
            ];
            static $languages = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
            foreach ($names as $i => $name) {
                $obj                  = new \stdClass();
                $obj->name            = $name;
                $obj->value           = $values[$i];
                $obj->angezeigterName = \Shop::Lang()->get($languages[$i]);

                $sortingOptions[] = $obj;
            }

            return $sortingOptions;
        }
        while (($obj = $this->getNextSearchPriority($search)) !== null) {
            $search[] = $obj->name;
            unset($obj->name);
            $sortingOptions[] = $obj;
        }

        return $sortingOptions;
    }

    /**
     * @inheritdoc
     */
    public function getNextSearchPriority(array $search)
    {
        $max = 0;
        $obj = null;
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_name']
            && !in_array('suche_sortierprio_name', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_name';
            $obj->value           = SEARCH_SORT_NAME_ASC;
            $obj->angezeigterName = \Shop::Lang()->get('sortNameAsc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_name_ab']
            && !in_array('suche_sortierprio_name_ab', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_name_ab';
            $obj->value           = SEARCH_SORT_NAME_DESC;
            $obj->angezeigterName = \Shop::Lang()->get('sortNameDesc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_name_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis']
            && !in_array('suche_sortierprio_preis', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_preis';
            $obj->value           = SEARCH_SORT_PRICE_ASC;
            $obj->angezeigterName = \Shop::Lang()->get('sortPriceAsc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab']
            && !in_array('suche_sortierprio_preis_ab', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_preis_ab';
            $obj->value           = SEARCH_SORT_PRICE_DESC;
            $obj->angezeigterName = \Shop::Lang()->get('sortPriceDesc');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_preis_ab'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_ean']
            && !in_array('suche_sortierprio_ean', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_ean';
            $obj->value           = SEARCH_SORT_EAN;
            $obj->angezeigterName = \Shop::Lang()->get('sortEan');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_ean'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum']
            && !in_array('suche_sortierprio_erstelldatum', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_erstelldatum';
            $obj->value           = SEARCH_SORT_NEWEST_FIRST;
            $obj->angezeigterName = \Shop::Lang()->get('sortNewestFirst');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erstelldatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer']
            && !in_array('suche_sortierprio_artikelnummer', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_artikelnummer';
            $obj->value           = SEARCH_SORT_PRODUCTNO;
            $obj->angezeigterName = \Shop::Lang()->get('sortProductno');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_artikelnummer'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand']
            && !in_array('suche_sortierprio_lagerbestand', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_lagerbestand';
            $obj->value           = SEARCH_SORT_AVAILABILITY;
            $obj->angezeigterName = \Shop::Lang()->get('sortAvailability');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_lagerbestand'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_gewicht']
            && !in_array('suche_sortierprio_gewicht', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_gewicht';
            $obj->value           = SEARCH_SORT_WEIGHT;
            $obj->angezeigterName = \Shop::Lang()->get('sortWeight');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_gewicht'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum']
            && !in_array('suche_sortierprio_erscheinungsdatum', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_erscheinungsdatum';
            $obj->value           = SEARCH_SORT_DATEOFISSUE;
            $obj->angezeigterName = \Shop::Lang()->get('sortDateofissue');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_erscheinungsdatum'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bestseller']
            && !in_array('suche_sortierprio_bestseller', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_bestseller';
            $obj->value           = SEARCH_SORT_BESTSELLER;
            $obj->angezeigterName = \Shop::Lang()->get('bestseller');
            $max                  = $this->conf['artikeluebersicht']['suche_sortierprio_bestseller'];
        }
        if ($max < $this->conf['artikeluebersicht']['suche_sortierprio_bewertung']
            && !in_array('suche_sortierprio_bewertung', $search, true)
        ) {
            $obj                  = new \stdClass();
            $obj->name            = 'suche_sortierprio_bewertung';
            $obj->value           = SEARCH_SORT_RATING;
            $obj->angezeigterName = \Shop::Lang()->get('rating');
        }

        return $obj;
    }

    /**
     * @inheritdoc
     */
    public function setUserSort($currentCategory = null): MetadataInterface
    {
        $gpcSort = verifyGPCDataInteger('Sortierung');
        // Der User möchte die Standardsortierung wiederherstellen
        if ($gpcSort === 100) {
            unset($_SESSION['Usersortierung'], $_SESSION['nUsersortierungWahl'], $_SESSION['UsersortierungVorSuche']);
        }
        // Wenn noch keine Sortierung gewählt wurde => setze Standard-Sortierung aus Option
        if (!isset($_SESSION['Usersortierung'])) {
            unset($_SESSION['nUsersortierungWahl']);
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        if (!isset($_SESSION['nUsersortierungWahl'])) {
            $_SESSION['Usersortierung'] = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        }
        // Eine Suche wurde ausgeführt und die Suche wird auf die Suchtreffersuche eingestellt
        if (!isset($_SESSION['nUsersortierungWahl']) && $this->productFilter->getSearch()->getSearchCacheID() > 0) {
            // nur bei initialsuche Sortierung zurücksetzen
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['Usersortierung']         = SEARCH_SORT_STANDARD;
        }
        // Kategorie Funktionsattribut
        if (!empty($currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG])) {
            $_SESSION['Usersortierung'] = static::mapUserSorting(
                $currentCategory->categoryFunctionAttributes[KAT_ATTRIBUT_ARTIKELSORTIERUNG]
            );
        }
        // Wurde zuvor etwas gesucht? Dann die Einstellung des Users vor der Suche wiederherstellen
        if (isset($_SESSION['UsersortierungVorSuche']) && (int)$_SESSION['UsersortierungVorSuche'] > 0) {
            $_SESSION['Usersortierung'] = (int)$_SESSION['UsersortierungVorSuche'];
        }
        // Suchspecial sortierung
        if ($this->productFilter->hasSearchSpecial()) {
            // Gibt die Suchspecials als Assoc Array zurück, wobei die Keys des Arrays der kKey vom Suchspecial sind.
            $oSuchspecialEinstellung_arr = self::getSearchSpecialConfigMapping($this->conf['suchspecials']);
            // -1 = Keine spezielle Sortierung
            $idx    = $this->productFilter->getSearchSpecial()->getValue();
            $ssConf = isset($oSuchspecialEinstellung_arr[$idx]) ?: null;
            if ($ssConf !== null && $ssConf !== -1 && count($oSuchspecialEinstellung_arr) > 0) {
                $_SESSION['Usersortierung'] = (int)$oSuchspecialEinstellung_arr[$idx];
            }
        }
        // Der User hat expliziet eine Sortierung eingestellt
        if ($gpcSort > 0 && $gpcSort !== 100) {
            $_SESSION['Usersortierung']         = $gpcSort;
            $_SESSION['UsersortierungVorSuche'] = $_SESSION['Usersortierung'];
            $_SESSION['nUsersortierungWahl']    = 1;
            setFsession(0, $_SESSION['Usersortierung'], 0);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function mapUserSorting($sort): int
    {
        // Ist die Usersortierung ein Integer => Return direkt den Integer
        preg_match('/\d+/', $sort, $cTreffer_arr);
        if (isset($cTreffer_arr[0]) && strlen($sort) === strlen($cTreffer_arr[0])) {
            return (int)$sort;
        }
        // Usersortierung ist ein String aus einem Kategorieattribut
        switch (strtolower($sort)) {
            case SEARCH_SORT_CRITERION_NAME:
            case SEARCH_SORT_CRITERION_NAME_ASC:
                return SEARCH_SORT_NAME_ASC;

            case SEARCH_SORT_CRITERION_NAME_DESC:
                return SEARCH_SORT_NAME_DESC;

            case SEARCH_SORT_CRITERION_PRODUCTNO:
                return SEARCH_SORT_PRODUCTNO;

            case SEARCH_SORT_CRITERION_AVAILABILITY:
                return SEARCH_SORT_AVAILABILITY;

            case SEARCH_SORT_CRITERION_WEIGHT:
                return SEARCH_SORT_WEIGHT;

            case SEARCH_SORT_CRITERION_PRICE_ASC:
            case SEARCH_SORT_CRITERION_PRICE:
                return SEARCH_SORT_PRICE_ASC;

            case SEARCH_SORT_CRITERION_PRICE_DESC:
                return SEARCH_SORT_PRICE_DESC;

            case SEARCH_SORT_CRITERION_EAN:
                return SEARCH_SORT_EAN;

            case SEARCH_SORT_CRITERION_NEWEST_FIRST:
                return SEARCH_SORT_NEWEST_FIRST;

            case SEARCH_SORT_CRITERION_DATEOFISSUE:
                return SEARCH_SORT_DATEOFISSUE;

            case SEARCH_SORT_CRITERION_BESTSELLER:
                return SEARCH_SORT_BESTSELLER;

            case SEARCH_SORT_CRITERION_RATING:
                return SEARCH_SORT_RATING;

            default:
                return SEARCH_SORT_STANDARD;
        }
    }

    /**
     * @inheritdoc
     */
    public function getProductsPerPageLimit(): int
    {
        if ($this->productFilter->getProductLimit() !== 0) {
            $limit = (int)$this->productFilter->getProductLimit();
        } elseif (isset($_SESSION['ArtikelProSeite']) && $_SESSION['ArtikelProSeite'] !== 0) {
            $limit = (int)$_SESSION['ArtikelProSeite'];
        } elseif (isset($_SESSION['oErweiterteDarstellung']->nAnzahlArtikel)
            && $_SESSION['oErweiterteDarstellung']->nAnzahlArtikel !== 0
        ) {
            $limit = (int)$_SESSION['oErweiterteDarstellung']->nAnzahlArtikel;
        } else {
            $limit = ($max = $this->conf['artikeluebersicht']['artikeluebersicht_artikelproseite']) !== 0
                ? (int)$max
                : 20;
        }

        return min($limit, ARTICLES_PER_PAGE_HARD_LIMIT);
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (property_exists($this, $name)) {
            return true;
        }
        $mapped = self::getMapping($name);
        if ($mapped === null) {
            return false;
        }
        $method = 'get' . $mapped;
        $result = $this->$method();

        return $result !== null;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = get_object_vars($this);
        $res['conf']          = '*truncated*';
        $res['productFilter'] = '*truncated*';

        return $res;
    }
}
