<?php declare(strict_types=1);

namespace JTL\Backend;

use Exception;
use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\pluck;

/**
 * Class AdminAccountManager
 * @package JTL\Backend
 * @deprecated since 5.2.0
 */
class AdminAccountManager
{
    /**
     * @var array
     */
    private array $messages = [
        'notice' => '',
        'error'  => ''
    ];

    /**
     * AdminAccountManager constructor.
     *
     * @param JTLSmarty $smarty
     * @param DbInterface $db
     * @param AlertServiceInterface $alertService
     */
    public function __construct(private JTLSmarty $smarty, private DbInterface $db, AlertServiceInterface $alertService)
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param int $adminID
     * @return null|stdClass
     */
    public function getAdminLogin(int $adminID): ?stdClass
    {
        return null;
    }

    /**
     * @return stdClass[]
     */
    public function getAdminList(): array
    {
        return [];
    }

    /**
     * @return stdClass[]
     */
    public function getAdminGroups(): array
    {
        return [];
    }

    /**
     * @return stdClass[]
     */
    public function getAdminDefPermissions(): array
    {
        return [];
    }

    /**
     * @param int $groupID
     * @return null|stdClass
     */
    public function getAdminGroup(int $groupID): ?stdClass
    {
        return $this->db->select('tadminlogingruppe', 'kAdminlogingruppe', $groupID);
    }

    /**
     * @param int $groupID
     * @return array
     */
    public function getAdminGroupPermissions(int $groupID): array
    {
        return pluck($this->db->selectAll('tadminrechtegruppe', 'kAdminlogingruppe', $groupID), 'cRecht');
    }

    /**
     * @param string     $row
     * @param string|int $value
     * @return bool
     */
    public function getInfoInUse(string $row, $value): bool
    {
        return \is_object($this->db->select('tadminlogin', $row, $value));
    }

    /**
     * @param string $languageTag
     */
    public function changeAdminUserLanguage(string $languageTag): void
    {
    }

    /**
     * @param int $adminID
     * @return array
     */
    public function getAttributes(int $adminID): array
    {
        return [];
    }

    /**
     * @param stdClass $account
     * @param array $extAttribs
     * @param array $errorMap
     * @return bool
     */
    public function saveAttributes(stdClass $account, array $extAttribs, array &$errorMap): bool
    {
        return false;
    }

    /**
     * @param array $attribs
     * @return bool
     */
    public function validateAccount(array &$attribs): bool
    {
        return false;
    }

    /**
     * @param array $tmpFile
     * @param string $attribName
     * @return bool
     */
    public function uploadAvatarImage(array $tmpFile, string $attribName): bool
    {
        return false;
    }

    /**
     * @param stdClass $account
     * @return bool
     */
    public function deleteAttributes(stdClass $account): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function actionAccountLock(): string
    {
        return 'index_redirect';
    }

    /**
     * @return string
     */
    public function actionAccountUnLock(): string
    {
        return 'index_redirect';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function actionAccountEdit(): string
    {
        return 'account_edit';
    }

    /**
     * @return string
     */
    public function actionAccountDelete(): string
    {
        return 'index_redirect';
    }

    /**
     * @return string
     */
    public function actionGroupEdit(): string
    {
        return 'group_edit';
    }

    /**
     * @return string
     */
    public function actionGroupDelete(): string
    {
        return 'group_redirect';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getNextAction(): string
    {
        return 'account_view';
    }

    /**
     *
     */
    public function actionQuickChangeLanguage(): void
    {
        $this->changeAdminUserLanguage(Request::verifyGPDataString('language'));
        $url = Request::verifyGPDataString('referer');
        if (!\str_starts_with($url, Shop::getAdminURL())) {
            return;
        }
        \header('Location: ' . $url);
    }

    /**
     * @param string $tab
     */
    public function benutzerverwaltungRedirect(string $tab = ''): void
    {
        exit;
    }

    /**
     * @param string $step
     * @throws \SmartyException
     */
    public function finalize(string $step): void
    {
        $this->smarty->assign('action', $step)
            ->assign('cTab', Text::filterXSS(Request::verifyGPDataString('tab')))
            ->display('benutzer.tpl');
    }

    /**
     * @param string $error
     */
    public function addError(string $error): void
    {
        $this->messages['error'] .= $error;
    }

    /**
     * @param string $notice
     */
    public function addNotice(string $notice): void
    {
        $this->messages['notice'] .= $notice;
    }

    /**
     * @return string
     */
    public function getNotice(): string
    {
        return $this->messages['notice'];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->messages['error'];
    }
}
