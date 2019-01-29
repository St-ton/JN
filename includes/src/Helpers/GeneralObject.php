<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Helpers;

use stdClass;

/**
 * Class GeneralObject
 * @package Helpers
 * @since 5.0.0
 */
class GeneralObject
{
    /**
     * @param array  $data
     * @param string $key
     * @param bool   $bStringToLower
     * @former objectSort()
     * @since 5.0.0
     */
    public static function sortBy(&$data, $key, $bStringToLower = false): void
    {
        $dataCount = \count($data);
        for ($i = $dataCount - 1; $i >= 0; $i--) {
            $swapped = false;
            for ($j = 0; $j < $i; $j++) {
                $dataJ  = $data[$j]->$key;
                $dataJ1 = $data[$j + 1]->$key;
                if ($bStringToLower) {
                    $dataJ  = \mb_convert_case($dataJ, MB_CASE_LOWER);
                    $dataJ1 = \mb_convert_case($dataJ1, MB_CASE_LOWER);
                }
                if ($dataJ > $dataJ1) {
                    $tmp          = $data[$j];
                    $data[$j]     = $data[$j + 1];
                    $data[$j + 1] = $tmp;
                    $swapped      = true;
                }
            }
            if (!$swapped) {
                return;
            }
        }
    }

    /**
     * @param object $originalObj
     * @return stdClass|object
     * @former kopiereMembers()
     * @since 5.0.0
     */
    public static function copyMembers($originalObj)
    {
        if (!\is_object($originalObj)) {
            return $originalObj;
        }
        $obj = new stdClass();
        foreach (\array_keys(\get_object_vars($originalObj)) as $member) {
            $obj->$member = $originalObj->$member;
        }

        return $obj;
    }

    /**
     * @param stdClass|object $src
     * @param stdClass|object $dest
     * @since 5.0.0
     */
    public static function memberCopy($src, &$dest): void
    {
        if ($dest === null) {
            $dest = new stdClass();
        }
        foreach (\array_keys(\get_object_vars($src)) as $key) {
            if (!\is_object($src->$key) && !\is_array($src->$key)) {
                $dest->$key = $src->$key;
            }
        }
    }

    /**
     * @param object $oObj
     * @return mixed
     * @since 5.0.0
     */
    public static function deepCopy($oObj)
    {
        return \unserialize(\serialize($oObj));
    }
}
