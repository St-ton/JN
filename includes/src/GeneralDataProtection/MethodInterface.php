<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

/**
 * Interface MethodInterface
 * @package JTL\GeneralDataProtection
 */
interface MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute(): void;

    /**
     *
     */
    public function getIsFinished(): bool;

    /**
     *
     */
    public function getWorkSum(): int;
}
