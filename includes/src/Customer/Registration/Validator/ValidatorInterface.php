<?php declare(strict_types=1);

namespace JTL\Customer\Registration\Validator;

/**
 * Interface ValidatorInterface
 * @package JTL\Customer\Registration\Validator
 */
interface ValidatorInterface
{

    /**
     * @return bool
     */
    public function validate(): bool;

    /**
     * @return array
     */
    public function getErrors(): array;
}
