<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Plausi
 */
class Plausi
{
    /**
     * @var array
     */
    protected $xPostVar_arr = [];

    /**
     * @var array
     */
    protected $xPlausiVar_arr = [];

    /**
     * @return array
     */
    public function getPostVar(): array
    {
        return $this->xPostVar_arr;
    }

    /**
     * @return array
     */
    public function getPlausiVar(): array
    {
        return $this->xPlausiVar_arr;
    }

    /**
     * @param array      $xVar_arr
     * @param array|null $hasHTML_arr
     * @param bool       $toEntities
     * @return bool
     */
    public function setPostVar($xVar_arr, $hasHTML_arr = null, bool $toEntities = false): bool
    {
        if (is_array($xVar_arr) && count($xVar_arr) > 0) {
            if (is_array($hasHTML_arr)) {
                $exclude_keys = array_fill_keys($hasHTML_arr, 1);
                $filter_arr   = array_diff_key($xVar_arr, $exclude_keys);
                $exclude_arr  = array_intersect_key($xVar_arr, $exclude_keys);

                if ($toEntities) {
                    array_walk($exclude_arr, function (&$value) {
                        $value = htmlentities($value);
                    });
                }
                $this->xPostVar_arr = array_merge($xVar_arr, $filter_arr, $exclude_arr);
            } else {
                $this->xPostVar_arr = $xVar_arr;
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $xVar_arr
     * @return bool
     */
    public function setPlausiVar($xVar_arr): bool
    {
        if (is_array($xVar_arr) && count($xVar_arr) > 0) {
            $this->xPlausiVar_arr = $xVar_arr;

            return true;
        }

        return false;
    }

    /**
     * @param null $cTyp
     * @param bool $bUpdate
     * @return bool
     */
    public function doPlausi($cTyp = null, bool $bUpdate = false): bool
    {
        return false;
    }
}
