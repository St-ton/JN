<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PlausiTrennzeichen
 */
class PlausiTrennzeichen extends Plausi
{
    /**
     * @param null|string $cTyp
     * @param bool        $bUpdate
     * @return bool
     */
    public function doPlausi($cTyp = null, bool $bUpdate = false): bool
    {
        if (count($this->xPostVar_arr) === 0) {
            return false;
        }
        $nEinheit_arr = [JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_LENGTH, JTL_SEPARATOR_AMOUNT];
        foreach ($nEinheit_arr as $nEinheit) {
            // Anzahl Dezimalstellen
            $idx = 'nDezimal_' . $nEinheit;
            if (!isset($this->xPostVar_arr[$idx])) {
                $this->xPlausiVar_arr[$idx] = 1;
            }
            if ($nEinheit === JTL_SEPARATOR_AMOUNT && $this->xPostVar_arr[$idx] > 2) {
                $this->xPlausiVar_arr[$idx] = 2;
            }
            // Dezimaltrennzeichen
            $idx = 'cDezZeichen_' . $nEinheit;
            if (!isset($this->xPostVar_arr[$idx]) || strlen($this->xPostVar_arr[$idx]) === 0) {
                $this->xPlausiVar_arr[$idx] = 1;
            }
            // Tausendertrennzeichen
            $idx = 'cTausenderZeichen_' . $nEinheit;
            if (!isset($this->xPostVar_arr[$idx])) {
                $this->xPlausiVar_arr[$idx] = 1;
            }
        }

        return false;
    }
}
