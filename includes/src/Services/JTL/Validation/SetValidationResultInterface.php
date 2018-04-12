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
    public function setFieldResult($fieldName, ValidationResultInterface $valueValidationResult);

    /**
     * @param string $fieldName
     * @return ValidationResultInterface
     */
    public function getFieldResult($fieldName);

    /**
     * @return array|null
     */
    public function getSetAsArray();

    /**
     * @return array
     */
    public function getSetAsArrayInsecure();

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
    public function isValid();
}