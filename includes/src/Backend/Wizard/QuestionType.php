<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard;

/**
 * Class QuestionType
 * @package JTL\Backend\Wizard
 */
class QuestionType
{
    public const BOOL = 0;

    public const TEXT = 1;

    public const EMAIL = 2;

    public const SELECT = 3;

    public const MULTI_BOOL = 4;
}
