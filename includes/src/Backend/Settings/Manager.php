<?php declare(strict_types=1);

namespace JTL\Backend\Settings;

use Illuminate\Support\Collection;
use JTL\Backend\AdminAccount;
use JTL\Backend\Settings\Sections\SectionInterface;
use JTL\DB\DbInterface;
use JTL\GeneralDataProtection\IpAnonymizer;
use JTL\Helpers\Request;
use JTL\L10n\GetText;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class Manager
 * @package JTL\Backend\Settings
 */
class Manager
{
    /**
     * @var string[]
     */
    protected array $listboxLogged = [];

    /**
     * Manager constructor.
     * @param DbInterface $db
     * @param JTLSmarty $smarty
     * @param AdminAccount $adminAccount
     * @param GetText $getText
     * @param AlertServiceInterface $alertService
     */
    public function __construct(
        protected DbInterface $db,
        protected JTLSmarty $smarty,
        protected AdminAccount $adminAccount,
        protected GetText $getText,
        protected AlertServiceInterface $alertService
    ) {
        $getText->loadAdminLocale('configs/configs');
        $getText->loadConfigLocales(true, true);
    }

    /**
     * @param string $setting
     * @param null|string $oldValue
     * @param null|string $newValue
     */
    public function addLog(string $setting, ?string $oldValue, ?string $newValue): void
    {
        if ($oldValue === null
            || $newValue === null
            || $oldValue === $newValue
        ) {
            return;
        }

        //do not write any password to the log
        if (str_ends_with($setting, '_pass')) {
            $oldValue = '***';
            $newValue = '***';
        }

        $this->db->queryPrepared(
            'INSERT INTO teinstellungenlog (kAdminlogin, cAdminname, cIP, cEinstellungenName, cEinstellungenWertAlt,
                               cEinstellungenWertNeu, dDatum)
                SELECT tadminlogin.kAdminlogin, tadminlogin.cName, :cIP, :cEinstellungenName, :cEinstellungenWertAlt,
                               :cEinstellungenWertNeu, NOW()
                FROM tadminlogin
                WHERE tadminlogin.kAdminlogin = :kAdminLogin',
            [
                'kAdminLogin'           => $this->adminAccount->getID(),
                'cIP'                   => (new IpAnonymizer(Request::getRealIP()))->anonymize(),
                'cEinstellungenName'    => $setting,
                'cEinstellungenWertAlt' => $oldValue,
                'cEinstellungenWertNeu' => $newValue,
            ]
        );
    }

    /**
     * @param string $setting
     * @param array $newValue
     */
    public function addLogListbox(string $setting, array $newValue): void
    {
        if (\in_array($setting, $this->listboxLogged, true)) {
            return;
        }
        $this->listboxLogged[] = $setting;
        $oldValues             = $this->db->getCollection(
            'SELECT cWert
                FROM teinstellungen
                WHERE cName = :setting',
            ['setting' => $setting]
        )->pluck('cWert')->toArray();
        \sort($oldValues);
        \sort($newValue);

        $this->addLog($setting, \implode(',', $oldValues), \implode(',', $newValue));
    }

    /**
     * @param string $settingName
     * @return string
     * @throws \SmartyException
     */
    public function getSettingLog(string $settingName): string
    {
        $logs = [];
        $data = $this->db->getObjects(
            "SELECT el.*, IF(
                    al.cName = el.cAdminname,
                    el.cAdminname,
                    CONCAT(el.cAdminname, ' (', COALESCE(al.cName, :unknown), ')')
                ) AS adminName , ec.cInputTyp as settingType
                FROM teinstellungenlog AS el
                LEFT JOIN tadminlogin AS al
                    USING (kAdminlogin)
                LEFT JOIN teinstellungenconf AS ec
                    ON ec.cWertName = el.cEinstellungenName
                WHERE el.cEinstellungenName = :settingName
                ORDER BY el.dDatum DESC",
            [
                'settingName' => $settingName,
                'unknown'     => \__('unknown'),
            ]
        );
        foreach ($data as $log) {
            $logs[] = (new Log())->init($log);
        }

        return $this->smarty->assign('logs', $logs)->fetch('snippets/einstellungen_log_content.tpl');
    }

    /**
     * @param string $settingName
     */
    public function resetSetting(string $settingName): void
    {
        $defaultValue = $this->db->getSingleObject(
            'SELECT cWert
                 FROM teinstellungen_default
                 WHERE cName = :settingName',
            ['settingName' => $settingName]
        );
        if ($defaultValue === null) {
            $this->alertService->addDanger(
                \sprintf(\__('resetSettingDefaultValueNotFound'), $settingName),
                'resetSettingDefaultValueNotFound'
            );
            return;
        }

        $oldValue = $this->db->getSingleObject(
            'SELECT cWert
                 FROM teinstellungen
                 WHERE cName = :settingName',
            ['settingName' => $settingName]
        );
        $this->db->queryPrepared(
            'UPDATE teinstellungen
                 SET cWert = :defaultValue
                 WHERE cName = :settingName',
            [
                'settingName'  => $settingName,
                'defaultValue' => $defaultValue->cWert
            ]
        );
        $this->addLog($settingName, $oldValue->cWert ?? '', $defaultValue->cWert);
    }

    /**
     * @param string $where
     * @param string $limit
     * @return Collection
     */
    public function getAllSettingLogs(string $where = '', string $limit = ''): Collection
    {
        $this->getText->loadConfigLocales();

        return $this->db->getCollection(
            "SELECT el.*, IF(
                    al.cName = el.cAdminname,
                    el.cAdminname,
                    CONCAT(el.cAdminname, ' (', COALESCE(al.cName, :unknown), ')')
                ) AS adminName , ec.cInputTyp as settingType
                FROM teinstellungenlog AS el
                LEFT JOIN tadminlogin AS al
                    USING (kAdminlogin)
                LEFT JOIN teinstellungenconf AS ec
                    ON ec.cWertName = el.cEinstellungenName" .
                ($where !== '' ? ' WHERE ' . $where : '') .
                ' ORDER BY dDatum DESC ' .
                ($limit !== '' ? ' LIMIT ' . $limit : ''),
            [
                'unknown' => \__('unknown'),
            ]
        )->map(static function ($item): Log {
            return (new Log())->init($item);
        });
    }

    /**
     * @param string $where
     * @return int
     */
    public function getAllSettingLogsCount(string $where = ''): int
    {
        return (int)$this->db->getSingleObject(
            'SELECT COUNT(kEinstellungenLog) AS cnt
                FROM teinstellungenlog' .
                ($where !== '' ? ' WHERE ' . $where : '')
        )->cnt;
    }

    /**
     * @return SectionInterface[]
     */
    public function getAllSections(): array
    {
        $sections   = [];
        $factory    = new SectionFactory();
        $sectionIDs = $this->db->getObjects(
            'SELECT kEinstellungenSektion AS id
                FROM teinstellungensektion
                ORDER BY kEinstellungenSektion'
        );
        foreach ($sectionIDs as $item) {
            $sections[] = $factory->getSection((int)$item->id, $this);
        }

        return $sections;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return JTLSmarty
     */
    public function getSmarty(): JTLSmarty
    {
        return $this->smarty;
    }

    /**
     * @param JTLSmarty $smarty
     */
    public function setSmarty(JTLSmarty $smarty): void
    {
        $this->smarty = $smarty;
    }

    /**
     * @return AdminAccount
     */
    public function getAdminAccount(): AdminAccount
    {
        return $this->adminAccount;
    }

    /**
     * @param AdminAccount $adminAccount
     */
    public function setAdminAccount(AdminAccount $adminAccount): void
    {
        $this->adminAccount = $adminAccount;
    }

    /**
     * @return GetText
     */
    public function getGetText(): GetText
    {
        return $this->getText;
    }

    /**
     * @param GetText $getText
     */
    public function setGetText(GetText $getText): void
    {
        $this->getText = $getText;
    }

    /**
     * @return AlertServiceInterface
     */
    public function getAlertService(): AlertServiceInterface
    {
        return $this->alertService;
    }

    /**
     * @param AlertServiceInterface $alertService
     */
    public function setAlertService(AlertServiceInterface $alertService): void
    {
        $this->alertService = $alertService;
    }
}
