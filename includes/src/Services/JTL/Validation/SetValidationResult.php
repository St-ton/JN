<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

/**
 * Class ObjectValidationResult
 * @package Services\JTL\Validation
 */
class SetValidationResult implements SetValidationResultInterface
{
    protected $fieldResults = [];
    protected $set;

    /**
     * ObjectValidationResult constructor.
     * @param object|array $set
     */
    public function __construct($set)
    {
        $this->set = $set;
    }

    /**
     * @inheritdoc
     */
    public function setFieldResult($fieldName, ValidationResultInterface $valueValidationResult)
    {
        $this->fieldResults[$fieldName] = $valueValidationResult;
    }

    /**
     * @inheritdoc
     */
    public function getFieldResult($fieldName)
    {
        return $this->fieldResults[$fieldName];
    }

    /**
     * @inheritdoc
     */
    public function getSetAsArray()
    {
        return $this->isValid() ? $this->set : null;
    }

    /**
     * @inheritdoc
     */
    public function getSetAsArrayInsecure()
    {
        return $this->set;
    }

    /**
     * @inheritdoc
     */
    public function getSetAsObject()
    {
        return $this->isValid() ? (object)$this->set : null;
    }

    /**
     * @inheritdoc
     */
    public function getSetAsObjectInsecure()
    {
        return (object)$this->set;
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        /** @var ValidationResultInterface $fieldResult */
        foreach ($this->fieldResults as $fieldResult) {
            if (!$fieldResult->isValid()) {
                return false;
            }
        }

        return true;
    }
}
