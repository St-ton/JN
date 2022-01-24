<?php declare(strict_types=1);


namespace JTL\Backend\Settings;

use JTL\Backend\Settings\Sections\SectionInterface;
use JTL\DB\DbInterface;
use JTL\DB\SqlObject;
use JTL\Helpers\Text;
use JTL\L10n\GetText;
use stdClass;

class Search
{
    /**
     * @var int
     */
    public $mode = 0;

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var GetText
     */
    protected $getText;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var Manager
     */
    protected $manager;

    public function __construct(DbInterface $db, GetText $gettext, Manager $manager)
    {
        $this->db      = $db;
        $this->getText = $gettext;
        $this->manager = $manager;
    }

    private function getSqlObject(string $query): SqlObject
    {
        $sql      = new SqlObject();
        $where    = "(ec.cModulId IS NULL OR ec.cModulId = '') AND ec.kEinstellungenSektion != " . \CONF_EXPORTFORMATE . ' ';
        $idList   = \explode(',', $query);
        $isIdList = count($idList) > 1;
        if ($isIdList) {
            foreach ($idList as $i => $item) {
                $idList[$i] = (int)$item;

                if ($idList[$i] === 0) {
                    $isIdList = false;
                    break;
                }
            }
        }

        if ($isIdList) {
            $where      .= ' AND kEinstellungenConf IN (' . \implode(', ', $idList) . ')';
            $this->mode  = 1;
            $this->title = \sprintf(\__('searchForID'), \implode(', ', $idList));
        } else {
            $rangeList = \explode('-', $query);
            $isIdRange = count($rangeList) === 2;
            if ($isIdRange) {
                $rangeList[0] = (int)$rangeList[0];
                $rangeList[1] = (int)$rangeList[1];
                if ($rangeList[0] === 0 || $rangeList[1] === 0) {
                    $isIdRange = false;
                }
            }
            if ($isIdRange) {
                $where      .= ' AND kEinstellungenConf BETWEEN ' . $rangeList[0] . ' AND ' . $rangeList[1];
                $where      .= " AND cConf = 'Y'";
                $this->mode  = 2;
                $this->title = \sprintf(\__('searchForIDRange'), $rangeList[0] . ' - ' . $rangeList[1]);
            } elseif ((int)$query > 0) {
                $this->mode  = 3;
                $this->title = \sprintf(\__('searchForID'), $query);
                $where      .= ' AND kEinstellungenConf = ' . (int)$query;
            } else {
                $query              = \mb_convert_case($query, \MB_CASE_LOWER);
                $queryEnt           = Text::htmlentities($query);
                $this->mode         = 4;
                $this->title        = \sprintf(\__('searchForName'), $query);
                $configTranslations = $this->getText->getAdminTranslations('configs/configs');
                $valueNames         = [];
                foreach ($configTranslations->getIterator() as $translation) {
                    $orig  = $translation->getOriginal();
                    $trans = $translation->getTranslation();
                    if ((mb_stripos($trans, $query) !== false || mb_stripos($trans, $queryEnt) !== false)
                        && mb_substr($orig, -5) === '_name'
                    ) {
                        $valueName    = \preg_replace('/(_name|_desc)$/', '', $orig);
                        $valueNames[] = "'" . $valueName . "'";
                    }
                }
                $where .= ' AND cWertName IN (' . (\implode(', ', $valueNames) ?: "''") . ')';
                $where .= " AND cConf = 'Y'";
            }
        }
        $sql->setWhere($where);

        return $sql;
    }

    /**
     * @param string $query
     * @return SectionInterface[]
     */
    public function getResultSections(string $query): array
    {
        $sqlObject  = $this->getSqlObject($query);
        $sql        = 'SELECT ec.*, e.cWert AS currentValue, ed.cWert AS defaultValue
            FROM teinstellungenconf AS ec
            LEFT JOIN teinstellungen AS e
              ON e.cName = ec.cWertName
            LEFT JOIN teinstellungen_default AS ed
              ON ed.cName = ec.cWertName
            WHERE ' . $sqlObject->getWhere() . '
            ORDER BY ec.kEinstellungenSektion, nSort';
        $data       = $this->db->getCollection($sql);
        $sectionIDs = $data->pluck('kEinstellungenSektion')->unique()->map('\intval');
        $configIDs  = $data->pluck('kEinstellungenConf')->unique()->map('\intval')->toArray();
        $factory    = new SectionFactory();
        $sections   = [];
        foreach ($sectionIDs as $sectionID) {
            $section = $factory->getSection($sectionID, $this->manager);
            $section->load();
            foreach ($section->getSubsections() as $subsection) {
                $subsection->setShow(false);
                foreach ($subsection->getItems() as $item) {
                    $menuEntry = $this->mapConfigSectionToMenuEntry($sectionID, $item->getValueName());
                    $isSpecial = $menuEntry->specialSetting ?? false;
                    if ($isSpecial !== false) {
                        $url = ($menuEntry->url ?? '') . ($menuEntry->settingsAnchor ?? '');
                    } else {
                        $url = 'einstellungen.php?cSuche=' . $item->getID() . '&einstellungen_suchen=1';
                    }
                    $item->setURL($url);
                    if (\in_array($item->getID(), $configIDs, true)) {
                        $subsection->setShow(true);
                        $subsection->setPath($menuEntry->path ?? '');
                        $subsection->setURL($menuEntry->url ?? '');
                        $item->setHighlight(true);
                    }
                }
            }
            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * @param int    $sectionID
     * @param string $groupName
     * @return stdClass
     */
    private function mapConfigSectionToMenuEntry(int $sectionID, string $groupName = 'all')
    {
        global $sectionMenuMapping;

        if (isset($sectionMenuMapping[$sectionID])) {
            if (!isset($sectionMenuMapping[$sectionID][$groupName])) {
                $groupName = 'all';
            }

            return $sectionMenuMapping[$sectionID][$groupName];
        }

        return (object)[];
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
}
