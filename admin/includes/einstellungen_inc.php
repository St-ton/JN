<?php declare(strict_types=1);

/**
 * @param int $sectionID
 * @return string
 * @deprecated since 5.2.0
 */
function filteredConfDescription(int $sectionID): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return __('prefDesc' . $sectionID);
}

/**
 * @param string $query
 * @param bool   $save
 * @return stdClass
 * @deprecated since 5.2.0
 */
function bearbeiteEinstellungsSuche(string $query, bool $save = false): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new stdClass();
}

/**
 * @param stdClass $sql
 * @param bool     $save
 * @return stdClass
 * @deprecated since 5.2.0
 */
function holeEinstellungen(stdClass $sql, bool $save): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return $sql;
}

/**
 * @param stdClass $sql
 * @param int      $sort
 * @param int      $sectionID
 * @return stdClass
 * @deprecated since 5.2.0
 */
function holeEinstellungAbteil(stdClass $sql, int $sort, int $sectionID): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return $sql;
}

/**
 * @param int $sort
 * @param int $sectionID
 * @return stdClass|null
 * @deprecated since 5.2.0
 */
function holeEinstellungHeadline(int $sort, int $sectionID): ?stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return null;
}

/**
 * @param int   $sectionID
 * @param mixed $groupName
 * @return string
 * @deprecated since 5.0.2
 */
function gibEinstellungsSektionsPfad(int $sectionID, $groupName): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param int   $sectionID
 * @param mixed $groupName
 * @return string
 * @deprecated since 5.0.2
 */
function getSectionMenuPath(int $sectionID, $groupName): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param int    $sectionID
 * @param string $groupName
 * @return bool
 * @deprecated since 5.0.2
 */
function getSpecialSetting(int $sectionID, $groupName): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int   $sectionID
 * @param mixed $groupName
 * @return string
 * @deprecated since 5.0.2
 */
function getSettingsAnchor(int $sectionID, $groupName): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param int    $sectionID
 * @param string $groupName
 * @return stdClass
 * @deprecated since 5.2.0
 */
function mapConfigSectionToMenuEntry(int $sectionID, string $groupName = 'all'): stdClass
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return (object)[];
}

/**
 * @param stdClass $menuEntry
 * @return string
 * @deprecated since 5.2.0
 */
function getConfigSectionPath(stdClass $menuEntry): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param stdClass $menuEntry
 * @return string
 * @deprecated since 5.2.0
 */
function getConfigSectionUrl(stdClass $menuEntry): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return $menuEntry->url ?? '';
}

/**
 * @param stdClass $menuEntry
 * @return bool
 * @deprecated since 5.2.0
 */
function isConfigSectionSpecialSetting(stdClass $menuEntry): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param stdClass $menuEntry
 * @return string
 * @deprecated since 5.2.0
 */
function getConfigSectionAnchor(stdClass $menuEntry): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param array $config
 * @return array
 * @deprecated since 5.2.0
 */
function sortiereEinstellungen(array $config): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}
