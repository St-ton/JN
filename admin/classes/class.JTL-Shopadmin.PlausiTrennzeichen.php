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
    public function doPlausi($cTyp = null, $bUpdate = false)
    {
        if (count($this->xPostVar_arr) > 0) {
            $nEinheit_arr = [JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_AMOUNT];
            foreach ($nEinheit_arr as $nEinheit) {
                // Anzahl Dezimalstellen
                if (!isset($this->xPostVar_arr['nDezimal_' . $nEinheit]) || strlen($this->xPostVar_arr['nDezimal_' . $nEinheit]) === 0) {
                    $this->xPlausiVar_arr['nDezimal_' . $nEinheit] = 1;
                } else {
                    switch ($nEinheit) {
                        case JTL_SEPARATOR_AMOUNT:
                            if ($this->xPostVar_arr['nDezimal_' . $nEinheit] > 2) {
                                $this->xPlausiVar_arr['nDezimal_' . $nEinheit] = 2;
                            }
                            break;
                    }
                }
                // Dezimaltrennzeichen
                if (!isset($this->xPostVar_arr['cDezZeichen_' . $nEinheit]) || strlen($this->xPostVar_arr['cDezZeichen_' . $nEinheit]) === 0) {
                    $this->xPlausiVar_arr['cDezZeichen_' . $nEinheit] = 1;
                }
                // Tausendertrennzeichen
                if (!isset($this->xPostVar_arr['cTausenderZeichen_' . $nEinheit]) || strlen($this->xPostVar_arr['cTausenderZeichen_' . $nEinheit]) === 0) {
                    $this->xPlausiVar_arr['cTausenderZeichen_' . $nEinheit] = 1;
                }
            }
        }

        return false;
    }
}
