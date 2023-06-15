<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AdminAccount;
use JTL\Backend\Settings\Manager;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\Subsection;
use JTL\Cache\JTLCacheInterface;
use JTL\Campaign;
use JTL\DB\DbInterface;
use JTL\DB\SqlObject;
use JTL\Exceptions\PermissionException;
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
     * @var JTLSmarty|null
     */
    protected ?JTLSmarty $smarty = null;

    /**
     * @var string
     */
    protected string $step = '';

    /**
     * @var string
     */
    protected string $route = '';

    /**
     * @var string
     */
    protected string $baseURL;

    /**
     * @var int
     */
    protected int $currentLanguageID = 0;

    /**
     * @var string
     */
    protected string $currentLanguageCode;

    /**
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AlertServiceInterface $alertService
     * @param AdminAccount          $account
     * @param GetText               $getText
     */
    public function __construct(
        protected DbInterface $db,
        protected JTLCacheInterface $cache,
        protected AlertServiceInterface $alertService,
        protected AdminAccount $account,
        protected GetText $getText
    ) {
        $this->baseURL = Shop::getAdminURL(true);
        $this->setLanguage();
        $this->init();
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
    }

    /**
     * Gets the current scroll position and assigns it to smarty if save and continue button was used.
     * @return void
     */
    protected function assignScrollPosition(): void
    {
        $scrollPosition    = Text::filterXSS(Request::verifyGPDataString('scrollPosition'));
        $isSaveAndContinue = Request::postVar('saveAndContinue', false) !== false;
        $scrollPosition    = $isSaveAndContinue === true && \is_string($scrollPosition) ? $scrollPosition : '';

        $this->smarty->assign('scrollPosition', $scrollPosition);
    }

    /**
     * @param string $permissions
     * @return void
     * @throws PermissionException
     */
    protected function checkPermissions(string $permissions): void
    {
        // grant full access to admin
        $account = $this->account->account();
        if ($account !== false && (int)$account->oGroup->kAdminlogingruppe === \ADMINGROUP) {
            return;
        }
        $hasAccess = (isset($_SESSION['AdminAccount']->oGroup->oPermission_arr)
            && \is_array($_SESSION['AdminAccount']->oGroup->oPermission_arr)
            && \in_array($permissions, $_SESSION['AdminAccount']->oGroup->oPermission_arr, true));
        if (!$hasAccess) {
            throw new PermissionException('No permissions to access page');
        }
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
    public function setLanguage(): void
    {
        if (Form::validateToken() && Request::verifyGPCDataInt('sprachwechsel') === 1) {
            // Wähle explizit gesetzte Sprache als aktuelle Sprache
            $language = $this->db->select('tsprache', 'kSprache', Request::postInt('kSprache'));
            if ($language !== null && (int)$language->kSprache > 0) {
                $_SESSION['editLanguageID']   = (int)$language->kSprache;
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }

        if (!isset($_SESSION['editLanguageID'])) {
            $_SESSION['editLanguageID']   = 1;
            $_SESSION['editLanguageCode'] = 'ger';
            // Wähle Standardsprache als aktuelle Sprache
            $language = $this->db->select('tsprache', 'cShopStandard', 'Y');
            if ($language !== null && (int)$language->kSprache > 0) {
                $_SESSION['editLanguageID']   = (int)$language->kSprache;
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }
        if (isset($_SESSION['editLanguageID']) && empty($_SESSION['editLanguageCode'])) {
            // Fehlendes cISO ergänzen
            $language = $this->db->select('tsprache', 'kSprache', $_SESSION['editLanguageID']);
            if ($language !== null && (int)$language->kSprache > 0) {
                $_SESSION['editLanguageCode'] = $language->cISO;
            }
        }
        $this->currentLanguageID   = $_SESSION['editLanguageID'];
        $this->currentLanguageCode = $_SESSION['editLanguageCode'];
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
            $val = (object)[
                'cWert'                 => $post[$config->cWertName] ?? null,
                'cName'                 => $config->cWertName,
                'kEinstellungenSektion' => (int)$config->kEinstellungenSektion
            ];
            switch ($config->cInputTyp) {
                case 'kommazahl':
                    $val->cWert = (float)$val->cWert;
                    break;
                case 'zahl':
                case 'number':
                    $val->cWert = (int)$val->cWert;
                    break;
                case 'text':
                    $val->cWert = Text::filterXSS(\mb_substr($val->cWert, 0, 255));
                    break;
                case 'listbox':
                    $this->updateListBox($val->cWert, $val->cName, $val->kEinstellungenSektion, $manager);
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
     * @former bearbeiteListBox()
     */
    private function updateListBox(mixed $listBoxes, string $valueName, int $configSectionID, Manager $manager): void
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
            if ($customerGroup !== null && $customerGroup->kKundengruppe > 0) {
                $this->db->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$configSectionID, $valueName]
                );
                $newConf                        = new stdClass();
                $newConf->cWert                 = $customerGroup->kKundengruppe;
                $newConf->cName                 = $valueName;
                $newConf->kEinstellungenSektion = \CONF_BEWERTUNG;

                $this->db->insert('teinstellungen', $newConf);
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
            $this->smarty,
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
     * @param int|int[] $configSectionID
     * @param bool      $byName
     * @return stdClass[]
     */
    public function getAdminSectionSettings(array|int $configSectionID, bool $byName = false): array
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
     * @param bool             $getInternal
     * @param bool             $activeOnly
     * @param DbInterface|null $db
     * @return array
     */
    public static function getCampaigns(
        bool $getInternal = false,
        bool $activeOnly = true,
        ?DbInterface $db = null
    ): array {
        $activeSQL  = $activeOnly ? ' WHERE nAktiv = 1' : '';
        $interalSQL = '';
        if (!$getInternal && $activeOnly) {
            $interalSQL = ' AND nInternal = 0';
        } elseif (!$getInternal) {
            $interalSQL = ' WHERE nInternal = 0';
        }
        $campaigns = [];
        $items     = ($db ?? Shop::Container()->getDB())->getInts(
            'SELECT kKampagne
                FROM tkampagne
                ' . $activeSQL . '
                ' . $interalSQL . '
                ORDER BY kKampagne',
            'kKampagne'
        );
        foreach ($items as $campaignID) {
            $campaign = new Campaign($campaignID);
            if ($campaign->kKampagne > 0) {
                $campaigns[$campaign->kKampagne] = $campaign;
            }
        }

        return $campaigns;
    }

    /**
     * @param string|int $size
     * @return float|int|string
     * @former getMaxFileSize()
     * @since 5.2.0
     */
    public static function getMaxFileSize(string|int $size): float|int|string
    {
        return match (\mb_substr((string)$size, -1)) {
            'M', 'm' => (int)$size * 1048576,
            'K', 'k' => (int)$size * 1024,
            'G', 'g' => (int)$size * 1073741824,
            default  => $size,
        };
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
