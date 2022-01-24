<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\Backend\Settings\Item;
use JTL\Backend\Settings\Manager;
use JTL\DB\DbInterface;
use JTL\DB\SqlObject;
use JTL\Helpers\Text;
use JTL\L10n\GetText;
use JTL\MagicCompatibilityTrait;
use JTL\Smarty\JTLSmarty;
use stdClass;
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
    protected $hasSectionMarkup = false;

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
     * @var string
     */
    protected $sectionMarkup = '';

    /**
     * @var int
     */
    protected $menuID = 0;

    /**
     * @var int
     */
    protected $sortID = 0;

    /**
     * @var int
     */
    protected $configCount = 0;

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
     * @var GetText
     */
    protected $getText;

    /**
     * @var Item[]
     */
    protected $items = [];

    /**
     * @var Subsection[]
     */
    protected $subsections = [];

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var string[]
     */
    protected $mapping = [
        'cName'                 => 'Name',
        'kEinstellungenSektion' => 'SectionID',
        'nSort'                 => 'Sort',
        'anz'                   => 'ConfigCount',
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
        $this->getText = $manager->getGetText();
        $this->id      = $sectionID;
        $this->initBaseData();
    }

    public function load(): void
    {
        $data        = $this->db->getObjects(
            'SELECT ec.*, e.cWert AS currentValue, ted.cWert AS defaultValue
                FROM teinstellungenconf AS ec
                LEFT JOIN teinstellungen AS e
                    ON e.cName = ec.cWertName
                LEFT JOIN teinstellungen_default AS ted
                    ON ted.cName = ec.cWertName
                WHERE ec.kEinstellungenSektion = :sid
                    #AND ec.nModul = 0 AND ec.nStandardAnzeigen != 0
                ORDER BY ec.nSort',
            ['sid' => $this->id]
        );
        $configItems = [];
        foreach ($data as $item) {
            if ($item->cConf === 'N' && ($item->cInputTyp === '' || $item->cInputTyp === null)) {
                $config = new Subsection();
            } else {
                $config = new Item();
            }
            $config->parseFromDB($item);
            $this->getText->localizeConfig($config);
            //@ToDo: Setting 492 is the only one listbox at the moment.
            //But In special case of setting 492 values come from kKundengruppe instead of teinstellungenconfwerte
            if ($config->getInputType() === 'listbox' && $config->getID() === 492) {
                $config->setValues($this->db->getObjects(
                    'SELECT kKundengruppe AS cWert, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC'
                ));
            } elseif (\in_array($config->getInputType(), ['selectbox', 'listbox'], true)) {
                $setValues = $this->db->selectAll(
                    'teinstellungenconfwerte',
                    'kEinstellungenConf',
                    $config->getID(),
                    '*',
                    'nSort'
                );
                $this->getText->localizeConfigValues($config, $setValues);
                $config->setValues($setValues);
            } elseif ($config->getInputType() === 'selectkdngrp') {
                $config->setValues($this->db->getObjects(
                    'SELECT kKundengruppe, cName
                        FROM tkundengruppe
                        ORDER BY cStandard DESC'
                ));
            }
            if ($config->getInputType() === 'listbox') {
                $setValue = $this->db->selectAll(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$config->getConfigSectionID(), $config->getValueName()]
                );
                $config->setSetValue($setValue);
            } elseif ($config->getInputType() === 'selectkdngrp') {
                $setValue = $this->db->selectAll(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$config->getConfigSectionID(), $config->getValueName()]
                );
                $config->setSetValue($setValue);
            } else {
                $setValue = $this->db->select(
                    'teinstellungen',
                    'kEinstellungenSektion',
                    $config->getConfigSectionID(),
                    'cName',
                    $config->getValueName()
                );
                $config->setSetValue(isset($setValue->cWert)
                    ? Text::htmlentities($setValue->cWert)
                    : null);
            }
            $configItems[] = $config;
        }
        $this->subsections = [];
        $currentSubsection = null;
        foreach ($configItems as $item) {
            if (\get_class($item) === Subsection::class) {
                if ($currentSubsection !== null) {
                    $this->subsections[] = $currentSubsection;
                }
                $currentSubsection = $item;
            } else {
                if ($currentSubsection === null) {
                    $currentSubsection = new Subsection();
                }
                $currentSubsection->addItem($item);
            }
        }
        $this->subsections[] = $currentSubsection;
        $this->subsections   = \array_filter($this->subsections);
    }

    protected function initBaseData(): void
    {
        $data = $this->db->select('teinstellungensektion', 'kEinstellungenSektion', $this->id);
        if ($data !== null) {
            $this->configCount = (int)$this->db->getSingleObject(
                "SELECT COUNT(*) AS cnt
                    FROM teinstellungenconf
                    WHERE kEinstellungenSektion = :sid
                        AND cConf = 'Y'
                        AND nStandardAnzeigen = 1
                        AND nModul = 0",
                ['sid' => $this->id]
            )->cnt;
            $this->name        = \__('configsection_' . $this->id);
            $this->menuID      = (int)$data->kAdminmenueGruppe;
            $this->sortID      = (int)$data->nSort;
            $this->permission  = $data->cRecht;
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
        return $this->sectionMarkup;
    }

    /**
     * @inheritdoc
     */
    public function setSectionMarkup(string $markup): void
    {
        $this->sectionMarkup = $markup;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData(): array
    {
        return $this->configData ?? $this->generateConfigData();
    }

    /**
     * @param SqlObject|null $sql
     * @return Item[]
     */
    public function generateConfigData(SqlObject $sql = null): array
    {
        if ($sql === null) {
            $sql = new SqlObject();
            $sql->setWhere('ec.kEinstellungenSektion = :sid
                    AND ec.nModul = 0
                    AND ec.nStandardanzeigen = 1');
            $sql->addParam('sid', $this->id);
        }
        if ($sql->getOrder() === '') {
            $sql->setOrder('ec.nSort');
        }

        $data             = $this->db->getObjects(
            'SELECT ec.*, e.cWert AS currentValue, ted.cWert AS defaultValue
                FROM teinstellungenconf AS ec
                LEFT JOIN teinstellungen AS e
                    ON e.cName = ec.cWertName
                LEFT JOIN teinstellungen_default AS ted
                    ON ted.cName = ec.cWertName
                WHERE ' . $sql->getWhere() . '
                ORDER BY ' . $sql->getOrder(),
            $sql->getParams()
        );
        $this->items      = [];
        $this->configData = [];

        foreach ($data as $item) {
            $config = new Item();
            $config->parseFromDB($item);
            $this->configData[] = $config;
//            $this->items[] = $config;
        }

        return $this->configData;
    }

    public function setConfigData(array $data): void
    {
        $this->configData = $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function update(array $data, bool $filter = true): array
    {
        $unfiltered = $data;
        if ($filter === true) {
            $data = Text::filterXSS($data);
        }
        $value   = new stdClass();
        $updated = [];
        foreach ($this->getConfigData() as $sectionData) {
            $id = $sectionData->getValueName();
            if (!isset($data[$id])) {
                continue;
            }
            $value->cWert                 = $data[$id];
            $value->cName                 = $id;
            $value->kEinstellungenSektion = $sectionData->getConfigSectionID();
            switch ($sectionData->getInputType()) {
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
                    $value->cWert = $unfiltered[$id];
                    break;
                default:
                    break;
            }
            if (!$this->validate($sectionData, $data[$id])) {
                continue;
            }
            if (\is_array($data[$id])) {
                $this->manager->addLogListbox($id, $data[$id]);
            }
            $this->db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [$sectionData->getConfigSectionID(), $id]
            );
            if (\is_array($data[$id])) {
                foreach ($data[$id] as $cWert) {
                    $value->cWert = $cWert;
                    $this->db->insert('teinstellungen', $value);
                }
            } else {
                $this->db->insert('teinstellungen', $value);
                $this->manager->addLog(
                    $id,
                    $sectionData->getCurrentValue(),
                    $data[$id]
                );
            }
            $updated[] = ['id' => $id, 'value' => $data[$id]];
        }

        return $updated;
    }

    /**
     * @return Item[]
     */
    public function getItem(): array
    {
        return $this->items;
    }

    /**
     * @todo: should be renamed.
     * @todo: add to interface
     * @inheritdoc
     */
    public function loadCurrentData(): array
    {
        $this->items = [];
        foreach ($this->getConfigData() as $config) {
            $this->getText->localizeConfig($config);
            //@ToDo: Setting 492 is the only one listbox at the moment.
            //But In special case of setting 492 values come from kKundengruppe instead of teinstellungenconfwerte
            if ($config->getInputType() === 'listbox' && $config->getID() === 492) {
                $config->setValues($this->db->getObjects(
                    'SELECT kKundengruppe AS cWert, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC'
                ));
            } elseif (\in_array($config->getInputType(), ['selectbox', 'listbox'], true)) {
                $setValues = $this->db->selectAll(
                    'teinstellungenconfwerte',
                    'kEinstellungenConf',
                    $config->getID(),
                    '*',
                    'nSort'
                );
                $this->getText->localizeConfigValues($config, $setValues);
                $config->setValues($setValues);
            }
            if ($config->getInputType() === 'listbox') {
                $setValue = $this->db->selectAll(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$config->getConfigSectionID(), $config->getValueName()]
                );
                $config->setSetValue($setValue);
            } else {
                $setValue = $this->db->select(
                    'teinstellungen',
                    'kEinstellungenSektion',
                    $config->getConfigSectionID(),
                    'cName',
                    $config->getValueName()
                );
                $config->setSetValue(isset($setValue->cWert)
                    ? Text::htmlentities($setValue->cWert)
                    : null);
            }
            $this->setValue($config, $setValue);
            $this->items[] = $config;
        }
//        Shop::dbg(count($this->items), false, 'count:');

        return $this->items;
    }

    /**
     * settings page is separated but has same config group as parent config page, filter these settings
     *
     * @param array  $confData
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

            return filter($confData, static function (Item $e) use ($keysToFilter) {
                return \in_array($e->getValueName(), $keysToFilter, true);
            });
        }
        $keysToFilter = flatten($keys);

        return filter($confData, static function (Item $e) use ($keysToFilter) {
            return !\in_array($e->getValueName(), $keysToFilter, true);
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
     * @return int
     */
    public function getConfigCount(): int
    {
        return $this->configCount;
    }

    /**
     * @param int $configCount
     */
    public function setConfigCount(int $configCount): void
    {
        $this->configCount = $configCount;
    }

    /**
     * @return Subsection[]
     */
    public function getSubsections(): array
    {
        return $this->subsections;
    }

    /**
     * @param Subsection[] $subsections
     */
    public function setSubsections(array $subsections): void
    {
        $this->subsections = $subsections;
    }

    /**
     * @return bool
     */
    public function hasSectionMarkup(): bool
    {
        return $this->hasSectionMarkup;
    }

    /**
     * @param bool $hasSectionMarkup
     */
    public function setHasSectionMarkup(bool $hasSectionMarkup): void
    {
        $this->hasSectionMarkup = $hasSectionMarkup;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setURL(?string $url): void
    {
        $this->url = $url;
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
