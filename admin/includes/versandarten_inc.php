<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Checkout\Versandart;
use JTL\Checkout\Versandzuschlag;
use JTL\Alert\Alert;

/**
 * @param float $fPreis
 * @param float $fSteuersatz
 * @return float
 */
function berechneVersandpreisBrutto($fPreis, $fSteuersatz)
{
    return $fPreis > 0
        ? round((float)($fPreis * ((100 + $fSteuersatz) / 100)), 2)
        : 0.0;
}

/**
 * @param float $fPreis
 * @param float $fSteuersatz
 * @return float
 */
function berechneVersandpreisNetto($fPreis, $fSteuersatz)
{
    return $fPreis > 0
        ? round($fPreis * ((100 / (100 + $fSteuersatz)) * 100) / 100, 2)
        : 0.0;
}

/**
 * @param array  $obj_arr
 * @param string $key
 * @return array
 */
function reorganizeObjectArray($obj_arr, $key)
{
    $res = [];
    if (is_array($obj_arr)) {
        foreach ($obj_arr as $obj) {
            $arr  = get_object_vars($obj);
            $keys = array_keys($arr);
            if (in_array($key, $keys)) {
                $res[$obj->$key]           = new stdClass();
                $res[$obj->$key]->checked  = 'checked';
                $res[$obj->$key]->selected = 'selected';
                foreach ($keys as $k) {
                    if ($key != $k) {
                        $res[$obj->$key]->$k = $obj->$k;
                    }
                }
            }
        }
    }

    return $res;
}

/**
 * @param array $arr
 * @return array
 */
function P($arr)
{
    $newArr = [];
    if (is_array($arr)) {
        foreach ($arr as $ele) {
            $newArr = bauePot($newArr, $ele);
        }
    }

    return $newArr;
}

/**
 * @param array  $arr
 * @param string $key
 * @return array
 */
function bauePot($arr, $key)
{
    $cnt = count($arr);
    for ($i = 0; $i < $cnt; ++$i) {
        $obj                 = new stdClass();
        $obj->kVersandklasse = $arr[$i]->kVersandklasse . '-' . $key->kVersandklasse;
        $obj->cName          = $arr[$i]->cName . ', ' . $key->cName;
        $arr[]               = $obj;
    }
    $arr[] = $key;

    return $arr;
}

/**
 * @param string $cVersandklassen
 * @return array
 */
function gibGesetzteVersandklassen($cVersandklassen)
{
    if (trim($cVersandklassen) === '-1') {
        return ['alle' => true];
    }
    $gesetzteVK = [];
    $uniqueIDs  = [];
    $cVKarr     = explode(' ', trim($cVersandklassen));
    // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
    foreach ($cVKarr as $idString) {
        // we want the single kVersandklasse IDs to reduce the possible amount of combinations
        foreach (explode('-', $idString) as $kVersandklasse) {
            $uniqueIDs[] = (int)$kVersandklasse;
        }
    }
    $PVersandklassen = P(Shop::Container()->getDB()->query(
        'SELECT * 
            FROM tversandklasse
            WHERE kVersandklasse IN (' . implode(',', $uniqueIDs) . ')  
            ORDER BY kVersandklasse',
        ReturnType::ARRAY_OF_OBJECTS
    ));
    foreach ($PVersandklassen as $vk) {
        $gesetzteVK[$vk->kVersandklasse] = in_array($vk->kVersandklasse, $cVKarr, true);
    }

    return $gesetzteVK;
}

/**
 * @param string $cVersandklassen
 * @return array
 */
function gibGesetzteVersandklassenUebersicht($cVersandklassen)
{
    if (trim($cVersandklassen) === '-1') {
        return ['Alle'];
    }
    $gesetzteVK = [];
    $uniqueIDs  = [];
    $cVKarr     = explode(' ', trim($cVersandklassen));
    // $cVersandklassen is a string like "1 3-4 5-6-7 6-8 7-8 3-7 3-8 5-6 5-7"
    foreach ($cVKarr as $idString) {
        // we want the single kVersandklasse IDs to reduce the possible amount of combinations
        foreach (explode('-', $idString) as $kVersandklasse) {
            $uniqueIDs[] = (int)$kVersandklasse;
        }
    }
    $PVersandklassen = P(Shop::Container()->getDB()->query(
        'SELECT * 
            FROM tversandklasse 
            WHERE kVersandklasse IN (' . implode(',', $uniqueIDs) . ')
            ORDER BY kVersandklasse',
        ReturnType::ARRAY_OF_OBJECTS
    ));
    foreach ($PVersandklassen as $vk) {
        if (in_array($vk->kVersandklasse, $cVKarr, true)) {
            $gesetzteVK[] = $vk->cName;
        }
    }

    return $gesetzteVK;
}

/**
 * @param string $customerGroupsString
 * @return array
 */
function gibGesetzteKundengruppen($customerGroupsString)
{
    $activeGroups = [];
    $groups       = Text::parseSSK($customerGroupsString);
    $groupData    = Shop::Container()->getDB()->query(
        'SELECT kKundengruppe
            FROM tkundengruppe
            ORDER BY kKundengruppe',
        ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($groupData as $group) {
        $activeGroups[(int)$group->kKundengruppe] = in_array($group->kKundengruppe, $groups);
    }
    $activeGroups['alle'] = $customerGroupsString === '-1';

    return $activeGroups;
}

/**
 * @param int   $kVersandart
 * @param array $oSprache_arr
 * @return array
 */
function getShippingLanguage(int $kVersandart, $oSprache_arr)
{
    $oVersandartSpracheAssoc_arr = [];
    $oVersandartSprache_arr      = Shop::Container()->getDB()->selectAll(
        'tversandartsprache',
        'kVersandart',
        $kVersandart
    );
    if (is_array($oSprache_arr)) {
        foreach ($oSprache_arr as $oSprache) {
            $oVersandartSpracheAssoc_arr[$oSprache->cISO] = new stdClass();
        }
    }
    foreach ($oVersandartSprache_arr as $oVersandartSprache) {
        if (isset($oVersandartSprache->kVersandart) && $oVersandartSprache->kVersandart > 0) {
            $oVersandartSpracheAssoc_arr[$oVersandartSprache->cISOSprache] = $oVersandartSprache;
        }
    }

    return $oVersandartSpracheAssoc_arr;
}

/**
 * @param int $kVersandzuschlag
 * @return array
 */
function getZuschlagNames(int $kVersandzuschlag)
{
    $names = [];
    if (!$kVersandzuschlag) {
        return $names;
    }
    $zuschlagnamen = Shop::Container()->getDB()->selectAll(
        'tversandzuschlagsprache',
        'kVersandzuschlag',
        $kVersandzuschlag
    );
    foreach ($zuschlagnamen as $name) {
        $names[$name->cISOSprache] = $name->cName;
    }

    return $names;
}

/**
 * @param string $cSearch
 * @return array
 */
function getShippingByName(string $cSearch)
{
    $byName = [];
    $db     = Shop::Container()->getDB();
    foreach (explode(',', $cSearch) as $cSearchPos) {
        $cSearchPos = trim($cSearchPos);
        if (mb_strlen($cSearchPos) > 2) {
            $shippingByName_arr = $db->queryPrepared(
                'SELECT va.kVersandart, va.cName
                    FROM tversandart AS va
                    LEFT JOIN tversandartsprache AS vs 
                        ON vs.kVersandart = va.kVersandart
                        AND vs.cName LIKE :search
                    WHERE va.cName LIKE :search
                    OR vs.cName LIKE :search',
                ['search' => '%' . $cSearchPos . '%'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (!empty($shippingByName_arr)) {
                if (count($shippingByName_arr) > 1) {
                    foreach ($shippingByName_arr as $shippingByName) {
                        $byName[$shippingByName->kVersandart] = $shippingByName;
                    }
                } else {
                    $byName[$shippingByName_arr[0]->kVersandart] = $shippingByName_arr[0];
                }
            }
        }
    }

    return $byName;
}

/**
 * @param array $shipClasses
 * @param int   $length
 * @return array
 */
function getCombinations(array $shipClasses, int $length)
{
    $baselen = count($shipClasses);
    if ($baselen === 0) {
        return [];
    }
    if ($length === 1) {
        $return = [];
        foreach ($shipClasses as $b) {
            $return[] = [$b];
        }

        return $return;
    }

    // get one level lower combinations
    $oneLevelLower = getCombinations($shipClasses, $length - 1);
    // for every one level lower combinations add one element to them
    // that the last element of a combination is preceeded by the element
    // which follows it in base array if there is none, does not add
    $newCombs = [];
    foreach ($oneLevelLower as $oll) {
        $lastEl = $oll[$length - 2];
        $found  = false;
        foreach ($shipClasses as $key => $b) {
            if ($b === $lastEl) {
                $found = true;
                continue;
                // last element found
            }
            if ($found === true && $key < $baselen) {
                // add to combinations with last element
                $tmp              = $oll;
                $newCombination   = array_slice($tmp, 0);
                $newCombination[] = $b;
                $newCombs[]       = array_slice($newCombination, 0);
            }
        }
    }

    return $newCombs;
}

/**
 * @return array|int -1 if too many shipping classes exist
 */
function getMissingShippingClassCombi()
{
    $shippingClasses         = Shop::Container()->getDB()->selectAll('tversandklasse', [], [], 'kVersandklasse');
    $shipClasses             = [];
    $combinationsInShippings = Shop::Container()->getDB()->selectAll('tversandart', [], [], 'cVersandklassen');
    $combinationInUse        = [];

    foreach ($shippingClasses as $sc) {
        $shipClasses[] = $sc->kVersandklasse;
    }

    foreach ($combinationsInShippings as $com) {
        $vk     = trim($com->cVersandklassen);
        $vk_arr = explode(' ', $vk);
        if (is_array($vk_arr)) {
            foreach ($vk_arr as $_vk) {
                $combinationInUse[] = trim($_vk);
            }
        } else {
            $combinationInUse[] = trim($com->cVersandklassen);
        }
    }

    // if a shipping method is valid for all classes return
    if (in_array('-1', $combinationInUse, false)) {
        return [];
    }

    $len = count($shipClasses);
    if ($len > SHIPPING_CLASS_MAX_VALIDATION_COUNT) {
        return -1;
    }

    $possibleShippingClassCombinations = [];
    for ($i = 1; $i <= $len; $i++) {
        $result = getCombinations($shipClasses, $i);
        foreach ($result as $c) {
            $possibleShippingClassCombinations[] = implode('-', $c);
        }
    }

    $res = array_diff($possibleShippingClassCombinations, $combinationInUse);
    foreach ($res as &$mscc) {
        $mscc = gibGesetzteVersandklassenUebersicht($mscc)[0];
    }

    return $res;
}

/**
 * @param $shippingType
 * @param $ISO
 * @return stdClass
 * @throws SmartyException
 */
function getZuschlagsListen($shippingType, $ISO)
{
    $smarty     = JTLSmarty::getInstance(false, ContextType::BACKEND);
    $zuschlaege = (new Versandart($shippingType))->getSurchargesForCountry($ISO);

    $result       = new stdClass();
    $result->body = $smarty->assign('zuschlaglisten', $zuschlaege)
                           ->assign('sprachen', Sprache::getAllLanguages())
                           ->fetch('tpl_inc/zuschlaglisten.tpl');
    return $result;
}

/**
 * @param $surcharge
 * @return stdClass
 * @throws SmartyException
 */
function saveZuschlagsListe(array $surcharge)
{
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    Shop::Container()->getGetText()->loadAdminLocale('pages/versandarten');

    $alertHelper = Shop::Container()->getAlertService();
    $smarty      = JTLSmarty::getInstance(false, ContextType::BACKEND);
    $post        = [];
    foreach ($surcharge as $item) {
        $post[$item['name']] = $item['value'];
    }
    $surcharge = (float)str_replace(',', '.', $post['fZuschlag']);

    if (!$post['cName']) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorListNameMissing'), 'errorListNameMissing');
    }
    if (empty($surcharge)) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorListPriceMissing'), 'errorListPriceMissing');
    }
    if (!$alertHelper->alertTypeExists(Alert::TYPE_ERROR)) {
        $languages = Sprache::getAllLanguages();
        if (isset($post['kVersandzuschlag'])) {
            $surchargeTMP = (new Versandzuschlag((int)$post['kVersandzuschlag']))
                ->setTitle($post['cName'])
                ->setSurcharge($post['fZuschlag']);
        } else {
            $surchargeTMP = (new Versandzuschlag())
                ->setISO($post['cISO'])
                ->setSurcharge($surcharge)
                ->setShippingMethod($post['kVersandart'])
                ->setTitle($post['cName']);

            $surchargeLists = getZuschlagsListen($post['kVersandart'], $post['cISO']);
        }
        foreach ($languages as $lang) {
            if (isset($post['cName_' . $lang->cISO])) {
                $surchargeTMP->setName($post['cName_' . $lang->cISO] ?: $post['cName'], $lang->kSprache);
            }
        }
        $surchargeTMP->save(isset($post['kVersandzuschlag']));
    }
    $message = $smarty->assign('alertList', $alertHelper)
                      ->fetch('snippets/alert_list.tpl');

    return (object)[
        'surcharges' => $surchargeLists ?? '',
        'message'    => $message,
        'error'      => $alertHelper->alertTypeExists(Alert::TYPE_ERROR)
    ];
}

/**
 * @param $surchargeID
 * @return object
 */
function deleteZuschlagsListe($surchargeID)
{
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    Shop::Container()->getDB()->queryPrepared(
        'DELETE tversandzuschlag, tversandzuschlagsprache, tversandzuschlagplz
            FROM tversandzuschlag
            LEFT JOIN tversandzuschlagsprache USING(kVersandzuschlag)
            LEFT JOIN tversandzuschlagplz USING(kVersandzuschlag)
            WHERE tversandzuschlag.kVersandzuschlag = :surchargeID',
        ['surchargeID' => $surchargeID],
        ReturnType::DEFAULT
    );

    return (object)['surchargeID' => $surchargeID];
}

/**
 * @param $surchargeID
 * @param $ZIP
 * @return string
 */
function deleteZuschlagsListeZIP($surchargeID, $ZIP)
{
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    $partsZIP = explode('-', $ZIP);
    if (count($partsZIP) === 1) {
        Shop::Container()->getDB()->queryPrepared(
            'DELETE 
            FROM tversandzuschlagplz
            WHERE kVersandzuschlag = :surchargeID
              AND cPLZ = :ZIP',
            [
                'surchargeID' => $surchargeID,
                'ZIP' => $partsZIP[0]
            ],
            ReturnType::DEFAULT
        );
    } elseif (count($partsZIP) === 2) {
        Shop::Container()->getDB()->queryPrepared(
            'DELETE 
            FROM tversandzuschlagplz
            WHERE kVersandzuschlag = :surchargeID
              AND cPLZab = :ZIPFrom
              AND cPLZbis = :ZIPTo',
            [
                'surchargeID' => $surchargeID,
                'ZIPFrom' => $partsZIP[0],
                'ZIPTo' => $partsZIP[1]
            ],
            ReturnType::DEFAULT
        );
    }

    return (object)['surchargeID' => $surchargeID, 'ZIP' => $ZIP];
}

/**
 * @param array $data
 * @return string
 * @throws SmartyException
 */
function createZuschlagsListeZIP(array $data)
{
    Shop::Container()->getCache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
    Shop::Container()->getGetText()->loadAdminLocale('pages/versandarten');

    $post = [];
    foreach ($data as $item) {
        $post[$item['name']] = $item['value'];
    }
    $alertHelper   = Shop::Container()->getAlertService();
    $db            = Shop::Container()->getDB();
    $smarty        = JTLSmarty::getInstance(false, ContextType::BACKEND);
    $surcharge     = new Versandzuschlag((int)$post['kVersandzuschlag']);
    $oZipValidator = new ZipValidator($surcharge->getISO());
    $ZuschlagPLZ   = new stdClass();

    $ZuschlagPLZ->kVersandzuschlag = $surcharge->getID();
    if (!empty($post['cPLZ'])) {
        $ZuschlagPLZ->cPLZ = $oZipValidator->validateZip($post['cPLZ']);
    } elseif (!empty($post['cPLZAb']) && !empty($post['cPLZBis'])) {
        if ($post['cPLZAb'] > $post['cPLZBis']) {
            $ZuschlagPLZ->cPLZAb  = $oZipValidator->validateZip($post['cPLZBis']);
            $ZuschlagPLZ->cPLZBis = $oZipValidator->validateZip($post['cPLZAb']);
        } else {
            $ZuschlagPLZ->cPLZAb  = $oZipValidator->validateZip($post['cPLZAb']);
            $ZuschlagPLZ->cPLZBis = $oZipValidator->validateZip($post['cPLZBis']);
        }
    }

    if (empty($ZuschlagPLZ->cPLZ) && empty($ZuschlagPLZ->cPLZAb)) {
        $szErrorString = $oZipValidator->getError();
        if ($szErrorString !== '') {
            $alertHelper->addAlert(Alert::TYPE_ERROR, $szErrorString, 'errorZIPValidator');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorZIPMissing'), 'errorZIPMissing');
        }
    } elseif (!empty($ZuschlagPLZ->cPLZ) && $surcharge->hasZIPCode($ZuschlagPLZ->cPLZ)) {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            sprintf(
                __('errorZIPOverlap'),
                $ZuschlagPLZ->cPLZ,
                'overlap'
            ),
            'errorZIPOverlap'
        );
    } elseif ((!empty($ZuschlagPLZ->cPLZAb) && $surcharge->hasZIPCode($ZuschlagPLZ->cPLZAb))
        || (!empty($ZuschlagPLZ->cPLZBis) && $surcharge->hasZIPCode($ZuschlagPLZ->cPLZBis))
    ) {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            sprintf(
                __('errorZIPAreaOverlap'),
                $ZuschlagPLZ->cPLZAb . '-' . $ZuschlagPLZ->cPLZBis,
                'overlap'
            ),
            'errorZIPAreaOverlap'
        );
    } elseif ($db->insert('tversandzuschlagplz', $ZuschlagPLZ)) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successZIPAdd'), 'successZIPAdd');
    }

    $message = $smarty->assign('alertList', $alertHelper)
                      ->fetch('snippets/alert_list.tpl');
    $badges  = $smarty->assign('zuschlagliste', new Versandzuschlag($surcharge->getID()))
                      ->fetch('snippets/zuschlagliste_plz_badges.tpl');

    return (object)['message' => $message, 'badges' => $badges, 'surchargeID' => $surcharge->getID()];
}
