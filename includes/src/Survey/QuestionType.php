<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Survey;

use MyCLabs\Enum\Enum;

/**
 * Class QuestionType
 *
 * @package JTL\Survey
 * @method static QuestionType MULTI()
 * @method static QuestionType MULTI_SINGLE()
 * @method static QuestionType SELECT_SINGLE()
 * @method static QuestionType SELECT_MULTI()
 * @method static QuestionType TEXT_SMALL()
 * @method static QuestionType TEXT_BIG()
 * @method static QuestionType MATRIX_SINGLE()
 * @method static QuestionType MATRIX_MULTI()
 * @method static QuestionType TEXT_STATIC()
 * @method static QuestionType TEXT_PAGE_CHANGE()
 */
class QuestionType extends Enum
{
    public const MULTI = 'multiple_multi';

    public const MULTI_SINGLE = 'multiple_single';

    public const SELECT_SINGLE = 'select_single';

    public const SELECT_MULTI = 'select_multi';

    public const TEXT_SMALL = 'text_klein';

    public const TEXT_BIG = 'text_gross';

    public const MATRIX_SINGLE = 'matrix_single';

    public const MATRIX_MULTI = 'matrix_multi';

    public const TEXT_STATIC = 'text_statisch';

    public const TEXT_PAGE_CHANGE = 'text_statisch_seitenwechsel';
}
