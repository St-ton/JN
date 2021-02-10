<?php declare(strict_types=1);

namespace JTL\Backend\Settings;

use JTL\Backend\AdminAccount;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\L10n\GetText;
use JTL\Smarty\JTLSmarty;

/**
 * Class SettingSection
 * @package Backend\Settings
 */
class Manager
{
    /**
     * @var bool
     */
    public $hasSectionMarkup = false;

    /**
     * @var bool
     */
    public $hasValueMarkup = false;

    /**
     * @var Manager[]
     */
    private $instances = [];

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * @var AdminAccount
     */
    protected $adminAccount;

    /**
     * @var GetText
     */
    protected $getText;

    /**
     * Manager constructor.
     * @param DbInterface $db
     * @param JTLSmarty $smarty
     * @param AdminAccount $adminAccount
     * @param GetText $getText
     */
    public function __construct(DbInterface $db, JTLSmarty $smarty, AdminAccount $adminAccount, GetText $getText)
    {
        $getText->loadConfigLocales(true, true);

        $this->db           = $db;
        $this->smarty       = $smarty;
        $this->adminAccount = $adminAccount;
        $this->getText      = $getText;
    }

    /**
     * @param int $sectionID
     * @return static
     */
    public function getInstance(int $sectionID)
    {
        if (!isset($this->instances[$sectionID])) {
            $section = $this->db->select('teinstellungensektion', 'kEinstellungenSektion', $sectionID);
            if (isset($section->kEinstellungenSektion)) {
                $className = 'JTL\Backend\Settings\Sections\\' . \preg_replace(
                    ['([üäöÜÄÖ])', '/[^a-zA-Z_]/'],
                    ['$1e', ''],
                    $section->cName
                );
                if (\class_exists($className)) {
                    $this->instances[$sectionID] = new $className($this->db, $this->smarty);

                    return $this->instances[$sectionID];
                }
            }
            $this->instances[$sectionID] = new self($this->db, $this->smarty, $this->adminAccount, $this->getText);
        }

        return $this->instances[$sectionID];
    }

    /**
     * @param object $conf
     * @param object $confValue
     * @return bool
     */
    public function validate($conf, &$confValue): bool
    {
        return true;
    }

    /**
     * @param object $conf
     * @param mixed  $value
     * @return static
     */
    public function setValue(&$conf, $value): self
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getSectionMarkup(): string
    {
        return '';
    }

    /**
     * @param object $conf
     * @return string
     */
    public function getValueMarkup($conf): string
    {
        return '';
    }


    /**
     * @param string $setting
     * @param null|string $oldValue
     * @param null|string $newValue
     */
    public function addLog(string $setting, ?string $oldValue, ?string $newValue): void
    {
        if ($oldValue === null || $newValue === null) {
            return;
        }
        $log                        = new \stdClass();
        $log->kAdminLogin           = $this->adminAccount->getID();
        $log->cEinstellungenName    = $setting;
        $log->cEinstellungenWertAlt = $oldValue;
        $log->cEinstellungenWertNeu = $newValue;
        $log->dDatum                = 'NOW()';

        $this->db->insert('teinstellungenlog', $log);
    }

    /**
     * @param string $settingName
     * @return string
     * @throws \SmartyException
     */
    public function getSettingLog(string $settingName): string
    {
        $logs    = [];
        $logsTMP = $this->db->queryPrepared(
            'SELECT el.*, al.cName as adminName , ec.cInputTyp as settingType
              FROM teinstellungenlog as el
              LEFT JOIN tadminlogin as al 
                USING(kAdminlogin)
              LEFT JOIN teinstellungenconf as ec
                ON ec.cWertName=el.cEinstellungenName
              WHERE el.cEinstellungenName = :settingName
              ORDER BY el.dDatum DESC',
            ['settingName' => $settingName],
            ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($logsTMP as $log) {
            $logs[] = (new Log())->init($log);
        }
        $this->smarty->assign('logs', $logs);

        return $this->smarty->fetch('snippets/einstellungen_log_content.tpl');
    }

    /**
     * @param string $settingName
     */
    public function resetSetting(string $settingName): void
    {
        $defaultValue = $this->db->queryPrepared(
            'SELECT cWert
              FROM teinstellungen_default
              WHERE cName=:settingName',
            ['settingName' => $settingName],
            ReturnType::SINGLE_OBJECT
        );
        $oldValue     = $this->db->queryPrepared(
            'SELECT cWert
              FROM teinstellungen
              WHERE cName=:settingName',
            ['settingName' => $settingName],
            ReturnType::SINGLE_OBJECT
        );
        $this->db->queryPrepared(
            'UPDATE teinstellungen
              SET cWert=:defaultValue
              WHERE cName=:settingName',
            [
                'settingName' => $settingName,
                'defaultValue' => $defaultValue->cWert
            ],
            ReturnType::DEFAULT
        );
        $this->addLog($settingName, $oldValue->cWert, $defaultValue->cWert);
    }
}
