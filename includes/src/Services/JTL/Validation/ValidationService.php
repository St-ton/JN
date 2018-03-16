<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

/**
 * Class ValidationService
 * @package Services\JTL\Validation
 */
class ValidationService implements ValidationServiceInterface
{
    protected $ruleSets = [];
    protected $fieldDefinitions = [];
    protected $classDefinitions = [];

    protected $get;
    protected $post;
    protected $cookie;

    /**
     * ValidationService constructor.
     * @param array $get
     * @param array $post
     * @param array $cookie
     */
    public function __construct(array $get, array $post, array $cookie)
    {
        $this->get    = $get;
        $this->post   = $post;
        $this->cookie = $cookie;
    }

    /**
     * @inheritdoc
     */
    public function getRuleSet(string $name)
    {
        return $this->ruleSets[$name];
    }

    /**
     * @inheritdoc
     */
    public function setRuleSet(string $name, RuleSet $ruleSet)
    {
        $this->ruleSets[$name] = $ruleSet;
    }

    /**
     * @inheritdoc
     */
    public function validate($value, $ruleSet)
    {
        if ($ruleSet instanceof RuleSet) {
            return $this->_validate($value, $ruleSet);
        } elseif (isset($this->ruleSets[$ruleSet])) {
            return $this->_validate($value, $this->ruleSets[$ruleSet]);
        } else {
            throw new \InvalidArgumentException('Invalid RuleSet');
        }
    }

    /**
     * @param mixed   $value
     * @param RuleSet $ruleSet
     * @return ValidationResult
     */
    protected function _validate($value, RuleSet $ruleSet)
    {
        $result        = new ValidationResult($value);
        $filteredValue = $value;
        foreach ($ruleSet->getRules() as $rule) {
            $ruleResult    = $rule->validate($filteredValue);
            $filteredValue = $ruleResult->getTransformedValue();
            $result->addRuleResult($ruleResult);
        }
        $result->setValue($filteredValue);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasGet(string $name): bool
    {
        return isset($this->get[$name]);
    }

    /**
     * @inheritdoc
     */
    public function hasPost(string $name): bool
    {
        return isset($this->post[$name]);
    }

    /**
     * @inheritdoc
     */
    public function hasCookie(string $name): bool
    {
        return isset($this->cookie[$name]);
    }

    /**
     * @inheritdoc
     */
    public function hasGPC(string $name): bool
    {
        return $this->hasGP($name) || $this->hasCookie($name);
    }

    /**
     * @inheritdoc
     */
    public function hasGP(string $name): bool
    {
        return $this->hasGet($name) || $this->hasPost($name);
    }

    /**
     * @inheritdoc
     */
    public function validateGet(string $name, $ruleSet): ValidationResultInterface
    {
        if ($this->hasGet($name)) {
            return $this->validate($this->get[$name], $ruleSet);
        } else {
            return $this->createMissingValueResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function validatePost(string $name, $ruleSet): ValidationResultInterface
    {
        if ($this->hasPost($name)) {
            return $this->validate($this->post[$name], $ruleSet);
        } else {
            return $this->createMissingValueResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function validateCookie(string $name, $ruleSet): ValidationResultInterface
    {
        if ($this->hasCookie($name)) {
            return $this->validate($this->cookie[$name], $ruleSet);
        } else {
            return $this->createMissingValueResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function validateGPC(string $name, $ruleSet): ValidationResultInterface
    {
        if ($this->hasGet($name)) {
            return $this->validateGet($name, $ruleSet);
        } elseif ($this->hasPost($name)) {
            return $this->validatePost($name, $ruleSet);
        } elseif ($this->hasCookie($name)) {
            return $this->validateCookie($name, $ruleSet);
        } else {
            return $this->createMissingValueResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function validateGP(string $name, $ruleSet): ValidationResultInterface
    {
        if ($this->hasGet($name)) {
            return $this->validateGet($name, $ruleSet);
        } elseif ($this->hasPost($name)) {
            return $this->validatePost($name, $ruleSet);
        } else {
            return $this->createMissingValueResult();
        }
    }


    /**
     * @inheritdoc
     */
    public function validateSet($set, $rulesConfig): SetValidationResultInterface
    {
        $keyDiff = array_diff(array_keys($set), array_keys($rulesConfig));
        if (!empty($keyDiff)) {
            throw new \Exception("RulesConfig/Set mismatch detected");
        }
        foreach ($set as $index => $value) {
            if (is_array($value) || is_object($value)) {
                throw new \Exception("Nested sets are not supported right now");
            }
        }

        $result = new SetValidationResult($set);
        $set    = (array)$set;
        foreach ($rulesConfig as $fieldName => $config) {
            $result->setFieldResult($fieldName, $this->validate($set[$fieldName], $config));
        }

        return $result;
    }


    /**
     * @inheritdoc
     */
    public function validateFullGet(array $rulesConfig): SetValidationResultInterface
    {
        return $this->validateSet($this->get, $rulesConfig);
    }

    /**
     * @inheritdoc
     */
    public function validateFullPost(array $rulesConfig): SetValidationResultInterface
    {
        return $this->validateSet($this->post, $rulesConfig);
    }

    /**
     * @inheritdoc
     */
    public function validateFullCookie(array $rulesConfig): SetValidationResultInterface
    {
        return $this->validateSet($this->cookie, $rulesConfig);
    }

    protected function createMissingValueResult()
    {
        $result = new ValidationResult(null);
        $result->setValue(null);
        $result->addRuleResult(new RuleResult(false, 'missing value', null));

        return $result;
    }
}
