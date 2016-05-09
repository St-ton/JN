<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    /**
     * Class AuswahlAssistent
     */
    class AuswahlAssistent
    {
        /**
         * @param string $cKey
         * @param int    $kKey
         */
        public function __construct($cKey, $kKey)
        {
        }

        /**
         * @return mixed
         */
        public static function getLinks()
        {
            return Shop::DB()->query(
                "SELECT tlink.*
                    FROM tlink
                    WHERE tlink.nLinkart = " . LINKTYP_AUSWAHLASSISTENT, 2
            );
        }

        /**
         * @param string $cKey
         * @param int    $kKey
         * @param int    $kSprache
         * @return AuswahlAssistentGruppe|bool
         */
        public static function getGroupsByLocation($cKey, $kKey, $kSprache)
        {
            if (strlen($cKey) > 0 && intval($kKey) > 0 && intval($kSprache) > 0) {
                $oOrt = Shop::DB()->query(
                    "SELECT tauswahlassistentort.kAuswahlAssistentGruppe
                        FROM tauswahlassistentort
                        JOIN tauswahlassistentgruppe ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                            AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                        WHERE tauswahlassistentort.cKey = '" . Shop::DB()->escape($cKey) . "'
                            AND tauswahlassistentort.kKey = " . (int)$kKey, 1
                );

                if (isset($oOrt->kAuswahlAssistentGruppe) && $oOrt->kAuswahlAssistentGruppe > 0) {
                    return new AuswahlAssistentGruppe($oOrt->kAuswahlAssistentGruppe);
                }
            }

            return false;
        }
    }
}
