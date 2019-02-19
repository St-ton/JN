<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS;

use JTL\Shop;

/**
 * Class Synclogin
 * @package JTL\dbeS
 */
class Synclogin
{
    /**
     * @var string
     */
    public $cMail;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cPass;

    /**
     * Synclogin constructor.
     * get wawi sync user/pass from db
     */
    public function __construct()
    {
        $obj = Shop::Container()->getDB()->select('tsynclogin', 'kSynclogin', 1);
        if ($obj !== null) {
            foreach (\array_keys(\get_object_vars($obj)) as $member) {
                $this->$member = $obj->$member;
            }
        } else {
            Shop::Container()->getLogService()->error('Kein Sync-Login gefunden.');
        }
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool
     * @throws \Exception
     */
    public function checkLogin($user, $pass): bool
    {
        return $this->cName !== null
            && $this->cPass !== null
            && $this->cName === $user
            && Shop::Container()->getPasswordService()->verify($pass, $this->cPass) === true;
    }
}
