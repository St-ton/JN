<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard\Steps;

use JTL\Backend\Wizard\Question;
use JTL\Backend\Wizard\QuestionInterface;
use JTL\Backend\Wizard\QuestionType;
use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class PaymentPlugins
 * @package JTL\Backend\Wizard\Steps
 */
final class PaymentPlugins extends AbstractStep
{
    /**
     * PaymentPlugins constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        $this->setTitle(__('stepThree'));
        $this->setDescription(__('stepThreeDesc'));
        $this->setID(3);
    }
}
