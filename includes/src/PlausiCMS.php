<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

/**
 * Class PlausiCMS
 * @package JTL
 */
class PlausiCMS extends Plausi
{
    /**
     * @param null|string $cType
     * @param bool        $update
     * @return bool
     */
    public function doPlausi($cType = null, bool $update = false): bool
    {
        if (\count($this->xPostVar_arr) === 0 || \mb_strlen($cType) === 0) {
            return false;
        }
        switch ($cType) {
            case 'lnk':
                // cName
                if (!isset($this->xPostVar_arr['cName']) || \mb_strlen($this->xPostVar_arr['cName']) === 0) {
                    $this->xPlausiVar_arr['cName'] = 1;
                }
                // cKundengruppen
                if (!\is_array($this->xPostVar_arr['cKundengruppen'])
                    || \count($this->xPostVar_arr['cKundengruppen']) === 0
                ) {
                    $this->xPlausiVar_arr['cKundengruppen'] = 1;
                }
                // nLinkart
                if (!isset($this->xPostVar_arr['nLinkart']) || (int)$this->xPostVar_arr['nLinkart'] === 0) {
                    $this->xPlausiVar_arr['nLinkart'] = 1;
                } elseif ((int)$this->xPostVar_arr['nLinkart'] === 3
                    && (!isset($this->xPostVar_arr['nSpezialseite']) || (int)$this->xPostVar_arr['nSpezialseite'] <= 0)
                ) {
                    $this->xPlausiVar_arr['nLinkart'] = 3;
                }

                return true;

            case 'grp':
                // cName
                if (!isset($this->xPostVar_arr['cName']) || \mb_strlen($this->xPostVar_arr['cName']) === 0) {
                    $this->xPlausiVar_arr['cName'] = 1;
                }

                // cTempaltename
                if (!isset($this->xPostVar_arr['cTemplatename'])
                    || \mb_strlen($this->xPostVar_arr['cTemplatename']) === 0
                ) {
                    $this->xPlausiVar_arr['cTemplatename'] = 1;
                }

                return true;
        }

        return false;
    }
}
