<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\Backend\Settings\Item;
use JTL\Backend\Settings\Manager;
use JTL\DB\DbInterface;
use JTL\DB\SqlObject;
use JTL\Helpers\Text;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;
use StringHandler;
use function Functional\filter;
use function Functional\flatten;

/**
 * Class Base
 * @package Backend\Settings
 */
class Base implements Section
{
    use MagicCompatibilityTrait;

    /**
     * @var bool
     */
    public $hasSectionMarkup = false;

    /**
     * @var bool
     */
    public $hasValueMarkup = false;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $menuID = 0;

    /**
     * @var int
     */
    protected $sortID = 0;

    /**
     * @var string
     */
    protected $permission;

    /**
     * @var array
     */
    protected $configData;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Item[]
     */
    protected $items = [];

    /**
     * @var string[]
     */
    protected $mapping = [
        'cName'                 => 'Name',
        'kEinstellungenSektion' => 'SectionID',
        'nSort'                 => 'Sort',
        'kAdminmenueGruppe'     => 'MenuID'
    ];

    /**
     * @inheritdoc
     */
    public function __construct(Manager $manager, int $sectionID)
    {
        $this->manager = $manager;
        $this->db      = $manager->getDB();
        $this->smarty  = $manager->getSmarty();
        $this->id      = $sectionID;
        $this->initBaseData();
    }

    protected function initBaseData(): void
    {
        $data = $this->db->select('teinstellungensektion', 'kEinstellungenSektion', $this->id);
        if ($data !== null) {
            $this->name       = \__('configsection_' . $this->id);
            $this->menuID     = (int)$data->kAdminmenueGruppe;
            $this->sortID     = (int)$data->nSort;
            $this->permission = $data->cRecht;
        }
    }

    /**
     * @inheritdoc
     */
    public function validate($conf, &$confValue): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setValue(&$conf, $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getSectionMarkup(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getValueMarkup($conf): string
    {
        return '';
    }

    public function getConfigData(): array
    {
        return $this->configData ?? $this->generateConfigData();
    }

    /**
     * @param SqlObject|null $sql
     * @return array
     */
    public function generateConfigData(SqlObject $sql = null): array
    {
        $sql = $sql ?? new SqlObject();

        $this->configData = $this->db->getObjects(
            "SELECT ec.*, e.cWert AS currentValue, ted.cWert AS defaultValue
                FROM teinstellungenconf AS ec
                LEFT JOIN teinstellungen AS e
                    ON e.cName = ec.cWertName
                LEFT JOIN teinstellungen_default AS ted
                    ON ted.cName = ec.cWertName
                WHERE ec.kEinstellungenSektion = :sid
                    AND ec.nModul = 0
                    AND ec.nStandardanzeigen = 1 " . $sql->getWhere() . '
                ORDER BY ec.nSort',
            \array_merge(['sid' => $this->id], $sql->getParams())
        );

        return $this->configData;
    }

    public function setConfigData(array $data): void
    {
        $this->configData = $data;
    }

    public function update(array $data): bool
    {
        $filtered = StringHandler::filterXSS($data);
        $value    = new stdClass();
        $confData = $this->getConfigData();
        foreach ($confData as $sectionData) {
            if (!isset($filtered[$sectionData->cWertName])) {
                continue;
            }
            $value->cWert                 = $filtered[$sectionData->cWertName];
            $value->cName                 = $sectionData->cWertName;
            $value->kEinstellungenSektion = $sectionData->kEinstellungenSektion;
            switch ($sectionData->cInputTyp) {
                case 'kommazahl':
                    $value->cWert = (float)\str_replace(',', '.', $value->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $value->cWert = (int)$value->cWert;
                    break;
                case 'text':
                    $value->cWert = \mb_substr($value->cWert, 0, 255);
                    break;
                case 'pass':
                    $value->cWert = $data[$sectionData->cWertName];
                    break;
                default:
                    break;
            }
            if (!$this->validate($sectionData, $filtered[$sectionData->cWertName])) {
                continue;
            }
            if (\is_array($filtered[$sectionData->cWertName])) {
                $this->manager->addLogListbox($sectionData->cWertName, $filtered[$sectionData->cWertName]);
            }
            $this->db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$sectionData->kEinstellungenSektion, $sectionData->cWertName]
            );
            if (\is_array($filtered[$sectionData->cWertName])) {
                foreach ($filtered[$sectionData->cWertName] as $cWert) {
                    $value->cWert = $cWert;
                    $this->db->insert('teinstellungen', $value);
                }
            } else {
                $this->db->insert('teinstellungen', $value);
                $this->manager->addLog(
                    $sectionData->cWertName,
                    $sectionData->currentValue,
                    $filtered[$sectionData->cWertName]
                );
            }
        }

        return true;
    }

    public function loadCurrentData(): array
    {
        $getText   = Shop::Container()->getGetText();
        foreach ($this->getConfigData() as $config2) {
            $config = new Item();
            $config->cConf = $config2->cConf;
            $config->kEinstellungenConf    = (int)$config2->kEinstellungenConf;
            $config->kEinstellungenSektion = (int)$config2->kEinstellungenSektion;
            $config->nStandardAnzeigen     = (int)$config2->nStandardAnzeigen;
            $config->nSort                 = (int)$config2->nSort;
            $config->nModul                = (int)$config2->nModul;
            $config->cInputTyp                = $config2->cInputTyp;
            $config->cWertName                = $config2->cWertName;
            $config->defaultValue = $config2->defaultValue;
            $config->currentValue = $config2->currentValue;
            $getText->localizeConfig($config);
            //@ToDo: Setting 492 is the only one listbox at the moment.
            //But In special case of setting 492 values come from kKundengruppe instead of teinstellungenconfwerte
            if ($config->cInputTyp === 'listbox' && $config->kEinstellungenConf === 492) {
                $config->ConfWerte = $this->db->getObjects(
                    'SELECT kKundengruppe AS cWert, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC'
                );
            } elseif (\in_array($config->cInputTyp, ['selectbox', 'listbox'], true)) {
                $config->ConfWerte = $this->db->selectAll(
                    'teinstellungenconfwerte',
                    'kEinstellungenConf',
                    $config->kEinstellungenConf,
                    '*',
                    'nSort'
                );

                $getText->localizeConfigValues($config, $config->ConfWerte);
            }
            if ($config->cInputTyp === 'listbox') {
                $setValue              = $this->db->selectAll(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [(int)$config->kEinstellungenSektion, $config->cWertName]
                );
                $config->gesetzterWert = $setValue;
            } else {
                $setValue              = $this->db->select(
                    'teinstellungen',
                    'kEinstellungenSektion',
                    (int)$config->kEinstellungenSektion,
                    'cName',
                    $config->cWertName
                );
                $config->gesetzterWert = isset($setValue->cWert)
                    ? Text::htmlentities($setValue->cWert)
                    : null;
            }
            $this->setValue($config, $setValue);
            $this->items[] = $config;
        }

        return $this->items;
    }



    /**
     * settings page is separated but has same config group as parent config page, filter these settings
     *
     * @param array $confData
     * @param string $filter
     * @return array
     */
    public function getFilteredConfData(array $confData, string $filter): array
    {
        $keys = [
            'configgroup_5_product_question'  => [
                'configgroup_5_product_question',
                'artikeldetails_fragezumprodukt_anzeigen',
                'artikeldetails_fragezumprodukt_email',
                'produktfrage_abfragen_anrede',
                'produktfrage_abfragen_vorname',
                'produktfrage_abfragen_nachname',
                'produktfrage_abfragen_firma',
                'produktfrage_abfragen_tel',
                'produktfrage_abfragen_fax',
                'produktfrage_abfragen_mobil',
                'produktfrage_kopiekunde',
                'produktfrage_sperre_minuten',
                'produktfrage_abfragen_captcha'
            ],
            'configgroup_5_product_available' => [
                'configgroup_5_product_available',
                'benachrichtigung_nutzen',
                'benachrichtigung_abfragen_vorname',
                'benachrichtigung_abfragen_nachname',
                'benachrichtigung_sperre_minuten',
                'benachrichtigung_abfragen_captcha',
                'benachrichtigung_min_lagernd'
            ]
        ];
        if (!\extension_loaded('soap')) {
            $keys['configgroup_6_vat_id'] = [
                'shop_ustid_bzstpruefung',
                'shop_ustid_force_remote_check'
            ];
        }

        if ($filter !== '' && isset($keys[$filter])) {
            $keysToFilter = $keys[$filter];

            return filter($confData, static function ($e) use ($keysToFilter) {
                return \in_array($e->cWertName, $keysToFilter, true);
            });
        }
        $keysToFilter = flatten($keys);

        return filter($confData, static function ($e) use ($keysToFilter) {
            return !\in_array($e->cWertName, $keysToFilter, true);
        });
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getMenuID(): int
    {
        return $this->menuID;
    }

    /**
     * @param int $menuID
     */
    public function setMenuID(int $menuID): void
    {
        $this->menuID = $menuID;
    }

    /**
     * @return int
     */
    public function getSortID(): int
    {
        return $this->sortID;
    }

    /**
     * @param int $sortID
     */
    public function setSortID(int $sortID): void
    {
        $this->sortID = $sortID;
    }

    /**
     * @return string
     */
    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * @param string $permission
     */
    public function setPermission(string $permission): void
    {
        $this->permission = $permission;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                 = \get_object_vars($this);
        $res['db']           = '*truncated*';
        $res['smarty']       = '*truncated*';
        $res['getText']      = '*truncated*';
        $res['alertService'] = '*truncated*';
        $res['manager']      = '*truncated*';
        $res['adminAccount'] = '*truncated*';

        return $res;
    }
}
