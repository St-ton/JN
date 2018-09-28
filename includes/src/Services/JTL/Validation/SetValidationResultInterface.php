<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

/**
 * Interface ObjectValidationResultInterface
 * @package Services\JTL\Validation
 */
interface SetValidationResultInterface
{
    /**
     * @param                           $fieldName
     * @param ValidationResultInterface $valueValidationResult
     * @return void
     */
    public function setFieldResult($fieldName, ValidationResultInterface $valueValidationResult): void;

    /**
     * @param string $fieldName
     * @return ValidationResultInterface
     */
    public function getFieldResult($fieldName): ValidationResultInterface;

    /**
     * @return array|null
     */
    public function getSetAsArray(): ?array;

    /**
     * @return array
     */
    public function getSetAsArrayInsecure(): array;

    /**
     * @return object|null
     */
    public function getSetAsObject();

    /**
     * @return object
     */
    public function getSetAsObjectInsecure();

    /**
     * @return bool
     */
    public function isValid(): bool;
}
