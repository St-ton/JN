<?php declare(strict_types=1);

use JTL\Checkout\Kupon;
use JTL\Language\LanguageModel;

/**
 * @param int[] $ids
 * @return bool
 * @deprecated since 5.2.0
 */
function loescheKupons(array $ids): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int $id
 * @return array - key = lang-iso ; value = localized coupon name
 * @deprecated since 5.2.0
 */
function getCouponNames(int $id): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string|null $selectedManufacturers
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function getManufacturers(?string $selectedManufacturers = ''): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string|null $selectedCategories
 * @param int         $categoryID
 * @param int         $depth
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function getCategories(?string $selectedCategories = '', int $categoryID = 0, int $depth = 0): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string|null $string
 * @return string|null
 * @deprecated since 5.2.0
 */
function normalizeDate(?string $string): ?string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return null;
}

/**
 * @param string $type
 * @param string $where
 * @param string $order
 * @param string $limit
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function getRawCoupons(
    string $type = Kupon::TYPE_STANDARD,
    string $where = '',
    string $order = '',
    string $limit = ''
): array {
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $type
 * @param string $whereSQL - an SQL WHERE clause (col1 = val1 AND vol2 LIKE ...)
 * @param string $orderSQL - an SQL ORDER BY clause (cName DESC)
 * @param string $limitSQL - an SQL LIMIT clause  (10,20)
 * @return Kupon[]
 * @deprecated since 5.2.0
 */
function getCoupons(
    string $type = Kupon::TYPE_STANDARD,
    string $whereSQL = '',
    string $orderSQL = '',
    string $limitSQL = ''
): array {
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $type
 * @param string $whereSQL
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function getExportableCoupons(string $type = Kupon::TYPE_STANDARD, string $whereSQL = ''): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int $id
 * @return Kupon $oKupon
 * @deprecated since 5.2.0
 */
function getCoupon(int $id): Kupon
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $coupon = new Kupon($id);
    $coupon->augment();

    return $coupon;
}

/**
 * @param Kupon $coupon
 * @deprecated since 5.2.0
 */
function augmentCoupon(Kupon $coupon): void
{
    trigger_error(__FUNCTION__ . ' is deprecated - use Kupon::augment() instead', E_USER_DEPRECATED);
    $coupon->augment();
}

/**
 * @param string $type - Kupon::TYPE_STANDRAD, Kupon::TYPE_SHIPPING, Kupon::TYPE_NEWCUSTOMER
 * @return Kupon
 * @deprecated since 5.2.0
 */
function createNewCoupon(string $type): Kupon
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new Kupon();
}

/**
 * @return Kupon
 * @throws Exception
 * @deprecated since 5.2.0
 */
function createCouponFromInput(): Kupon
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new Kupon();
}

/**
 * @param string $type
 * @param string $whereSQL
 * @return int
 * @deprecated since 5.2.0
 */
function getCouponCount(string $type = Kupon::TYPE_STANDARD, string $whereSQL = ''): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
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
 * @param Kupon $coupon
 * @param LanguageModel[] $languages
 * @return int - 0 on failure ; kKupon on success
 * @deprecated since 5.2.0
 */
function saveCoupon(Kupon $coupon, array $languages)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param Kupon $coupon
 * @deprecated since 5.2.0 (disabled via template SHOP-5794)
 */
function informCouponCustomers(Kupon $coupon)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.2.0
 */
function deactivateOutdatedCoupons(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @deprecated since 5.2.0
 */
function deactivateExhaustedCoupons(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}
