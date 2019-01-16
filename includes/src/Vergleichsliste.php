<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;

/**
 * Class Vergleichsliste
 */
class Vergleichsliste
{
    /**
     * @var array
     */
    public $oArtikel_arr = [];

    /**
     * Konstruktor
     *
     * @param int   $kArtikel - Falls angegeben, wird der Artikel mit angegebenem kArtikel aus der DB geholt
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
            if (is_array($variations) && count($variations) > 0) {
                $oArtikel->Variationen = $variations;
            }
            $this->oArtikel_arr[] = $oArtikel;

            executeHook(HOOK_VERGLEICHSLISTE_CLASS_EINFUEGEN);
        } elseif (isset($_SESSION['Vergleichsliste'])) {
            $this->loadFromSession();
        }
    }

    /**
     * load comparelist from session
     */
    public function loadFromSession(): void
    {
        $compareList = \Session\Frontend::get('Vergleichsliste');
        if ($compareList !== null) {
            $defaultOptions = Artikel::getDefaultOptions();
            $linkHelper     = Shop::Container()->getLinkService();
            $baseURL        = $linkHelper->getStaticRoute('vergleichsliste.php');
            foreach ($compareList->oArtikel_arr as $oArtikel) {
                $product = new Artikel();
                $product->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                $product->cURLDEL = $baseURL . '?vlplo=' . $oArtikel->kArtikel;
                if (isset($oArtikel->oVariationen_arr) && count($oArtikel->oVariationen_arr) > 0) {
                    $product->Variationen = $oArtikel->oVariationen_arr;
                }
                $this->oArtikel_arr[] = $product;
            }
        }
    }

    /**
     * Holt alle Artikel mit der aktuellen Sprache bzw Waehrung aus der DB und weißt sie neu der Session zu
     *
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
            if ($kKonfigitem > 0 && class_exists('Konfigitem')) {
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
        return \Functional\some($this->oArtikel_arr, function ($e) use ($kArtikel) {
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
            if (count($oArtikel->oMerkmale_arr) > 0) {
                // Falls das Merkmal Array nicht leer ist
                if (count($attributes) > 0) {
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
            if (count($oArtikel->Variationen) > 0) {
                if (count($variations) > 0) {
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
        return \Functional\some($attributes, function ($e) use ($kMerkmal) {
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
        return \Functional\some($variations, function ($e) use ($cName) {
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
        if ($conf['vergleichsliste_artikelnummer'] > $max && !in_array('cArtNr', $exclude, true)) {
            $max = $conf['vergleichsliste_artikelnummer'];
            $col = 'cArtNr';
        }
        if ($conf['vergleichsliste_hersteller'] > $max && !in_array('cHersteller', $exclude, true)) {
            $max = $conf['vergleichsliste_hersteller'];
            $col = 'cHersteller';
        }
        if ($conf['vergleichsliste_beschreibung'] > $max && !in_array('cBeschreibung', $exclude, true)) {
            $max = $conf['vergleichsliste_beschreibung'];
            $col = 'cBeschreibung';
        }
        if ($conf['vergleichsliste_kurzbeschreibung'] > $max && !in_array('cKurzBeschreibung', $exclude, true)) {
            $max = $conf['vergleichsliste_kurzbeschreibung'];
            $col = 'cKurzBeschreibung';
        }
        if ($conf['vergleichsliste_artikelgewicht'] > $max && !in_array('fArtikelgewicht', $exclude, true)) {
            $max = $conf['vergleichsliste_artikelgewicht'];
            $col = 'fArtikelgewicht';
        }
        if ($conf['vergleichsliste_versandgewicht'] > $max && !in_array('fGewicht', $exclude, true)) {
            $max = $conf['vergleichsliste_versandgewicht'];
            $col = 'fGewicht';
        }
        if ($conf['vergleichsliste_merkmale'] > $max && !in_array('Merkmale', $exclude, true)) {
            $max = $conf['vergleichsliste_merkmale'];
            $col = 'Merkmale';
        }
        if ($conf['vergleichsliste_variationen'] > $max && !in_array('Variationen', $exclude, true)) {
            $col = 'Variationen';
        }

        return $col;
    }

    /**
     * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
     * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
     *
     * @param Vergleichsliste $compareList
     */
    public static function setComparison(Vergleichsliste $compareList): void
    {
        if (count($compareList->oArtikel_arr) === 0) {
            return;
        }
        $oVergleiche = Shop::Container()->getDB()->queryPrepared(
            'SELECT COUNT(kVergleichsliste) AS nVergleiche
                FROM tvergleichsliste
                WHERE cIP = :ip
                    AND dDate > DATE_SUB(NOW(),INTERVAL 1 DAY)',
            ['ip' => Request::getRealIP()],
            \DB\ReturnType::SINGLE_OBJECT
        );

        if ($oVergleiche->nVergleiche < 3) {
            $compareListTable        = new stdClass();
            $compareListTable->cIP   = Request::getRealIP();
            $compareListTable->dDate = date('Y-m-d H:i:s');
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
