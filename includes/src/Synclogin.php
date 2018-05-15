<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Synclogin
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
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        } else {
            Jtllog::writeLog('Kein Sync-Login gefunden.');
        }
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool
     * @throws Exception
     */
    public function checkLogin($user, $pass): bool
    {
        return $this->cName !== null
            && $this->cPass !== null
            && $this->cName === $user
            && Shop::Container()->getPasswordService()->verify($pass, $this->cPass) === true;
    }
}
