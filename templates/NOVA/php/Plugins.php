<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Nova;

use Illuminate\Support\Collection;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Filter\Config;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Category;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Tax;
use JTL\Link\Link;
use JTL\Link\LinkGroupInterface;
use JTL\Media\Image;
use JTL\Media\MediaImage;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Staat;

/**
 * Class Plugins
 * @package Nova
 */
class Plugins
{
    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return array|void
     */
    public function getProductList($params, $smarty)
    {
        $limit            = (int)($params['nLimit'] ?? 10);
        $sort             = (int)($params['nSortierung'] ?? 0);
        $cAssign          = (isset($params['cAssign']) && \strlen($params['cAssign']) > 0)
            ? $params['cAssign']
            : 'oCustomArtikel_arr';
        $attributeFilters = isset($params['cMerkmalFilter'])
            ? ProductFilter::initAttributeFilter(\explode(';', $params['cMerkmalFilter']))
            : [];
        $searchFilters    = isset($params['cSuchFilter'])
            ? ProductFilter::initSearchFilter(\explode(';', $params['cSuchFilter']))
            : [];
        $tagFilters       = isset($params['cTagFilter'])
            ? ProductFilter::initTagFilter(\explode(';', $params['cTagFilter']))
            : [];
        $params           = [
            'kKategorie'             => $params['kKategorie'] ?? null,
            'kHersteller'            => $params['kHersteller'] ?? null,
            'kArtikel'               => $params['kArtikel'] ?? null,
            'kVariKindArtikel'       => $params['kVariKindArtikel'] ?? null,
            'kSeite'                 => $params['kSeite'] ?? null,
            'kSuchanfrage'           => $params['kSuchanfrage'] ?? null,
            'kMerkmalWert'           => $params['kMerkmalWert'] ?? null,
            'kTag'                   => $params['kTag'] ?? null,
            'kSuchspecial'           => $params['kSuchspecial'] ?? null,
            'kKategorieFilter'       => $params['kKategorieFilter'] ?? null,
            'kHerstellerFilter'      => $params['kHerstellerFilter'] ?? null,
            'nBewertungSterneFilter' => $params['nBewertungSterneFilter'] ?? null,
            'cPreisspannenFilter'    => $params['cPreisspannenFilter'] ?? '',
            'kSuchspecialFilter'     => $params['kSuchspecialFilter'] ?? null,
            'nSortierung'            => $sort,
            'MerkmalFilter_arr'      => $attributeFilters,
            'TagFilter_arr'          => $tagFilters,
            'SuchFilter_arr'         => $searchFilters,
            'nArtikelProSeite'       => $params['nArtikelProSeite'] ?? null,
            'cSuche'                 => $params['cSuche'] ?? null,
            'seite'                  => $params['seite'] ?? null
        ];
        if ($params['kArtikel'] !== null) {
            $products = [];
            if (!\is_array($params['kArtikel'])) {
                $params['kArtikel'] = [$params['kArtikel']];
            }
            foreach ($params['kArtikel'] as $kArtikel) {
                $product    = new Artikel();
                $products[] = $product->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
            }
        } else {
            $products = (new ProductFilter(
                Config::getDefault(),
                Shop::Container()->getDB(),
                Shop::Container()->getCache()
            ))
                ->initStates($params)
                ->generateSearchResults(null, true, $limit)
                ->getProducts()
                ->all();
        }

        $smarty->assign($cAssign, $products);

        if (isset($params['bReturn'])) {
            return $products;
        }
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return bool|string
     */
    public function getStaticRoute($params, $smarty)
    {
        if (isset($params['id'])) {
            $full   = !isset($params['full']) || $params['full'] === true;
            $secure = isset($params['secure']) && $params['secure'] === true;
            $url    = Shop::Container()->getLinkService()->getStaticRoute($params['id'], $full, $secure);
            $qp     = isset($params['params'])
                ? (array)$params['params']
                : [];

            if (\count($qp) > 0) {
                $url = $url . (\parse_url($url, \PHP_URL_QUERY) ? '&' : '?') . \http_build_query($qp, '', '&');
            }
            if (isset($params['assign'])) {
                $smarty->assign($params['assign'], $url);
            } else {
                return $url;
            }
        }

        return false;
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return array|void
     */
    public function getManufacturers($params, $smarty)
    {
        $manufacturers = Manufacturer::getInstance()->getManufacturers();
        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $manufacturers);

            return;
        }

        return $manufacturers;
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return array|void
     */
    public function getBoxesByPosition($params, $smarty)
    {
        if (isset($params['position'])) {
            $data  = Shop::Container()->getBoxService()->boxes;
            $boxes = $data[$params['position']] ?? [];
            if (isset($params['assign'])) {
                $smarty->assign($params['assign'], $boxes);
            } else {
                return $boxes;
            }
        }
    }

    /**
     * @param array                         $params - categoryId mainCategoryId. 0 for first level categories
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return array|void
     */
    public function getCategoryArray($params, $smarty)
    {
        $id = isset($params['categoryId']) ? (int)$params['categoryId'] : 0;
        if ($id === 0) {
            $categories = Category::getInstance();
            $list       = $categories->combinedGetAll();
        } else {
            $categories = new KategorieListe();
            $list       = $categories->getAllCategoriesOnLevel($id);
        }

        if (isset($params['categoryBoxNumber']) && (int)$params['categoryBoxNumber'] > 0) {
            $list2 = [];
            foreach ($list as $key => $oList) {
                if (isset($oList->categoryFunctionAttributes[\KAT_ATTRIBUT_KATEGORIEBOX])
                    && $oList->categoryFunctionAttributes[\KAT_ATTRIBUT_KATEGORIEBOX] == $params['categoryBoxNumber']
                ) {
                    $list2[$key] = $oList;
                }
            }
            $list = $list2;
        }

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $list);

            return;
        }

        return $list;
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return array|void
     */
    public function getCategoryParents($params, $smarty)
    {
        $id         = isset($params['categoryId']) ? (int)$params['categoryId'] : 0;
        $categories = new KategorieListe();
        $list       = $categories->getOpenCategories(new Kategorie($id));

        \array_shift($list);
        $list = \array_reverse($list);

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $list);

            return;
        }

        return $list;
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return string
     */
    public function getImgTag($params, $smarty): string
    {
        if (empty($params['src'])) {
            return '';
        }
        $size = $this->getImageSize($params['src']);

        $url   = $params['src'];
        $id    = isset($params['id']) ? ' id="' . $params['id'] . '"' : '';
        $alt   = isset($params['alt']) ? ' alt="' . \truncate($params['alt'], 75) . '"' : '';
        $title = isset($params['title']) ? ' title="' . \truncate($params['title'], 75) . '"' : '';
        $class = isset($params['class']) ? ' class="' . \truncate($params['class'], 75) . '"' : '';
        if (\strpos($url, 'http') !== 0) {
            $url = Shop::getImageBaseURL() . \ltrim($url, '/');
        }
        if ($size !== null && $size->size->width > 0 && $size->size->height > 0) {
            return '<img src="' . $url . '" width="' . $size->size->width . '" height="' .
                $size->size->height . '"' . $id . $alt . $title . $class . ' />';
        }

        return '<img src="' . $url . '"' . $id . $alt . $title . $class . ' />';
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     */
    public function hasBoxes($params, $smarty): void
    {
        $boxData = $smarty->getTemplateVars('boxes');
        $smarty->assign($params['assign'], !empty($boxData[$params['position']]));
    }

    /**
     * @param string $text
     * @param int    $numb
     * @return string
     */
    public function truncate($text, $numb)
    {
        if (\strlen($text) > $numb) {
            $text = \substr($text, 0, $numb);
            $text = \substr($text, 0, \strrpos($text, ' '));
            $text .= '...';
        }

        return $text;
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return mixed|string
     */
    public function getLocalizedPrice($params, $smarty)
    {
        $oAufpreis                     = new \stdClass();
        $oAufpreis->cAufpreisLocalized = '';
        $oAufpreis->cPreisInklAufpreis = '';

        if ((float)$params['fAufpreisNetto'] != 0) {
            $fAufpreisNetto         = (float)$params['fAufpreisNetto'];
            $fVKNetto               = (float)$params['fVKNetto'];
            $kSteuerklasse          = (int)$params['kSteuerklasse'];
            $fVPEWert               = (float)$params['fVPEWert'];
            $cVPEEinheit            = $params['cVPEEinheit'];
            $FunktionsAttribute_arr = $params['FunktionsAttribute'];
            $nGenauigkeit           = (isset($FunktionsAttribute_arr[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT])
                && (int)$FunktionsAttribute_arr[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT] > 0)
                ? (int)$FunktionsAttribute_arr[\FKT_ATTRIBUT_GRUNDPREISGENAUIGKEIT]
                : 2;

            if ((int)$params['nNettoPreise'] === 1) {
                $oAufpreis->cAufpreisLocalized = Preise::getLocalizedPriceString($fAufpreisNetto);
                $oAufpreis->cPreisInklAufpreis = Preise::getLocalizedPriceString($fAufpreisNetto + $fVKNetto);
                $oAufpreis->cAufpreisLocalized = ($fAufpreisNetto > 0)
                    ? ('+ ' . $oAufpreis->cAufpreisLocalized)
                    : \str_replace('-', '- ', $oAufpreis->cAufpreisLocalized);

                if ($fVPEWert > 0) {
                    $oAufpreis->cPreisVPEWertAufpreis     = Preise::getLocalizedPriceString(
                        $fAufpreisNetto / $fVPEWert,
                        Frontend::getCurrency()->getCode(),
                        true,
                        $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer') . ' ' . $cVPEEinheit;
                    $oAufpreis->cPreisVPEWertInklAufpreis = Preise::getLocalizedPriceString(
                        ($fAufpreisNetto + $fVKNetto) / $fVPEWert,
                        Frontend::getCurrency()->getCode(),
                        true,
                        $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer') . ' ' . $cVPEEinheit;

                    $oAufpreis->cAufpreisLocalized = $oAufpreis->cAufpreisLocalized . ', ' .
                        $oAufpreis->cPreisVPEWertAufpreis;
                    $oAufpreis->cPreisInklAufpreis = $oAufpreis->cPreisInklAufpreis . ', ' .
                        $oAufpreis->cPreisVPEWertInklAufpreis;
                }
            } else {
                $oAufpreis->cAufpreisLocalized = Preise::getLocalizedPriceString(
                    Tax::getGross($fAufpreisNetto, $_SESSION['Steuersatz'][$kSteuerklasse], 4)
                );
                $oAufpreis->cPreisInklAufpreis = Preise::getLocalizedPriceString(
                    Tax::getGross($fAufpreisNetto + $fVKNetto, $_SESSION['Steuersatz'][$kSteuerklasse], 4)
                );
                $oAufpreis->cAufpreisLocalized = ($fAufpreisNetto > 0)
                    ? ('+ ' . $oAufpreis->cAufpreisLocalized)
                    : \str_replace('-', '- ', $oAufpreis->cAufpreisLocalized);

                if ($fVPEWert > 0) {
                    $oAufpreis->cPreisVPEWertAufpreis     = Preise::getLocalizedPriceString(
                        Tax::getGross($fAufpreisNetto / $fVPEWert, $_SESSION['Steuersatz'][$kSteuerklasse]),
                        Frontend::getCurrency()->getCode(),
                        true,
                        $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer') . ' ' . $cVPEEinheit;
                    $oAufpreis->cPreisVPEWertInklAufpreis = Preise::getLocalizedPriceString(
                        Tax::getGross(
                            ($fAufpreisNetto + $fVKNetto) / $fVPEWert,
                            $_SESSION['Steuersatz'][$kSteuerklasse]
                        ),
                        Frontend::getCurrency()->getCode(),
                        true,
                        $nGenauigkeit
                    ) . ' ' . Shop::Lang()->get('vpePer') . ' ' . $cVPEEinheit;

                    $oAufpreis->cAufpreisLocalized = $oAufpreis->cAufpreisLocalized .
                        ', ' . $oAufpreis->cPreisVPEWertAufpreis;
                    $oAufpreis->cPreisInklAufpreis = $oAufpreis->cPreisInklAufpreis .
                        ', ' . $oAufpreis->cPreisVPEWertInklAufpreis;
                }
            }
        }

        return (isset($params['bAufpreise']) && (int)$params['bAufpreise'] > 0)
            ? $oAufpreis->cAufpreisLocalized
            : $oAufpreis->cPreisInklAufpreis;
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     */
    public function hasCheckBoxForLocation($params, $smarty): void
    {
        $smarty->assign(
            $params['bReturn'],
            \count((new CheckBox())->getCheckBoxFrontend((int)$params['nAnzeigeOrt'], 0, true, true)) > 0
        );
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     */
    public function getCheckBoxForLocation($params, $smarty): void
    {
        $langID     = Shop::getLanguageID();
        $cid        = 'cb_' . (int)$params['nAnzeigeOrt'] . '_' . $langID;
        $checkBoxes = Shop::has($cid)
            ? Shop::get($cid)
            : (new CheckBox())->getCheckBoxFrontend((int)$params['nAnzeigeOrt'], 0, true, true);
        if (\count($checkBoxes) > 0) {
            foreach ($checkBoxes as $checkBox) {
                $url                     = $checkBox->kLink > 0
                    ? $checkBox->getLink()->getURL()
                    : '';
                $error                   = isset($params['cPlausi_arr'][$checkBox->cID]);
                $checkBox->isActive      = isset($params['cPost_arr'][$checkBox->cID]);
                $checkBox->cName         = $checkBox->oCheckBoxSprache_arr[$langID]->cText ?? '';
                $checkBox->cLinkURL      = $url;
                $checkBox->cLinkURLFull  = $url;
                $checkBox->cBeschreibung = !empty($checkBox->oCheckBoxSprache_arr[$langID]->cBeschreibung)
                    ? $checkBox->oCheckBoxSprache_arr[$langID]->cBeschreibung
                    : '';
                $checkBox->cErrormsg     = $error
                    ? Shop::Lang()->get('pleasyAccept', 'account data')
                    : '';
            }
            Shop::set($cid, $checkBoxes);
            if (isset($params['assign'])) {
                $smarty->assign($params['assign'], $checkBoxes);
            }
        }
    }

    /**
     * @param array $params
     * @return string
     */
    public function aaURLEncode($params): string
    {
        $reset  = (isset($params['nReset']) && (int)$params['nReset'] === 1);
        $url    = $_SERVER['REQUEST_URI'];
        $params = ['&aaParams', '?aaParams', '&aaReset', '?aaReset'];
        $exists = false;
        foreach ($params as $param) {
            $exists = \strpos($url, $param);
            if ($exists !== false) {
                $url = \substr($url, 0, $exists);
                break;
            }
            $exists = false;
        }
        if ($exists !== false) {
            $url = \substr($url, 0, $exists);
        }
        if (isset($params['bUrlOnly']) && (int)$params['bUrlOnly'] === 1) {
            return $url;
        }
        $paramString = '';
        unset($params['nReset']);
        if (\is_array($params) && \count($params) > 0) {
            foreach ($params as $key => $param) {
                $paramString .= $key . '=' . $param . ';';
            }
        }

        $sep = (\strpos($url, '?') === false) ? '?' : '&';

        return $url . $sep . ($reset ? 'aaReset=' : 'aaParams=') . \base64_encode($paramString);
    }

    /**
     * @param array                         $params - ['type'] Templatename of link, ['assign'] array name to assign
     * @param \Smarty_Internal_TemplateBase $smarty
     */
    public function getNavigation($params, $smarty): void
    {
        if (!isset($params['assign'])) {
            return;
        }
        $identifier = $params['linkgroupIdentifier'];
        $linkGroup  = null;
        if (\strlen($identifier) > 0) {
            $linkGroups = Shop::Container()->getLinkService()->getVisibleLinkGroups();
            $linkGroup  = $linkGroups->getLinkgroupByTemplate($identifier);
        }
        if ($linkGroup !== null && $linkGroup->isAvailableInLanguage(Shop::getLanguageID())) {
            $smarty->assign($params['assign'], $this->buildNavigationSubs($linkGroup));
        }
    }

    /**
     * @param LinkGroupInterface $linkGroup
     * @param int                $parentID
     * @return Collection
     */
    public function buildNavigationSubs(LinkGroupInterface $linkGroup, $parentID = 0): Collection
    {
        $parentID = (int)$parentID;
        $links    = new Collection();
        if ($linkGroup->getTemplate() === 'hidden' || $linkGroup->getName() === 'hidden') {
            return $links;
        }
        foreach ($linkGroup->getLinks() as $link) {
            /** @var Link $link */
            if ($link->getParent() !== $parentID) {
                continue;
            }
            $link->setChildLinks($this->buildNavigationSubs($linkGroup, $link->getID()));
            $link->setIsActive($link->getIsActive() || (Shop::$kLink > 0 && Shop::$kLink === $link->getID()));
            $links->push($link);
        }

        return $links;
    }

    /**
     * @param array $params
     * @return string|object|null
     */
    public function prepareImageDetails($params)
    {
        if (!isset($params['item'])) {
            return null;
        }

        $result = [
            'xs' => $this->getImageSize($params['item']->cPfadMini),
            'sm' => $this->getImageSize($params['item']->cPfadKlein),
            'md' => $this->getImageSize($params['item']->cPfadNormal),
            'lg' => $this->getImageSize($params['item']->cPfadGross)
        ];

        if (isset($params['type'])) {
            $type = $params['type'];
            if (isset($result[$type])) {
                $result = $result[$type];
            }
        }
        $imageBaseURL = Shop::getImageBaseURL();
        foreach ($result as $size => $data) {
            if (isset($data->src) && \strpos($data->src, 'http') !== 0) {
                $data->src = $imageBaseURL . $data->src;
            }
        }
        $result = (object)$result;

        return (isset($params['json']) && $params['json'])
            ? \json_encode($result, \JSON_FORCE_OBJECT)
            : $result;
    }

    /**
     * @param string $image
     * @return object|null
     */
    public function getImageSize($image)
    {
        $path = \strpos($image, \PFAD_BILDER) === 0
            ? PFAD_ROOT . $image
            : $image;
        if (\file_exists($path)) {
            [$width, $height, $type] = \getimagesize($path);
        } else {
            $req = MediaImage::toRequest($path);

            if (!\is_object($req)) {
                return null;
            }

            $settings = Image::getSettings();
            $refImage = $req->getRaw();
            if ($refImage === null) {
                return null;
            }

            [$width, $height, $type] = \getimagesize($refImage);

            $size       = $settings['size'][$req->getSizeType()];
            $max_width  = $size['width'];
            $max_height = $size['height'];
            $old_width  = $width;
            $old_height = $height;

            $scale  = \min($max_width / $old_width, $max_height / $old_height);
            $width  = \ceil($scale * $old_width);
            $height = \ceil($scale * $old_height);
        }

        return (object)[
            'src'  => $image,
            'size' => (object)[
                'width'  => $width,
                'height' => $height
            ],
            'type' => $type
        ];
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return mixed
     */
    public function getCMSContent($params, $smarty)
    {
        if (isset($params['kLink']) && (int)$params['kLink'] > 0) {
            $linkID   = (int)$params['kLink'];
            $link   = Shop::Container()->getLinkService()->getLinkByID($linkID);
            $content = $link !== null ? $link->getContent() : null;
            if (isset($params['assign'])) {
                $smarty->assign($params['assign'], $content);
            } else {
                return $content;
            }
        }

        return null;
    }

    /**
     * @param array                         $params - variationen, maxVariationCount, maxWerteCount
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return int|null|bool
     * 0: no listable variations
     * 1: normal listable variations
     * 2: only child listable variations
     */
    public function hasOnlyListableVariations($params, $smarty)
    {
        if (!isset($params['artikel']->Variationen)) {
            if (isset($params['assign'])) {
                $smarty->assign($params['assign'], 0);

                return null;
            }

            return 0;
        }

        $maxVariationCount = isset($params['maxVariationCount']) ? (int)$params['maxVariationCount'] : 1;
        $maxWerteCount     = isset($params['maxWerteCount']) ? (int)$params['maxWerteCount'] : 3;
        $variationCheck    = function ($Variationen, $maxVariationCount, $maxWerteCount) {
            $result   = true;
            $varCount = \is_array($Variationen) ? \count($Variationen) : 0;

            if ($varCount > 0 && $varCount <= $maxVariationCount) {
                foreach ($Variationen as $oVariation) {
                    if ($oVariation->cTyp !== 'SELECTBOX'
                        && (!\in_array($oVariation->cTyp, ['TEXTSWATCHES', 'IMGSWATCHES', 'RADIO'], true)
                            || \count($oVariation->Werte) > $maxWerteCount)) {
                        $result = false;
                        break;
                    }
                }
            } else {
                $result = false;
            }

            return $result;
        };

        $result = $variationCheck($params['artikel']->Variationen, $maxVariationCount, $maxWerteCount) ? 1 : 0;
        if ($result === 0 && $params['artikel']->kVaterArtikel > 0) {
            // Hat das Kind evtl. mehr Variationen als der Vater?
            $result = $variationCheck($params['artikel']->oVariationenNurKind_arr, $maxVariationCount, $maxWerteCount)
                ? 2
                : 0;
        }

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $result);

            return null;
        }

        return $result;
    }

    /**
     * Input: ['ger' => 'Titel', 'eng' => 'Title']
     *
     * @param string|array $mixed
     * @param string|null  $to - locale
     * @return null|string
     */
    public function getTranslation($mixed, $to = null): ?string
    {
        $to = $to ?: Shop::getLanguageCode();

        if ($this->hasTranslation($mixed, $to)) {
            return \is_string($mixed) ? $mixed : $mixed[$to];
        }

        return null;
    }

    /**
     * Has any translation
     *
     * @param string|array $mixed
     * @param string|null  $to - locale
     * @return bool
     */
    public function hasTranslation($mixed, $to = null): bool
    {
        $to = $to ?: Shop::getLanguageCode();

        return \is_string($mixed) ?: isset($mixed[$to]);
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return string
     */
    public function captchaMarkup($params, $smarty): string
    {
        if (isset($params['getBody']) && $params['getBody']) {
            return Shop::Container()->getCaptchaService()->getBodyMarkup($smarty);
        }

        return Shop::Container()->getCaptchaService()->getHeadMarkup($smarty);
    }

    /**
     * @param array                         $params
     * @param \Smarty_Internal_TemplateBase $smarty
     * @return array|null|void
     */
    public function getStates($params, $smarty)
    {
        $regions = Staat::getRegions($params['cIso']);
        if (!isset($params['assign'])) {
            return $regions;
        }
        $smarty->assign($params['assign'], $regions);
    }

    /**
     * @param $params
     * @return int
     */
    public function getDecimalLength($params): int
    {
        return \strlen(\substr(\strrchr(\str_replace(',', '.', $params['quantity']), '.'), 1));
    }
}
