<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use MyCLabs\Enum\Enum;

/**
 * Class InputType
 * @package Plugin\Admin
 */
class InputType extends Enum
{
    public const SELECT = 'selectbox';

    public const COLORPICKER = 'colorpicker';

    public const PASSWORD = 'password';

    public const TEXTAREA = 'textarea';

    public const NUMBER = 'number';

    public const CHECKBOX = 'checkbox';

    public const RADIO = 'radio';

    public const NONE = 'none';
}
