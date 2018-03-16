<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

/**
 * Interface ValidationResultInterface
 * @package Services\JTL\Validation
 */
interface ValidationResultInterface extends ValueCarrierInterface
{
    /**
     * @param RuleResultInterface $ruleResult
     * @return void
     */
    public function addRuleResult(RuleResultInterface $ruleResult);

    /**
     * @return array|RuleResultInterface[]
     */
    public function getRuleResults();

    /**
     * @return bool
     */
    public function isValid();
}
