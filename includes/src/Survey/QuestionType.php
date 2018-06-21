<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Survey;

use MyCLabs\Enum\Enum;

/**
 * Class ComponentPropertyType
 *
 * @package Filter
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
    const MULTI = 'multiple_multi';

    const MULTI_SINGLE = 'multiple_single';

    const SELECT_SINGLE = 'select_single';

    const SELECT_MULTI = 'select_multi';

    const TEXT_SMALL = 'text_klein';

    const TEXT_BIG = 'text_gross';

    const MATRIX_SINGLE = 'matrix_single';

    const MATRIX_MULTI = 'matrix_multi';

    const TEXT_STATIC = 'text_statisch';

    const TEXT_PAGE_CHANGE = 'text_statisch_seitenwechsel';
}
