<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

use Helpers\Cart;

/**
 * Class Konfigurator
 *
 * @package Extensions
 */
class Konfigurator
{
    /**
     * @var array
     */
    private static $groups = [];

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return \Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_KONFIGURATOR);
    }

    /**
     * getKonfig
     *
     * @param int $kArtikel
     * @param int $kSprache
     * @return array
     */
    public static function getKonfig(int $kArtikel, int $kSprache = 0): array
    {
        if (isset(self::$groups[$kArtikel])) {
            //#7482
            return self::$groups[$kArtikel];
        }
        $groups = \Shop::Container()->getDB()->selectAll(
            'tartikelkonfiggruppe',
            'kArtikel',
            $kArtikel,
            'kArtikel, kKonfigGruppe',
            'nSort ASC'
        );
        if (!\is_array($groups) || \count($groups) === 0 || !self::checkLicense()) {
            return [];
        }
        if (!$kSprache) {
            $kSprache = \Shop::getLanguageID();
        }
        foreach ($groups as &$group) {
            $group = new Konfiggruppe((int)$group->kKonfigGruppe, $kSprache);
        }
        unset($group);

        self::$groups[$kArtikel] = $groups;

        return $groups;
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public static function hasKonfig(int $kArtikel): bool
    {
        if (!self::checkLicense()) {
            return false;
        }
        $groups = \Shop::Container()->getDB()->query(
            'SELECT kArtikel, kKonfigGruppe
                 FROM tartikelkonfiggruppe
                 WHERE tartikelkonfiggruppe.kArtikel = ' . $kArtikel . '
                 ORDER BY tartikelkonfiggruppe.nSort ASC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        return \is_array($groups) && \count($groups) > 0;
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public static function validateKonfig($kArtikel): bool
    {
        /* Vorvalidierung deaktiviert */
        return true;
    }

    /**
     * @param object $oBasket
     */
    public static function postcheckBasket(&$oBasket): void
    {
        if (!\is_array($oBasket->PositionenArr) || \count($oBasket->PositionenArr) === 0 || !self::checkLicense()) {
            return;
        }
        $deletedPositions = [];
        foreach ($oBasket->PositionenArr as $index => $position) {
            $deleted = false;
            if ($position->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($position->cUnique && $position->kKonfigitem == 0) {
                    $configItems = [];
                    // Alle Kinder suchen
                    foreach ($oBasket->PositionenArr as $childPosition) {
                        if ($childPosition->cUnique
                            && $childPosition->cUnique === $position->cUnique
                            && $childPosition->kKonfigitem > 0
                        ) {
                            $configItems[] = new Konfigitem($childPosition->kKonfigitem);
                        }
                    }
                    // Konfiguration validieren
                    if (self::validateBasket($position->kArtikel, $configItems) !== true) {
                        $deleted            = true;
                        $deletedPositions[] = $index;
                        //loescheWarenkorbPosition($nPos);
                    }
                } elseif (!$position->cUnique) {
                    // Konfiguration vorhanden -> löschen
                    if (self::hasKonfig($position->kArtikel)) {
                        $deleted            = true;
                        $deletedPositions[] = $index;
                        //loescheWarenkorbPosition($nPos);
                    }
                }
                if ($deleted) {
                    \Shop::Container()->getLogService()->error(
                        'Validierung der Konfiguration fehlgeschlagen - Warenkorbposition wurde entfernt: ' .
                        $position->cName[$_SESSION['cISOSprache']] . '(' . $position->kArtikel . ')'
                    );
                }
            }
        }
        Cart::deleteCartPositions($deletedPositions);
    }

    /**
     * @param int   $kArtikel
     * @param array $configItems
     * @return array|bool
     */
    public static function validateBasket(int $kArtikel, $configItems)
    {
        if ($kArtikel === 0 || !is_array($configItems)) {
            \Shop::Container()->getLogService()->error(
                'Validierung der Konfiguration fehlgeschlagen - Ungültige Daten'
            );

            return false;
        }
        // Gesamtpreis
        $fFinalPrice = 0.0;
        // Hauptartikel
        $oArtikel = new \Artikel();
        $oArtikel->fuelleArtikel($kArtikel, \Artikel::getDefaultOptions());
        // Grundpreis
        if ($oArtikel && (int)$oArtikel->kArtikel > 0) {
            $fFinalPrice += $oArtikel->Preise->fVKNetto;
        }
        foreach ($configItems as $configItem) {
            if (!isset($configItem->fAnzahl) ||
                $configItem->fAnzahl < $configItem->getMin() ||
                $configItem->fAnzahl > $configItem->getMax()) {
                $configItem->fAnzahl = $configItem->getInitial();
            }
            $fFinalPrice += $configItem->getPreis(true) * $configItem->fAnzahl;
        }
        $errors = [];
        foreach (self::getKonfig($kArtikel) as $group) {
            $itemCount     = 0;
            $kKonfiggruppe = $group->getKonfiggruppe();
            foreach ($configItems as $configItem) {
                if ($configItem->getKonfiggruppe() == $kKonfiggruppe) {
                    $itemCount++;
                }
            }
            if ($itemCount < $group->getMin() && $group->getMin() > 0) {
                if ($group->getMin() == $group->getMax()) {
                    $errors[$kKonfiggruppe] =
                        \Shop::Lang()->get('configChooseNComponents', 'productDetails', $group->getMin());
                } else {
                    $errors[$kKonfiggruppe] =
                        \Shop::Lang()->get('configChooseMinComponents', 'productDetails', $group->getMin());
                }
                $errors[$kKonfiggruppe] .= self::langComponent($group->getMin() > 1);
            } elseif ($itemCount > $group->getMax() && $group->getMax() > 0) {
                if ($group->getMin() == $group->getMax()) {
                    $errors[$kKonfiggruppe] =
                        \Shop::Lang()->get('configChooseNComponents', 'productDetails', $group->getMin()) .
                        self::langComponent($group->getMin() > 1);
                } else {
                    $errors[$kKonfiggruppe] =
                        \Shop::Lang()->get('configChooseMaxComponents', 'productDetails', $group->getMax()) .
                        self::langComponent($group->getMax() > 1);
                }
            }
        }

        if ($fFinalPrice < 0.0) {
            $cError = \sprintf(
                "Negative Konfigurationssumme für Artikel '%s' (Art.Nr.: %s, Netto: %s) - Vorgang abgebrochen",
                $oArtikel->cName,
                $oArtikel->cArtNr,
                \Preise::getLocalizedPriceString($fFinalPrice)
            );
            \Shop::Container()->getLogService()->error($cError);

            return false;
        }

        return \count($errors) === 0 ? true : $errors;
    }

    /**
     * @param bool $bPlural
     * @param bool $bSpace
     * @return string
     */
    private static function langComponent(bool $bPlural = false, bool $bSpace = true): string
    {
        $component = $bSpace ? ' ' : '';

        return $component . \Shop::Lang()->get($bPlural ? 'configComponents' : 'configComponent', 'productDetails');
    }
}
