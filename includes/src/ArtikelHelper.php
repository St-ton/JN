<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ArtikelHelper
 */
class ArtikelHelper
{
    /**
     * @param int $kArtikel
     * @return bool
     */
    public static function isVariChild(int $kArtikel): bool
    {
        if ($kArtikel > 0) {
            $oArtikel = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'kEigenschaftKombi'
            );

            return isset($oArtikel->kEigenschaftKombi) && (int)$oArtikel->kEigenschaftKombi > 0;
        }

        return false;
    }

    /**
     * @param int $kArtikel
     * @return int
     */
    public static function getParent(int $kArtikel): int
    {
        if ($kArtikel > 0) {
            $oArtikel = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'kVaterArtikel'
            );

            return (isset($oArtikel->kVaterArtikel) && (int)$oArtikel->kVaterArtikel > 0)
                ? (int)$oArtikel->kVaterArtikel
                : 0;
        }

        return 0;
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public static function isVariCombiChild(int $kArtikel): bool
    {
        return self::getParent($kArtikel) > 0;
    }

    /**
     * Holt fuer einen kVaterArtikel + gesetzte Eigenschaften, den kArtikel vom Variationskombikind
     *
     * @param int $kArtikel
     * @return int
     */
    public static function getArticleForParent(int $kArtikel): int
    {
        $kKundengruppe       = Session::CustomerGroup()->getID();
        $properties          = self::getChildPropertiesForParent($kArtikel, $kKundengruppe);
        $kVariationKombi_arr = [];
        $nGueltig            = 1;
        foreach ($properties as $i => $kAlleEigenschaftWerteProEigenschaft) {
            if (!self::hasSelectedVariationValue($i)) {
                $nGueltig = 0;
                break;
            }
            $kVariationKombi_arr[$i] = self::getSelectedVariationValue($i);
        }
        if ($nGueltig) {
            $attributes      = [];
            $attributeValues = [];
            if (count($kVariationKombi_arr) > 0) {
                foreach ($kVariationKombi_arr as $i => $kVariationKombi) {
                    $attributes[]      = $i;
                    $attributeValues[] = (int)$kVariationKombi;
                }
                $oArtikelTMP = Shop::Container()->getDB()->query(
                    "SELECT tartikel.kArtikel
                        FROM teigenschaftkombiwert
                        JOIN tartikel
                            ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        LEFT JOIN tartikelsichtbarkeit
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                        WHERE teigenschaftkombiwert.kEigenschaft IN (" . implode(',', $attributes) . ")
                            AND teigenschaftkombiwert.kEigenschaftWert IN (" . implode(',', $attributeValues) . ")
                            AND tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = " . $kArtikel . "
                        GROUP BY tartikel.kArtikel
                        HAVING count(*) = " . count($kVariationKombi_arr),
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($oArtikelTMP->kArtikel) && $oArtikelTMP->kArtikel > 0) {
                    return (int)$oArtikelTMP->kArtikel;
                }
            }
            if (!isset($_SESSION['variBoxAnzahl_arr'])) {
                //redirekt zum artikel, um variation/en zu waehlen / MBM beachten
                header('Location: ' . Shop::getURL() .
                    '/?a=' . $kArtikel .
                    '&n=' . $_POST['anzahl'] .
                    '&r=' . R_VARWAEHLEN, true, 302);
                exit();
            }
        }

        return 0;
    }

    /**
     * Holt fuer einen kVaterArtikel alle Eigenschaften und Eigenschaftswert Assoc als Array
     * z.b. $properties[kEigenschaft] = EigenschaftWert
     *
     * @former: gibAlleKindEigenschaftenZuVater()
     * @param int $kArtikel
     * @param int $kKundengruppe
     * @return array
     */
    public static function getChildPropertiesForParent(int $kArtikel, int $kKundengruppe): array
    {
        $varCombinations = self::getPossibleVariationCombinations($kArtikel, $kKundengruppe);
        $properties      = [];
        foreach ($varCombinations as $comb) {
            if (!isset($properties[$comb->kEigenschaft]) || !is_array($properties[$comb->kEigenschaft])) {
                $properties[$comb->kEigenschaft] = [];
            }
            if (!isset($comb->kEigenschaftWert, $properties[$comb->kEigenschaft])
                || !in_array($comb->kEigenschaftWert, $properties[$comb->kEigenschaft], true)
            ) {
                $properties[$comb->kEigenschaft][] = $comb->kEigenschaftWert;
            }
        }

        return $properties;
    }

    /**
     * @param int  $kVaterArtikel
     * @param int  $kKundengruppe
     * @param bool $bGroupBy
     * @return array
     */
    public static function getPossibleVariationCombinations(
        int $kVaterArtikel,
        int $kKundengruppe = 0,
        bool $bGroupBy = false
    ): array {
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $cGroupBy = $bGroupBy ? "GROUP BY teigenschaftkombiwert.kEigenschaftWert" : '';

        return array_map(function ($e) {
            $e->kEigenschaft      = (int)$e->kEigenschaft;
            $e->kEigenschaftKombi = (int)$e->kEigenschaftKombi;
            $e->kEigenschaftWert  = (int)$e->kEigenschaftWert;

            return $e;
        },
            Shop::Container()->getDB()->query(
                "SELECT teigenschaftkombiwert.*
                    FROM teigenschaftkombiwert
                    JOIN tartikel
                        ON tartikel.kVaterArtikel = " . $kVaterArtikel . "
                        AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    {$cGroupBy}
                    ORDER BY teigenschaftkombiwert.kEigenschaftWert",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            )
        );
    }

    /**
     * @former gibGewaehlteEigenschaftenZuVariKombiArtikel()
     * @param int $kArtikel
     * @param int $nArtikelVariAufbau
     * @return array
     */
    public static function getSelectedPropertiesForVarCombiArticle(int $kArtikel, int $nArtikelVariAufbau = 0): array
    {
        if ($kArtikel <= 0) {
            return [];
        }
        $customerGroup  = Session::CustomerGroup()->getID();
        $oProperties    = [];
        $propertyValues = [];
        $nVorhanden     = 1;
        // Hole EigenschaftWerte zur gewaehlten VariationKombi
        $oVariationKombiKind_arr = Shop::Container()->getDB()->query(
            "SELECT teigenschaftkombiwert.kEigenschaftWert, teigenschaftkombiwert.kEigenschaft, tartikel.kVaterArtikel
                FROM teigenschaftkombiwert
                JOIN tartikel
                    ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    AND tartikel.kArtikel = " . $kArtikel . "
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $customerGroup . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY tartikel.kArtikel",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($oVariationKombiKind_arr) === 0) {
            return [];
        }
        $kVaterArtikel = (int)$oVariationKombiKind_arr[0]->kVaterArtikel;
        foreach ($oVariationKombiKind_arr as $oVariationKombiKind) {
            if (!isset($propertyValues[$oVariationKombiKind->kEigenschaft])
                || !is_array($propertyValues[$oVariationKombiKind->kEigenschaft])
            ) {
                $propertyValues[(int)$oVariationKombiKind->kEigenschaft] = (int)$oVariationKombiKind->kEigenschaftWert;
            }
        }
        $attributes       = [];
        $attributeValues  = [];
        $kSprache         = Shop::getLanguage();
        $attr             = new stdClass();
        $attr->cSELECT    = '';
        $attr->cJOIN      = '';
        $attrVal          = new stdClass();
        $attrVal->cSELECT = '';
        $attrVal->cJOIN   = '';
        foreach ($propertyValues as $i => $kEigenschaftWertProEigenschaft) {
            $attributes[]      = $i;
            $attributeValues[] = $propertyValues[$i];
        }
        if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
            $attr->cSELECT = "teigenschaftsprache.cName AS cName_teigenschaftsprache, ";
            $attr->cJOIN   = "LEFT JOIN teigenschaftsprache 
                                        ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                        AND teigenschaftsprache.kSprache = " . $kSprache;

            $attrVal->cSELECT = "teigenschaftwertsprache.cName AS cName_teigenschaftwertsprache, ";
            $attrVal->cJOIN   = "LEFT JOIN teigenschaftwertsprache 
                                            ON teigenschaftwertsprache.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                            AND teigenschaftwertsprache.kSprache = " . $kSprache;
        }

        $oEigenschaft_arr = Shop::Container()->getDB()->query(
            "SELECT teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName, " . $attrVal->cSELECT . "
                teigenschaftwertsichtbarkeit.kKundengruppe, teigenschaftwert.kEigenschaft, teigenschaft.cTyp, " .
            $attr->cSELECT . " teigenschaft.cName AS cNameEigenschaft, teigenschaft.kArtikel
                FROM teigenschaftwert
                LEFT JOIN teigenschaftwertsichtbarkeit
                    ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                    AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $customerGroup . "
                JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftwert.kEigenschaft
                LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = " . $customerGroup . "
                " . $attr->cJOIN . "
                " . $attrVal->cJOIN . "
                WHERE teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                    AND teigenschaftwert.kEigenschaft IN (" . implode(',', $attributes) . ")
                    AND teigenschaftwert.kEigenschaftWert IN (" . implode(',', $attributeValues) . ")",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $oEigenschaftTMP_arr = Shop::Container()->getDB()->query(
            "SELECT teigenschaft.kEigenschaft,teigenschaft.cName,teigenschaft.cTyp
                FROM teigenschaft
                LEFT JOIN teigenschaftsichtbarkeit
                    ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = " . $customerGroup . "
                WHERE (teigenschaft.kArtikel = " . $kVaterArtikel . "
                    OR teigenschaft.kArtikel = " . $kArtikel . ")
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                    AND (teigenschaft.cTyp = 'FREIFELD'
                    OR teigenschaft.cTyp = 'PFLICHT-FREIFELD')",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        if (is_array($oEigenschaft_arr) && count($oEigenschaft_arr) > 0) {
            if (is_array($oEigenschaftTMP_arr)) {
                $oEigenschaft_arr = array_merge($oEigenschaft_arr, $oEigenschaftTMP_arr);
            }

            foreach ($oEigenschaft_arr as $oEigenschaft) {
                if ($oEigenschaft->cTyp !== 'FREIFELD' && $oEigenschaft->cTyp !== 'PFLICHT-FREIFELD') {
                    // Ist kEigenschaft zu eigenschaftwert vorhanden
                    if (self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)) {
                        $oEigenschaftWertVorhanden = Shop::Container()->getDB()->query(
                            "SELECT teigenschaftwert.kEigenschaftWert
                                FROM teigenschaftwert
                                LEFT JOIN teigenschaftwertsichtbarkeit
                                    ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                    AND teigenschaftwertsichtbarkeit.kKundengruppe = " . $customerGroup . "
                                WHERE teigenschaftwert.kEigenschaftWert = " . (int)$oEigenschaft->kEigenschaftWert . "
                                    AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                    AND teigenschaftwert.kEigenschaft = " . (int)$oEigenschaft->kEigenschaft,
                            \DB\ReturnType::SINGLE_OBJECT
                        );

                        if ($oEigenschaftWertVorhanden->kEigenschaftWert) {
                            unset($oEigenschaftwerte);
                            $oEigenschaftwerte                   = new stdClass();
                            $oEigenschaftwerte->kEigenschaftWert = $oEigenschaft->kEigenschaftWert;
                            $oEigenschaftwerte->kEigenschaft     = $oEigenschaft->kEigenschaft;
                            $oEigenschaftwerte->cTyp             = $oEigenschaft->cTyp;

                            if ($kSprache > 0 && !Sprache::isDefaultLanguageActive()) {
                                $oEigenschaftwerte->cEigenschaftName     = $oEigenschaft->cName_teigenschaftsprache;
                                $oEigenschaftwerte->cEigenschaftWertName = $oEigenschaft->cName_teigenschaftwertsprache;
                            } else {
                                $oEigenschaftwerte->cEigenschaftName     = $oEigenschaft->cNameEigenschaft;
                                $oEigenschaftwerte->cEigenschaftWertName = $oEigenschaft->cName;
                            }
                            $oProperties[] = $oEigenschaftwerte;
                        } else {
                            $nVorhanden = 0;
                            break;
                        }
                    } else {
                        if (!isset($_SESSION['variBoxAnzahl_arr'])) {
                            //redirekt zum artikel, um variation/en zu waehlen / MBM beachten
                            header('Location: ' . Shop::getURL() .
                                '/?a=' . $kArtikel .
                                '&n=' . (int)$_POST['anzahl'] .
                                '&r=' . R_VARWAEHLEN, true, 302);
                            exit();
                        }
                    }
                } else {
                    unset($oEigenschaftwerte);
                    if ($oEigenschaft->cTyp === 'PFLICHT-FREIFELD'
                        && self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)
                        && strlen(self::getSelectedVariationValue($oEigenschaft->kEigenschaft)) === 0
                    ) {
                        header('Location: ' . Shop::getURL() .
                            '/?a=' . $kArtikel .
                            '&n=' . (int)$_POST['anzahl'] .
                            '&r=' . R_VARWAEHLEN, true, 302);
                        exit();
                    }
                    $oEigenschaftwerte                = new stdClass();
                    $oEigenschaftwerte->cFreifeldWert = StringHandler::filterXSS(
                        self::getSelectedVariationValue($oEigenschaft->kEigenschaft)
                    );
                    $oEigenschaftwerte->kEigenschaft  = $oEigenschaft->kEigenschaft;
                    $oEigenschaftwerte->cTyp          = $oEigenschaft->cTyp;
                    $oProperties[]                    = $oEigenschaftwerte;
                }
            }
        }

        if (!$nVorhanden && !isset($_SESSION['variBoxAnzahl_arr'])) {
            //redirekt zum artikel, weil variation nicht vorhanden
            header('Location: ' . Shop::getURL() .
                '/?a=' . $kArtikel .
                '&n=' . (int)$_POST['anzahl'] .
                '&r=' . R_VARWAEHLEN, true, 301);
            exit();
        }
        // Wie beim Artikel die Variationen aufbauen
        if ($nArtikelVariAufbau > 0) {
            $variations = [];
            if (is_array($oProperties) && count($oProperties) > 0) {
                foreach ($oProperties as $i => $oEigenschaftwerte) {
                    $oEigenschaftWert                   = new stdClass();
                    $oEigenschaftWert->kEigenschaftWert = $oEigenschaftwerte->kEigenschaftWert;
                    $oEigenschaftWert->kEigenschaft     = $oEigenschaftwerte->kEigenschaft;
                    $oEigenschaftWert->cName            = $oEigenschaftwerte->cEigenschaftWertName;

                    $variations[$i]               = new stdClass();
                    $variations[$i]->kEigenschaft = $oEigenschaftwerte->kEigenschaft;
                    $variations[$i]->kArtikel     = $kArtikel;
                    $variations[$i]->cWaehlbar    = 'Y';
                    $variations[$i]->cTyp         = $oEigenschaftwerte->cTyp;
                    $variations[$i]->cName        = $oEigenschaftwerte->cEigenschaftName;
                    $variations[$i]->Werte        = [];
                    $variations[$i]->Werte[]      = $oEigenschaftWert;
                }

                return $variations;
            }
        }

        return $oProperties;
    }

    /**
     * @former gibGewaehlteEigenschaftenZuArtikel()
     * @param int  $kArtikel
     * @param bool $bRedirect
     * @return array
     */
    public static function getSelectedPropertiesForArticle(int $kArtikel, bool $bRedirect = true): array
    {
        $kKundengruppe = Session::CustomerGroup()->getID();
        // Pruefe welche kEigenschaft gesetzt ist
        $oEigenschaft_arr = Shop::Container()->getDB()->queryPrepared(
            'SELECT teigenschaft.kEigenschaft,teigenschaft.cName,teigenschaft.cTyp
                FROM teigenschaft
                LEFT JOIN teigenschaftsichtbarkeit 
                    ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = :cgroupid
                WHERE teigenschaft.kArtikel = :articleid
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL',
            ['cgroupid' => $kKundengruppe, 'articleid' => $kArtikel],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        // $oProperties anlegen
        $oProperties = [];
        $nVorhanden  = 1;
        if (!is_array($oEigenschaft_arr) || count($oEigenschaft_arr) === 0) {
            return [];
        }
        foreach ($oEigenschaft_arr as $oEigenschaft) {
            $oEigenschaft->kEigenschaft = (int)$oEigenschaft->kEigenschaft;
            if ($oEigenschaft->cTyp !== 'FREIFELD' && $oEigenschaft->cTyp !== 'PFLICHT-FREIFELD') {
                // Ist kEigenschaft zu eigenschaftwert vorhanden
                if (self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)) {
                    $oEigenschaftWertVorhanden = Shop::Container()->getDB()->queryPrepared(
                        'SELECT teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName, 
                            teigenschaftwertsichtbarkeit.kKundengruppe
                            FROM teigenschaftwert
                            LEFT JOIN teigenschaftwertsichtbarkeit
                                ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                AND teigenschaftwertsichtbarkeit.kKundengruppe = :cgroupid
                            WHERE teigenschaftwert.kEigenschaftWert = :attribvalueid
                                AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                AND teigenschaftwert.kEigenschaft = :attribid',
                        [
                            'cgroupid'      => $kKundengruppe,
                            'attribvalueid' => self::getSelectedVariationValue($oEigenschaft->kEigenschaft),
                            'attribid'      => $oEigenschaft->kEigenschaft
                        ],
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if ($oEigenschaftWertVorhanden->kEigenschaftWert) {
                        $val                       = new stdClass();
                        $val->kEigenschaftWert     = (int)self::getSelectedVariationValue($oEigenschaft->kEigenschaft);
                        $val->kEigenschaft         = $oEigenschaft->kEigenschaft;
                        $val->cEigenschaftName     = $oEigenschaft->cName;
                        $val->cEigenschaftWertName = $oEigenschaftWertVorhanden->cName;
                        $val->cTyp                 = $oEigenschaft->cTyp;
                        $oProperties[]             = $val;
                    } else {
                        $nVorhanden = 0;
                        break;
                    }
                } else {
                    if (!isset($_SESSION['variBoxAnzahl_arr']) && $bRedirect) {
                        //redirekt zum artikel, um variation/en zu waehlen  MBM beachten
                        header('Location: ' . Shop::getURL() .
                            '/?a=' . $kArtikel .
                            '&n=' . (int)$_POST['anzahl'] .
                            '&r=' . R_VARWAEHLEN, true, 302);
                        exit();
                    }
                }
            } else {
                if ($oEigenschaft->cTyp === 'PFLICHT-FREIFELD'
                    && $bRedirect
                    && self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)
                    && strlen(self::getSelectedVariationValue($oEigenschaft->kEigenschaft)) === 0
                ) {
                    header('Location: ' . Shop::getURL() .
                        '/?a=' . $kArtikel .
                        '&n=' . (int)$_POST['anzahl'] .
                        '&r=' . R_VARWAEHLEN, true, 302);
                    exit();
                }
                $val                = new stdClass();
                $val->cFreifeldWert = Shop::Container()->getDB()->escape(
                    StringHandler::filterXSS(self::getSelectedVariationValue($oEigenschaft->kEigenschaft))
                );
                $val->kEigenschaft  = $oEigenschaft->kEigenschaft;
                $val->cTyp          = $oEigenschaft->cTyp;
                $oProperties[]      = $val;
            }
        }

        if (!$nVorhanden && $bRedirect && !isset($_SESSION['variBoxAnzahl_arr'])) {
            //redirect zum artikel, weil variation nicht vorhanden
            header('Location: ' . Shop::getURL() .
                '/?a=' . $kArtikel .
                '&n=' . (int)$_POST['anzahl'] .
                '&r=' . R_VARWAEHLEN, true, 302);
            exit();
        }

        return $oProperties;
    }

    /**
     * Holt zu einem $kVaterArtikel alle kArtikel zu den Variationskinder
     *
     * @former holeKinderzuVater()
     * @param int $kVaterArtikel
     * @return array
     */
    public static function getChildren(int $kVaterArtikel): array
    {
        return $kVaterArtikel > 0
            ? Shop::Container()->getDB()->selectAll(
                'tartikel',
                'kVaterArtikel',
                $kVaterArtikel,
                'kArtikel, kEigenschaftKombi'
            )
            : [];
    }

    /**
     * @former pruefeIstVaterArtikel()
     * @param int $kArtikel
     * @return bool
     */
    public static function isParent(int $kArtikel): bool
    {
        $oArtikelTMP = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $kArtikel,
            null,
            null,
            null,
            null,
            false,
            'nIstVater'
        );

        return isset($oArtikelTMP->nIstVater) && $oArtikelTMP->nIstVater > 0;
    }

    /**
     * @param int  $kArtikel
     * @param bool $bInfo
     * @return bool|stdClass
     */
    public static function isStuecklisteKomponente(int $kArtikel, $bInfo = false)
    {
        if ($kArtikel > 0) {
            $oObj = Shop::Container()->getDB()->select('tstueckliste', 'kArtikel', $kArtikel);
            if (isset($oObj->kStueckliste) && $oObj->kStueckliste > 0) {
                return $bInfo ? $oObj : true;
            }
        }

        return false;
    }

    /**
     * Fallback für alte Formular-Struktur
     *
     * alt: eigenschaftwert_{kEigenschaft}
     * neu: eigenschaftwert[{kEigenschaft}]
     *
     * @param int $groupId
     * @return string|bool
     */
    protected static function getSelectedVariationValue(int $groupId)
    {
        $idx = 'eigenschaftwert_' . $groupId;
        if (isset($_POST[$idx])) {
            return $_POST[$idx];
        }

        return $_POST['eigenschaftwert'][$groupId] ?? false;
    }

    /**
     * @param int $groupId
     * @return bool
     */
    protected static function hasSelectedVariationValue(int $groupId): bool
    {
        return self::getSelectedVariationValue($groupId) !== false;
    }

    /**
     * @param Artikel  $artikel
     * @param object[] $variationPicturesArr
     */
    public static function addVariationPictures(Artikel $artikel, $variationPicturesArr)
    {
        if (is_array($variationPicturesArr) && count($variationPicturesArr) > 0) {
            $artikel->Bilder = array_filter($artikel->Bilder, function ($item) {
                return !(isset($item->isVariation) && $item->isVariation);
            });
            if (count($variationPicturesArr) === 1) {
                array_unshift($artikel->Bilder, $variationPicturesArr[0]);
            } else {
                $artikel->Bilder = array_merge($artikel->Bilder, $variationPicturesArr);
            }

            $nNr = 1;
            foreach (array_keys($artikel->Bilder) as $key) {
                $artikel->Bilder[$key]->nNr = $nNr++;
            }

            $artikel->cVorschaubild = $artikel->Bilder[0]->cURLKlein;
        }
    }

    /**
     * @param Artikel $artikel
     * @param float   $fPreis
     * @param int     $nAnzahl
     * @return stdClass
     */
    public static function getBasePriceUnit(Artikel $artikel, $fPreis, $nAnzahl): stdClass
    {
        $unitMappings = [
            'mg'  => 'kg',
            'g'   => 'kg',
            'mL'  => 'L',
            'cm3' => 'L',
            'cL'  => 'L',
            'dL'  => 'L',
        ];

        $result = (object)[
            'fGrundpreisMenge' => $artikel->fGrundpreisMenge,
            'fMassMenge'       => $artikel->fMassMenge * $nAnzahl,
            'fBasePreis'       => $fPreis / $artikel->fVPEWert,
            'fVPEWert'         => (float)$artikel->fVPEWert,
            'cVPEEinheit'      => $artikel->cVPEEinheit,
        ];

        $gpUnit   = UnitsOfMeasure::getUnit($artikel->kGrundpreisEinheit);
        $massUnit = UnitsOfMeasure::getUnit($artikel->kMassEinheit);

        if (isset($gpUnit, $massUnit, $unitMappings[$gpUnit->cCode], $unitMappings[$massUnit->cCode])) {
            $fFactor    = UnitsOfMeasure::getConversionFaktor($unitMappings[$massUnit->cCode], $massUnit->cCode);
            $threshold  = 250 * $fFactor / 1000;
            $nAmount    = 1;
            $mappedCode = $unitMappings[$massUnit->cCode];

            if ($threshold > 0 && $result->fMassMenge > $threshold) {
                $result->fGrundpreisMenge = $nAmount;
                $result->fMassMenge       /= $fFactor;
                $result->fVPEWert         = $result->fMassMenge / $nAnzahl / $result->fGrundpreisMenge;
                $result->fBasePreis       = $fPreis / $result->fVPEWert;
                $result->cVPEEinheit      = $result->fGrundpreisMenge . ' ' .
                    UnitsOfMeasure::getPrintAbbreviation($mappedCode);
            }
        }

        return $result;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0
     */
    public static function getDataByAttribute(string $attribute, $value, callable $callback = null)
    {
        $res = Shop::Container()->getDB()->select('tartikel', $attribute, $value);

        return is_callable($callback)
            ? $callback($res)
            : $res;
    }

    /**
     * @param string        $attribute
     * @param string        $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getProductByAttribute($attribute, $value, callable $callback = null)
    {
        $art = ($res = self::getDataByAttribute($attribute, $value)) !== null
            ? (new Artikel())->fuelleArtikel($res->kArtikel, Artikel::getDefaultOptions())
            : null;

        return is_callable($callback)
            ? $callback($art)
            : $art;
    }



    /**
     * Gibt den kArtikel von einem Varikombi Kind zurück und braucht dafür Eigenschaften und EigenschaftsWerte
     * Klappt nur bei max. 2 Dimensionen
     *
     * @param int $kArtikel
     * @param int $es0
     * @param int $esWert0
     * @param int $es1
     * @param int $esWert1
     * @return int
     * @since 5.0.0
     * @former findeKindArtikelZuEigenschaft()
     */
    public static function getChildProdctIDByAttribute(int $kArtikel, int $es0, int $esWert0, int $es1 = 0, int $esWert1 = 0): int
    {
        if ($es0 > 0 && $esWert0 > 0) {
            $cSQLJoin   = " JOIN teigenschaftkombiwert
                          ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                          AND teigenschaftkombiwert.kEigenschaft = " . $es0 . "
                          AND teigenschaftkombiwert.kEigenschaftWert = " . $esWert0;
            $cSQLHaving = '';
            if ($es1 > 0 && $esWert1 > 0) {
                $cSQLJoin = " JOIN teigenschaftkombiwert
                              ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                              AND teigenschaftkombiwert.kEigenschaft IN(" . $es0 . ", " . $es1 . ")
                              AND teigenschaftkombiwert.kEigenschaftWert IN(" . $esWert0 . ", " . $esWert1 . ")";

                $cSQLHaving = " HAVING COUNT(*) = 2";
            }
            $oArtikel = Shop::Container()->getDB()->query(
                "SELECT kArtikel
                    FROM tartikel
                    " . $cSQLJoin . "
                    WHERE tartikel.kVaterArtikel = " . $kArtikel . "
                    GROUP BY teigenschaftkombiwert.kEigenschaftKombi" . $cSQLHaving,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oArtikel->kArtikel) && count($oArtikel->kArtikel) > 0) {
                return (int)$oArtikel->kArtikel;
            }
        }

        return 0;
    }

    /**
     * @param int  $kArtikel
     * @param bool $bSichtbarkeitBeachten
     * @return array
     * @since 5.0.0
     * @former gibVarKombiEigenschaftsWerte()
     */
    public static function getVarCombiAttributeValues(int $kArtikel, bool $bSichtbarkeitBeachten = true): array
    {
        $oEigenschaftwerte_arr = [];
        if ($kArtikel > 0 && self::isVariChild($kArtikel)) {
            $oArtikel                            = new Artikel();
            $oArtikelOptionen                    = new stdClass();
            $oArtikelOptionen->nMerkmale         = 0;
            $oArtikelOptionen->nAttribute        = 0;
            $oArtikelOptionen->nArtikelAttribute = 0;
            $oArtikelOptionen->nVariationKombi   = 1;

            if (!$bSichtbarkeitBeachten) {
                $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
            }

            $oArtikel->fuelleArtikel($kArtikel, $oArtikelOptionen);

            if ($oArtikel->oVariationenNurKind_arr !== null
                && is_array($oArtikel->oVariationenNurKind_arr)
                && count($oArtikel->oVariationenNurKind_arr) > 0
            ) {
                foreach ($oArtikel->oVariationenNurKind_arr as $oVariationenNurKind) {
                    $oEigenschaftwerte                       = new stdClass();
                    $oEigenschaftwerte->kEigenschaftWert     = $oVariationenNurKind->Werte[0]->kEigenschaftWert;
                    $oEigenschaftwerte->kEigenschaft         = $oVariationenNurKind->kEigenschaft;
                    $oEigenschaftwerte->cEigenschaftName     = $oVariationenNurKind->cName;
                    $oEigenschaftwerte->cEigenschaftWertName = $oVariationenNurKind->Werte[0]->cName;

                    $oEigenschaftwerte_arr[] = $oEigenschaftwerte;
                }
            }
        }

        return $oEigenschaftwerte_arr;
    }

    /**
     * @param array $oVariation_arr
     * @param int   $kEigenschaft
     * @param int   $kEigenschaftWert
     * @return bool|object
     * @former findeVariation()
     * @since 5.0.0
     */
    public static function findVariation(array $oVariation_arr, int $kEigenschaft, int $kEigenschaftWert): bool
    {
        foreach ($oVariation_arr as $oVariation) {
            $oVariation->kEigenschaft = (int)$oVariation->kEigenschaft;
            if ($oVariation->kEigenschaft === $kEigenschaft
                && isset($oVariation->Werte)
                && is_array($oVariation->Werte)
                && count($oVariation->Werte) > 0
            ) {
                foreach ($oVariation->Werte as $oWert) {
                    $oWert->kEigenschaftWert = (int)$oWert->kEigenschaftWert;
                    if ($oWert->kEigenschaftWert === $kEigenschaftWert) {
                        return $oWert;
                    }
                }
            }
        }

        return false;
    }
}
