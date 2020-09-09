<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use MyCLabs\Enum\Enum;

/**
 * Class QuestionValidationCode
 * @package JTL\Backend\Wizard
 */
class QuestionValidationCode extends Enum
{
    public const OK = 1;

    public const ERROR_REQUIRED = 2;

    public const INVALID_EMAIL = 3;

    public const ERROR_SSL = 4;

    public const ERROR_SSL_PLUGIN = 5;
}
