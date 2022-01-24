<?php declare(strict_types=1);

use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Backend\Settings\Search;
use JTL\Backend\Settings\Sections\Section;
use JTL\DB\SqlObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_menu.php';

/**
 * @param string $query
 * @return Section[]
 */
function configSearch(string $query): array
{
    $db             = Shop::Container()->getDB();
    $gettext        = Shop::Container()->getGetText();
    $manager        = new SettingsManager(
        $db,
        Shop::Smarty(),
        Shop::Container()->getAdminAccount(),
        $gettext,
        Shop::Container()->getAlertService()
    );
    $searchInstance = new Search($db, $gettext, $manager);
    return $searchInstance->getResultSections($query);
}

/**
 * @param string $query
 * @param bool   $save
 * @return stdClass
 */
function bearbeiteEinstellungsSuche(string $query, bool $save = false): stdClass
{
    return holeEinstellungen($result, $save);
}

/**
 * @param stdClass $sql
 * @param bool   $save
 * @return stdClass
 * @deprecated since 5.2.0
 */
function holeEinstellungen(stdClass $sql, bool $save): stdClass
{
//    \trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    if (mb_strlen($sql->cWHERE) <= 0) {
        return $sql;
    }
    $manager         = new SettingsManager(
        Shop::Container()->getDB(),
        Shop::Smarty(),
        Shop::Container()->getAdminAccount(),
        Shop::Container()->getGetText(),
        Shop::Container()->getAlertService()
    );
    $section         = new Search($manager, 0);
    $configData      = $section->generateConfigData($sql->sql);
    $sql->configData = $configData;
    Shop::Container()->getGetText()->loadConfigLocales();
    $section->enhanceSearchResults($sql);
    // Aufräumen
    if (count($sql->oEinstellung_arr) > 0) {
        $configIDs = [];
        foreach ($sql->oEinstellung_arr as $i => $config) {
            if ($config->kEinstellungenConf > 0 && !in_array($config->kEinstellungenConf, $configIDs, true)) {
                $configIDs[$i] = $config->kEinstellungenConf;
            } else {
                unset($sql->oEinstellung_arr[$i]);
            }
            if ($save && $config->cConf === 'N') {
                unset($sql->oEinstellung_arr[$i]);
            }
        }
        $sql->oEinstellung_arr  = sortiereEinstellungen($sql->oEinstellung_arr);
        $sql->configData        = $section->sortiereEinstellungen($sql->configData);
        $sql->groupedConfigData = $section->groupByHeadline($sql->configData);
    }

    return $sql;
}

/**
 * @param stdClass $sql
 * @param int    $sort
 * @param int    $sectionID
 * @return stdClass
 */
function holeEinstellungAbteil(stdClass $sql, int $sort, int $sectionID): stdClass
{
    if ($sort <= 0 || $sectionID <= 0) {
        return $sql;
    }
    $items = Shop::Container()->getDB()->getObjects(
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
    foreach ($items as $item) {
        $item->kEinstellungenConf    = (int)$item->kEinstellungenConf;
        $item->kEinstellungenSektion = (int)$item->kEinstellungenSektion;
        $item->nSort                 = (int)$item->nSort;
        $item->nStandardAnzeigen     = (int)$item->nStandardAnzeigen;
        $item->nModul                = (int)$item->nModul;
        if ($item->cConf !== 'N') {
            $sql->oEinstellung_arr[] = $item;
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
function holeEinstellungHeadline(int $sort, int $sectionID): ?stdClass
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

/**
 * @param int   $sectionID
 * @param mixed $groupName
 * @return string
 * @deprecated since 5.0.2
 */
function gibEinstellungsSektionsPfad(int $sectionID, $groupName): string
{
    return getConfigSectionPath(mapConfigSectionToMenuEntry($sectionID, $groupName));
}

/**
 * @param int   $sectionID
 * @param mixed $groupName
 * @return string
 * @deprecated since 5.0.2
 */
function getSectionMenuPath(int $sectionID, $groupName): string
{
    return getConfigSectionUrl(mapConfigSectionToMenuEntry($sectionID, $groupName));
}

/**
 * @param int $sectionID
 * @return boolean
 * @deprecated since 5.0.2
 */
function getSpecialSetting(int $sectionID, $groupName): bool
{
    return isConfigSectionSpecialSetting(mapConfigSectionToMenuEntry($sectionID, $groupName));
}

/**
 * @param int   $sectionID
 * @param mixed $groupName
 * @return string
 * @deprecated since 5.0.2
 */
function getSettingsAnchor(int $sectionID, $groupName): string
{
    return getConfigSectionAnchor(mapConfigSectionToMenuEntry($sectionID, $groupName));
}

/**
 * @param int $sectionID
 * @param string $groupName
 * @return stdClass
 */
function mapConfigSectionToMenuEntry(int $sectionID, string $groupName = 'all')
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
 * @param stdClass $menuEntry
 * @return string
 */
function getConfigSectionPath(stdClass $menuEntry): string
{
    return $menuEntry->path ?? '';
}

/**
 * @param stdClass $menuEntry
 * @return string
 */
function getConfigSectionUrl(stdClass $menuEntry): string
{
    return $menuEntry->url ?? '';
}

/**
 * @param stdClass $menuEntry
 * @return bool
 */
function isConfigSectionSpecialSetting(stdClass $menuEntry): bool
{
    return $menuEntry->specialSetting ?? false;
}

/**
 * @param stdClass $menuEntry
 * @return string
 */
function getConfigSectionAnchor(stdClass $menuEntry): string
{
    return $menuEntry->settingsAnchor ?? '';
}

/**
 * @param array $config
 * @return array
 */
function sortiereEinstellungen(array $config): array
{
    if (count($config) === 0) {
        return [];
    }
    $sectionIDs = [];
    $sprt       = [];
    $tmpConf    = [];
    $sections   = [];
    foreach ($config as $conf) {
        if (isset($conf->kEinstellungenSektion) && $conf->cConf !== 'N') {
            $headline = holeEinstellungHeadline((int)$conf->nSort, (int)$conf->kEinstellungenSektion);
            if ($headline !== null && !isset($sections[$headline->cWertName])) {
                $sections[$headline->cWertName] = true;
                $tmpConf[]                      = $headline;
            }
            $tmpConf[] = $conf;
        }
    }
    foreach ($tmpConf as $key => $value) {
        $sectionIDs[$key] = $value->kEinstellungenSektion;
        $sprt[$key]       = $value->nSort;
    }
    array_multisort($sectionIDs, SORT_ASC, $sprt, SORT_ASC, $tmpConf);

    return $tmpConf;
}

/**
 *  settings page is separated but has same config group as parent config page, get separate description
 *
 * @param int $sectionID
 * @return string
 */
function filteredConfDescription(int $sectionID): string
{
    switch (Request::verifyGPDataString('group')) {
        case 'configgroup_5_product_question':
            $desc = __('prefDesc5ProductQuestion');
            break;
        default:
            $desc = __('prefDesc' . $sectionID);
            break;
    }

    return $desc;
}
