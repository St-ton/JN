<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog;

use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Merkmal;
use JTL\DB\ReturnType;
use JTL\Extensions\Konfigitem;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;
use function Functional\some;
use function Functional\map;
use function Functional\sort;

/**
 * Class Vergleichsliste
 * @package JTL\Catalog
 */
class Vergleichsliste
{
    /**
     * @var array
     */
    public $oArtikel_arr = [];

    /**
     * Vergleichsliste constructor.
     * @param int   $productID
     * @param array $variations
     */
    public function __construct(int $productID = 0, array $variations = [])
    {
        if ($productID > 0) {
            $product           = new stdClass();
            $tmpProduct        = (new Artikel())->fuelleArtikel($productID);
            $product->kArtikel = $productID;
            $product->cName    = $tmpProduct !== null ? $tmpProduct->cName : '';
            $product->cURLFull = $tmpProduct !== null ? $tmpProduct->cURLFull : '';
            if (\is_array($variations) && \count($variations) > 0) {
                $product->Variationen = $variations;
            }
            $this->oArtikel_arr[] = $product;

            \executeHook(\HOOK_VERGLEICHSLISTE_CLASS_EINFUEGEN);
        } elseif (isset($_SESSION['Vergleichsliste'])) {
            $this->loadFromSession();
        }
    }

    /**
     * load comparelist from session
     */
    public function loadFromSession(): void
    {
        $compareList = Frontend::get('Vergleichsliste');
        if ($compareList !== null) {
            $defaultOptions = Artikel::getDefaultOptions();
            $linkHelper     = Shop::Container()->getLinkService();
            $baseURL        = $linkHelper->getStaticRoute('vergleichsliste.php');
            foreach ($compareList->oArtikel_arr as $item) {
                $product = new Artikel();
                $product->fuelleArtikel($item->kArtikel, $defaultOptions);
                $product->cURLDEL = $baseURL . '?vlplo=' . $item->kArtikel;
                if (isset($item->oVariationen_arr) && \count($item->oVariationen_arr) > 0) {
                    $product->Variationen = $item->oVariationen_arr;
                }
                $this->oArtikel_arr[] = $product;
            }
        }
    }

    /**
     * @return $this
     */
    public function umgebungsWechsel(): self
    {
        foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $item) {
            $tmpProduct                                    = new stdClass();
            $tmpProduct->kArtikel                          = $item->kArtikel;
            $_SESSION['Vergleichsliste']->oArtikel_arr[$i] = $tmpProduct;
        }

        return $this;
    }

    /**
     * @param int  $productID
     * @param bool $saveToSession
     * @param int  $configItemID
     * @return $this
     */
    public function fuegeEin(int $productID, bool $saveToSession = true, int $configItemID = 0): self
    {
        // Existiert der Key und ist er noch nicht vorhanden?
        if ($productID > 0 && !$this->artikelVorhanden($productID)) {
            //new slim variant for compare list
            $product           = new Artikel();
            $product->kArtikel = $productID;
            if ($configItemID > 0) {
                // Falls Konfigitem gesetzt Preise + Name überschreiben
                $configItem = new Konfigitem($configItemID);
                if ($configItem->getKonfigitem() > 0) {
                    $product->Preise->cVKLocalized[0] = $configItem->getPreisLocalized(true, false);
                    $product->Preise->cVKLocalized[1] = $configItem->getPreisLocalized(true, false, true);
                    $product->kSteuerklasse           = $configItem->getSteuerklasse();
                    unset($product->cLocalizedVPE);

                    if ($configItem->getUseOwnName()) {
                        $product->cName             = $configItem->getName();
                        $product->cBeschreibung     = $configItem->getBeschreibung();
                        $product->cKurzBeschreibung = $configItem->getBeschreibung();
                    }
                }
            }
            if ($product->kArtikel > 0) {
                $this->oArtikel_arr[] = $product;
            }
            if ($saveToSession) {
                $_SESSION['Vergleichsliste']->oArtikel_arr = $this->oArtikel_arr;
            }
        }

        return $this;
    }

    /**
     * @param int $productID
     * @return bool
     */
    public function artikelVorhanden(int $productID): bool
    {
        return some($this->oArtikel_arr, function ($e) use ($productID) {
            return (int)$e->kArtikel === $productID;
        });
    }

    /**
     * @param Vergleichsliste $compareList
     * @return array
     * @former baueMerkmalundVariation()
     * @since 5.0.0
     */
    public static function buildAttributeAndVariation(Vergleichsliste $compareList): array
    {
        $attributes = [];
        $variations = [];
        foreach ($compareList->oArtikel_arr as $product) {
            /** @var Artikel $product */
            if (\count($product->oMerkmale_arr) > 0) {
                // Falls das Merkmal Array nicht leer ist
                if (\count($attributes) > 0) {
                    foreach ($product->oMerkmale_arr as $oMerkmale) {
                        if (!self::containsAttribute($attributes, $oMerkmale->kMerkmal)) {
                            $attributes[] = $oMerkmale;
                        }
                    }
                } else {
                    $attributes = $product->oMerkmale_arr;
                }
            }
            // Falls ein Artikel min. eine Variation enthält
            if (\count($product->Variationen) > 0) {
                if (\count($variations) > 0) {
                    foreach ($product->Variationen as $oVariationen) {
                        if (!self::containsVariation($variations, $oVariationen->cName)) {
                            $variations[] = $oVariationen;
                        }
                    }
                } else {
                    $variations = $product->Variationen;
                }
            }
        }
        if (\count($attributes) > 0) {
            \uasort($attributes, function (Merkmal $a, Merkmal $b) {
                return $a->nSort > $b->nSort;
            });
        }

        return [$attributes, $variations];
    }

    /**
     * @param array $attributes
     * @param int   $id
     * @return bool
     * @former istMerkmalEnthalten()
     * @since 5.0.0
     */
    public static function containsAttribute(array $attributes, int $id): bool
    {
        return some($attributes, function ($e) use ($id) {
            return (int)$e->kMerkmal === $id;
        });
    }

    /**
     * @param array  $variations
     * @param string $name
     * @return bool
     * @former istVariationEnthalten()
     * @since 5.0.0
     */
    public static function containsVariation(array $variations, string $name): bool
    {
        return some($variations, function ($e) use ($name) {
            return $e->cName === $name;
        });
    }

    /**
     * @param array $exclude
     * @param array $config
     * @return string
     * @since 5.0.0
     */
    public static function gibMaxPrioSpalteV(array $exclude, array $config): string
    {
        $max  = 0;
        $col  = '';
        $conf = $config['vergleichsliste'];
        if ($conf['vergleichsliste_artikelnummer'] > $max && !\in_array('cArtNr', $exclude, true)) {
            $max = $conf['vergleichsliste_artikelnummer'];
            $col = 'cArtNr';
        }
        if ($conf['vergleichsliste_hersteller'] > $max && !\in_array('cHersteller', $exclude, true)) {
            $max = $conf['vergleichsliste_hersteller'];
            $col = 'cHersteller';
        }
        if ($conf['vergleichsliste_beschreibung'] > $max && !\in_array('cBeschreibung', $exclude, true)) {
            $max = $conf['vergleichsliste_beschreibung'];
            $col = 'cBeschreibung';
        }
        if ($conf['vergleichsliste_kurzbeschreibung'] > $max && !\in_array('cKurzBeschreibung', $exclude, true)) {
            $max = $conf['vergleichsliste_kurzbeschreibung'];
            $col = 'cKurzBeschreibung';
        }
        if ($conf['vergleichsliste_artikelgewicht'] > $max && !\in_array('fArtikelgewicht', $exclude, true)) {
            $max = $conf['vergleichsliste_artikelgewicht'];
            $col = 'fArtikelgewicht';
        }
        if ($conf['vergleichsliste_versandgewicht'] > $max && !\in_array('fGewicht', $exclude, true)) {
            $max = $conf['vergleichsliste_versandgewicht'];
            $col = 'fGewicht';
        }
        if ($conf['vergleichsliste_merkmale'] > $max && !\in_array('Merkmale', $exclude, true)) {
            $max = $conf['vergleichsliste_merkmale'];
            $col = 'Merkmale';
        }
        if ($conf['vergleichsliste_variationen'] > $max && !\in_array('Variationen', $exclude, true)) {
            $col = 'Variationen';
        }

        return $col;
    }

    /**
     * @param bool $keysOnly
     * @param bool $newStandard
     * @return array
     */
    public static function getPrioRows(bool $keysOnly = false, bool $newStandard = true): array
    {
        $conf               = Shop::getSettings([\CONF_VERGLEICHSLISTE]);
        $possibleRowsToView = [
            'vergleichsliste_artikelnummer',
            'vergleichsliste_hersteller',
            'vergleichsliste_beschreibung',
            'vergleichsliste_kurzbeschreibung',
            'vergleichsliste_artikelgewicht',
            'vergleichsliste_versandgewicht',
            'vergleichsliste_merkmale',
            'vergleichsliste_variationen'
        ];
        if ($newStandard) {
            $possibleRowsToView[] = 'vergleichsliste_verfuegbarkeit';
            $possibleRowsToView[] = 'vergleichsliste_lieferzeit';
        }
        $prioRows  = [];
        $ignoreRow = 0;
        foreach ($possibleRowsToView as $row) {
            if ($conf['vergleichsliste'][$row] > $ignoreRow) {
                $prioRows[$row] = self::getMappedRowNames($row);
            }
        }
        $prioRows = sort($prioRows, function (array $left, array $right) {
            return $left['priority'] < $right['priority'];
        });

        return $keysOnly ? map($prioRows, function (array $row) {
            return $row['key'];
        }) : $prioRows;
    }

    /**
     * @param string $confName
     * @return array
     */
    public static function getMappedRowNames(string $confName): array
    {
        $conf = Shop::getSettings([\CONF_VERGLEICHSLISTE])['vergleichsliste'];
        switch ($confName) {
            case 'vergleichsliste_artikelnummer':
                return [
                    'key'      => 'cArtNr',
                    'name'     => Shop::Lang()->get('productNumber', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_hersteller':
                return [
                    'key'      => 'cHersteller',
                    'name'     => Shop::Lang()->get('manufacturer', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_beschreibung':
                return [
                    'key'      => 'cBeschreibung',
                    'name'     => Shop::Lang()->get('description', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_kurzbeschreibung':
                return [
                    'key'      => 'cKurzBeschreibung',
                    'name'     => Shop::Lang()->get('shortDescription', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_artikelgewicht':
                return [
                    'key'      => 'fArtikelgewicht',
                    'name'     => Shop::Lang()->get('productWeight', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_versandgewicht':
                return [
                    'key'      => 'fGewicht',
                    'name'     => Shop::Lang()->get('shippingWeight', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_merkmale':
                return [
                    'key'      => 'Merkmale',
                    'name'     => Shop::Lang()->get('characteristics', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_variationen':
                return [
                    'key'      => 'Variationen',
                    'name'     => Shop::Lang()->get('variations', 'comparelist'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_verfuegbarkeit':
                return [
                    'key'      => 'verfuegbarkeit',
                    'name'     => Shop::Lang()->get('availability', 'productOverview'),
                    'priority' => $conf[$confName]
                ];
                break;
            case 'vergleichsliste_lieferzeit':
                return [
                    'key'      => 'lieferzeit',
                    'name'     => Shop::Lang()->get('shippingTime'),
                    'priority' => $conf[$confName]
                ];
                break;
            default:
                return [
                    'key'      => '',
                    'name'     => '',
                    'priority' => 0
                ];
                break;
        }
    }

    /**
     * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
     * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
     *
     * @param Vergleichsliste $compareList
     */
    public static function setComparison(Vergleichsliste $compareList): void
    {
        if (\count($compareList->oArtikel_arr) === 0) {
            return;
        }
        $data = Shop::Container()->getDB()->queryPrepared(
            'SELECT COUNT(kVergleichsliste) AS nVergleiche
                FROM tvergleichsliste
                WHERE cIP = :ip
                    AND dDate > DATE_SUB(NOW(),INTERVAL 1 DAY)',
            ['ip' => Request::getRealIP()],
            ReturnType::SINGLE_OBJECT
        );
        if ($data->nVergleiche < 3) {
            $ins        = new stdClass();
            $ins->cIP   = Request::getRealIP();
            $ins->dDate = \date('Y-m-d H:i:s');
            $id         = Shop::Container()->getDB()->insert('tvergleichsliste', $ins);
            foreach ($compareList->oArtikel_arr as $product) {
                $item                   = new stdClass();
                $item->kVergleichsliste = $id;
                $item->kArtikel         = $product->kArtikel;
                $item->cArtikelName     = $product->cName;

                Shop::Container()->getDB()->insert('tvergleichslistepos', $item);
            }
        }
    }
}
