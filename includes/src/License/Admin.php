<?php declare(strict_types=1);

namespace JTL\License;

use JTL\Smarty\JTLSmarty;

/**
 * Class Admin
 * @package JTL\License
 * @deprecated since 5.2.0
 */
class Admin
{
    public const ACTION_EXTEND = 'extendLicense';

    public const ACTION_UPGRADE = 'upgradeLicense';

    public const ACTION_SET_BINDING = 'setbinding';

    public const ACTION_CLEAR_BINDING = 'clearbinding';

    public const ACTION_ENTER_TOKEN = 'entertoken';

    public const ACTION_SAVE_TOKEN = 'savetoken';

    public const ACTION_RECHECK = 'recheck';

    public const ACTION_REVOKE = 'revoke';

    public const ACTION_REDIRECT = 'redirect';

    public const ACTION_UPDATE = 'update';

    public const ACTION_INSTALL = 'install';

    public const STATE_APPROVED = 'approved';

    public const STATE_CREATED = 'created';

    public const STATE_FAILED = 'failed';

    /**
     * Admin constructor.
     */
    public function __construct()
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }

    public function handleAuth(): void
    {
    }

    /**
     * @param JTLSmarty $smarty
     */
    public function handle(JTLSmarty $smarty): void
    {
    }
}
