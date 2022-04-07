<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AdminAccount;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\Subsection;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\L10n\GetText;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\pluck;

/**
 * Class AbstractController
 * @package JTL\Router\Controller\Backend
 */
abstract class AbstractBackendController implements ControllerInterface
{
    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @var JTLCacheInterface
     */
    protected JTLCacheInterface $cache;

    /**
     * @var JTLSmarty
     */
    protected JTLSmarty $smarty;

    /**
     * @var AlertServiceInterface
     */
    protected AlertServiceInterface $alertService;

    /**
     * @var AdminAccount
     */
    protected AdminAccount $account;

    /**
     * @var GetText
     */
    protected GetText $getText;

    /**
     * @var string
     */
    protected string $step = '';

    /**
     * @var string
     */
    protected string $route = '';

    /**
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AlertServiceInterface $alertService
     * @param AdminAccount          $account
     * @param GetText               $getText
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        AlertServiceInterface $alertService,
        AdminAccount $account,
        GetText $getText
    ) {
        $this->db           = $db;
        $this->cache        = $cache;
        $this->alertService = $alertService;
        $this->account      = $account;
        $this->getText      = $getText;
    }

    /**
     * @param string $permissions
     * @return void
     */
    protected function checkPermissions(string $permissions): void
    {
        $this->account->permission($permissions, true, true);
    }

    /**
     * @param string $permissions
     * @return bool
     */
    protected function hasPermissions(string $permissions): bool
    {
        return $this->account->permission($permissions);
    }

    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     * @todo!!!!
     */
    public function notFoundResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        return (new Response())->withStatus(404);
    }

    /**
     * @former setzeSprache()
     */
    public function setzeSprache(): void
    {
        if (Form::validateToken() && Request::verifyGPCDataInt('sprachwechsel') === 1) {
            // Wähle explizit gesetzte Sprache als aktuelle Sprache
            $language = $this->db->select('tsprache', 'kSprache', Request::postInt('kSprache'));
            if ((int)$language->kSprache > 0) {
                $_SESSION['editLanguageID']   = (int)$language->kSprache;
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }

        if (!isset($_SESSION['editLanguageID'])) {
            // Wähle Standardsprache als aktuelle Sprache
            $language = $this->db->select('tsprache', 'cShopStandard', 'Y');
            if ((int)$language->kSprache > 0) {
                $_SESSION['editLanguageID']   = (int)$language->kSprache;
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }
        if (isset($_SESSION['editLanguageID']) && empty($_SESSION['editLanguageCode'])) {
            // Fehlendes cISO ergänzen
            $language = $this->db->select('tsprache', 'kSprache', (int)$_SESSION['editLanguageID']);
            if ((int)$language->kSprache > 0) {
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }
    }

    /**
     * @param array $settingsIDs
     * @param array $post
     * @param array $tags
     * @param bool $byName
     * @return string
     */
    public function saveAdminSettings(
        array $settingsIDs,
        array $post,
        array $tags = [\CACHING_GROUP_OPTION],
        bool $byName = false
    ): string {
        $manager = new Manager($this->db, $this->smarty, $this->account, $this->getText, $this->alertService);
        if (Request::postVar('resetSetting') !== null) {
            $manager->resetSetting(Request::postVar('resetSetting'));

            return \__('successConfigReset');
        }
        $where    = $byName
            ? "WHERE ec.cWertName IN ('" . \implode("','", $settingsIDs) . "')"
            : 'WHERE ec.kEinstellungenConf IN (' . \implode(',', \array_map('\intval', $settingsIDs)) . ')';
        $confData = $this->db->getObjects(
            'SELECT ec.*, e.cWert AS currentValue
                FROM teinstellungenconf AS ec
                LEFT JOIN teinstellungen AS e 
                    ON e.cName = ec.cWertName
                ' . $where . "
                AND ec.cConf = 'Y'
                ORDER BY ec.nSort"
        );
        if (\count($confData) === 0) {
            return \__('errorConfigSave');
        }
        foreach ($confData as $config) {
            $val                        = new stdClass();
            $val->cWert                 = $post[$config->cWertName] ?? null;
            $val->cName                 = $config->cWertName;
            $val->kEinstellungenSektion = (int)$config->kEinstellungenSektion;
            switch ($config->cInputTyp) {
                case 'kommazahl':
                    $val->cWert = (float)$val->cWert;
                    break;
                case 'zahl':
                case 'number':
                    $val->cWert = (int)$val->cWert;
                    break;
                case 'text':
                    $val->cWert = Text::filterXSS(mb_substr($val->cWert, 0, 255));
                    break;
                case 'listbox':
                    $this->bearbeiteListBox($val->cWert, $val->cName, $val->kEinstellungenSektion, $manager);
                    break;
                default:
                    break;
            }
            if ($config->cInputTyp !== 'listbox') {
                $this->db->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [(int)$config->kEinstellungenSektion, $config->cWertName]
                );
                $this->db->insert('teinstellungen', $val);

                $manager->addLog($config->cWertName, $config->currentValue, $post[$config->cWertName]);
            }
        }
        $this->cache->flushTags($tags);

        return \__('successConfigSave');
    }

    /**
     * @param mixed   $listBoxes
     * @param string  $valueName
     * @param int     $configSectionID
     * @param Manager $manager
     * @return void
     */
    private function bearbeiteListBox($listBoxes, string $valueName, int $configSectionID, Manager $manager): void
    {
        if (\is_array($listBoxes) && \count($listBoxes) > 0) {
            $manager->addLogListbox($valueName, $listBoxes);
            $this->db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$configSectionID, $valueName]
            );
            foreach ($listBoxes as $listBox) {
                $newConf                        = new stdClass();
                $newConf->cWert                 = $listBox;
                $newConf->cName                 = $valueName;
                $newConf->kEinstellungenSektion = $configSectionID;

                $this->db->insert('teinstellungen', $newConf);
            }
        } elseif ($valueName === 'bewertungserinnerung_kundengruppen') {
            // Leere Kundengruppen Work Around
            $customerGroup = $this->db->select('tkundengruppe', 'cStandard', 'Y');
            if ($customerGroup->kKundengruppe > 0) {
                $this->db->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$configSectionID, $valueName]
                );
                $newConf                        = new stdClass();
                $newConf->cWert                 = $customerGroup->kKundengruppe;
                $newConf->cName                 = $valueName;
                $newConf->kEinstellungenSektion = \CONF_BEWERTUNG;

                $db->insert('teinstellungen', $newConf);
            }
        }
    }

    /**
     * @param int   $sectionID
     * @param array $post
     * @param array $tags
     * @return string
     */
    public function saveAdminSectionSettings(int $sectionID, array $post, array $tags = [\CACHING_GROUP_OPTION]): string
    {
        if (!Form::validateToken()) {
            $msg = \__('errorCSRF');
            $this->alertService->addError($msg, 'saveSettingsErrCsrf');

            return $msg;
        }
        $manager = new Manager(
            $this->db,
            Shop::Smarty(),
            $this->account,
            $this->getText,
            $this->alertService
        );
        if (Request::postVar('resetSetting') !== null) {
            $manager->resetSetting(Request::postVar('resetSetting'));

            return \__('successConfigReset');
        }
        $section = (new SectionFactory())->getSection($sectionID, $manager);
        $section->update($post, true, $tags);
        $invalid = $section->getUpdateErrors();

        if ($invalid > 0) {
            $msg = \__('errorConfigSave');
            $this->alertService->addError($msg, 'saveSettingsErr');

            return $msg;
        }
        $msg = \__('successConfigSave');
        $this->alertService->addSuccess($msg, 'saveSettings');

        return $msg;
    }

    /**
     * @param int|array $configSectionID
     * @param bool $byName
     * @return stdClass[]
     */
    public function getAdminSectionSettings($configSectionID, bool $byName = false): array
    {
        $sections       = [];
        $filterNames    = [];
        $sectionFactory = new SectionFactory();
        $settingManager = new Manager($this->db, $this->smarty, $this->account, $this->getText, $this->alertService);
        if ($byName) {
            $sql = new SqlObject();
            $in  = [];
            foreach ($configSectionID as $i => $item) {
                $sql->addParam(':itm' . $i, $item);
                $in[] = ':itm' . $i;
            }
            $sectionIDs      = $this->db->getObjects(
                'SELECT DISTINCT ec.kEinstellungenSektion AS id
                FROM teinstellungenconf AS ec
                LEFT JOIN teinstellungen_default AS e
                    ON e.cName = ec.cWertName 
                    WHERE ec.cWertName IN (' . \implode(',', $in) . ')
                    ORDER BY ec.nSort',
                $sql->getParams()
            );
            $filterNames     = $configSectionID;
            $configSectionID = \array_map('\intval', pluck($sectionIDs, 'id'));
        }
        foreach ((array)$configSectionID as $id) {
            $section = $sectionFactory->getSection($id, $settingManager);
            $section->load();
            $sections[] = $section;
        }
        if (\count($filterNames) > 0) {
            $section    = $sectionFactory->getSection(1, $settingManager);
            $subsection = new Subsection();
            foreach ($sections as $_section) {
                foreach ($_section->getSubsections() as $_subsection) {
                    foreach ($_subsection->getItems() as $item) {
                        if (\in_array($item->getValueName(), $filterNames, true)) {
                            $subsection->addItem($item);
                        }
                    }
                }
            }
            $section->setSubsections([$subsection]);
            $sections = [$section];
        }
        $this->smarty->assign('sections', $sections);

        return $sections;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     */
    public function setRoute(string $route): void
    {
        $this->route = $route;
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
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->cache;
    }

    /**
     * @param JTLCacheInterface $cache
     */
    public function setCache(JTLCacheInterface $cache): void
    {
        $this->cache = $cache;
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

    /**
     * @return AdminAccount
     */
    public function getAccount(): AdminAccount
    {
        return $this->account;
    }

    /**
     * @param AdminAccount $account
     */
    public function setAccount(AdminAccount $account): void
    {
        $this->account = $account;
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
     * @return string
     */
    public function getStep(): string
    {
        return $this->step;
    }

    /**
     * @param string $step
     */
    public function setStep(string $step): void
    {
        $this->step = $step;
    }
}
