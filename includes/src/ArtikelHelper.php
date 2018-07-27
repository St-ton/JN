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
        $product = Shop::Container()->getDB()->select(
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

        return isset($product->kEigenschaftKombi) && (int)$product->kEigenschaftKombi > 0;
    }

    /**
     * @param int $productID
     * @return int
     */
    public static function getParent(int $productID): int
    {
        $product = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $productID,
            null,
            null,
            null,
            null,
            false,
            'kVaterArtikel'
        );

        return (int)($product->kVaterArtikel ?? 0);
    }

    /**
     * @param int $productID
     * @return bool
     */
    public static function isVariCombiChild(int $productID): bool
    {
        return self::getParent($productID) > 0;
    }

    /**
     * Holt fuer einen kVaterArtikel + gesetzte Eigenschaften, den kArtikel vom Variationskombikind
     *
     * @param int $productID
     * @return int
     */
    public static function getArticleForParent(int $productID): int
    {
        $customerGroupID = Session::CustomerGroup()->getID();
        $properties      = self::getChildPropertiesForParent($productID, $customerGroupID);
        $combinations    = [];
        $valid           = true;
        foreach ($properties as $i => $kAlleEigenschaftWerteProEigenschaft) {
            if (!self::hasSelectedVariationValue($i)) {
                $valid = false;
                break;
            }
            $combinations[$i] = self::getSelectedVariationValue($i);
        }
        if ($valid) {
            $attributes      = [];
            $attributeValues = [];
            if (count($combinations) > 0) {
                foreach ($combinations as $i => $kVariationKombi) {
                    $attributes[]      = $i;
                    $attributeValues[] = (int)$kVariationKombi;
                }
                $product = Shop::Container()->getDB()->query(
                    'SELECT tartikel.kArtikel
                        FROM teigenschaftkombiwert
                        JOIN tartikel
                            ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                        LEFT JOIN tartikelsichtbarkeit
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                        WHERE teigenschaftkombiwert.kEigenschaft IN (' . implode(',', $attributes) . ')
                            AND teigenschaftkombiwert.kEigenschaftWert IN (' . implode(',', $attributeValues) . ')
                            AND tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikel.kVaterArtikel = ' . $productID . '
                        GROUP BY tartikel.kArtikel
                        HAVING count(*) = ' . count($combinations),
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($product->kArtikel) && $product->kArtikel > 0) {
                    return (int)$product->kArtikel;
                }
            }
            if (!isset($_SESSION['variBoxAnzahl_arr'])) {
                header('Location: ' . Shop::getURL() .
                    '/?a=' . $productID .
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
     * @param int  $parentID
     * @param int  $customerGroupID
     * @param bool $group
     * @return array
     */
    public static function getPossibleVariationCombinations(
        int $parentID,
        int $customerGroupID = 0,
        bool $group = false
    ): array {
        if (!$customerGroupID) {
            $customerGroupID = Kundengruppe::getDefaultGroupID();
        }
        $cGroupBy = $group ? 'GROUP BY teigenschaftkombiwert.kEigenschaftWert ' : '';

        return array_map(function ($e) {
            $e->kEigenschaft      = (int)$e->kEigenschaft;
            $e->kEigenschaftKombi = (int)$e->kEigenschaftKombi;
            $e->kEigenschaftWert  = (int)$e->kEigenschaftWert;

            return $e;
        },
            Shop::Container()->getDB()->query(
                'SELECT teigenschaftkombiwert.*
                    FROM teigenschaftkombiwert
                    JOIN tartikel
                        ON tartikel.kVaterArtikel = ' . $parentID . '
                        AND tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    LEFT JOIN tartikelsichtbarkeit
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL ' . $cGroupBy .
                'ORDER BY teigenschaftkombiwert.kEigenschaftWert',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            )
        );
    }

    /**
     * @former gibGewaehlteEigenschaftenZuVariKombiArtikel()
     * @param int $productID
     * @param int $nArtikelVariAufbau
     * @return array
     */
    public static function getSelectedPropertiesForVarCombiArticle(int $productID, int $nArtikelVariAufbau = 0): array
    {
        if ($productID <= 0) {
            return [];
        }
        $customerGroup  = Session::CustomerGroup()->getID();
        $properties     = [];
        $propertyValues = [];
        $exists         = true;
        // Hole EigenschaftWerte zur gewaehlten VariationKombi
        $oVariationKombiKind_arr = Shop::Container()->getDB()->query(
            'SELECT teigenschaftkombiwert.kEigenschaftWert, teigenschaftkombiwert.kEigenschaft, tartikel.kVaterArtikel
                FROM teigenschaftkombiwert
                JOIN tartikel
                    ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                    AND tartikel.kArtikel = ' . $productID . '
                LEFT JOIN tartikelsichtbarkeit
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                ORDER BY tartikel.kArtikel',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($oVariationKombiKind_arr) === 0) {
            return [];
        }
        $parentID = (int)$oVariationKombiKind_arr[0]->kVaterArtikel;
        foreach ($oVariationKombiKind_arr as $oVariationKombiKind) {
            if (!isset($propertyValues[$oVariationKombiKind->kEigenschaft])
                || !is_array($propertyValues[$oVariationKombiKind->kEigenschaft])
            ) {
                $propertyValues[(int)$oVariationKombiKind->kEigenschaft] = (int)$oVariationKombiKind->kEigenschaftWert;
            }
        }
        $attributes       = [];
        $attributeValues  = [];
        $langID           = Shop::getLanguage();
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
        if ($langID > 0 && !Sprache::isDefaultLanguageActive()) {
            $attr->cSELECT = 'teigenschaftsprache.cName AS cName_teigenschaftsprache, ';
            $attr->cJOIN   = 'LEFT JOIN teigenschaftsprache 
                                        ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                        AND teigenschaftsprache.kSprache = ' . $langID;

            $attrVal->cSELECT = 'teigenschaftwertsprache.cName AS cName_teigenschaftwertsprache, ';
            $attrVal->cJOIN   = 'LEFT JOIN teigenschaftwertsprache 
                                            ON teigenschaftwertsprache.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                            AND teigenschaftwertsprache.kSprache = ' . $langID;
        }

        $oEigenschaft_arr = Shop::Container()->getDB()->query(
            'SELECT teigenschaftwert.kEigenschaftWert, teigenschaftwert.cName, ' . $attrVal->cSELECT . '
                teigenschaftwertsichtbarkeit.kKundengruppe, teigenschaftwert.kEigenschaft, teigenschaft.cTyp, ' .
            $attr->cSELECT . ' teigenschaft.cName AS cNameEigenschaft, teigenschaft.kArtikel
                FROM teigenschaftwert
                LEFT JOIN teigenschaftwertsichtbarkeit
                    ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                    AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                JOIN teigenschaft ON teigenschaft.kEigenschaft = teigenschaftwert.kEigenschaft
                LEFT JOIN teigenschaftsichtbarkeit ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                ' . $attr->cJOIN . '
                ' . $attrVal->cJOIN . '
                WHERE teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                    AND teigenschaftwert.kEigenschaft IN (' . implode(',', $attributes) . ')
                    AND teigenschaftwert.kEigenschaftWert IN (' . implode(',', $attributeValues) . ')',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $oEigenschaftTMP_arr = Shop::Container()->getDB()->query(
            "SELECT teigenschaft.kEigenschaft,teigenschaft.cName,teigenschaft.cTyp
                FROM teigenschaft
                LEFT JOIN teigenschaftsichtbarkeit
                    ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = " . $customerGroup . "
                WHERE (teigenschaft.kArtikel = " . $parentID . "
                    OR teigenschaft.kArtikel = " . $productID . ")
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL
                    AND (teigenschaft.cTyp = 'FREIFELD'
                    OR teigenschaft.cTyp = 'PFLICHT-FREIFELD')",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        if (is_array($oEigenschaftTMP_arr)) {
            $oEigenschaft_arr = array_merge($oEigenschaft_arr, $oEigenschaftTMP_arr);
        }

        foreach ($oEigenschaft_arr as $oEigenschaft) {
            if ($oEigenschaft->cTyp !== 'FREIFELD' && $oEigenschaft->cTyp !== 'PFLICHT-FREIFELD') {
                // Ist kEigenschaft zu eigenschaftwert vorhanden
                if (self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)) {
                    $oEigenschaftWertVorhanden = Shop::Container()->getDB()->query(
                        'SELECT teigenschaftwert.kEigenschaftWert
                            FROM teigenschaftwert
                            LEFT JOIN teigenschaftwertsichtbarkeit
                                ON teigenschaftwertsichtbarkeit.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                                AND teigenschaftwertsichtbarkeit.kKundengruppe = ' . $customerGroup . '
                            WHERE teigenschaftwert.kEigenschaftWert = ' . (int)$oEigenschaft->kEigenschaftWert . '
                                AND teigenschaftwertsichtbarkeit.kEigenschaftWert IS NULL
                                AND teigenschaftwert.kEigenschaft = ' . (int)$oEigenschaft->kEigenschaft,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if ($oEigenschaftWertVorhanden->kEigenschaftWert) {
                        unset($propValue);
                        $propValue                   = new stdClass();
                        $propValue->kEigenschaftWert = $oEigenschaft->kEigenschaftWert;
                        $propValue->kEigenschaft     = $oEigenschaft->kEigenschaft;
                        $propValue->cTyp             = $oEigenschaft->cTyp;

                        if ($langID > 0 && !Sprache::isDefaultLanguageActive()) {
                            $propValue->cEigenschaftName     = $oEigenschaft->cName_teigenschaftsprache;
                            $propValue->cEigenschaftWertName = $oEigenschaft->cName_teigenschaftwertsprache;
                        } else {
                            $propValue->cEigenschaftName     = $oEigenschaft->cNameEigenschaft;
                            $propValue->cEigenschaftWertName = $oEigenschaft->cName;
                        }
                        $properties[] = $propValue;
                    } else {
                        $exists = false;
                        break;
                    }
                } elseif (!isset($_SESSION['variBoxAnzahl_arr'])) {
                    header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . (int)$_POST['anzahl'] .
                        '&r=' . R_VARWAEHLEN, true, 302);
                    exit();
                }
            } else {
                unset($propValue);
                if ($oEigenschaft->cTyp === 'PFLICHT-FREIFELD'
                    && self::hasSelectedVariationValue($oEigenschaft->kEigenschaft)
                    && strlen(self::getSelectedVariationValue($oEigenschaft->kEigenschaft)) === 0
                ) {
                    header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . (int)$_POST['anzahl'] .
                        '&r=' . R_VARWAEHLEN, true, 302);
                    exit();
                }
                $propValue                = new stdClass();
                $propValue->cFreifeldWert = StringHandler::filterXSS(
                    self::getSelectedVariationValue($oEigenschaft->kEigenschaft)
                );
                $propValue->kEigenschaft  = $oEigenschaft->kEigenschaft;
                $propValue->cTyp          = $oEigenschaft->cTyp;
                $properties[]             = $propValue;
            }
        }

        if (!$exists && !isset($_SESSION['variBoxAnzahl_arr'])) {
            header('Location: ' . Shop::getURL() .
                '/?a=' . $productID .
                '&n=' . (int)$_POST['anzahl'] .
                '&r=' . R_VARWAEHLEN, true, 301);
            exit();
        }
        if ($nArtikelVariAufbau > 0) {
            $variations = [];
            foreach ($properties as $i => $propValue) {
                $oEigenschaftWert                   = new stdClass();
                $oEigenschaftWert->kEigenschaftWert = $propValue->kEigenschaftWert;
                $oEigenschaftWert->kEigenschaft     = $propValue->kEigenschaft;
                $oEigenschaftWert->cName            = $propValue->cEigenschaftWertName;

                $variations[$i]               = new stdClass();
                $variations[$i]->kEigenschaft = $propValue->kEigenschaft;
                $variations[$i]->kArtikel     = $productID;
                $variations[$i]->cWaehlbar    = 'Y';
                $variations[$i]->cTyp         = $propValue->cTyp;
                $variations[$i]->cName        = $propValue->cEigenschaftName;
                $variations[$i]->Werte        = [];
                $variations[$i]->Werte[]      = $oEigenschaftWert;
            }

            return $variations;
        }

        return $properties;
    }

    /**
     * @param int  $productID
     * @param bool $redirect
     * @return array
     * @former gibGewaehlteEigenschaftenZuArtikel()
     * @since 5.0.0
     */
    public static function getSelectedPropertiesForArticle(int $productID, bool $redirect = true): array
    {
        $customerGroupID = Session::CustomerGroup()->getID();
        $propData        = Shop::Container()->getDB()->queryPrepared(
            'SELECT teigenschaft.kEigenschaft,teigenschaft.cName,teigenschaft.cTyp
                FROM teigenschaft
                LEFT JOIN teigenschaftsichtbarkeit 
                    ON teigenschaft.kEigenschaft = teigenschaftsichtbarkeit.kEigenschaft
                    AND teigenschaftsichtbarkeit.kKundengruppe = :cgroupid
                WHERE teigenschaft.kArtikel = :articleid
                    AND teigenschaftsichtbarkeit.kEigenschaft IS NULL',
            ['cgroupid' => $customerGroupID, 'articleid' => $productID],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $properties      = [];
        $exists          = true;
        if (!is_array($propData) || count($propData) === 0) {
            return [];
        }
        foreach ($propData as $prop) {
            $prop->kEigenschaft = (int)$prop->kEigenschaft;
            if ($prop->cTyp !== 'FREIFELD' && $prop->cTyp !== 'PFLICHT-FREIFELD') {
                if (self::hasSelectedVariationValue($prop->kEigenschaft)) {
                    $propExists = Shop::Container()->getDB()->queryPrepared(
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
                            'cgroupid'      => $customerGroupID,
                            'attribvalueid' => self::getSelectedVariationValue($prop->kEigenschaft),
                            'attribid'      => $prop->kEigenschaft
                        ],
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if ($propExists->kEigenschaftWert) {
                        $val                       = new stdClass();
                        $val->kEigenschaftWert     = (int)self::getSelectedVariationValue($prop->kEigenschaft);
                        $val->kEigenschaft         = $prop->kEigenschaft;
                        $val->cEigenschaftName     = $prop->cName;
                        $val->cEigenschaftWertName = $propExists->cName;
                        $val->cTyp                 = $prop->cTyp;
                        $properties[]              = $val;
                    } else {
                        $exists = false;
                        break;
                    }
                } elseif (!isset($_SESSION['variBoxAnzahl_arr']) && $redirect) {
                    header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . (int)$_POST['anzahl'] .
                        '&r=' . R_VARWAEHLEN, true, 302);
                    exit();
                }
            } else {
                if ($prop->cTyp === 'PFLICHT-FREIFELD'
                    && $redirect
                    && self::hasSelectedVariationValue($prop->kEigenschaft)
                    && strlen(self::getSelectedVariationValue($prop->kEigenschaft)) === 0
                ) {
                    header('Location: ' . Shop::getURL() .
                        '/?a=' . $productID .
                        '&n=' . (int)$_POST['anzahl'] .
                        '&r=' . R_VARWAEHLEN, true, 302);
                    exit();
                }
                $val                = new stdClass();
                $val->cFreifeldWert = Shop::Container()->getDB()->escape(
                    StringHandler::filterXSS(self::getSelectedVariationValue($prop->kEigenschaft))
                );
                $val->kEigenschaft  = $prop->kEigenschaft;
                $val->cTyp          = $prop->cTyp;
                $properties[]       = $val;
            }
        }

        if (!$exists && $redirect && !isset($_SESSION['variBoxAnzahl_arr'])) {
            header('Location: ' . Shop::getURL() .
                '/?a=' . $productID .
                '&n=' . (int)$_POST['anzahl'] .
                '&r=' . R_VARWAEHLEN, true, 302);
            exit();
        }

        return $properties;
    }

    /**
     * @former holeKinderzuVater()
     * @param int $parentID
     * @return array
     */
    public static function getChildren(int $parentID): array
    {
        return $parentID > 0
            ? Shop::Container()->getDB()->selectAll(
                'tartikel',
                'kVaterArtikel',
                $parentID,
                'kArtikel, kEigenschaftKombi'
            )
            : [];
    }

    /**
     * @former pruefeIstVaterArtikel()
     * @param int $productID
     * @return bool
     */
    public static function isParent(int $productID): bool
    {
        $oArtikelTMP = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $productID,
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
     * @param int  $productID
     * @param bool $info
     * @return bool|stdClass
     */
    public static function isStuecklisteKomponente(int $productID, bool $info = false)
    {
        if ($productID > 0) {
            $oObj = Shop::Container()->getDB()->select('tstueckliste', 'kArtikel', $productID);
            if (isset($oObj->kStueckliste) && $oObj->kStueckliste > 0) {
                return $info ? $oObj : true;
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
     * @param int $groupID
     * @return string|bool
     */
    protected static function getSelectedVariationValue(int $groupID)
    {
        $idx = 'eigenschaftwert_' . $groupID;
        if (isset($_POST[$idx])) {
            return $_POST[$idx];
        }

        return $_POST['eigenschaftwert'][$groupID] ?? false;
    }

    /**
     * @param int $groupID
     * @return bool
     */
    protected static function hasSelectedVariationValue(int $groupID): bool
    {
        return self::getSelectedVariationValue($groupID) !== false;
    }

    /**
     * @param Artikel  $product
     * @param object[] $variationPicturesArr
     */
    public static function addVariationPictures(Artikel $product, $variationPicturesArr)
    {
        if (is_array($variationPicturesArr) && count($variationPicturesArr) > 0) {
            $product->Bilder = array_filter($product->Bilder, function ($item) {
                return !(isset($item->isVariation) && $item->isVariation);
            });
            if (count($variationPicturesArr) === 1) {
                array_unshift($product->Bilder, $variationPicturesArr[0]);
            } else {
                $product->Bilder = array_merge($product->Bilder, $variationPicturesArr);
            }

            $nNr = 1;
            foreach (array_keys($product->Bilder) as $key) {
                $product->Bilder[$key]->nNr = $nNr++;
            }

            $product->cVorschaubild = $product->Bilder[0]->cURLKlein;
        }
    }

    /**
     * @param Artikel $product
     * @param float   $price
     * @param int     $amount
     * @return stdClass
     */
    public static function getBasePriceUnit(Artikel $product, $price, $amount): stdClass
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
            'fGrundpreisMenge' => $product->fGrundpreisMenge,
            'fMassMenge'       => $product->fMassMenge * $amount,
            'fBasePreis'       => $price / $product->fVPEWert,
            'fVPEWert'         => (float)$product->fVPEWert,
            'cVPEEinheit'      => $product->cVPEEinheit,
        ];

        $gpUnit   = UnitsOfMeasure::getUnit($product->kGrundpreisEinheit);
        $massUnit = UnitsOfMeasure::getUnit($product->kMassEinheit);

        if (isset($gpUnit, $massUnit, $unitMappings[$gpUnit->cCode], $unitMappings[$massUnit->cCode])) {
            $fFactor    = UnitsOfMeasure::getConversionFaktor($unitMappings[$massUnit->cCode], $massUnit->cCode);
            $threshold  = 250 * $fFactor / 1000;
            $nAmount    = 1;
            $mappedCode = $unitMappings[$massUnit->cCode];

            if ($threshold > 0 && $result->fMassMenge > $threshold) {
                $result->fGrundpreisMenge = $nAmount;
                $result->fMassMenge       /= $fFactor;
                $result->fVPEWert         = $result->fMassMenge / $amount / $result->fGrundpreisMenge;
                $result->fBasePreis       = $price / $result->fVPEWert;
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
     * @param int $productID
     * @param int $es0
     * @param int $esWert0
     * @param int $es1
     * @param int $esWert1
     * @return int
     * @since 5.0.0
     * @former findeKindArtikelZuEigenschaft()
     */
    public static function getChildProdctIDByAttribute(
        int $productID,
        int $es0,
        int $esWert0,
        int $es1 = 0,
        int $esWert1 = 0
    ): int {
        if ($es0 > 0 && $esWert0 > 0) {
            $cSQLJoin   = ' JOIN teigenschaftkombiwert
                          ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                          AND teigenschaftkombiwert.kEigenschaft = ' . $es0 . '
                          AND teigenschaftkombiwert.kEigenschaftWert = ' . $esWert0;
            $cSQLHaving = '';
            if ($es1 > 0 && $esWert1 > 0) {
                $cSQLJoin = ' JOIN teigenschaftkombiwert
                              ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                              AND teigenschaftkombiwert.kEigenschaft IN(' . $es0 . ', ' . $es1 . ')
                              AND teigenschaftkombiwert.kEigenschaftWert IN(' . $esWert0 . ', ' . $esWert1 . ')';

                $cSQLHaving = ' HAVING COUNT(*) = 2';
            }
            $product = Shop::Container()->getDB()->query(
                'SELECT kArtikel
                    FROM tartikel' . $cSQLJoin . '
                    WHERE tartikel.kVaterArtikel = ' . $productID . '
                    GROUP BY teigenschaftkombiwert.kEigenschaftKombi' . $cSQLHaving,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($product->kArtikel) && count($product->kArtikel) > 0) {
                return (int)$product->kArtikel;
            }
        }

        return 0;
    }

    /**
     * @param int  $productID
     * @param bool $visibility
     * @return array
     * @since 5.0.0
     * @former gibVarKombiEigenschaftsWerte()
     */
    public static function getVarCombiAttributeValues(int $productID, bool $visibility = true): array
    {
        $attributeValues = [];
        if ($productID <= 0 || !self::isVariChild($productID)) {
            return $attributeValues;
        }
        $product                           = new Artikel();
        $productOptions                    = new stdClass();
        $productOptions->nMerkmale         = 0;
        $productOptions->nAttribute        = 0;
        $productOptions->nArtikelAttribute = 0;
        $productOptions->nVariationKombi   = 1;
        if (!$visibility) {
            $productOptions->nKeineSichtbarkeitBeachten = 1;
        }

        $product->fuelleArtikel($productID, $productOptions);

        if ($product->oVariationenNurKind_arr !== null
            && is_array($product->oVariationenNurKind_arr)
            && count($product->oVariationenNurKind_arr) > 0
        ) {
            foreach ($product->oVariationenNurKind_arr as $child) {
                $attributeValue                       = new stdClass();
                $attributeValue->kEigenschaftWert     = $child->Werte[0]->kEigenschaftWert;
                $attributeValue->kEigenschaft         = $child->kEigenschaft;
                $attributeValue->cEigenschaftName     = $child->cName;
                $attributeValue->cEigenschaftWertName = $child->Werte[0]->cName;

                $attributeValues[] = $attributeValue;
            }
        }

        return $attributeValues;
    }

    /**
     * @param array $variations
     * @param int   $kEigenschaft
     * @param int   $kEigenschaftWert
     * @return bool|object
     * @former findeVariation()
     * @since 5.0.0
     */
    public static function findVariation(array $variations, int $kEigenschaft, int $kEigenschaftWert): bool
    {
        foreach ($variations as $oVariation) {
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
     * @param Artikel $product
     * @param string  $config
     * @return int
     * @former gibVerfuegbarkeitsformularAnzeigen()
     * @since 5.0.0
     */
    public static function showAvailabilityForm(Artikel $product, string $config): int
    {
        if ($config !== 'N'
            && ((int)$product->inWarenkorbLegbar === INWKNICHTLEGBAR_LAGER
                || (int)$product->inWarenkorbLegbar === INWKNICHTLEGBAR_LAGERVAR
                || ($product->fLagerbestand <= 0 && $product->cLagerKleinerNull === 'Y'))
        ) {
            switch ($config) {
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
     * @param int       $productID
     * @param bool|null $isParent
     * @return stdClass|null
     * @former gibArtikelXSelling()
     * @since 5.0.0
     */
    public static function getXSelling(int $productID, $isParent = null)
    {
        if ($productID <= 0) {
            return null;
        }
        $xSelling = new stdClass();
        $config   = Shop::getSettings([CONF_ARTIKELDETAILS])['artikeldetails'];
        if ($config['artikeldetails_xselling_standard_anzeigen'] === 'Y') {
            $xSelling->Standard = new stdClass();
            $stockFilterSQL     = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $xsell              = Shop::Container()->getDB()->queryPrepared(
                'SELECT txsell.*, txsellgruppe.cName, txsellgruppe.cBeschreibung
                    FROM txsell
                    JOIN tartikel
                        ON txsell.kXSellArtikel = tartikel.kArtikel 
                    LEFT JOIN txsellgruppe
                        ON txsellgruppe.kXSellGruppe = txsell.kXSellGruppe
                        AND txsellgruppe.kSprache = :lid
                    WHERE txsell.kArtikel = :aid' . $stockFilterSQL . '
                    ORDER BY tartikel.cName',
                ['lid' => Shop::getLanguageID(), 'aid' => $productID],
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
                        $product             = (new Artikel())->fuelleArtikel($xs->kXSellArtikel, $defaultOptions);
                        if ($product !== null && $product->kArtikel > 0 && $product->aufLagerSichtbarkeit()) {
                            $group->Artikel[] = $product;
                        }
                    }
                    $xSelling->Standard->XSellGruppen[] = $group;
                }
            }
        }

        if ($config['artikeldetails_xselling_kauf_anzeigen'] === 'Y') {
            $anzahl = (int)$config['artikeldetails_xselling_kauf_anzahl'];
            if ($isParent === null) {
                $isParent = self::isParent($productID);
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
                    "SELECT {$productID} AS kArtikel,
                        {$selectorXSellArtikel} AS kXSellArtikel,
                        SUM(txsellkauf.nAnzahl) nAnzahl
                        FROM txsellkauf
                        JOIN tartikel ON tartikel.kArtikel = txsellkauf.kXSellArtikel
                        WHERE (txsellkauf.kArtikel IN (
                                SELECT tartikel.kArtikel
                                FROM tartikel
                                WHERE tartikel.kVaterArtikel = {$productID}
                            ) OR txsellkauf.kArtikel = {$productID})
                            AND {$filterXSellParentArtikel} != {$productID}
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
                        WHERE txsellkauf.kArtikel = {$productID}
                            AND (tartikel.kVaterArtikel != (
                                SELECT tartikel.kVaterArtikel
                                FROM tartikel
                                WHERE tartikel.kArtikel = {$productID}
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
                    $productID,
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
                    $product = new Artikel();
                    $product->fuelleArtikel($xs->kXSellArtikel, $defaultOptions);
                    if ($product->kArtikel > 0 && $product->aufLagerSichtbarkeit()) {
                        $xSelling->Kauf->Artikel[] = $product;
                    }
                }
            }
        }
        executeHook(HOOK_ARTIKEL_INC_XSELLING, [
            'kArtikel' => $productID,
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
            $missingData = self::getMissingProductQuestionFormData();
            Shop::Smarty()->assign('fehlendeAngaben_fragezumprodukt', $missingData);
            $resultCode = FormHelper::eingabenKorrekt($missingData);

            executeHook(HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT_PLAUSI);

            if ($resultCode) {
                if (!self::checkProductQuestionFloodProtection((int)$conf['artikeldetails']['produktfrage_sperre_minuten'])) {
                    $checkBox      = new CheckBox();
                    $kKundengruppe = Session\Session::CustomerGroup()->getID();
                    $oAnfrage      = self::getProductQuestionFormDefaults();

                    executeHook(HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT);
                    if (empty($oAnfrage->cNachname)) {
                        $oAnfrage->cNachname = '';
                    }
                    if (empty($oAnfrage->cVorname)) {
                        $oAnfrage->cVorname = '';
                    }
                    $checkBox->triggerSpecialFunction(
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
            } elseif (isset($missingData['email']) && $missingData['email'] === 3) {
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
        $checkBox = new CheckBox();
        $ret      = array_merge(
            $ret,
            $checkBox->validateCheckBox(
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

        $conf             = Shop::getSettings([CONF_EMAILS, CONF_ARTIKELDETAILS, CONF_GLOBAL]);
        $data             = new stdClass();
        $data->tartikel   = $GLOBALS['AktuellerArtikel'];
        $data->tnachricht = self::getProductQuestionFormDefaults();
        $empfaengerName   = '';
        if ($data->tnachricht->cVorname) {
            $empfaengerName = $data->tnachricht->cVorname . ' ';
        }
        if ($data->tnachricht->cNachname) {
            $empfaengerName .= $data->tnachricht->cNachname;
        }
        if ($data->tnachricht->cFirma) {
            if ($data->tnachricht->cNachname || $data->tnachricht->cVorname) {
                $empfaengerName .= ' - ';
            }
            $empfaengerName .= $data->tnachricht->cFirma;
        }
        $mail = new stdClass();
        if (isset($conf['artikeldetails']['artikeldetails_fragezumprodukt_email'])) {
            $mail->toEmail = $conf['artikeldetails']['artikeldetails_fragezumprodukt_email'];
        }
        if (empty($mail->toEmail)) {
            $mail->toEmail = $conf['emails']['email_master_absender'];
        }
        $mail->toName       = $conf['global']['global_shopname'];
        $mail->replyToEmail = $data->tnachricht->cMail;
        $mail->replyToName  = $empfaengerName;
        $data->mail         = $mail;

        sendeMail(MAILTEMPLATE_PRODUKTANFRAGE, $data);

        if ($conf['artikeldetails']['produktfrage_kopiekunde'] === 'Y') {
            $mail->toEmail      = $data->tnachricht->cMail;
            $mail->toName       = $empfaengerName;
            $mail->replyToEmail = $data->tnachricht->cMail;
            $mail->replyToName  = $empfaengerName;
            $data->mail         = $mail;
            sendeMail(MAILTEMPLATE_PRODUKTANFRAGE, $data);
        }
        $history             = new stdClass();
        $history->kSprache   = Shop::getLanguage();
        $history->kArtikel   = Shop::$kArtikel;
        $history->cAnrede    = $data->tnachricht->cAnrede;
        $history->cVorname   = $data->tnachricht->cVorname;
        $history->cNachname  = $data->tnachricht->cNachname;
        $history->cFirma     = $data->tnachricht->cFirma;
        $history->cTel       = $data->tnachricht->cTel;
        $history->cMobil     = $data->tnachricht->cMobil;
        $history->cFax       = $data->tnachricht->cFax;
        $history->cMail      = $data->tnachricht->cMail;
        $history->cNachricht = $data->tnachricht->cNachricht;
        $history->cIP        = RequestHelper::getIP();
        $history->dErstellt  = 'now()';

        $inquiryID                     = Shop::Container()->getDB()->insert('tproduktanfragehistory', $history);
        $GLOBALS['PositiveFeedback'][] = Shop::Lang()->get('thankYouForQuestion', 'messages');
        if (isset($_SESSION['Kampagnenbesucher'])) {
            Kampagne::setCampaignAction(KAMPAGNE_DEF_FRAGEZUMPRODUKT, $inquiryID, 1.0);
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
            'SELECT kProduktanfrageHistory
                FROM tproduktanfragehistory
                WHERE cIP = :ip
                    AND date_sub(now(), INTERVAL :min MINUTE) < dErstellt',
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
        if (!isset($_POST['a'], $conf['artikeldetails']['benachrichtigung_nutzen'])
            || (int)$_POST['a'] <= 0
            || $conf['artikeldetails']['benachrichtigung_nutzen'] === 'N'
        ) {
            return;
        }
        $missingData = self::getMissingAvailibilityFormData();
        Shop::Smarty()->assign('fehlendeAngaben_benachrichtigung', $missingData);
        $resultCode = FormHelper::eingabenKorrekt($missingData);

        executeHook(HOOK_ARTIKEL_INC_BENACHRICHTIGUNG_PLAUSI);
        if ($resultCode) {
            if (!self::checkAvailibityFormFloodProtection($conf['artikeldetails']['benachrichtigung_sperre_minuten'])) {
                $inquiry            = self::getAvailabilityFormDefaults();
                $inquiry->kSprache  = Shop::getLanguage();
                $inquiry->kArtikel  = (int)$_POST['a'];
                $inquiry->cIP       = RequestHelper::getIP();
                $inquiry->dErstellt = 'now()';
                $inquiry->nStatus   = 0;
                $checkBox           = new CheckBox();
                $customerGroupID    = Session::CustomerGroup()->getID();
                if (empty($inquiry->cNachname)) {
                    $inquiry->cNachname = '';
                }
                if (empty($inquiry->cVorname)) {
                    $inquiry->cVorname = '';
                }
                executeHook(HOOK_ARTIKEL_INC_BENACHRICHTIGUNG, ['Benachrichtigung' => $inquiry]);
                $checkBox->triggerSpecialFunction(
                    CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT,
                    $customerGroupID,
                    true,
                    $_POST,
                    ['oKunde' => $inquiry, 'oNachricht' => $inquiry]
                )->checkLogging(CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT, $customerGroupID, $_POST, true);

                $inquiryID = Shop::Container()->getDB()->queryPrepared(
                    'INSERT INTO tverfuegbarkeitsbenachrichtigung 
                        (cVorname, cNachname, cMail, kSprache, kArtikel, cIP, dErstellt, nStatus) 
                        VALUES 
                        (:cVorname, :cNachname, :cMail, :kSprache, :kArtikel, :cIP, now(), :nStatus)
                        ON DUPLICATE KEY UPDATE 
                            cVorname = :cVorname, cNachname = :cNachname, ksprache = :kSprache, 
                            cIP = :cIP, dErstellt = now(), nStatus = :nStatus', get_object_vars($inquiry),
                    \DB\ReturnType::LAST_INSERTED_ID
                );
                if (isset($_SESSION['Kampagnenbesucher'])) {
                    Kampagne::setCampaignAction(KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE, $inquiryID, 1.0);
                }
                $GLOBALS['PositiveFeedback'][] = Shop::Lang()->get(
                    'thankYouForNotificationSubscription',
                    'messages'
                );
            } else {
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('notificationNotPossible', 'messages');
            }
        } elseif (isset($missingData['email']) && $missingData['email'] === 3) {
            $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('blockedEmail');
        } else {
            Shop::Smarty()->assign('Benachrichtigung', self::getAvailabilityFormDefaults());
            $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('fillOutNotification', 'messages');
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
     * @param int $productID
     * @param int $categoryID
     * @return stdClass
     * @former gibNaviBlaettern()
     * @since 5.0.0
     */
    public static function getProductNavigation(int $productID, int $categoryID): stdClass
    {
        $nav             = new stdClass();
        $customerGroupID = Session::CustomerGroup()->getID();
        // Wurde der Artikel von der Artikelübersicht aus angeklickt?
        if ($productID > 0
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
            $nArrayPos          = $collection->search($productID, true);
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
                $nav->naechsterArtikel = (new Artikel())
                    ->fuelleArtikel($kArtikelNaechster, Artikel::getDefaultOptions());
                if ($nav->naechsterArtikel === null) {
                    unset($nav->naechsterArtikel);
                }
            }
            if ($kArtikelVorheriger > 0) {
                $nav->vorherigerArtikel = (new Artikel())
                    ->fuelleArtikel($kArtikelVorheriger, Artikel::getDefaultOptions());
                if ($nav->vorherigerArtikel->kArtikel === null) {
                    unset($nav->vorherigerArtikel);
                }
            }
        }
        // Ist der Besucher nicht von der Artikelübersicht gekommen?
        if ($categoryID > 0 && (!isset($nav->vorherigerArtikel) && !isset($nav->naechsterArtikel))) {
            $stockFilter = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $prev        = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tpreise, tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tkategorieartikel.kKategorie = ' . $categoryID . '
                        AND tpreise.kArtikel = tartikel.kArtikel
                        AND tartikel.kArtikel < ' . $productID . '
                        AND tpreise.kKundengruppe = ' . $customerGroupID . ' ' . $stockFilter . '
                    ORDER BY tartikel.kArtikel DESC
                    LIMIT 1',
                \DB\ReturnType::SINGLE_OBJECT
            );
            $next        = Shop::Container()->getDB()->query(
                'SELECT tartikel.kArtikel
                    FROM tkategorieartikel, tpreise, tartikel
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        AND tartikel.kArtikel = tkategorieartikel.kArtikel
                        AND tkategorieartikel.kKategorie = ' . $categoryID . '
                        AND tpreise.kArtikel = tartikel.kArtikel
                        AND tartikel.kArtikel > ' . $productID . '
                        AND tpreise.kKundengruppe = ' . $customerGroupID . ' ' . $stockFilter . '
                    ORDER BY tartikel.kArtikel
                    LIMIT 1',
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (!empty($prev->kArtikel)) {
                $nav->vorherigerArtikel = (new Artikel())
                    ->fuelleArtikel($prev->kArtikel, Artikel::getDefaultOptions());
            }
            if (!empty($next->kArtikel)) {
                $nav->naechsterArtikel = (new Artikel())
                    ->fuelleArtikel($next->kArtikel, Artikel::getDefaultOptions());
            }
        }

        return $nav;
    }

    /**
     * @param int $attributeValue
     * @return array
     * @former gibNichtErlaubteEigenschaftswerte()
     * @since 5.0.0
     */
    public static function getNonAllowedAttributeValues(int $attributeValue): array
    {
        $nonAllowed  = Shop::Container()->getDB()->selectAll(
            'teigenschaftwertabhaengigkeit',
            'kEigenschaftWert',
            $attributeValue,
            'kEigenschaftWertZiel AS EigenschaftWert'
        );
        $nonAllowed2 = Shop::Container()->getDB()->selectAll(
            'teigenschaftwertabhaengigkeit',
            'kEigenschaftWertZiel',
            $attributeValue,
            'kEigenschaftWert AS EigenschaftWert'
        );

        return array_merge($nonAllowed, $nonAllowed2);
    }

    /**
     * @param null|string|array $redirectParam
     * @param bool              $renew
     * @param null|Artikel      $product
     * @param null|float        $amount
     * @param int               $configItemID
     * @return array
     * @former baueArtikelhinweise()
     * @since 5.0.0
     */
    public static function getProductMessages(
        $redirectParam = null,
        $renew = false,
        $product = null,
        $amount = null,
        $configItemID = 0
    ): array {
        if ($redirectParam === null && isset($_GET['r'])) {
            $redirectParam = $_GET['r'];
        }
        if ($renew || !isset($GLOBALS['Artikelhinweise']) || !is_array($GLOBALS['Artikelhinweise'])) {
            $GLOBALS['Artikelhinweise'] = [];
        }
        if ($renew || !isset($GLOBALS['PositiveFeedback']) || !is_array($GLOBALS['PositiveFeedback'])) {
            $GLOBALS['PositiveFeedback'] = [];
        }
        if ($redirectParam) {
            $messages = is_array($redirectParam) ? $redirectParam : explode(',', $redirectParam);
            $messages = array_unique($messages);

            foreach ($messages as $message) {
                switch ($message) {
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
                        $GLOBALS['Artikelhinweise'][] = lang_mindestbestellmenge(
                            $product ?? $GLOBALS['AktuellerArtikel'],
                            $amount ?? $_GET['n'],
                            $configItemID
                        );
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
        if (RequestHelper::verifyGPCDataInt('produktTag') !== 1) {
            return null;
        }
        $tag             = StringHandler::filterXSS(RequestHelper::verifyGPDataString('tag'));
        $variKindArtikel = RequestHelper::verifyGPDataString('variKindArtikel');
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
            Shop::Container()->getDB()->query(
                "DELETE FROM ttagkunde 
                    WHERE dZeit < DATE_SUB(now(),INTERVAL 1 MONTH)",
                \DB\ReturnType::DEFAULT
            );
            if (($conf['artikeldetails']['tagging_freischaltung'] === 'Y'
                    && isset($_SESSION['Kunde']->kKunde)
                    && $_SESSION['Kunde']->kKunde > 0)
                || $conf['artikeldetails']['tagging_freischaltung'] === 'O'
            ) {
                $ip = RequestHelper::getIP();
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
                } else {
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
                            $tagArticle->nAnzahlTagging = (int)$tagArticle->nAnzahlTagging + 1;
                            $tagArticle->updateInDB();
                        } else {
                            $tagArticle->kTag           = $kTag;
                            $tagArticle->kArtikel       = (int)$product->kArtikel;
                            $tagArticle->nAnzahlTagging = 1;
                            $tagArticle->insertInDB();
                        }

                        if (!empty($variKindArtikel)) {
                            $childTag = new TagArticle($kTag, (int)$variKindArtikel);
                            if (!empty($childTag->kTag)) {
                                $childTag->nAnzahlTagging = (int)$childTag->nAnzahlTagging + 1;
                                $childTag->updateInDB();
                            } else {
                                $childTag->kTag           = $kTag;
                                $childTag->kArtikel       = (int)$variKindArtikel;
                                $childTag->nAnzahlTagging = 1;
                                $childTag->insertInDB();
                            }
                        }
                    } else {
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
        $navigation         = new stdClass();
        $navigation->nAktiv = 0;
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
            $navigation->nSeiten             = $nSeiten;
            $navigation->nVoherige           = $nVoherige;
            $navigation->nNaechste           = $nNaechste;
            $navigation->nAnfang             = $nAnfang;
            $navigation->nEnde               = $nEnde;
            $navigation->nBlaetterAnzahl_arr = $nBlaetterAnzahl_arr;
            $navigation->nAktiv              = 1;
        }

        $navigation->nSterne        = $ratingStars;
        $navigation->nAktuelleSeite = $ratingPage;
        $navigation->nVon           = (($navigation->nAktuelleSeite - 1) * $pageCount) + 1;
        $navigation->nBis           = $navigation->nAktuelleSeite * $pageCount;

        if ($navigation->nBis > $ratingCount) {
            --$navigation->nBis;
        }

        return $navigation;
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
            case 'f01':
                $error = Shop::Lang()->get('bewertungWrongdata', 'errorMessages');
                break;
            case 'f02':
                $error = Shop::Lang()->get('bewertungBewexist', 'errorMessages');
                break;
            case 'f03':
                $error = Shop::Lang()->get('bewertungBewnotbought', 'errorMessages');
                break;
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
     * @param int $productID
     * @return array
     * @former holeAehnlicheArtikel()
     * @since 5.0.0
     */
    public static function getSimilarProductsByID(int $productID): array
    {
        $products        = [];
        $cLimit          = ' LIMIT 3';
        $conf            = Shop::getSettings([CONF_ARTIKELDETAILS]);
        $oXSeller        = self::getXSelling($productID);
        $xsellProductIDs = [];
        if (isset($oXSeller->Standard->XSellGruppen)
            && is_array($oXSeller->Standard->XSellGruppen)
            && count($oXSeller->Standard->XSellGruppen) > 0
        ) {
            foreach ($oXSeller->Standard->XSellGruppen as $oXSeller) {
                if (is_array($oXSeller->Artikel) && count($oXSeller->Artikel) > 0) {
                    foreach ($oXSeller->Artikel as $product) {
                        $product->kArtikel = (int)$product->kArtikel;
                        if (!in_array($product->kArtikel, $xsellProductIDs, true)) {
                            $xsellProductIDs[] = $product->kArtikel;
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
                    foreach ($oXSeller->Artikel as $product) {
                        $product->kArtikel = (int)$product->kArtikel;
                        if (!in_array($product->kArtikel, $xsellProductIDs, true)) {
                            $xsellProductIDs[] = $product->kArtikel;
                        }
                    }
                }
            }
        }

        $xsellSQL = count($xsellProductIDs) > 0
            ? ' AND tartikel.kArtikel NOT IN (' . implode(',', $xsellProductIDs) . ') '
            : '';

        if ($productID > 0) {
            if ((int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0) {
                $cLimit = " LIMIT " . (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'];
            }
            $stockFilterSQL    = Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
            $customerGroupID   = Session::CustomerGroup()->getID();
            $productAttributes = Shop::Container()->getDB()->queryPrepared(
                'SELECT tartikelmerkmal.kArtikel, tartikel.kVaterArtikel
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
                        AND tartikelmerkmal.kArtikel != :kArtikel ' . $stockFilterSQL . ' ' . $xsellSQL . '
                    GROUP BY tartikelmerkmal.kArtikel
                    ORDER BY COUNT(tartikelmerkmal.kMerkmal) DESC
                    ' . $cLimit,
                [
                    'kArtikel'        => $productID,
                    'customerGroupID' => $customerGroupID
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (is_array($productAttributes) && count($productAttributes) > 0) {
                $defaultOptions = Artikel::getDefaultOptions();
                foreach ($productAttributes as $oArtikelMerkmal) {
                    $product = new Artikel();
                    $id      = ($oArtikelMerkmal->kVaterArtikel > 0)
                        ? $oArtikelMerkmal->kVaterArtikel
                        : $oArtikelMerkmal->kArtikel;
                    $product->fuelleArtikel($id, $defaultOptions);
                    if ($product->kArtikel > 0) {
                        $products[] = $product;
                    }
                }
            } else { // Falls es keine Merkmale gibt, in tsuchcachetreffer und ttagartikel suchen
                $searchCacheHits = Shop::Container()->getDB()->query(
                    'SELECT tsuchcachetreffer.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kSuchCache
                            FROM tsuchcachetreffer
                            WHERE kArtikel = ' . $productID . '
                            AND nSort <= 10
                        ) AS ssSuchCache
                        JOIN tsuchcachetreffer 
                            ON tsuchcachetreffer.kSuchCache = ssSuchCache.kSuchCache
                            AND tsuchcachetreffer.kArtikel != ' . $productID . '
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tsuchcachetreffer.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                        JOIN tartikel 
                            ON tartikel.kArtikel = tsuchcachetreffer.kArtikel
                            AND tartikel.kVaterArtikel != ' . $productID . '
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL ' . $stockFilterSQL . ' ' . $xsellSQL . '
                        GROUP BY tsuchcachetreffer.kArtikel
                        ORDER BY COUNT(*) DESC' . $cLimit,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                if (count($searchCacheHits) > 0) {
                    $defaultOptions = Artikel::getDefaultOptions();
                    foreach ($searchCacheHits as $oArtikelSuchcacheTreffer) {
                        $product = new Artikel();
                        $id      = ($oArtikelSuchcacheTreffer->kVaterArtikel > 0)
                            ? $oArtikelSuchcacheTreffer->kVaterArtikel
                            : $oArtikelSuchcacheTreffer->kArtikel;
                        $product->fuelleArtikel($id, $defaultOptions);
                        if ($product->kArtikel > 0) {
                            $products[] = $product;
                        }
                    }
                } else {
                    $taggedProducts = Shop::Container()->getDB()->query(
                        'SELECT ttagartikel.kArtikel, tartikel.kVaterArtikel
                            FROM
                            (
                                SELECT kTag
                                    FROM ttagartikel
                                    WHERE kArtikel = ' . $productID . '
                            ) AS ssTag
                            JOIN ttagartikel 
                                ON ttagartikel.kTag = ssTag.kTag
                                AND ttagartikel.kArtikel != ' . $productID . '
                            LEFT JOIN tartikelsichtbarkeit 
                                ON ttagartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                            JOIN tartikel 
                                ON tartikel.kArtikel = ttagartikel.kArtikel
                                AND tartikel.kVaterArtikel != ' . $productID . '
                            WHERE tartikelsichtbarkeit.kArtikel IS NULL ' . $stockFilterSQL . ' ' . $xsellSQL . '
                            GROUP BY ttagartikel.kArtikel
                            ORDER BY COUNT(*) DESC' . $cLimit,
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    $defaultOptions = Artikel::getDefaultOptions();
                    foreach ($taggedProducts as $taggedProduct) {
                        $product = new Artikel();
                        $id      = $taggedProduct->kVaterArtikel > 0
                            ? $taggedProduct->kVaterArtikel
                            : $taggedProduct->kArtikel;
                        $product->fuelleArtikel((int)$id, $defaultOptions);
                        if ($product->kArtikel > 0) {
                            $products[] = $product;
                        }
                    }
                }
            }
        }
        executeHook(HOOK_ARTIKEL_INC_AEHNLICHEARTIKEL, ['oArtikel_arr' => &$products]);

        foreach ($products as $i => $product) {
            foreach ($xsellProductIDs as $kArtikelXSellerKey) {
                if ($product->kArtikel === $kArtikelXSellerKey) {
                    unset($products[$i]);
                }
            }
        }

        return $products;
    }

    /**
     * @param int $productID
     * @return bool
     * @former ProductBundleWK()
     * @since 5.0.0
     */
    public static function addProductBundleToCart(int $productID): bool
    {
        if ($productID <= 0) {
            return false;
        }
        $options                             = new stdClass();
        $options->nMerkmale                  = 1;
        $options->nAttribute                 = 1;
        $options->nArtikelAttribute          = 1;
        $options->nKeineSichtbarkeitBeachten = 1;

        return WarenkorbHelper::addProductIDToCart($productID, 1, [], 0, false, 0, $options);
    }

    /**
     * @param int       $productID
     * @param float|int $amount
     * @param array     $variations
     * @param array     $configGroups
     * @param array     $configGroupAmounts
     * @param array     $configItemAmounts
     * @return stdClass|null
     * @since 5.0.0
     */
    public static function buildConfig(
        int $productID,
        $amount,
        $variations,
        $configGroups,
        $configGroupAmounts,
        $configItemAmounts
    ) {
        $config                  = new stdClass;
        $config->fAnzahl         = $amount;
        $config->fGesamtpreis    = [0.0, 0.0];
        $config->cPreisLocalized = [];
        $config->cPreisString    = Shop::Lang()->get('priceAsConfigured', 'productDetails');

        if (!class_exists('Konfigurator') || !Konfigurator::validateKonfig($productID)) {
            return null;
        }
        foreach ($variations as $i => $nVariation) {
            $_POST['eigenschaftwert_' . $i] = $nVariation;
        }
        if (self::isParent($productID)) {
            $productID          = self::getArticleForParent($productID);
            $selectedProperties = self::getSelectedPropertiesForVarCombiArticle($productID);
        } else {
            $selectedProperties = self::getSelectedPropertiesForArticle($productID, false);
        }

        $product                               = new Artikel();
        $productOptions                        = new stdClass();
        $productOptions->nKonfig               = 1;
        $productOptions->nAttribute            = 1;
        $productOptions->nArtikelAttribute     = 1;
        $productOptions->nVariationKombi       = 1;
        $productOptions->nVariationKombiKinder = 1;
        $product->fuelleArtikel($productID, $productOptions);

        $config->nMinDeliveryDays      = $product->nMinDeliveryDays;
        $config->nMaxDeliveryDays      = $product->nMaxDeliveryDays;
        $config->cEstimatedDelivery    = $product->cEstimatedDelivery;
        $config->Lageranzeige          = new stdClass();
        $config->Lageranzeige->nStatus = $product->Lageranzeige->nStatus;

        $amount = max($amount, 1);
        if ($product->cTeilbar !== 'Y' && (int)$amount != $amount) {
            $amount = (int)$amount;
        }

        $config->fGesamtpreis = [
            TaxHelper::getGross(
                $product->gibPreis($amount, $selectedProperties),
                TaxHelper::getSalesTax($product->kSteuerklasse)
            ) * $amount,
            $product->gibPreis($amount, $selectedProperties) * $amount
        ];
        $config->oKonfig_arr  = $product->oKonfig_arr;

        foreach ($configGroups as $i => $data) {
            $configGroups[$i] = (array)$data;
        }
        /** @var Konfiggruppe $configGroup */
        foreach ($config->oKonfig_arr as $i => &$configGroup) {
            $configGroup->bAktiv = false;
            $configGroupID       = $configGroup->getKonfiggruppe();
            $configItems         = $configGroups[$configGroupID] ?? [];
            foreach ($configGroup->oItem_arr as $j => &$configItem) {
                /** @var Konfigitem $configItem */
                $configItemID        = $configItem->getKonfigitem();
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
                    $configItem->fAnzahlWK *= $amount;
                }
                $configItem->bAktiv = in_array($configItemID, $configItems);

                if ($configItem->bAktiv) {
                    $config->fGesamtpreis[0] += $configItem->getPreis() * $configItem->fAnzahlWK;
                    $config->fGesamtpreis[1] += $configItem->getPreis(true) * $configItem->fAnzahlWK;
                    $configGroup->bAktiv     = true;
                    if ($configItem->getArtikel() !== null
                        && $configItem->getArtikel()->cLagerBeachten === 'Y'
                        && $config->nMinDeliveryDays < $configItem->getArtikel()->nMinDeliveryDays
                    ) {
                        $config->nMinDeliveryDays      = $configItem->getArtikel()->nMinDeliveryDays;
                        $config->nMaxDeliveryDays      = $configItem->getArtikel()->nMaxDeliveryDays;
                        $config->cEstimatedDelivery    = $configItem->getArtikel()->cEstimatedDelivery;
                        $config->Lageranzeige->nStatus = $configItem->getArtikel()->Lageranzeige->nStatus;
                    }
                }
            }
            unset($configItem);
            $configGroup->oItem_arr = array_values($configGroup->oItem_arr);
        }
        unset($configGroup);
        if (Session::CustomerGroup()->mayViewPrices()) {
            $config->cPreisLocalized = [
                Preise::getLocalizedPriceString($config->fGesamtpreis[0]),
                Preise::getLocalizedPriceString($config->fGesamtpreis[1])
            ];
        } else {
            $config->cPreisLocalized = [Shop::Lang()->get('priceHidden')];
        }
        $config->nNettoPreise = Session::CustomerGroup()->getIsMerchant();

        return $config;
    }

    /**
     * @param int       $configID
     * @param JTLSmarty $smarty
     * @former holeKonfigBearbeitenModus()
     * @since 5.0.0
     */
    public static function getEditConfigMode($configID, $smarty)
    {
        $cart = Session::Cart();
        if (!isset($cart->PositionenArr[$configID]) || !class_exists('Konfigitem')) {
            return;
        }
        /** @var WarenkorbPos $basePosition */
        $basePosition = $cart->PositionenArr[$configID];
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
                   ->assign('kEditKonfig', $configID)
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
