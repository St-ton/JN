<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_menu.php';

/**
 * @param string $query
 * @param bool   $save
 * @return object
 */
function bearbeiteEinstellungsSuche(string $query, bool $save = false)
{
    $result = (object)[
        'cSearch'          => '',
        'cWHERE'           => '',
        'nSuchModus'       => 0,
        'cSuche'           => $query,
        'oEinstellung_arr' => [],
    ];

    if (mb_strlen($query) === 0) {
        return $result;
    }

    $result->cWHERE = "(cModulId IS NULL OR cModulId = '') AND kEinstellungenSektion != 101 ";
    $idList         = explode(',', $query);
    $isIdList       = count($idList) > 1;

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
        $result->nSuchModus = 1;
        $result->cSearch    = 'Suche nach ID: ' . implode(', ', $idList);
        $result->cWHERE    .= ' AND kEinstellungenConf IN (' . implode(', ', $idList) . ')';
        $result->confIds    = $idList;
    } else {
        $rangeList = explode('-', $query);
        $isIdRange = count($rangeList) === 2;

        if ($isIdRange) {
            $rangeList[0] = (int)$rangeList[0];
            $rangeList[1] = (int)$rangeList[1];

            if ($rangeList[0] === 0 || $rangeList[1] === 0) {
                $isIdRange = false;
            }
        }

        if ($isIdRange) {
            $result->nSuchModus = 2;
            $result->cSearch    = 'Suche nach ID Range: ' . $rangeList[0] . ' - ' . $rangeList[1];
            $result->cWHERE    .= ' AND kEinstellungenConf BETWEEN ' . $rangeList[0] . ' AND ' . $rangeList[1];
            $result->cWHERE    .= " AND cConf = 'Y'";
            $result->confIdFrom = $rangeList[0];
            $result->confIdTo   = $rangeList[1];
        } elseif ((int)$query > 0) {
            $result->nSuchModus = 3;
            $result->cSearch    = 'Suche nach ID: ' . $query;
            $result->cWHERE    .= " AND kEinstellungenConf = '" . (int)$query . "'";
        } else {
            $query              = mb_convert_case($query, MB_CASE_LOWER);
            $queryEnt           = Text::htmlentities($query);
            $result->nSuchModus = 4;
            $result->cSearch    = 'Suche nach Name: ' . $query;
            $getText            = Shop::Container()->getGetText();
            $configTranslations = $getText->getAdminTranslations('configs/configs');
            $valueNames         = [];

            foreach ($configTranslations->getIterator() as $translation) {
                $orig  = $translation->getOriginal();
                $trans = $translation->getTranslation();

                if ((mb_stripos($trans, $query) !== false || mb_stripos($trans, $queryEnt) !== false)
                    && mb_substr($orig, -5) === '_name'
                ) {
                    $valueName    = preg_replace('/(_name|_desc)$/', '', $orig);
                    $valueNames[] = "'" . $valueName . "'";
                }
            }

            $result->cWHERE .= ' AND cWertName IN (' . implode(', ', $valueNames) . ')';
            $result->cWHERE .= " AND cConf = 'Y'";
        }
    }

    return holeEinstellungen($result, $save);
}

/**
 * @param object $oSQL
 * @param bool   $bSpeichern
 * @return object
 */
function holeEinstellungen($oSQL, bool $bSpeichern)
{
    if (mb_strlen($oSQL->cWHERE) <= 0) {
        return $oSQL;
    }

    $oSQL->oEinstellung_arr = Shop::Container()->getDB()->query(
        'SELECT *
            FROM teinstellungenconf
            WHERE ' . $oSQL->cWHERE . '
            ORDER BY kEinstellungenSektion, nSort',
        ReturnType::ARRAY_OF_OBJECTS
    );
    Shop::Container()->getGetText()->loadConfigLocales();
    foreach ($oSQL->oEinstellung_arr as $j => $oEinstellung) {
        Shop::Container()->getGetText()->localizeConfig($oEinstellung);

        if ((int)$oSQL->nSuchModus === 3 && $oEinstellung->cConf === 'Y') {
            $oSQL->oEinstellung_arr = [];
            $configHead             = holeEinstellungHeadline(
                $oEinstellung->nSort,
                $oEinstellung->kEinstellungenSektion
            );
            if (isset($configHead->kEinstellungenConf)
                && $configHead->kEinstellungenConf > 0
            ) {
                $oSQL->oEinstellung_arr[] = $configHead;
                $oSQL                     = holeEinstellungAbteil(
                    $oSQL,
                    $configHead->nSort,
                    $configHead->kEinstellungenSektion
                );
            }
        } elseif ($oEinstellung->cConf === 'N') {
            $oSQL = holeEinstellungAbteil(
                $oSQL,
                $oEinstellung->nSort,
                $oEinstellung->kEinstellungenSektio
            );
        }
    }
    // Aufräumen
    if (count($oSQL->oEinstellung_arr) > 0) {
        $kEinstellungenConf_arr = [];
        foreach ($oSQL->oEinstellung_arr as $i => $oEinstellung) {
            $oEinstellung->kEinstellungenConf = (int)$oEinstellung->kEinstellungenConf;
            if (isset($oEinstellung->kEinstellungenConf)
                && $oEinstellung->kEinstellungenConf > 0
                && !in_array($oEinstellung->kEinstellungenConf, $kEinstellungenConf_arr, true)
            ) {
                $kEinstellungenConf_arr[$i] = $oEinstellung->kEinstellungenConf;
            } else {
                unset($oSQL->oEinstellung_arr[$i]);
            }

            if ($bSpeichern && $oEinstellung->cConf === 'N') {
                unset($oSQL->oEinstellung_arr[$i]);
            }
        }
        $oSQL->oEinstellung_arr = sortiereEinstellungen($oSQL->oEinstellung_arr);
    }

    return $oSQL;
}

/**
 * @param object $oSQL
 * @param int    $nSort
 * @param int    $kEinstellungenSektion
 * @return mixed
 */
function holeEinstellungAbteil($oSQL, $nSort, $kEinstellungenSektion)
{
    if ((int)$nSort > 0 && (int)$kEinstellungenSektion > 0) {
        $oEinstellungTMP_arr = Shop::Container()->getDB()->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE nSort > ' . (int)$nSort . '
                    AND kEinstellungenSektion = ' . (int)$kEinstellungenSektion . '
                ORDER BY nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oEinstellungTMP_arr as $oEinstellungTMP) {
            if ($oEinstellungTMP->cConf !== 'N') {
                $oSQL->oEinstellung_arr[] = $oEinstellungTMP;
            } else {
                break;
            }
        }
    }

    return $oSQL;
}

/**
 * @param int $nSort
 * @param int $sectionID
 * @return stdClass
 */
function holeEinstellungHeadline(int $nSort, int $sectionID)
{
    $configHead = new stdClass();
    if ($nSort > 0 && $sectionID > 0) {
        $oEinstellungTMP_arr = Shop::Container()->getDB()->query(
            'SELECT *
                FROM teinstellungenconf
                WHERE nSort < ' . $nSort . '
                    AND kEinstellungenSektion = ' . $sectionID . '
                ORDER BY nSort DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oEinstellungTMP_arr as $oEinstellungTMP) {
            if ($oEinstellungTMP->cConf === 'N') {
                $configHead                = $oEinstellungTMP;
                $configHead->cSektionsPfad = gibEinstellungsSektionsPfad($sectionID);
                $configHead->cURL          = getSectionMenuPath($sectionID);
                break;
            }
        }
    }

    return $configHead;
}

/**
 * @param int $sectionID
 * @return string
 */
function gibEinstellungsSektionsPfad(int $sectionID)
{
    global $sectionMenuMapping;

    if (isset($sectionMenuMapping[$sectionID])) {
        return $sectionMenuMapping[$sectionID]->path;
    }

    return '';
}

/**
 * @param int $sectionID
 * @return string
 */
function getSectionMenuPath(int $sectionID)
{
    global $sectionMenuMapping;

    if (isset($sectionMenuMapping[$sectionID])) {
        return $sectionMenuMapping[$sectionID]->url;
    }

    return '';
}

/**
 * @param array $config
 * @return array
 */
function sortiereEinstellungen($config)
{
    if (is_array($config) && count($config) > 0) {
        $nSort                   = [];
        $oEinstellungTMP_arr     = [];
        $oEinstellungSektion_arr = [];
        foreach ($config as $i => $oEinstellung) {
            if (isset($oEinstellung->kEinstellungenSektion) && $oEinstellung->cConf !== 'N') {
                if (!isset($oEinstellungSektion_arr[$oEinstellung->kEinstellungenSektion])) {
                    $headline = holeEinstellungHeadline($oEinstellung->nSort, $oEinstellung->kEinstellungenSektion);
                    if (isset($headline->kEinstellungenSektion)) {
                        $oEinstellungSektion_arr[$oEinstellung->kEinstellungenSektion] = true;
                        $oEinstellungTMP_arr[]                                         = $headline;
                    }
                }
                $oEinstellungTMP_arr[] = $oEinstellung;
            }
        }
        foreach ($oEinstellungTMP_arr as $key => $value) {
            $kEinstellungenSektion[$key] = $value->kEinstellungenSektion;
            $nSort[$key]                 = $value->nSort;
        }
        array_multisort($kEinstellungenSektion, SORT_ASC, $nSort, SORT_ASC, $oEinstellungTMP_arr);

        return $oEinstellungTMP_arr;
    }

    return [];
}
