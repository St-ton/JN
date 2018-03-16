<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

/**
 * Interface ValidationServiceInterface
 * @package Services\JTL\Validation
 */
interface ValidationServiceInterface
{
    /**
     * @param string $name
     * @return RuleSet
     */
    public function getRuleSet($name);

    /**
     * @param string  $name
     * @param RuleSet $ruleSet
     * @return void
     */
    public function setRuleSet($name, RuleSet $ruleSet);

    /**
     * @param mixed          $value
     * @param RuleSet|string $ruleSet
     * @return ValidationResultInterface
     */
    public function validate($value, $ruleSet);

    /**
     * @param string $name
     * @return bool
     */
    public function hasGet($name);

    /**
     * @param string $name
     * @return bool
     */
    public function hasPost($name);

    /**
     * @param string $name
     * @return bool
     */
    public function hasCookie($name);

    /**
     * @param string $name
     * @return bool
     */
    public function hasGPC($name);

    /**
     * @param string $name
     * @return bool
     */
    public function hasGP($name);

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateGet($name, $ruleSet);

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validatePost($name, $ruleSet);

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateCookie($name, $ruleSet);

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateGPC($name, $ruleSet);

    /**
     * @param string         $name
     * @param string|RuleSet $ruleSet
     * @return ValidationResultInterface
     */
    public function validateGP($name, $ruleSet);


    /**
     * @param array|object $set
     * @param array        $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateSet($set, $rulesConfig);


    /**
     * @param array $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateFullGet($rulesConfig);

    /**
     * @param array $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateFullPost($rulesConfig);

    /**
     * @param array $rulesConfig
     * @return SetValidationResultInterface
     */
    public function validateFullCookie($rulesConfig);
}
