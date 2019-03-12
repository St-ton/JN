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
     * @param int   $kArtikel
     * @param array $variations
     */
    public function __construct(int $kArtikel = 0, array $variations = [])
    {
        if ($kArtikel > 0) {
            $oArtikel           = new stdClass();
            $tmpName            = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'cName'
            );
            $oArtikel->kArtikel = $kArtikel;
            $oArtikel->cName    = $tmpName->cName;
            if (\is_array($variations) && \count($variations) > 0) {
                $oArtikel->Variationen = $variations;
            }
            $this->oArtikel_arr[] = $oArtikel;

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
            foreach ($compareList->oArtikel_arr as $oArtikel) {
                $product = new Artikel();
                $product->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                $product->cURLDEL = $baseURL . '?vlplo=' . $oArtikel->kArtikel;
                if (isset($oArtikel->oVariationen_arr) && \count($oArtikel->oVariationen_arr) > 0) {
                    $product->Variationen = $oArtikel->oVariationen_arr;
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
        foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $oArtikel) {
            $tmpProduct                                    = new stdClass();
            $tmpProduct->kArtikel                          = $oArtikel->kArtikel;
            $_SESSION['Vergleichsliste']->oArtikel_arr[$i] = $tmpProduct;
        }

        return $this;
    }

    /**
     * @param int  $kArtikel
     * @param bool $bAufSession
     * @param int  $kKonfigitem
     * @return $this
     */
    public function fuegeEin(int $kArtikel, bool $bAufSession = true, int $kKonfigitem = 0): self
    {
        // Existiert der Key und ist er noch nicht vorhanden?
        if ($kArtikel > 0 && !$this->artikelVorhanden($kArtikel)) {
            //new slim variant for compare list
            $product           = new Artikel();
            $product->kArtikel = $kArtikel;
            if ($kKonfigitem > 0) {
                // Falls Konfigitem gesetzt Preise + Name überschreiben
                $oKonfigitem = new Konfigitem($kKonfigitem);
                if ($oKonfigitem->getKonfigitem() > 0) {
                    $product->Preise->cVKLocalized[0] = $oKonfigitem->getPreisLocalized(true, false);
                    $product->Preise->cVKLocalized[1] = $oKonfigitem->getPreisLocalized(true, false, true);
                    $product->kSteuerklasse           = $oKonfigitem->getSteuerklasse();
                    unset($product->cLocalizedVPE);

                    if ($oKonfigitem->getUseOwnName()) {
                        $product->cName             = $oKonfigitem->getName();
                        $product->cBeschreibung     = $oKonfigitem->getBeschreibung();
                        $product->cKurzBeschreibung = $oKonfigitem->getBeschreibung();
                    }
                }
            }
            if ($product->kArtikel > 0) {
                $this->oArtikel_arr[] = $product;
            }
            if ($bAufSession) {
                $_SESSION['Vergleichsliste']->oArtikel_arr = $this->oArtikel_arr;
            }
        }

        return $this;
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public function artikelVorhanden(int $kArtikel): bool
    {
        return some($this->oArtikel_arr, function ($e) use ($kArtikel) {
            return (int)$e->kArtikel === $kArtikel;
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
        foreach ($compareList->oArtikel_arr as $oArtikel) {
            /** @var Artikel $oArtikel */
            if (\count($oArtikel->oMerkmale_arr) > 0) {
                // Falls das Merkmal Array nicht leer ist
                if (\count($attributes) > 0) {
                    foreach ($oArtikel->oMerkmale_arr as $oMerkmale) {
                        if (!self::containsAttribute($attributes, $oMerkmale->kMerkmal)) {
                            $attributes[] = $oMerkmale;
                        }
                    }
                } else {
                    $attributes = $oArtikel->oMerkmale_arr;
                }
            }
            // Falls ein Artikel min. eine Variation enthält
            if (\count($oArtikel->Variationen) > 0) {
                if (\count($variations) > 0) {
                    foreach ($oArtikel->Variationen as $oVariationen) {
                        if (!self::containsVariation($variations, $oVariationen->cName)) {
                            $variations[] = $oVariationen;
                        }
                    }
                } else {
                    $variations = $oArtikel->Variationen;
                }
            }
        }
        if (\count($attributes) > 0) {
            \uasort($attributes, function (Merkmal $a, Merkmal $b) {
                return $a->nSort > $b->nSort;
            });
        }

        return [
            $attributes,
            $variations
        ];
    }

    /**
     * @param array $attributes
     * @param int   $kMerkmal
     * @return bool
     * @former istMerkmalEnthalten()
     * @since 5.0.0
     */
    public static function containsAttribute(array $attributes, int $kMerkmal): bool
    {
        return some($attributes, function ($e) use ($kMerkmal) {
            return (int)$e->kMerkmal === $kMerkmal;
        });
    }

    /**
     * @param array  $variations
     * @param string $cName
     * @return bool
     * @former istVariationEnthalten()
     * @since 5.0.0
     */
    public static function containsVariation(array $variations, string $cName): bool
    {
        return some($variations, function ($e) use ($cName) {
            return $e->cName === $cName;
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
        $conf               = Shop::getSettings([CONF_VERGLEICHSLISTE]);
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
        $conf = Shop::getSettings([CONF_VERGLEICHSLISTE])['vergleichsliste'];
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
        $oVergleiche = Shop::Container()->getDB()->queryPrepared(
            'SELECT COUNT(kVergleichsliste) AS nVergleiche
                FROM tvergleichsliste
                WHERE cIP = :ip
                    AND dDate > DATE_SUB(NOW(),INTERVAL 1 DAY)',
            ['ip' => Request::getRealIP()],
            ReturnType::SINGLE_OBJECT
        );

        if ($oVergleiche->nVergleiche < 3) {
            $compareListTable        = new stdClass();
            $compareListTable->cIP   = Request::getRealIP();
            $compareListTable->dDate = \date('Y-m-d H:i:s');
            $kVergleichsliste        = Shop::Container()->getDB()->insert('tvergleichsliste', $compareListTable);
            foreach ($compareList->oArtikel_arr as $oArtikel) {
                $compareListPosTable                   = new stdClass();
                $compareListPosTable->kVergleichsliste = $kVergleichsliste;
                $compareListPosTable->kArtikel         = $oArtikel->kArtikel;
                $compareListPosTable->cArtikelName     = $oArtikel->cName;

                Shop::Container()->getDB()->insert('tvergleichslistepos', $compareListPosTable);
            }
        }
    }
}
