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
                    } elseif (!isset($_SESSION['variBoxAnzahl_arr'])) {
                        //redirekt zum artikel, um variation/en zu waehlen / MBM beachten
                        header('Location: ' . Shop::getURL() .
                            '/?a=' . $kArtikel .
                            '&n=' . (int)$_POST['anzahl'] .
                            '&r=' . R_VARWAEHLEN, true, 302);
                        exit();
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
                } elseif (!isset($_SESSION['variBoxAnzahl_arr']) && $bRedirect) {
                    //redirekt zum artikel, um variation/en zu waehlen  MBM beachten
                    header('Location: ' . Shop::getURL() .
                        '/?a=' . $kArtikel .
                        '&n=' . (int)$_POST['anzahl'] .
                        '&r=' . R_VARWAEHLEN, true, 302);
                    exit();
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
    public static function getChildProdctIDByAttribute(
        int $kArtikel,
        int $es0,
        int $esWert0,
        int $es1 = 0,
        int $esWert1 = 0
    ): int {
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

    /**
     * @param Artikel $Artikel
     * @param string  $einstellung
     * @return int
     * @former gibVerfuegbarkeitsformularAnzeigen()
     * @since 5.0.0
     */
    public static function showAvailabilityForm(Artikel $Artikel, string $einstellung): int
    {
        if ($einstellung !== 'N'
            && ((int)$Artikel->inWarenkorbLegbar === INWKNICHTLEGBAR_LAGER
                || (int)$Artikel->inWarenkorbLegbar === INWKNICHTLEGBAR_LAGERVAR
                || ($Artikel->fLagerbestand <= 0 && $Artikel->cLagerKleinerNull === 'Y'))
        ) {
            switch ($einstellung) {
                case 'Y':
                    return 1;
                case 'P':
                    return 2;
                case 'L':
                default:
                    return 3;
            }
        }

        return 0;
    }

    /**
     * @param int       $kArtikel
     * @param bool|null $isParent
     * @return stdClass|null
     * @former gibArtikelXSelling()
     * @since 5.0.0
     */
    public static function getXSelling(int $kArtikel, $isParent = null)
    {
        if ($kArtikel <= 0) {
            return null;
        }
        $xSelling = new stdClass();
        $config   = Shop::getSettings([CONF_ARTIKELDETAILS])['artikeldetails'];
        if ($config['artikeldetails_xselling_standard_anzeigen'] === 'Y') {
            $xSelling->Standard = new stdClass();
            $cSQLLager          = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $xsell              = Shop::Container()->getDB()->queryPrepared(
                "SELECT txsell.*, txsellgruppe.cName, txsellgruppe.cBeschreibung
                    FROM txsell
                    JOIN tartikel
                        ON txsell.kXSellArtikel = tartikel.kArtikel 
                    LEFT JOIN txsellgruppe
                        ON txsellgruppe.kXSellGruppe = txsell.kXSellGruppe
                        AND txsellgruppe.kSprache = :lid
                    WHERE txsell.kArtikel = :aid
                        {$cSQLLager}
                    ORDER BY tartikel.cName",
                ['lid' => Shop::getLanguageID(), 'aid' => $kArtikel],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (count($xsell) > 0) {
                $xsellgruppen                     = \Functional\group($xsell, function ($e) {
                    return $e->kXSellGruppe;
                });
                $xSelling->Standard->XSellGruppen = [];
                $defaultOptions                   = Artikel::getDefaultOptions();
                foreach ($xsellgruppen as $groupID => $articles) {
                    $group          = new stdClass();
                    $group->Artikel = [];
                    foreach ($articles as $xs) {
                        $group->Name         = $xs->cName;
                        $group->Beschreibung = $xs->cBeschreibung;
                        $artikel             = (new Artikel())->fuelleArtikel($xs->kXSellArtikel, $defaultOptions);
                        if ($artikel !== null && $artikel->kArtikel > 0 && $artikel->aufLagerSichtbarkeit()) {
                            $group->Artikel[] = $artikel;
                        }
                    }
                    $xSelling->Standard->XSellGruppen[] = $group;
                }
            }
        }

        if ($config['artikeldetails_xselling_kauf_anzeigen'] === 'Y') {
            $anzahl = (int)$config['artikeldetails_xselling_kauf_anzahl'];
            if ($isParent === null) {
                $isParent = self::isParent($kArtikel);
            }
            if ($isParent === true) {
                if ($config['artikeldetails_xselling_kauf_parent'] === 'Y') {
                    $selectorXSellArtikel     = 'IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel)';
                    $filterXSellParentArtikel = 'IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel)';
                } else {
                    $selectorXSellArtikel     = 'txsellkauf.kXSellArtikel';
                    $filterXSellParentArtikel = 'tartikel.kVaterArtikel';
                }
                $xsell = Shop::Container()->getDB()->query(
                    "SELECT {$kArtikel} AS kArtikel,
                        {$selectorXSellArtikel} AS kXSellArtikel,
                        SUM(txsellkauf.nAnzahl) nAnzahl
                        FROM txsellkauf
                        JOIN tartikel ON tartikel.kArtikel = txsellkauf.kXSellArtikel
                        WHERE (txsellkauf.kArtikel IN (
                                SELECT tartikel.kArtikel
                                FROM tartikel
                                WHERE tartikel.kVaterArtikel = {$kArtikel}
                            ) OR txsellkauf.kArtikel = {$kArtikel})
                            AND {$filterXSellParentArtikel} != {$kArtikel}
                        GROUP BY 1, 2
                        ORDER BY SUM(txsellkauf.nAnzahl) DESC
                        LIMIT {$anzahl}",
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            } elseif ($config['artikeldetails_xselling_kauf_parent'] === 'Y') {
                $xsell = Shop::Container()->getDB()->query(
                    "SELECT txsellkauf.kArtikel,
                    IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel) AS kXSellArtikel,
                    SUM(txsellkauf.nAnzahl) nAnzahl
                        FROM txsellkauf
                        JOIN tartikel 
                            ON tartikel.kArtikel = txsellkauf.kXSellArtikel
                        WHERE txsellkauf.kArtikel = {$kArtikel}
                            AND (tartikel.kVaterArtikel != (
                                SELECT tartikel.kVaterArtikel
                                FROM tartikel
                                WHERE tartikel.kArtikel = {$kArtikel}
                            ) OR tartikel.kVaterArtikel = 0)
                        GROUP BY 1, 2
                        ORDER BY SUM(txsellkauf.nAnzahl) DESC
                        LIMIT {$anzahl}",
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            } else {
                $xsell = Shop::Container()->getDB()->selectAll(
                    'txsellkauf',
                    'kArtikel',
                    $kArtikel,
                    '*',
                    'nAnzahl DESC',
                    $anzahl
                );
            }
            $xsellCount2 = is_array($xsell) ? count($xsell) : 0;
            if ($xsellCount2 > 0) {
                if (!isset($xSelling->Kauf)) {
                    $xSelling->Kauf = new stdClass();
                }
                $xSelling->Kauf->Artikel = [];
                $defaultOptions          = Artikel::getDefaultOptions();
                foreach ($xsell as $xs) {
                    $artikel = new Artikel();
                    $artikel->fuelleArtikel($xs->kXSellArtikel, $defaultOptions);
                    if ($artikel->kArtikel > 0 && $artikel->aufLagerSichtbarkeit()) {
                        $xSelling->Kauf->Artikel[] = $artikel;
                    }
                }
            }
        }
        executeHook(HOOK_ARTIKEL_INC_XSELLING, [
            'kArtikel' => $kArtikel,
            'xSelling' => &$xSelling
        ]);

        return $xSelling;
    }

    /**
     * @former bearbeiteFrageZumProdukt()
     * @since 5.0.0
     */
    public static function checkProductQuestion()
    {
        $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
        if ($conf['artikeldetails']['artikeldetails_fragezumprodukt_anzeigen'] !== 'N') {
            $fehlendeAngaben = self::getMissingProductQuestionFormData();
            Shop::Smarty()->assign('fehlendeAngaben_fragezumprodukt', $fehlendeAngaben);
            $nReturnValue = FormHelper::eingabenKorrekt($fehlendeAngaben);

            executeHook(HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT_PLAUSI);

            if ($nReturnValue) {
                if (!self::checkProductQuestionFloodProtection((int)$conf['artikeldetails']['produktfrage_sperre_minuten'])) {
                    $oCheckBox     = new CheckBox();
                    $kKundengruppe = Session\Session::CustomerGroup()->getID();
                    $oAnfrage      = self::getProductQuestionFormDefaults();

                    executeHook(HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT);

                    // Set empty string if it not exists
                    if (empty($oAnfrage->cNachname)) {
                        $oAnfrage->cNachname = '';
                    }
                    // Set empty string if it not exists
                    if (empty($oAnfrage->cVorname)) {
                        $oAnfrage->cVorname = '';
                    }
                    // CheckBox Spezialfunktion ausfuehren
                    $oCheckBox->triggerSpecialFunction(
                        CHECKBOX_ORT_FRAGE_ZUM_PRODUKT,
                        $kKundengruppe,
                        true,
                        $_POST,
                        ['oKunde' => $oAnfrage, 'oNachricht' => $oAnfrage]
                    )->checkLogging(CHECKBOX_ORT_FRAGE_ZUM_PRODUKT, $kKundengruppe, $_POST, true);
                    self::sendProductQuestion();
                } else {
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('questionNotPossible', 'messages');
                }
            } elseif (isset($fehlendeAngaben['email']) && $fehlendeAngaben['email'] === 3) {
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('blockedEmail');
            } else {
                Shop::Smarty()->assign('Anfrage', self::getProductQuestionFormDefaults());
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('fillOutQuestion', 'messages');
            }
        } else {
            $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('productquestionPleaseLogin', 'errorMessages');
        }
    }

    /**
     * @return array
     * @former gibFehlendeEingabenProduktanfrageformular()
     * @since 5.0.0
     */
    public static function getMissingProductQuestionFormData(): array
    {
        $ret  = [];
        $conf = Shop::getSettings([CONF_ARTIKELDETAILS, CONF_GLOBAL]);
        if (!$_POST['nachricht']) {
            $ret['nachricht'] = 1;
        }
        if (SimpleMail::checkBlacklist($_POST['email'])) {
            $ret['email'] = 3;
        }
        if (StringHandler::filterEmailAddress($_POST['email']) === false) {
            $ret['email'] = 2;
        }
        if (!$_POST['email']) {
            $ret['email'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_vorname'] === 'Y' && !$_POST['vorname']) {
            $ret['vorname'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_nachname'] === 'Y' && !$_POST['nachname']) {
            $ret['nachname'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_firma'] === 'Y' && !$_POST['firma']) {
            $ret['firma'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_fax'] === 'Y' && !$_POST['fax']) {
            $ret['fax'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_tel'] === 'Y' && !$_POST['tel']) {
            $ret['tel'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_mobil'] === 'Y' && !$_POST['mobil']) {
            $ret['mobil'] = 1;
        }
        if ($conf['artikeldetails']['produktfrage_abfragen_captcha'] !== 'N' && !FormHelper::validateCaptcha($_POST)) {
            $ret['captcha'] = 2;
        }
        // CheckBox Plausi
        $oCheckBox = new CheckBox();
        $ret       = array_merge(
            $ret,
            $oCheckBox->validateCheckBox(
                CHECKBOX_ORT_FRAGE_ZUM_PRODUKT,
                Session::CustomerGroup()->getID(),
                $_POST,
                true
            )
        );

        return $ret;
    }

    /**
     * @return stdClass
     * @former baueProduktanfrageFormularVorgaben()
     * @since 5.0.0
     */
    public static function getProductQuestionFormDefaults()
    {
        $msg             = new stdClass();
        $msg->cNachricht = isset($_POST['nachricht']) ? StringHandler::filterXSS($_POST['nachricht']) : null;
        $msg->cAnrede    = isset($_POST['anrede']) ? StringHandler::filterXSS($_POST['anrede']) : null;
        $msg->cVorname   = isset($_POST['vorname']) ? StringHandler::filterXSS($_POST['vorname']) : null;
        $msg->cNachname  = isset($_POST['nachname']) ? StringHandler::filterXSS($_POST['nachname']) : null;
        $msg->cFirma     = isset($_POST['firma']) ? StringHandler::filterXSS($_POST['firma']) : null;
        $msg->cMail      = isset($_POST['email']) ? StringHandler::filterXSS($_POST['email']) : null;
        $msg->cFax       = isset($_POST['fax']) ? StringHandler::filterXSS($_POST['fax']) : null;
        $msg->cTel       = isset($_POST['tel']) ? StringHandler::filterXSS($_POST['tel']) : null;
        $msg->cMobil     = isset($_POST['mobil']) ? StringHandler::filterXSS($_POST['mobil']) : null;
        if (strlen($msg->cAnrede) === 1) {
            if ($msg->cAnrede === 'm') {
                $msg->cAnredeLocalized = Shop::Lang()->get('salutationM');
            } elseif ($msg->cAnrede === 'w') {
                $msg->cAnredeLocalized = Shop::Lang()->get('salutationW');
            }
        }

        return $msg;
    }

    /**
     * @former sendeProduktanfrage()
     * @since 5.0.0
     */
    public static function sendProductQuestion()
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

        $conf               = Shop::getSettings([CONF_EMAILS, CONF_ARTIKELDETAILS, CONF_GLOBAL]);
        $Objekt             = new stdClass();
        $Objekt->tartikel   = $GLOBALS['AktuellerArtikel'];
        $Objekt->tnachricht = self::getProductQuestionFormDefaults();
        $empfaengerName     = '';
        if ($Objekt->tnachricht->cVorname) {
            $empfaengerName = $Objekt->tnachricht->cVorname . ' ';
        }
        if ($Objekt->tnachricht->cNachname) {
            $empfaengerName .= $Objekt->tnachricht->cNachname;
        }
        if ($Objekt->tnachricht->cFirma) {
            if ($Objekt->tnachricht->cNachname || $Objekt->tnachricht->cVorname) {
                $empfaengerName .= ' - ';
            }
            $empfaengerName .= $Objekt->tnachricht->cFirma;
        }
        $mail = new stdClass();
        if (isset($conf['artikeldetails']['artikeldetails_fragezumprodukt_email'])) {
            $mail->toEmail = $conf['artikeldetails']['artikeldetails_fragezumprodukt_email'];
        }
        if (empty($mail->toEmail)) {
            $mail->toEmail = $conf['emails']['email_master_absender'];
        }
        $mail->toName       = $conf['global']['global_shopname'];
        $mail->replyToEmail = $Objekt->tnachricht->cMail;
        $mail->replyToName  = $empfaengerName;
        $Objekt->mail       = $mail;

        sendeMail(MAILTEMPLATE_PRODUKTANFRAGE, $Objekt);

        if ($conf['artikeldetails']['produktfrage_kopiekunde'] === 'Y') {
            $mail->toEmail      = $Objekt->tnachricht->cMail;
            $mail->toName       = $empfaengerName;
            $mail->replyToEmail = $Objekt->tnachricht->cMail;
            $mail->replyToName  = $empfaengerName;
            $Objekt->mail       = $mail;
            sendeMail(MAILTEMPLATE_PRODUKTANFRAGE, $Objekt);
        }
        $history             = new stdClass();
        $history->kSprache   = Shop::getLanguage();
        $history->kArtikel   = Shop::$kArtikel;
        $history->cAnrede    = $Objekt->tnachricht->cAnrede;
        $history->cVorname   = $Objekt->tnachricht->cVorname;
        $history->cNachname  = $Objekt->tnachricht->cNachname;
        $history->cFirma     = $Objekt->tnachricht->cFirma;
        $history->cTel       = $Objekt->tnachricht->cTel;
        $history->cMobil     = $Objekt->tnachricht->cMobil;
        $history->cFax       = $Objekt->tnachricht->cFax;
        $history->cMail      = $Objekt->tnachricht->cMail;
        $history->cNachricht = $Objekt->tnachricht->cNachricht;
        $history->cIP        = RequestHelper::getIP();
        $history->dErstellt  = 'now()';

        $kProduktanfrageHistory        = Shop::Container()->getDB()->insert('tproduktanfragehistory', $history);
        $GLOBALS['PositiveFeedback'][] = Shop::Lang()->get('thankYouForQuestion', 'messages');
        // campaign
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Kampagne::setCampaignAction(KAMPAGNE_DEF_FRAGEZUMPRODUKT, $kProduktanfrageHistory, 1.0);
        }
    }

    /**
     * @param int $min
     * @return bool
     * @former floodSchutzProduktanfrage()
     * @since 5.0.0
     */
    public static function checkProductQuestionFloodProtection(int $min = 0): bool
    {
        if ($min <= 0) {
            return false;
        }
        $history = Shop::Container()->getDB()->queryPrepared(
            "SELECT kProduktanfrageHistory
                FROM tproduktanfragehistory
                WHERE cIP = :ip
                    AND date_sub(now(), INTERVAL :min MINUTE) < dErstellt",
            ['ip' => RequestHelper::getIP(), 'min' => $min],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($history->kProduktanfrageHistory) && $history->kProduktanfrageHistory > 0;
    }

    /**
     * @former bearbeiteBenachrichtigung()
     * @since 5.0.0
     */
    public static function checkAvailabilityMessage()
    {
        $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
        if (isset($_POST['a'], $conf['artikeldetails']['benachrichtigung_nutzen'])
            && (int)$_POST['a'] > 0
            && $conf['artikeldetails']['benachrichtigung_nutzen'] !== 'N'
        ) {
            $fehlendeAngaben = self::getMissingAvailibilityFormData();
            Shop::Smarty()->assign('fehlendeAngaben_benachrichtigung', $fehlendeAngaben);
            $nReturnValue = FormHelper::eingabenKorrekt($fehlendeAngaben);

            executeHook(HOOK_ARTIKEL_INC_BENACHRICHTIGUNG_PLAUSI);
            if ($nReturnValue) {
                if (!self::checkAvailibityFormFloodProtection($conf['artikeldetails']['benachrichtigung_sperre_minuten'])) {
                    $Benachrichtigung            = self::getAvailabilityFormDefaults();
                    $Benachrichtigung->kSprache  = Shop::getLanguage();
                    $Benachrichtigung->kArtikel  = (int)$_POST['a'];
                    $Benachrichtigung->cIP       = RequestHelper::getIP();
                    $Benachrichtigung->dErstellt = 'now()';
                    $Benachrichtigung->nStatus   = 0;
                    $oCheckBox                   = new CheckBox();
                    $kKundengruppe               = Session::CustomerGroup()->getID();
                    // Set empty string if not exists
                    if (empty($Benachrichtigung->cNachname)) {
                        $Benachrichtigung->cNachname = '';
                    }
                    // Set empty string if it not exists
                    if (empty($Benachrichtigung->cVorname)) {
                        $Benachrichtigung->cVorname = '';
                    }
                    executeHook(HOOK_ARTIKEL_INC_BENACHRICHTIGUNG, ['Benachrichtigung' => $Benachrichtigung]);
                    // CheckBox Spezialfunktion ausfuehren
                    $oCheckBox->triggerSpecialFunction(
                        CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT,
                        $kKundengruppe,
                        true,
                        $_POST,
                        ['oKunde' => $Benachrichtigung, 'oNachricht' => $Benachrichtigung]
                    )->checkLogging(CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT, $kKundengruppe, $_POST, true);

                    $kVerfuegbarkeitsbenachrichtigung = Shop::Container()->getDB()->queryPrepared(
                        'INSERT INTO tverfuegbarkeitsbenachrichtigung 
                            (cVorname, cNachname, cMail, kSprache, kArtikel, cIP, dErstellt, nStatus) 
                            VALUES 
                            (:cVorname, :cNachname, :cMail, :kSprache, :kArtikel, :cIP, now(), :nStatus)
                            ON DUPLICATE KEY UPDATE 
                                cVorname = :cVorname, cNachname = :cNachname, ksprache = :kSprache, 
                                cIP = :cIP, dErstellt = now(), nStatus = :nStatus', get_object_vars($Benachrichtigung),
                        \DB\ReturnType::LAST_INSERTED_ID
                    );
                    // Kampagne
                    if (isset($_SESSION['Kampagnenbesucher'])) {
                        // Verfügbarkeitsbenachrichtigung
                        Kampagne::setCampaignAction(KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE,
                            $kVerfuegbarkeitsbenachrichtigung, 1.0);
                    }
                    $GLOBALS['PositiveFeedback'][] = Shop::Lang()->get('thankYouForNotificationSubscription',
                        'messages');
                } else {
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('notificationNotPossible', 'messages');
                }
            } elseif (isset($fehlendeAngaben['email']) && $fehlendeAngaben['email'] === 3) {
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('blockedEmail');
            } else {
                Shop::Smarty()->assign('Benachrichtigung', self::getAvailabilityFormDefaults());
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('fillOutNotification', 'messages');
            }
        }
    }

    /**
     * @return array
     * @former gibFehlendeEingabenBenachrichtigungsformular()
     * @since 5.0.0
     */
    public static function getMissingAvailibilityFormData(): array
    {
        $ret  = [];
        $conf = Shop::getSettings([CONF_ARTIKELDETAILS, CONF_GLOBAL]);
        if (!$_POST['email']) {
            $ret['email'] = 1;
        } elseif (StringHandler::filterEmailAddress($_POST['email']) === false) {
            $ret['email'] = 2;
        }
        if (SimpleMail::checkBlacklist($_POST['email'])) {
            $ret['email'] = 3;
        }
        if (empty($_POST['vorname']) && $conf['artikeldetails']['benachrichtigung_abfragen_vorname'] === 'Y') {
            $ret['vorname'] = 1;
        }
        if (empty($_POST['nachname']) && $conf['artikeldetails']['benachrichtigung_abfragen_nachname'] === 'Y') {
            $ret['nachname'] = 1;
        }
        if ($conf['artikeldetails']['benachrichtigung_abfragen_captcha'] !== 'N' && !FormHelper::validateCaptcha($_POST)) {
            $ret['captcha'] = 2;
        }
        // CheckBox Plausi
        $oCheckBox     = new CheckBox();
        $kKundengruppe = Session::CustomerGroup()->getID();
        $ret           = array_merge(
            $ret,
            $oCheckBox->validateCheckBox(CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT, $kKundengruppe, $_POST, true)
        );

        return $ret;
    }

    /**
     * @return stdClass
     * @former baueFormularVorgabenBenachrichtigung()
     * @since 5.0.0
     */
    public static function getAvailabilityFormDefaults(): stdClass
    {
        $msg  = new stdClass();
        $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
        if (!empty($_POST['vorname']) && $conf['artikeldetails']['benachrichtigung_abfragen_vorname'] !== 'N') {
            $msg->cVorname = StringHandler::filterXSS($_POST['vorname']);
        }
        if (!empty($_POST['nachname']) && $conf['artikeldetails']['benachrichtigung_abfragen_nachname'] !== 'N') {
            $msg->cNachname = StringHandler::filterXSS($_POST['nachname']);
        }
        if (!empty($_POST['email'])) {
            $msg->cMail = StringHandler::filterXSS($_POST['email']);
        }

        return $msg;
    }

    /**
     * @param int $min
     * @return bool
     * @former floodSchutzBenachrichtigung()
     * @since 5.0.0
     */
    public static function checkAvailibityFormFloodProtection(int $min): bool
    {
        if (!$min) {
            return false;
        }
        $history = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kVerfuegbarkeitsbenachrichtigung
                FROM tverfuegbarkeitsbenachrichtigung
                WHERE cIP = :ip
                AND date_sub(now(), INTERVAL :min MINUTE) < dErstellt',
            ['ip' => RequestHelper::getIP(), 'min' => $min],
            \DB\ReturnType::SINGLE_OBJECT
        );

        return isset($history->kVerfuegbarkeitsbenachrichtigung) && $history->kVerfuegbarkeitsbenachrichtigung > 0;
    }

    /**
     * @param int $kArtikel
     * @param int $kKategorie
     * @return stdClass
     * @former gibNaviBlaettern()
     * @since 5.0.0
     */
    public static function getProductNavigation(int $kArtikel, int $kKategorie): stdClass
    {
        $navi            = new stdClass();
        $customerGroupID = Session::CustomerGroup()->getID();
        // Wurde der Artikel von der Artikelübersicht aus angeklickt?
        if ($kArtikel > 0
            && isset($_SESSION['oArtikelUebersichtKey_arr'])
            && count($_SESSION['oArtikelUebersichtKey_arr']) > 0
        ) {
            $collection = $_SESSION['oArtikelUebersichtKey_arr'];
            if (!($collection instanceof \Tightenco\Collect\Support\Collection)) {
                collect($collection);
            }
            // Such die Position des aktuellen Artikels im Array der Artikelübersicht
            $kArtikelVorheriger = 0;
            $kArtikelNaechster  = 0;
            $nArrayPos          = $collection->search($kArtikel, true);
            if ($nArrayPos === 0) {
                // Artikel ist an der ersten Position => es gibt nur einen nächsten Artikel (oder keinen :))
                $kArtikelNaechster = $collection[$nArrayPos + 1] ?? null;
            } elseif ($nArrayPos === ($collection->count() - 1)) {
                // Artikel ist an der letzten Position => es gibt nur einen voherigen Artikel
                $kArtikelVorheriger = $collection[$nArrayPos - 1];
            } elseif ($nArrayPos !== false) {
                $kArtikelNaechster  = $collection[$nArrayPos + 1];
                $kArtikelVorheriger = $collection[$nArrayPos - 1];
            }
            if ($kArtikelNaechster > 0) {
                $navi->naechsterArtikel = (new Artikel())
                    ->fuelleArtikel($kArtikelNaechster, Artikel::getDefaultOptions());
                if ($navi->naechsterArtikel === null) {
                    unset($navi->naechsterArtikel);
                }
            }
            if ($kArtikelVorheriger > 0) {
                $navi->vorherigerArtikel = (new Artikel())
                    ->fuelleArtikel($kArtikelVorheriger, Artikel::getDefaultOptions());
                if ($navi->vorherigerArtikel->kArtikel === null) {
                    unset($navi->vorherigerArtikel);
                }
            }
        }
        // Ist der Besucher nicht von der Artikelübersicht gekommen?
        if ($kKategorie > 0 && (!isset($navi->vorherigerArtikel) && !isset($navi->naechsterArtikel))) {
            $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $objArr_pre  = Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tpreise, tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . $customerGroupID . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tkategorieartikel.kKategorie = $kKategorie
                        AND tpreise.kArtikel = tartikel.kArtikel
                        AND tartikel.kArtikel < $kArtikel
                        AND tpreise.kKundengruppe = " . $customerGroupID . "
                        " . $stockFilter . "
                    ORDER BY tartikel.kArtikel DESC
                    LIMIT 1",
                \DB\ReturnType::SINGLE_OBJECT
            );
            $objArr_next = Shop::Container()->getDB()->query(
                "SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tpreise, tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . $customerGroupID . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tkategorieartikel.kKategorie = $kKategorie
                        AND tpreise.kArtikel = tartikel.kArtikel
                        AND tartikel.kArtikel > $kArtikel
                        AND tpreise.kKundengruppe = " . $customerGroupID . "
                        " . $stockFilter . "
                    ORDER BY tartikel.kArtikel
                    LIMIT 1",
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (!empty($objArr_pre->kArtikel)) {
                $navi->vorherigerArtikel = (new Artikel())
                    ->fuelleArtikel($objArr_pre->kArtikel, Artikel::getDefaultOptions());
            }
            if (!empty($objArr_next->kArtikel)) {
                $navi->naechsterArtikel = (new Artikel())
                    ->fuelleArtikel($objArr_next->kArtikel, Artikel::getDefaultOptions());
            }
        }

        return $navi;
    }

    /**
     * @param int $nEigenschaftWert
     * @return array
     * @former gibNichtErlaubteEigenschaftswerte()
     * @since 5.0.0
     */
    public static function getNonAllowedAttributeValues(int $nEigenschaftWert): array
    {
        if ($nEigenschaftWert) {
            $arNichtErlaubteEigenschaftswerte  = Shop::Container()->getDB()->selectAll(
                'teigenschaftwertabhaengigkeit',
                'kEigenschaftWert',
                $nEigenschaftWert,
                'kEigenschaftWertZiel AS EigenschaftWert'
            );
            $arNichtErlaubteEigenschaftswerte2 = Shop::Container()->getDB()->selectAll(
                'teigenschaftwertabhaengigkeit',
                'kEigenschaftWertZiel',
                $nEigenschaftWert,
                'kEigenschaftWert AS EigenschaftWert'
            );

            return array_merge(
                $arNichtErlaubteEigenschaftswerte,
                $arNichtErlaubteEigenschaftswerte2
            );
        }

        return [];
    }

    /**
     * @param null|string|array $cRedirectParam
     * @param bool              $bRenew
     * @param null|Artikel      $oArtikel
     * @param null|float        $fAnzahl
     * @param int               $kKonfigitem
     * @return array
     * @former baueArtikelhinweise()
     * @since 5.0.0
     */
    public static function getProductMessages(
        $cRedirectParam = null,
        $bRenew = false,
        $oArtikel = null,
        $fAnzahl = null,
        $kKonfigitem = 0
    ): array {
        if ($cRedirectParam === null && isset($_GET['r'])) {
            $cRedirectParam = $_GET['r'];
        }
        if ($bRenew || !isset($GLOBALS['Artikelhinweise']) || !is_array($GLOBALS['Artikelhinweise'])) {
            $GLOBALS['Artikelhinweise'] = [];
        }
        if ($bRenew || !isset($GLOBALS['PositiveFeedback']) || !is_array($GLOBALS['PositiveFeedback'])) {
            $GLOBALS['PositiveFeedback'] = [];
        }
        if ($cRedirectParam) {
            $hin_arr = is_array($cRedirectParam) ? $cRedirectParam : explode(',', $cRedirectParam);
            $hin_arr = array_unique($hin_arr);

            foreach ($hin_arr as $hin) {
                switch ($hin) {
                    case R_LAGERVAR:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('quantityNotAvailableVar', 'messages');
                        break;
                    case R_VARWAEHLEN:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('chooseVariations', 'messages');
                        break;
                    case R_VORBESTELLUNG:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('preorderNotPossible', 'messages');
                        break;
                    case R_LOGIN:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('pleaseLogin', 'messages');
                        break;
                    case R_LAGER:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('quantityNotAvailable', 'messages');
                        break;
                    case R_MINDESTMENGE:
                        if ($oArtikel === null) {
                            $oArtikel = $GLOBALS['AktuellerArtikel'];
                        }
                        if ($fAnzahl === null) {
                            $fAnzahl = $_GET['n'];
                        }
                        $GLOBALS['Artikelhinweise'][] = lang_mindestbestellmenge($oArtikel, $fAnzahl, $kKonfigitem);
                        break;
                    case R_LOGIN_WUNSCHLISTE:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('loginWishlist', 'messages');
                        break;
                    case R_MAXBESTELLMENGE:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                        break;
                    case R_ARTIKELABNAHMEINTERVALL:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                        break;
                    case R_UNVERKAEUFLICH:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkUnsalable', 'messages');
                        break;
                    case R_AUFANFRAGE:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkOnrequest', 'messages');
                        break;
                    case R_EMPTY_TAG:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('tagArtikelEmpty', 'messages');
                        break;
                    case R_EMPTY_VARIBOX:
                        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('artikelVariBoxEmpty', 'messages');
                        break;
                    default:
                        break;
                }
                executeHook(HOOK_ARTIKEL_INC_ARTIKELHINWEISSWITCH);
            }
        }

        return $GLOBALS['Artikelhinweise'];
    }

    /**
     * @param Artikel $product
     * @return mixed
     * @former bearbeiteProdukttags()
     * @since 5.0.0
     */
    public static function editProductTags($product)
    {
        // Wurde etwas von der Tag Form gepostet?
        if (RequestHelper::verifyGPCDataInt('produktTag') !== 1) {
            return null;
        }
        $tag             = StringHandler::filterXSS(RequestHelper::verifyGPDataString('tag'));
        $variKindArtikel = RequestHelper::verifyGPDataString('variKindArtikel');
        // Wurde ein Tag gepostet?
        if (strlen($tag) > 0) {
            $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
            // Pruefe ob Kunde eingeloggt
            if (empty($_SESSION['Kunde']->kKunde) && $conf['artikeldetails']['tagging_freischaltung'] === 'Y') {
                $linkHelper = Shop::Container()->getLinkService();
                header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) .
                    '?a=' . (int)$_POST['a'] . '&tag=' .
                    StringHandler::htmlentities(StringHandler::filterXSS($_POST['tag'])) .
                    '&r=' . R_LOGIN_TAG . '&produktTag=1', true, 303);
                exit();
            }
            // Posts die älter als 24 Stunden sind löschen
            Shop::Container()->getDB()->query(
                "DELETE FROM ttagkunde 
                    WHERE dZeit < DATE_SUB(now(),INTERVAL 1 MONTH)",
                \DB\ReturnType::DEFAULT
            );
            // Admin Einstellungen prüfen
            if (($conf['artikeldetails']['tagging_freischaltung'] === 'Y'
                    && isset($_SESSION['Kunde']->kKunde)
                    && $_SESSION['Kunde']->kKunde > 0)
                || $conf['artikeldetails']['tagging_freischaltung'] === 'O'
            ) {
                $ip = RequestHelper::getIP();
                // Ist eine Kunde eingeloggt?
                if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                    $tagPostings = Shop::Container()->getDB()->queryPrepared(
                        'SELECT count(kTagKunde) AS Anzahl
                            FROM ttagkunde
                            WHERE dZeit > DATE_SUB(now(),INTERVAL 1 DAY)
                                AND kKunde = :kKunde',
                        ['kKunde' => (int)$_SESSION['Kunde']->kKunde],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    $kKunde      = (int)$_SESSION['Kunde']->kKunde;
                } else { // Wenn nicht, dann hat ein anonymer Besucher ein Tag gepostet
                    $tagPostings = Shop::Container()->getDB()->queryPrepared(
                        'SELECT count(kTagKunde) AS Anzahl FROM ttagkunde
                            WHERE dZeit > DATE_SUB(now(), INTERVAL 1 DAY)
                                AND cIP = :ip
                                AND kKunde = 0',
                        ['ip' => $ip],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    $kKunde      = 0;
                }
                // Wenn die max. eingestellte Anzahl der Posts pro Tag nicht überschritten wurde
                if ($tagPostings->Anzahl < (int)$conf['artikeldetails']['tagging_max_ip_count']) {
                    if ($kKunde === 0 && $conf['artikeldetails']['tagging_freischaltung'] === 'Y') {
                        return Shop::Lang()->get('pleaseLoginToAddTags', 'messages');
                    }
                    // Prüfe, ob der Tag bereits gemappt wurde
                    $tagmapping_objTMP = Shop::Container()->getDB()->select(
                        'ttagmapping',
                        'kSprache',
                        Shop::getLanguage(),
                        'cName',
                        Shop::Container()->getDB()->escape($tag)
                    );
                    $tagmapping_obj    = $tagmapping_objTMP;
                    if (isset($tagmapping_obj->cNameNeu) && strlen($tagmapping_obj->cNameNeu) > 0) {
                        $tag = $tagmapping_obj->cNameNeu;
                    }
                    // Prüfe ob der Tag bereits vorhanden ist
                    $tag_obj = new Tag();
                    $tag_obj->getByName($tag);
                    $kTag = isset($tag_obj->kTag) ? (int)$tag_obj->kTag : null;
                    if (!empty($kTag)) {
                        // Tag existiert bereits, TagArtikel updaten/anlegen
                        $tagArticle = new TagArticle($kTag, (int)$product->kArtikel);
                        if (!empty($tagArticle->kTag)) {
                            // TagArticle hinzufügen
                            $tagArticle->nAnzahlTagging = (int)$tagArticle->nAnzahlTagging + 1;
                            $tagArticle->updateInDB();
                        } else {
                            // TagArticle neu anlegen
                            $tagArticle->kTag           = $kTag;
                            $tagArticle->kArtikel       = (int)$product->kArtikel;
                            $tagArticle->nAnzahlTagging = 1;
                            $tagArticle->insertInDB();
                        }

                        if (!empty($variKindArtikel)) {
                            $childTag = new TagArticle($kTag, (int)$variKindArtikel);
                            if (!empty($childTag->kTag)) {
                                // TagArticle hinzufügen
                                $childTag->nAnzahlTagging = (int)$childTag->nAnzahlTagging + 1;
                                $childTag->updateInDB();
                            } else {
                                // TagArticle neu anlegen
                                $childTag->kTag           = $kTag;
                                $childTag->kArtikel       = (int)$variKindArtikel;
                                $childTag->nAnzahlTagging = 1;
                                $childTag->insertInDB();
                            }
                        }
                    } else {
                        // Tag muss angelegt werden
                        require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
                        $neuerTag           = new Tag();
                        $neuerTag->kSprache = Shop::getLanguage();
                        $neuerTag->cName    = $tag;
                        $neuerTag->cSeo     = getSeo($tag);
                        $neuerTag->cSeo     = checkSeo($neuerTag->cSeo);
                        $neuerTag->nAktiv   = 0;
                        $kTag               = $neuerTag->insertInDB();
                        if ($kTag > 0) {
                            $tagArticle                 = new TagArticle();
                            $tagArticle->kTag           = $kTag;
                            $tagArticle->kArtikel       = (int)$product->kArtikel;
                            $tagArticle->nAnzahlTagging = 1;
                            $tagArticle->insertInDB();
                            if (!empty($variKindArtikel)) {
                                $childTag = new TagArticle();
                                // TagArticle neu anlegen
                                $childTag->kTag           = $kTag;
                                $childTag->kArtikel       = (int)$variKindArtikel;
                                $childTag->nAnzahlTagging = 1;
                                $childTag->insertInDB();
                            }
                        }
                    }
                    $neuerTagKunde         = new stdClass();
                    $neuerTagKunde->kTag   = $kTag;
                    $neuerTagKunde->kKunde = $kKunde;
                    $neuerTagKunde->cIP    = $ip;
                    $neuerTagKunde->dZeit  = 'now()';
                    Shop::Container()->getDB()->insert('ttagkunde', $neuerTagKunde);

                    if ($tag_obj->nAktiv !== null && (int)$tag_obj->nAktiv === 0) {
                        return Shop::Lang()->get('tagAcceptedWaitCheck', 'messages');
                    }

                    return Shop::Lang()->get('tagAccepted', 'messages');
                }

                return Shop::Lang()->get('maxTagsExceeded', 'messages');
            }
        } elseif (isset($_POST['einloggen'])) {
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('jtl.php', true) .
                '?a=' . (int)$_POST['a'] . '&r=' . R_LOGIN_TAG, true, 303);
            exit();
        } else {
            $url = empty($product->cURLFull)
                ? (Shop::getURL() . '/?a=' . (int)$_POST['a'] . '&')
                : ($product->cURLFull . '?');
            header('Location: ' . $url . 'r=' . R_EMPTY_TAG, true, 303);
            exit();
        }

        return null;
    }

    /**
     * Baue Blätter Navi - Dient für die Blätternavigation unter Bewertungen in der Artikelübersicht
     *
     * @param int $ratingPage
     * @param int $ratingStars
     * @param int $ratingCount
     * @param int $pageCount
     * @return stdClass
     * @former baueBewertungNavi()
     * @since 5.0.0
     */
    public static function getRatingNavigation(
        int $ratingPage,
        int $ratingStars,
        int $ratingCount,
        int $pageCount = 0
    ): stdClass {
        $oBlaetterNavi         = new stdClass();
        $oBlaetterNavi->nAktiv = 0;
        if (!$pageCount) {
            $pageCount = 10;
        }
        // Ist die Anzahl der Bewertungen für einen bestimmten Artikel, in einer bestimmten Sprache größer als
        // die im Backend eingestellte maximale Anzahl an Bewertungen für eine Seite?
        if ($ratingCount > $pageCount) {
            $nBlaetterAnzahl_arr = [];
            // Anzahl an Seiten
            $nSeiten     = ceil($ratingCount / $pageCount);
            $nMaxAnzeige = 5; // Zeige in der Navigation nur maximal X Seiten an
            $nAnfang     = 0; // Wenn die aktuelle Seite - $nMaxAnzeige größer 0 ist, wird nAnfang gesetzt
            $nEnde       = 0; // Wenn die aktuelle Seite + $nMaxAnzeige <= $nSeitenist, wird nEnde gesetzt
            $nVoherige   = $ratingPage - 1; // Zum zurück blättern in der Navigation
            if ($nVoherige === 0) {
                $nVoherige = 1;
            }
            $nNaechste = $ratingPage + 1; // Zum vorwärts blättern in der Navigation
            if ($nNaechste >= $nSeiten) {
                $nNaechste = $nSeiten;
            }
            // Ist die maximale Anzahl an Seiten > als die Anzahl erlaubter Seiten in der Navigation?
            if ($nSeiten > $nMaxAnzeige) {
                // Diese Variablen ermitteln die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
                // Begrenzt durch $nMaxAnzeige.
                // Ist die aktuelle Seite nach dem abzug der Begrenzung größer oder gleich 1?
                if (($ratingPage - $nMaxAnzeige) >= 1) {
                    $nAnfang = 1;
                    $nVon    = ($ratingPage - $nMaxAnzeige) + 1;
                } else {
                    $nAnfang = 0;
                    $nVon    = 1;
                }
                // Ist die aktuelle Seite nach dem addieren der Begrenzung kleiner als die maximale Anzahl der Seiten
                if (($ratingPage + $nMaxAnzeige) < $nSeiten) {
                    $nEnde = $nSeiten;
                    $nBis  = ($ratingPage + $nMaxAnzeige) - 1;
                } else {
                    $nEnde = 0;
                    $nBis  = $nSeiten;
                }
                // Baue die Seiten für die Navigation
                for ($i = $nVon; $i <= $nBis; $i++) {
                    $nBlaetterAnzahl_arr[] = $i;
                }
            } else {
                // Baue die Seiten für die Navigation
                for ($i = 1; $i <= $nSeiten; $i++) {
                    $nBlaetterAnzahl_arr[] = $i;
                }
            }
            // Blaetter Objekt um später in Smarty damit zu arbeiten
            $oBlaetterNavi->nSeiten             = $nSeiten;
            $oBlaetterNavi->nVoherige           = $nVoherige;
            $oBlaetterNavi->nNaechste           = $nNaechste;
            $oBlaetterNavi->nAnfang             = $nAnfang;
            $oBlaetterNavi->nEnde               = $nEnde;
            $oBlaetterNavi->nBlaetterAnzahl_arr = $nBlaetterAnzahl_arr;
            $oBlaetterNavi->nAktiv              = 1;
        }

        $oBlaetterNavi->nSterne        = $ratingStars;
        $oBlaetterNavi->nAktuelleSeite = $ratingPage;
        $oBlaetterNavi->nVon           = (($oBlaetterNavi->nAktuelleSeite - 1) * $pageCount) + 1;
        $oBlaetterNavi->nBis           = $oBlaetterNavi->nAktuelleSeite * $pageCount;

        if ($oBlaetterNavi->nBis > $ratingCount) {
            --$oBlaetterNavi->nBis;
        }

        return $oBlaetterNavi;
    }

    /**
     * Mappt den Fehlercode für Bewertungen
     *
     * @param string $cCode
     * @param float  $fGuthaben
     * @return string
     * @former mappingFehlerCode()
     * @since 5.0.0
     */
    public static function mapErrorCode($cCode, $fGuthaben = 0.0): string
    {
        switch ($cCode) {
            // Fehler
            case 'f01':
                $error = Shop::Lang()->get('bewertungWrongdata', 'errorMessages');
                break;
            case 'f02':
                $error = Shop::Lang()->get('bewertungBewexist', 'errorMessages');
                break;
            case 'f03':
                $error = Shop::Lang()->get('bewertungBewnotbought', 'errorMessages');
                break;
            // Hinweise
            case 'h01':
                $error = Shop::Lang()->get('bewertungBewadd', 'messages');
                break;
            case 'h02':
                $error = Shop::Lang()->get('bewertungHilfadd', 'messages');
                break;
            case 'h03':
                $error = Shop::Lang()->get('bewertungHilfchange', 'messages');
                break;
            case 'h04':
                $error = sprintf(Shop::Lang()->get('bewertungBewaddCredits', 'messages'), (string)$fGuthaben);
                break;
            case 'h05':
                $error = Shop::Lang()->get('bewertungBewaddacitvate', 'messages');
                break;
            default:
                $error = '';
        }
        executeHook(HOOK_ARTIKEL_INC_BEWERTUNGHINWEISSWITCH, ['error' => $error]);

        return $error;
    }

    /**
     * @param Artikel $parent
     * @param Artikel $child
     * @return mixed
     * @former fasseVariVaterUndKindZusammen()
     * @since 5.0.0
     */
    public static function combineParentAndChild($parent, $child)
    {
        $product                                   = $child;
        $kVariKindArtikel                          = (int)$child->kArtikel;
        $product->kArtikel                         = (int)$parent->kArtikel;
        $product->kVariKindArtikel                 = $kVariKindArtikel;
        $product->nIstVater                        = 1;
        $product->kVaterArtikel                    = (int)$parent->kArtikel;
        $product->kEigenschaftKombi                = $parent->kEigenschaftKombi;
        $product->kEigenschaftKombi_arr            = $parent->kEigenschaftKombi_arr;
        $product->fDurchschnittsBewertung          = $parent->fDurchschnittsBewertung;
        $product->Bewertungen                      = $parent->Bewertungen ?? null;
        $product->HilfreichsteBewertung            = $parent->HilfreichsteBewertung ?? null;
        $product->oVariationKombiVorschau_arr      = $parent->oVariationKombiVorschau_arr ?? [];
        $product->oVariationDetailPreis_arr        = $parent->oVariationDetailPreis_arr;
        $product->nVariationKombiNichtMoeglich_arr = $parent->nVariationKombiNichtMoeglich_arr;
        $product->oVariationKombiVorschauText      = $parent->oVariationKombiVorschauText ?? null;
        $product->cVaterURL                        = $parent->cURL;
        $product->VaterFunktionsAttribute          = $parent->FunktionsAttribute;

        executeHook(HOOK_ARTIKEL_INC_FASSEVARIVATERUNDKINDZUSAMMEN, ['article' => $product]);

        return $product;
    }

    /**
     * @param int $kArtikel
     * @return array
     * @former holeAehnlicheArtikel()
     * @since 5.0.0
     */
    public static function getSimilarProductsByID(int $kArtikel): array
    {
        $oArtikel_arr           = [];
        $cLimit                 = ' LIMIT 3';
        $conf                   = Shop::getSettings([CONF_ARTIKELDETAILS]);
        $oXSeller               = self::getXSelling($kArtikel);
        $kArtikelXSellerKey_arr = [];
        if (isset($oXSeller->Standard->XSellGruppen)
            && is_array($oXSeller->Standard->XSellGruppen)
            && count($oXSeller->Standard->XSellGruppen) > 0
        ) {
            foreach ($oXSeller->Standard->XSellGruppen as $oXSeller) {
                if (is_array($oXSeller->Artikel) && count($oXSeller->Artikel) > 0) {
                    foreach ($oXSeller->Artikel as $oArtikel) {
                        $oArtikel->kArtikel = (int)$oArtikel->kArtikel;
                        if (!in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr, true)) {
                            $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                        }
                    }
                }
            }
        }
        if (isset($oXSeller->Kauf->XSellGruppen)
            && is_array($oXSeller->Kauf->XSellGruppen)
            && count($oXSeller->Kauf->XSellGruppen) > 0
        ) {
            foreach ($oXSeller->Kauf->XSellGruppen as $oXSeller) {
                if (is_array($oXSeller->Artikel) && count($oXSeller->Artikel) > 0) {
                    foreach ($oXSeller->Artikel as $oArtikel) {
                        $oArtikel->kArtikel = (int)$oArtikel->kArtikel;
                        if (!in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr, true)) {
                            $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                        }
                    }
                }
            }
        }

        $cSQLXSeller = '';
        if (count($kArtikelXSellerKey_arr) > 0) {
            $cSQLXSeller = " AND tartikel.kArtikel NOT IN (" . implode(',', $kArtikelXSellerKey_arr) . ") ";
        }

        if ($kArtikel > 0) {
            if ((int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0) {
                $cLimit = " LIMIT " . (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'];
            }
            $lagerFilter         = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $customerGroupID     = Session::CustomerGroup()->getID();
            $oArtikelMerkmal_arr = Shop::Container()->getDB()->queryPrepared(
                "SELECT tartikelmerkmal.kArtikel, tartikel.kVaterArtikel
                    FROM tartikelmerkmal
                        JOIN tartikel ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                            AND tartikel.kVaterArtikel != :kArtikel
                            AND (tartikel.nIstVater = 1 OR tartikel.kEigenschaftKombi = 0)
                        JOIN tartikelmerkmal similarMerkmal ON similarMerkmal.kArtikel = :kArtikel
                            AND similarMerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                            AND similarMerkmal.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                        LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :customerGroupID
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikelmerkmal.kArtikel != :kArtikel
                        {$lagerFilter}
                        {$cSQLXSeller}
                    GROUP BY tartikelmerkmal.kArtikel
                    ORDER BY COUNT(tartikelmerkmal.kMerkmal) DESC
                    " . $cLimit,
                [
                    'kArtikel'        => $kArtikel,
                    'customerGroupID' => $customerGroupID
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (is_array($oArtikelMerkmal_arr) && count($oArtikelMerkmal_arr) > 0) {
                $defaultOptions = Artikel::getDefaultOptions();
                foreach ($oArtikelMerkmal_arr as $oArtikelMerkmal) {
                    $oArtikel = new Artikel();
                    $id       = ($oArtikelMerkmal->kVaterArtikel > 0)
                        ? $oArtikelMerkmal->kVaterArtikel
                        : $oArtikelMerkmal->kArtikel;
                    $oArtikel->fuelleArtikel($id, $defaultOptions);
                    if ($oArtikel->kArtikel > 0) {
                        $oArtikel_arr[] = $oArtikel;
                    }
                }
            } else { // Falls es keine Merkmale gibt, in tsuchcachetreffer und ttagartikel suchen
                $oArtikelSuchcacheTreffer_arr = Shop::Container()->getDB()->query(
                    "SELECT tsuchcachetreffer.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kSuchCache
                            FROM tsuchcachetreffer
                            WHERE kArtikel = " . $kArtikel . "
                            AND nSort <= 10
                        ) AS ssSuchCache
                        JOIN tsuchcachetreffer 
                            ON tsuchcachetreffer.kSuchCache = ssSuchCache.kSuchCache
                            AND tsuchcachetreffer.kArtikel != " . $kArtikel . "
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tsuchcachetreffer.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . $customerGroupID . "
                        JOIN tartikel 
                            ON tartikel.kArtikel = tsuchcachetreffer.kArtikel
                            AND tartikel.kVaterArtikel != " . $kArtikel . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            {$lagerFilter}
                            {$cSQLXSeller}
                        GROUP BY tsuchcachetreffer.kArtikel
                        ORDER BY COUNT(*) DESC
                        " . $cLimit,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                if (count($oArtikelSuchcacheTreffer_arr) > 0) {
                    $defaultOptions = Artikel::getDefaultOptions();
                    foreach ($oArtikelSuchcacheTreffer_arr as $oArtikelSuchcacheTreffer) {
                        $oArtikel = new Artikel();
                        $id       = ($oArtikelSuchcacheTreffer->kVaterArtikel > 0)
                            ? $oArtikelSuchcacheTreffer->kVaterArtikel
                            : $oArtikelSuchcacheTreffer->kArtikel;
                        $oArtikel->fuelleArtikel($id, $defaultOptions);
                        if ($oArtikel->kArtikel > 0) {
                            $oArtikel_arr[] = $oArtikel;
                        }
                    }
                } else {
                    $oArtikelTags_arr = Shop::Container()->getDB()->query(
                        "SELECT ttagartikel.kArtikel, tartikel.kVaterArtikel
                            FROM
                            (
                                SELECT kTag
                                    FROM ttagartikel
                                    WHERE kArtikel = " . $kArtikel . "
                            ) AS ssTag
                            JOIN ttagartikel 
                                ON ttagartikel.kTag = ssTag.kTag
                                AND ttagartikel.kArtikel != " . $kArtikel . "
                            LEFT JOIN tartikelsichtbarkeit 
                                ON ttagartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = " . $customerGroupID . "
                            JOIN tartikel 
                                ON tartikel.kArtikel = ttagartikel.kArtikel
                                AND tartikel.kVaterArtikel != " . $kArtikel . "
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL
                                {$lagerFilter}
                                {$cSQLXSeller}
                            GROUP BY ttagartikel.kArtikel
                            ORDER BY COUNT(*) DESC
                            " . $cLimit,
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    $defaultOptions   = Artikel::getDefaultOptions();
                    foreach ($oArtikelTags_arr as $oArtikelTags) {
                        $oArtikel = new Artikel();
                        $id       = ($oArtikelTags->kVaterArtikel > 0)
                            ? $oArtikelTags->kVaterArtikel
                            : $oArtikelTags->kArtikel;
                        $oArtikel->fuelleArtikel($id, $defaultOptions);
                        if ($oArtikel->kArtikel > 0) {
                            $oArtikel_arr[] = $oArtikel;
                        }
                    }
                }
            }
        }
        executeHook(HOOK_ARTIKEL_INC_AEHNLICHEARTIKEL, [
            'oArtikel_arr' => &$oArtikel_arr
        ]);

        foreach ($oArtikel_arr as $i => $oArtikel) {
            foreach ($kArtikelXSellerKey_arr as $kArtikelXSellerKey) {
                if ($oArtikel->kArtikel === $kArtikelXSellerKey) {
                    unset($oArtikel_arr[$i]);
                }
            }
        }

        return $oArtikel_arr;
    }

    /**
     * @param int $productID
     * @return bool
     * @former ProductBundleWK()
     * @since 5.0.0
     */
    public static function addProductBundleToCart(int $productID): bool
    {
        if ($productID > 0) {
            $oOption                             = new stdClass();
            $oOption->nMerkmale                  = 1;
            $oOption->nAttribute                 = 1;
            $oOption->nArtikelAttribute          = 1;
            $oOption->nKeineSichtbarkeitBeachten = 1;

            return WarenkorbHelper::addProductIDToCart($productID, 1, [], 0, false, 0, $oOption);
        }

        return false;
    }

    /**
     * @param int       $kArtikel
     * @param float|int $fAnzahl
     * @param array     $nVariation_arr
     * @param array     $nKonfiggruppe_arr
     * @param array     $configGroupAmounts
     * @param array     $configItemAmounts
     * @return stdClass|null
     * @since 5.0.0
     */
    public static function buildConfig(
        int $kArtikel,
        $fAnzahl,
        $nVariation_arr,
        $nKonfiggruppe_arr,
        $configGroupAmounts,
        $configItemAmounts
    ) {
        $oKonfig                  = new stdClass;
        $oKonfig->fAnzahl         = $fAnzahl;
        $oKonfig->fGesamtpreis    = [0.0, 0.0];
        $oKonfig->cPreisLocalized = [];
        $oKonfig->cPreisString    = Shop::Lang()->get('priceAsConfigured', 'productDetails');

        if (!class_exists('Konfigurator') || !Konfigurator::validateKonfig($kArtikel)) {
            return null;
        }
        foreach ($nVariation_arr as $i => $nVariation) {
            $_POST['eigenschaftwert_' . $i] = $nVariation;
        }
        if (self::isParent($kArtikel)) {
            $kArtikel              = self::getArticleForParent($kArtikel);
            $oEigenschaftwerte_arr = self::getSelectedPropertiesForVarCombiArticle($kArtikel);
        } else {
            $oEigenschaftwerte_arr = self::getSelectedPropertiesForArticle($kArtikel, false);
        }

        $oArtikel                                = new Artikel();
        $oArtikelOptionen                        = new stdClass();
        $oArtikelOptionen->nKonfig               = 1;
        $oArtikelOptionen->nAttribute            = 1;
        $oArtikelOptionen->nArtikelAttribute     = 1;
        $oArtikelOptionen->nVariationKombi       = 1;
        $oArtikelOptionen->nVariationKombiKinder = 1;
        $oArtikel->fuelleArtikel($kArtikel, $oArtikelOptionen);

        $oKonfig->nMinDeliveryDays      = $oArtikel->nMinDeliveryDays;
        $oKonfig->nMaxDeliveryDays      = $oArtikel->nMaxDeliveryDays;
        $oKonfig->cEstimatedDelivery    = $oArtikel->cEstimatedDelivery;
        $oKonfig->Lageranzeige          = new stdClass();
        $oKonfig->Lageranzeige->nStatus = $oArtikel->Lageranzeige->nStatus;

        $fAnzahl = max($fAnzahl, 1);
        if ($oArtikel->cTeilbar !== 'Y' && (int)$fAnzahl != $fAnzahl) {
            $fAnzahl = (int)$fAnzahl;
        }

        $oKonfig->fGesamtpreis = [
            TaxHelper::getGross(
                $oArtikel->gibPreis($fAnzahl, $oEigenschaftwerte_arr),
                TaxHelper::getSalesTax($oArtikel->kSteuerklasse)
            ) * $fAnzahl,
            $oArtikel->gibPreis($fAnzahl, $oEigenschaftwerte_arr) * $fAnzahl
        ];
        $oKonfig->oKonfig_arr  = $oArtikel->oKonfig_arr;

        foreach ($nKonfiggruppe_arr as $i => $nKonfiggruppe) {
            $nKonfiggruppe_arr[$i] = (array)$nKonfiggruppe;
        }
        /** @var Konfiggruppe $oKonfiggruppe */
        foreach ($oKonfig->oKonfig_arr as $i => &$oKonfiggruppe) {
            $oKonfiggruppe->bAktiv = false;
            $kKonfiggruppe         = $oKonfiggruppe->getKonfiggruppe();
            $configItems           = $nKonfiggruppe_arr[$kKonfiggruppe] ?? [];
            foreach ($oKonfiggruppe->oItem_arr as $j => &$configItem) {
                /** @var Konfigitem $configItem */
                $kKonfigitem         = $configItem->getKonfigitem();
                $configItem->fAnzahl = (float)(
                    $configGroupAmounts[$configItem->getKonfiggruppe()] ?? $configItem->getInitial()
                );
                if ($configItem->fAnzahl > $configItem->getMax() || $configItem->fAnzahl < $configItem->getMin()) {
                    $configItem->fAnzahl = $configItem->getInitial();
                }
                if ($configItemAmounts && isset($configItemAmounts[$configItem->getKonfigitem()])) {
                    $configItem->fAnzahl = (float)$configItemAmounts[$configItem->getKonfigitem()];
                }
                if ($configItem->fAnzahl <= 0) {
                    $configItem->fAnzahl = 1;
                }
                $configItem->fAnzahlWK = $configItem->fAnzahl;
                if (!$configItem->ignoreMultiplier()) {
                    $configItem->fAnzahlWK *= $fAnzahl;
                }
                $configItem->bAktiv = in_array($kKonfigitem, $configItems);

                if ($configItem->bAktiv) {
                    $oKonfig->fGesamtpreis[0] += $configItem->getPreis() * $configItem->fAnzahlWK;
                    $oKonfig->fGesamtpreis[1] += $configItem->getPreis(true) * $configItem->fAnzahlWK;
                    $oKonfiggruppe->bAktiv    = true;
                    //Konfigitem mit Lagerinfos
                    if ($configItem->getArtikel() !== null
                        && $configItem->getArtikel()->cLagerBeachten === 'Y'
                        && $oKonfig->nMinDeliveryDays < $configItem->getArtikel()->nMinDeliveryDays
                    ) {
                        $oKonfig->nMinDeliveryDays      = $configItem->getArtikel()->nMinDeliveryDays;
                        $oKonfig->nMaxDeliveryDays      = $configItem->getArtikel()->nMaxDeliveryDays;
                        $oKonfig->cEstimatedDelivery    = $configItem->getArtikel()->cEstimatedDelivery;
                        $oKonfig->Lageranzeige->nStatus = $configItem->getArtikel()->Lageranzeige->nStatus;
                    }
                }
            }
            unset($configItem);
            $oKonfiggruppe->oItem_arr = array_values($oKonfiggruppe->oItem_arr);
        }
        unset($oKonfiggruppe);
        if (Session::CustomerGroup()->mayViewPrices()) {
            $oKonfig->cPreisLocalized = [
                Preise::getLocalizedPriceString($oKonfig->fGesamtpreis[0]),
                Preise::getLocalizedPriceString($oKonfig->fGesamtpreis[1])
            ];
        } else {
            $oKonfig->cPreisLocalized = [Shop::Lang()->get('priceHidden')];
        }
        $oKonfig->nNettoPreise = Session::CustomerGroup()->getIsMerchant();

        return $oKonfig;
    }

    /**
     * @param int       $kKonfig
     * @param JTLSmarty $smarty
     * @former holeKonfigBearbeitenModus()
     * @since 5.0.0
     */
    public static function getEditConfigMode($kKonfig, $smarty)
    {
        $cart = Session::Cart();
        if (!isset($cart->PositionenArr[$kKonfig]) || !class_exists('Konfigitem')) {
            return;
        }
        /** @var WarenkorbPos $basePosition */
        $basePosition = $cart->PositionenArr[$kKonfig];
        /** @var WarenkorbPos $basePosition */
        if ($basePosition->istKonfigVater()) {
            $configItems        = [];
            $configItemAmounts  = [];
            $configGroupAmounts = [];
            /** @var WarenkorbPos $oPosition */
            foreach ($cart->PositionenArr as &$oPosition) {
                if ($oPosition->cUnique !== $basePosition->cUnique || !$oPosition->istKonfigKind()) {
                    continue;
                }
                $configItem                                      = new Konfigitem($oPosition->kKonfigitem);
                $configItems[]                                   = $configItem->getKonfigitem();
                $configItemAmounts[$configItem->getKonfigitem()] = $oPosition->nAnzahl / $basePosition->nAnzahl;
                if ($configItem->ignoreMultiplier()) {
                    $configGroupAmounts[$configItem->getKonfiggruppe()] = $oPosition->nAnzahl;
                } else {
                    $configGroupAmounts[$configItem->getKonfiggruppe()] = $oPosition->nAnzahl / $basePosition->nAnzahl;
                }
            }
            unset($oPosition);

            $smarty->assign('fAnzahl', $basePosition->nAnzahl)
                   ->assign('kEditKonfig', $kKonfig)
                   ->assign('nKonfigitem_arr', $configItems)
                   ->assign('nKonfigitemAnzahl_arr', $configItemAmounts)
                   ->assign('nKonfiggruppeAnzahl_arr', $configGroupAmounts);
        }
        if (isset($basePosition->WarenkorbPosEigenschaftArr)) {
            $attrValues = [];
            foreach ($basePosition->WarenkorbPosEigenschaftArr as $attr) {
                $attrValues[$attr->kEigenschaft] = (object)[
                    'kEigenschaft'                  => $attr->kEigenschaft,
                    'kEigenschaftWert'              => $attr->kEigenschaftWert,
                    'cEigenschaftWertNameLocalized' => $attr->cEigenschaftWertName[$_SESSION['cISOSprache']],
                ];
            }

            if (count($attrValues) > 0) {
                $smarty->assign('oEigenschaftWertEdit_arr', $attrValues);
            }
        }
    }
}
