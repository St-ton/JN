<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use InvalidArgumentException;
use JTL\Backend\Settings\Headline;
use JTL\Backend\Settings\Item;
use JTL\DB\SqlObject;
use JTL\Shop;
use stdClass;
use function Functional\filter;

/**
 * Class Search
 * @package Backend\Settings
 */
class Search extends Base
{
    /**
     * @inheritdoc
     */
    public function generateConfigData(SqlObject $sql = null): array
    {
        if ($sql === null) {
            throw new InvalidArgumentException('SqlObject cannot be null here');
        }
        $sql->setOrder('ec.kEinstellungenSektion, nSort');

        return parent::generateConfigData($sql);
    }

    /**
     * @param Item[] $config
     * @return array
     */
    public function sortiereEinstellungen(array $config): array
    {
        if (\count($config) === 0) {
            return [];
        }
        $sectionIDs = [];
        $sprt       = [];
        $tmpConf    = [];
        $sections   = [];
        foreach ($config as $conf) {
            if ($conf->getConfigSectionID() > 0 && $conf->isConfigurable()) {
                $headlineData = $this->holeEinstellungHeadline($conf->getSort(), $conf->getConfigSectionID());
                if ($headlineData !== null && !isset($sections[$conf->getConfigSectionID()])) {
                    $headline = new Headline();
                    $headline->parseFromDB($headlineData);
                    $tmpConf[]                             = $headline;
                    $sections[$conf->getConfigSectionID()] = true;
                }
                $tmpConf[] = $conf;
            }
        }
        foreach ($tmpConf as $key => $value) {
            $sectionIDs[$key] = $value->getConfigSectionID();
            $sprt[$key]       = $value->getSort();
        }
        \array_multisort($sectionIDs, \SORT_ASC, $sprt, \SORT_ASC, $tmpConf);

        return $tmpConf;
    }

    /**
     * @param stdClass $sql
     * @param int      $sort
     * @param int      $sectionID
     * @return stdClass
     */
    private function holeEinstellungAbteil(stdClass $sql, int $sort, int $sectionID): stdClass
    {
        if ($sort <= 0 || $sectionID <= 0) {
            return $sql;
        }
        $items = $this->db->getObjects(
            'SELECT ec.*, e.cWert AS currentValue, ed.cWert AS defaultValue
            FROM teinstellungenconf AS ec
            LEFT JOIN teinstellungen AS e
              ON e.cName = ec.cWertName
            LEFT JOIN teinstellungen_default AS ed
              ON ed.cName = ec.cWertName
            WHERE ec.nSort > :srt
                AND ec.kEinstellungenSektion = :sid
            ORDER BY ec.nSort',
            ['srt' => $sort, 'sid' => $sectionID]
        );
        foreach ($items as $data) {
            $item = new Item();
            $item->parseFromDB($data);
            $data->kEinstellungenConf    = (int)$data->kEinstellungenConf;
            $data->kEinstellungenSektion = (int)$data->kEinstellungenSektion;
            $data->nSort                 = (int)$data->nSort;
            $data->nStandardAnzeigen     = (int)$data->nStandardAnzeigen;
            $data->nModul                = (int)$data->nModul;
            if ($item->isConfigurable()) {
                $sql->oEinstellung_arr[] = $data;
                $sql->configData[]       = $item;
            } else {
                break;
            }
        }

        return $sql;
    }

    /**
     * @param int $sort
     * @param int $sectionID
     * @return stdClass|null
     */
    private function holeEinstellungHeadline(int $sort, int $sectionID): ?stdClass
    {
        if ($sort <= 0 || $sectionID <= 0) {
            return null;
        }
        $item = Shop::Container()->getDB()->getSingleObject(
            "SELECT *
            FROM teinstellungenconf
            WHERE nSort < :srt
                AND kEinstellungenSektion = :sid
                AND cConf = 'N'
            ORDER BY nSort DESC",
            ['srt' => $sort, 'sid' => $sectionID]
        );
        if ($item === null) {
            return null;
        }
        $item->kEinstellungenConf    = (int)$item->kEinstellungenConf;
        $item->kEinstellungenSektion = (int)$item->kEinstellungenSektion;
        $item->nSort                 = (int)$item->nSort;
        $item->nStandardAnzeigen     = (int)$item->nStandardAnzeigen;

        $menuEntry                  = mapConfigSectionToMenuEntry($sectionID, $item->cWertName);
        $configHead                 = $item;
        $configHead->cSektionsPfad  = getConfigSectionPath($menuEntry);
        $configHead->cURL           = getConfigSectionUrl($menuEntry);
        $configHead->specialSetting = isConfigSectionSpecialSetting($menuEntry);
        $configHead->settingsAnchor = getConfigSectionAnchor($menuEntry);

        return $configHead;
    }

    public function enhanceSearchResults(stdClass $sql)
    {
        /** @var Item $config */
        foreach ($sql->configData as $config) {
            if ((int)$sql->nSuchModus === 3 && $config->isConfigurable()) {
                $sql->oEinstellung_arr = [];
                $sql->configData       = [];
                $configHead            = $this->holeEinstellungHeadline($config->getSort(), $config->getConfigSectionID());
//                Shop::dbg($configHead, false, '$configHead');
                if ($configHead !== null && $configHead->kEinstellungenConf > 0) {
                    $sql->oEinstellung_arr[] = $configHead;
                    $headline                = new Headline();
                    $headline->parseFromDB($configHead);
                    $sql->configData[] = $headline;
                    $sql               = $this->holeEinstellungAbteil(
                        $sql,
                        $headline->getSort(),
                        $headline->getConfigSectionID()
                    );
                }
            } elseif (!$config->isConfigurable()) {
                $sql = $this->holeEinstellungAbteil($sql, $config->getSort(), $config->getConfigSectionID());
            }
        }
    }

    /**
     * @param Item[] $configData
     * @return array
     */
    public function groupByHeadline(array $configData): array
    {
        $headlines = filter($configData, static function ($item) {
            return \get_class($item) === Headline::class;
        });
        $items     = filter($configData, static function ($item) {
            return \get_class($item) === Item::class;
        });
        /** @var Headline $headline */
        foreach ($headlines as $headline) {
            foreach ($items as $item) {
                if ($item->getConfigSectionID() === $headline->getConfigSectionID()) {
                    $headline->oEinstellung_arr[] = $item;
                }
            }
        }

        return $headlines;
    }
}
