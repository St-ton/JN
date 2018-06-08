<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
     * @param array $oVariationen_arr
     */
    public function __construct(int $kArtikel = 0, $oVariationen_arr = [])
    {
        if ($kArtikel > 0) {
            $oArtikel           = new stdClass();
            $tmpName            = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel', $kArtikel,
                null, null,
                null, null,
                false,
                'cName'
            );
            $oArtikel->kArtikel = $kArtikel;
            $oArtikel->cName    = $tmpName->cName;
            if (is_array($oVariationen_arr) && count($oVariationen_arr) > 0) {
                $oArtikel->Variationen = $oVariationen_arr;
            }
            $this->oArtikel_arr[] = $oArtikel;

            executeHook(HOOK_VERGLEICHSLISTE_CLASS_EINFUEGEN);
        } elseif (isset($_SESSION['Vergleichsliste'])) {
            $this->oArtikel_arr = $_SESSION['Vergleichsliste']->oArtikel_arr;
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
            $oArtikel_tmp           = new stdClass();
            $oArtikel_tmp->kArtikel = $oArtikel->kArtikel;
            $_SESSION['Vergleichsliste']->oArtikel_arr[$i] = $oArtikel_tmp;
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
            $oArtikel = new Artikel();
            $oArtikel->kArtikel = $kArtikel;
            if ($kKonfigitem > 0 && class_exists('Konfigitem')) {
                // Falls Konfigitem gesetzt Preise + Name überschreiben
                $oKonfigitem = new Konfigitem($kKonfigitem);
                if ($oKonfigitem->getKonfigitem() > 0) {
                    $oArtikel->Preise->cVKLocalized[0] = $oKonfigitem->getPreisLocalized(true, false);
                    $oArtikel->Preise->cVKLocalized[1] = $oKonfigitem->getPreisLocalized(true, false, true);
                    $oArtikel->kSteuerklasse           = $oKonfigitem->getSteuerklasse();
                    unset($oArtikel->cLocalizedVPE);

                    if ($oKonfigitem->getUseOwnName()) {
                        $oArtikel->cName             = $oKonfigitem->getName();
                        $oArtikel->cBeschreibung     = $oKonfigitem->getBeschreibung();
                        $oArtikel->cKurzBeschreibung = $oKonfigitem->getBeschreibung();
                    }
                }
            }
            if ($oArtikel->kArtikel > 0) {
                $this->oArtikel_arr[] = $oArtikel;
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
}
