<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\GenericOptin;

/**
 * Class GenericOptinFactory
 * @package JTL\GenericOptin
 */
abstract class GenericOptinFactory
{
    /**
     * @param int   $optinType
     * @param array $inheritData
     * @return OptinAvailAgain
     */
    public static function instantiate(int $optinType, ...$inheritData): GenericOptinInterface
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
