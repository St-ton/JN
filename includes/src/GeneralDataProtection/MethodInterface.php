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
     *
     * @return void
     */
    public function execute(): void;

    /**
     * @return bool
     */
    public function getIsFinished(): bool;

    /**
     * @return int
     */
    public function getWorkSum(): int;

    /**
     * @return int
     */
    public function getTaskRepetitions(): int;

    /**
     * @return int
     */
    public function getLastProductID(): int;

    /**
     * @return int
     */
    public function setLastProductID(): int;
}
