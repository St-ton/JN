<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Optin;

/**
 * Class OptinFactory
 * @package JTL\Optin
 */
abstract class OptinFactory
{
    /**
     * @param int   $optinType
     * @param array $inheritData
     * @return OptinAvailAgain
     */
    public static function instantiate(int $optinType, ...$inheritData): OptinInterface
    {
        switch ($optinType) {
            case OPTIN_AVAILAGAIN:
                return new OptinAvailAgain($inheritData);
//            case OPTIN_NEWSLETTER:
//                return new OptinNewsletter($inheritData);
            default:
                return null;
        }
    }
}
