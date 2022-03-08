<?php declare(strict_types=1);

use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\Customer\Customer;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageModel;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;

/**
 * @param int[] $ids
 * @return bool
 */
function loescheKupons(array $ids): bool
{
    if (count($ids) === 0) {
        return false;
    }
    $ids      = array_map('\intval', $ids);
    $affected = Shop::Container()->getDB()->getAffectedRows(
        'DELETE tkupon, tkuponsprache, tkuponkunde, tkuponbestellung
            FROM tkupon
            LEFT JOIN tkuponsprache
              ON tkuponsprache.kKupon = tkupon.kKupon
            LEFT JOIN tkuponkunde
              ON tkuponkunde.kKupon = tkupon.kKupon
            LEFT JOIN tkuponbestellung
              ON tkuponbestellung.kKupon = tkupon.kKupon
            WHERE tkupon.kKupon IN(' . implode(',', $ids) . ')'
    );

    return $affected >= count($ids);
}

/**
 * @param int $id
 * @return array - key = lang-iso ; value = localized coupon name
 */
function getCouponNames(int $id): array
{
    $names = [];
    if (!$id) {
        return $names;
    }
    foreach (Shop::Container()->getDB()->selectAll('tkuponsprache', 'kKupon', $id) as $coupon) {
        $names[$coupon->cISOSprache] = $coupon->cName;
    }

    return $names;
}

/**
 * @param string|null $selectedManufacturers
 * @return stdClass[]
 */
function getManufacturers(?string $selectedManufacturers = ''): array
{
    $selected = Text::parseSSKint($selectedManufacturers);
    $items    = Shop::Container()->getDB()->getObjects('SELECT kHersteller, cName FROM thersteller');
    $langID   = Shop::getLanguageID();
    foreach ($items as $item) {
        $item->kHersteller = (int)$item->kHersteller;
        $manufacturer      = new Hersteller($item->kHersteller, $langID);
        $item->cName       = $manufacturer->cName;
        $item->selected    = in_array($item->kHersteller, $selected, true);
        unset($manufacturer);
    }

    return $items;
}

/**
 * @param string|null $selectedCategories
 * @param int         $categoryID
 * @param int         $depth
 * @return stdClass[]
 */
function getCategories(?string $selectedCategories = '', int $categoryID = 0, int $depth = 0): array
{
    $selected = Text::parseSSKint($selectedCategories);
    $arr      = [];
    $items    = Shop::Container()->getDB()->selectAll(
        'tkategorie',
        'kOberKategorie',
        $categoryID,
        'kKategorie, cName'
    );
    foreach ($items as $item) {
        $item->kKategorie = (int)$item->kKategorie;
        for ($i = 0; $i < $depth; $i++) {
            $item->cName = '--' . $item->cName;
        }
        $item->selected = in_array($item->kKategorie, $selected, true);
        $arr[]          = $item;
        $arr            = array_merge($arr, getCategories($selectedCategories, $item->kKategorie, $depth + 1));
    }

    return $arr;
}

/**
 * Parse Datumsstring und formatiere ihn im DB-kompatiblen Standardformat
 *
 * @param string|null $string
 * @return string|null
 */
function normalizeDate(?string $string): ?string
{
    if ($string === null || $string === '') {
        return null;
    }
    $date = date_create($string);
    if ($date === false) {
        return $string;
    }

    return $date->format('Y-m-d H:i') . ':00';
}

/**
 * @param string $type
 * @param string $where
 * @param string $order
 * @param string $limit
 * @return stdClass[]
 */
function getRawCoupons(
    string $type = Kupon::TYPE_STANDARD,
    string $where = '',
    string $order = '',
    string $limit = ''
): array {
    return Shop::Container()->getDB()->getObjects(
        'SELECT k.*, MAX(kk.dErstellt) AS dLastUse
            FROM tkupon AS k
            LEFT JOIN tkuponkunde AS kk ON kk.kKupon = k.kKupon
            WHERE cKuponTyp = :type ' .
            ($where !== '' ? ' AND ' . $where : '') .
            'GROUP BY k.kKupon' .
            ($order !== '' ? ' ORDER BY ' . $order : '') .
            ($limit !== '' ? ' LIMIT ' . $limit : ''),
        ['type' => $type]
    );
}

/**
 * Get instances of existing coupons, each with some enhanced information that can be displayed
 *
 * @param string $type
 * @param string $whereSQL - an SQL WHERE clause (col1 = val1 AND vol2 LIKE ...)
 * @param string $orderSQL - an SQL ORDER BY clause (cName DESC)
 * @param string $limitSQL - an SQL LIMIT clause  (10,20)
 * @return Kupon[]
 */
function getCoupons(
    string $type = Kupon::TYPE_STANDARD,
    string $whereSQL = '',
    string $orderSQL = '',
    string $limitSQL = ''
): array {
    $raw = getRawCoupons($type, $whereSQL, $orderSQL, $limitSQL);
    $res = [];
    foreach ($raw as $item) {
        $res[] = getCoupon((int)$item->kKupon);
    }

    return $res;
}

/**
 * @param string $type
 * @param string $whereSQL
 * @return stdClass[]
 */
function getExportableCoupons(string $type = Kupon::TYPE_STANDARD, string $whereSQL = ''): array
{
    $coupons = getRawCoupons($type, $whereSQL);
    foreach ($coupons as $rawCoupon) {
        foreach (getCouponNames((int)$rawCoupon->kKupon) as $iso => $name) {
            $rawCoupon->{'cName_' . $iso} = $name;
        }
    }

    return $coupons;
}

/**
 * Get an instance of an existing coupon with some enhanced information that can be displayed
 *
 * @param int $id
 * @return Kupon $oKupon
 */
function getCoupon(int $id): Kupon
{
    $coupon = new Kupon($id);
    $coupon->augment();

    return $coupon;
}

/**
 * Enhance an existing Kupon instance with some extra information that can be displayed
 *
 * @param Kupon $coupon
 * @deprecated since 5.2.0
 */
function augmentCoupon(Kupon $coupon): void
{
    trigger_error(__FUNCTION__ . ' is deprecated - use Kupon::augment() instead', E_USER_DEPRECATED);
    $coupon->augment();
}

/**
 * Create a fresh Kupon instance with default values to be edited
 *
 * @param string $type - Kupon::TYPE_STANDRAD, Kupon::TYPE_SHIPPING, Kupon::TYPE_NEWCUSTOMER
 * @return Kupon
 */
function createNewCoupon(string $type): Kupon
{
    $coupon                        = new Kupon();
    $coupon->cKuponTyp             = $type;
    $coupon->cName                 = '';
    $coupon->fWert                 = 0.0;
    $coupon->cWertTyp              = 'festpreis';
    $coupon->cZusatzgebuehren      = 'N';
    $coupon->nGanzenWKRabattieren  = 1;
    $coupon->kSteuerklasse         = 1;
    $coupon->fMindestbestellwert   = 0.0;
    $coupon->cCode                 = '';
    $coupon->cLieferlaender        = '';
    $coupon->nVerwendungen         = 0;
    $coupon->nVerwendungenProKunde = 0;
    $coupon->cArtikel              = '';
    $coupon->kKundengruppe         = -1;
    $coupon->dGueltigAb            = date_create()->format('Y-m-d H:i');
    $coupon->dGueltigBis           = '';
    $coupon->cAktiv                = 'Y';
    $coupon->cHersteller           = '-1';
    $coupon->cKategorien           = '-1';
    $coupon->cKunden               = '-1';
    $coupon->kKupon                = 0;

    $coupon->augment();

    return $coupon;
}

/**
 * Read coupon settings from the edit page form and create a Kupon instance of it
 *
 * @return Kupon
 * @throws Exception
 */
function createCouponFromInput(): Kupon
{
    $input                         = Text::filterXSS($_POST);
    $coupon                        = new Kupon(Request::postInt('kKuponBearbeiten'));
    $coupon->cKuponTyp             = $input['cKuponTyp'];
    $coupon->cName                 = htmlspecialchars($input['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $coupon->fWert                 = !empty($input['fWert']) ? (float)str_replace(',', '.', $input['fWert']) : null;
    $coupon->cWertTyp              = !empty($input['cWertTyp']) ? $input['cWertTyp'] : null;
    $coupon->cZusatzgebuehren      = !empty($input['cZusatzgebuehren']) ? $input['cZusatzgebuehren'] : 'N';
    $coupon->nGanzenWKRabattieren  = Request::postInt('nGanzenWKRabattieren');
    $coupon->kSteuerklasse         = !empty($input['kSteuerklasse']) ? (int)$input['kSteuerklasse'] : null;
    $coupon->fMindestbestellwert   = (float)str_replace(',', '.', $input['fMindestbestellwert']);
    $coupon->cCode                 = !empty($input['cCode']) ? $input['cCode'] : '';
    $coupon->cLieferlaender        = !empty($input['cLieferlaender'])
        ? mb_convert_case($input['cLieferlaender'], MB_CASE_UPPER)
        : '';
    $coupon->nVerwendungen         = Request::postInt('nVerwendungen');
    $coupon->nVerwendungenProKunde = Request::postInt('nVerwendungenProKunde');
    $coupon->cArtikel              = !empty($input['cArtikel']) ? ';' . trim($input['cArtikel'], ";\t\n\r") . ';' : '';
    $coupon->cHersteller           = '-1';
    $coupon->kKundengruppe         = Request::postInt('kKundengruppe');
    $coupon->dGueltigAb            = normalizeDate(!empty($input['dGueltigAb'])
        ? $input['dGueltigAb']
        : date_create()->format('Y-m-d H:i') . ':00');
    $coupon->dGueltigBis           = normalizeDate(!empty($input['dGueltigBis']) ? $input['dGueltigBis'] : '');
    $coupon->cAktiv                = Request::postVar('cAktiv') === 'Y' ? 'Y' : 'N';
    $coupon->cKategorien           = '-1';
    if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
        $coupon->cKunden = '-1';
    }
    if (Request::postVar('bOpenEnd') === 'Y') {
        $coupon->dGueltigBis = null;
    } elseif (!empty($input['dDauerTage'])) {
        $coupon->dGueltigBis     = '';
        $actualTimestamp         = date_create();
        $actualTimestampEndofDay = date_time_set($actualTimestamp, 23, 59, 59);
        $setDays                 = new DateInterval('P' . $input['dDauerTage'] . 'D');
        $coupon->dGueltigBis     = date_add($actualTimestampEndofDay, $setDays)->format('Y-m-d H:i:s');
    }
    $manufacturers = array_map('\intval', ($input['kHersteller'] ?? []));
    $categories    = array_map('\intval', ($input['kKategorien'] ?? []));
    if (!in_array(-1, $manufacturers, true)) {
        $coupon->cHersteller = Text::createSSK($input['kHersteller']);
    }
    if (!in_array(-1, $categories, true)) {
        $coupon->cKategorien = Text::createSSK($input['kKategorien']);
    }
    if (!empty($input['cKunden']) && $input['cKunden'] !== '-1') {
        $coupon->cKunden = trim($input['cKunden'], ";\t\n\r") . ';';
    }
    if (isset($input['couponCreation'])) {
        $massCreationCoupon                  = new stdClass();
        $massCreationCoupon->cActiv          = Request::postInt('couponCreation');
        $massCreationCoupon->numberOfCoupons = ($massCreationCoupon->cActiv === 1 && !empty($input['numberOfCoupons']))
            ? (int)$input['numberOfCoupons']
            : 2;
        $massCreationCoupon->lowerCase       = ($massCreationCoupon->cActiv === 1 && !empty($input['lowerCase']));
        $massCreationCoupon->upperCase       = ($massCreationCoupon->cActiv === 1 && !empty($input['upperCase']));
        $massCreationCoupon->numbersHash     = ($massCreationCoupon->cActiv === 1 && !empty($input['numbersHash']));
        $massCreationCoupon->hashLength      = ($massCreationCoupon->cActiv === 1 && !empty($input['hashLength']))
            ? (int)$input['hashLength']
            : 4;
        $massCreationCoupon->prefixHash      = ($massCreationCoupon->cActiv === 1 && !empty($input['prefixHash']))
            ? $input['prefixHash']
            : '';
        $massCreationCoupon->suffixHash      = ($massCreationCoupon->cActiv === 1 && !empty($input['suffixHash']))
            ? $input['suffixHash']
            : '';
        $coupon->massCreationCoupon          = $massCreationCoupon;
    }

    return $coupon;
}

/**
 * Get the number of existing coupons of type $cKuponTyp
 *
 * @param string $type
 * @param string $whereSQL
 * @return int
 */
function getCouponCount(string $type = Kupon::TYPE_STANDARD, string $whereSQL = ''): int
{
    return Shop::Container()->getDB()->getSingleInt(
        'SELECT COUNT(kKupon) AS cnt
            FROM tkupon
            WHERE cKuponTyp = :tp' .
            ($whereSQL !== '' ? ' AND ' . $whereSQL : ''),
        'cnt',
        ['tp' => $type]
    );
}

/**
 * Validates the fields of a given Kupon instance
 *
 * @param Kupon $coupon
 * @return array - list of error messages
 * @deprecated since 5.2.0
 */
function validateCoupon(Kupon $coupon): array
{
    trigger_error(__FUNCTION__ . ' is deprecated - use Kupon::validate() instead', E_USER_DEPRECATED);
    return $coupon->validate();
}

/**
 * Save a new or already existing coupon in the DB
 *
 * @param Kupon $coupon
 * @param LanguageModel[] $languages
 * @return int - 0 on failure ; kKupon on success
 */
function saveCoupon(Kupon $coupon, array $languages)
{
    if ((int)$coupon->kKupon > 0) {
        // vorhandener Kupon
        $res = $coupon->update() === -1 ? 0 : $coupon->kKupon;
    } else {
        // neuer Kupon
        $coupon->nVerwendungenBisher = 0;
        $coupon->dErstellt           = 'NOW()';
        if (isset($coupon->massCreationCoupon)) {
            $massCreationCoupon = $coupon->massCreationCoupon;
            $coupon->kKupon     = [];
            unset($coupon->massCreationCoupon, $_POST['informieren']);
            for ($i = 1; $i <= $massCreationCoupon->numberOfCoupons; $i++) {
                if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER) {
                    $coupon->cCode = $coupon->generateCode(
                        $massCreationCoupon->hashLength,
                        $massCreationCoupon->lowerCase,
                        $massCreationCoupon->upperCase,
                        $massCreationCoupon->numbersHash,
                        $massCreationCoupon->prefixHash,
                        $massCreationCoupon->suffixHash
                    );
                }
                unset($coupon->translationList);
                $coupon->kKupon[] = (int)$coupon->save();
            }
        } else {
            if ($coupon->cKuponTyp !== Kupon::TYPE_NEWCUSTOMER && $coupon->cCode === '') {
                $coupon->cCode = $coupon->generateCode();
            }
            unset($coupon->translationList);
            $coupon->kKupon = (int)$coupon->save();
        }
        $res = $coupon->kKupon;
    }

    if ($res > 0) {
        $db = Shop::Container()->getDB();
        // Kupon-Sprachen aktualisieren
        if (is_array($coupon->kKupon)) {
            foreach ($coupon->kKupon as $couponID) {
                $db->delete('tkuponsprache', 'kKupon', $couponID);
                foreach ($languages as $language) {
                    $code          = $language->getIso();
                    $postVarName   = 'cName_' . $code;
                    $localizedName = Request::postVar($postVarName, '') !== ''
                        ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                        : $coupon->cName;

                    $localized              = new stdClass();
                    $localized->kKupon      = $couponID;
                    $localized->cISOSprache = $code;
                    $localized->cName       = Text::filterXSS($localizedName);
                    $db->insert('tkuponsprache', $localized);
                }
            }
        } else {
            $db->delete('tkuponsprache', 'kKupon', $coupon->kKupon);
            foreach ($languages as $language) {
                $code          = $language->getIso();
                $postVarName   = 'cName_' . $code;
                $localizedName = Request::postVar($postVarName, '') !== ''
                    ? htmlspecialchars($_POST[$postVarName], ENT_COMPAT | ENT_HTML401, JTL_CHARSET)
                    : $coupon->cName;

                $localized              = new stdClass();
                $localized->kKupon      = $coupon->kKupon;
                $localized->cISOSprache = $code;
                $localized->cName       = Text::filterXSS($localizedName);
                $db->insert('tkuponsprache', $localized);
            }
        }
    }

    return $res;
}

/**
 * Send notification emails to all customers admitted to this Kupon
 *
 * @param Kupon $coupon
 * @deprecated since 5.2.0 (disabled via template SHOP-5794)
 */
function informCouponCustomers(Kupon $coupon)
{
    $coupon->augment();
    $db              = Shop::Container()->getDB();
    $defaultLang     = $db->select('tsprache', 'cShopStandard', 'Y');
    $defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
    $defaultOptions  = Artikel::getDefaultOptions();
    // lokalisierter Kuponwert und MBW
    $coupon->cLocalizedWert = $coupon->cWertTyp === 'festpreis'
        ? Preise::getLocalizedPriceString($coupon->fWert, $defaultCurrency, false)
        : $coupon->fWert . ' %';
    $coupon->cLocalizedMBW  = Preise::getLocalizedPriceString($coupon->fMindestbestellwert, $defaultCurrency, false);
    // kKunde-Array aller auserwaehlten Kunden
    $customerIDs     = Text::parseSSKint($coupon->cKunden);
    $customerData    = $db->getInts(
        'SELECT kKunde
            FROM tkunde
            WHERE TRUE
                ' . ((int)$coupon->kKundengruppe === -1
            ? 'AND TRUE'
            : 'AND kKundengruppe = ' . (int)$coupon->kKundengruppe) . '
                ' . ($coupon->cKunden === '-1'
            ? 'AND TRUE'
            : 'AND kKunde IN (' . implode(',', $customerIDs) . ')'),
        'kKunde'
    );
    $productIDs      = [];
    $manufacturerIDs = Text::parseSSK($coupon->cHersteller);
    $itemNumbers     = Text::parseSSK($coupon->cArtikel);
    if (count($itemNumbers) > 0) {
        $itemNumbers = array_map(static function ($e) {
            return '"' . $e . '"';
        }, $itemNumbers);
        $productIDs  = $db->getInts(
            'SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (' . implode(',', $itemNumbers) . ')',
            'kArtikel'
        );
    }
    foreach ($customerData as $customerID) {
        $customer = new Customer($customerID);
        $language = Shop::Lang()->getIsoFromLangID($customer->kSprache);
        if (!$language) {
            $language = $defaultLang;
        }
        $localized  = $db->select(
            'tkuponsprache',
            ['kKupon', 'cISOSprache'],
            [$coupon->kKupon, $language->cISO]
        );
        $categories = [];
        if ($coupon->cKategorien !== '-1') {
            foreach (Text::parseSSKint($coupon->cKategorien) as $categoryID) {
                if ($categoryID <= 0) {
                    continue;
                }
                $category       = new Kategorie($categoryID, $customer->kSprache, $customer->kKundengruppe);
                $category->cURL = $category->cURLFull;
                $categories[]   = $category;
            }
        }
        $products = [];
        foreach ($productIDs as $productID) {
            $product = new Artikel($db);
            $product->fuelleArtikel(
                $productID,
                $defaultOptions,
                $customer->kKundengruppe,
                $customer->kSprache,
                true
            );
            $products[] = $product;
        }
        $manufacturers = [];
        foreach ($manufacturerIDs as $manufacturerID) {
            $manufacturers[] = new Hersteller($manufacturerID, $customer->kSprache);
        }
        // put all together
        $coupon->Kategorien      = $categories;
        $coupon->Artikel         = $products;
        $coupon->AngezeigterName = $localized->cName;
        $coupon->Hersteller      = $manufacturers;
        $obj                     = new stdClass();
        $obj->tkupon             = $coupon;
        $obj->tkunde             = $customer;
        $mailer                  = Shop::Container()->get(Mailer::class);
        $mail                    = new Mail();
        $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_KUPON, $obj));
    }
}

/**
 * Set all Coupons with an outdated dGueltigBis to cAktiv = 'N'
 */
function deactivateOutdatedCoupons(): void
{
    Shop::Container()->getDB()->query(
        "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE dGueltigBis > 0
            AND dGueltigBis <= NOW()"
    );
}

/**
 * Set all Coupons that reached nVerwendungenBisher to nVerwendungen to cAktiv = 'N'
 */
function deactivateExhaustedCoupons(): void
{
    Shop::Container()->getDB()->query(
        "UPDATE tkupon
            SET cAktiv = 'N'
            WHERE nVerwendungen > 0
            AND nVerwendungenBisher >= nVerwendungen"
    );
}
