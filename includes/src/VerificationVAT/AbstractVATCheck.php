<?php declare(strict_types=1);

namespace JTL\VerificationVAT;

use Psr\Log\LoggerInterface;

/**
 * Class AbstractVATCheck
 * @package JTL\VerificationVAT
 */
abstract class AbstractVATCheck implements VATCheckInterface
{
    /**
     * VATCheckEU constructor.
     * @param VATCheckDownSlots $downTimes
     * @param LoggerInterface   $logger
     */
    public function __construct(protected VATCheckDownSlots $downTimes, protected LoggerInterface $logger)
    {
    }

    /**
     * spaces can't handled by the VIES-system,
     * so we condense the ID-string here and let them out
     *
     * @param string $sourceString
     * @return string
     */
    public function condenseSpaces(string $sourceString): string
    {
        return \str_replace(' ', '', $sourceString);
    }
}
