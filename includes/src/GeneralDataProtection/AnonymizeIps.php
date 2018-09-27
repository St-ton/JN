<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * anonymize IPs in various tables.
 *
 * names of the tables, we manipulate:
 *
 * `tbestellung`
 * `tbesucher`
 * `tbesucherarchiv`
 * `tkontakthistory`
 * `tproduktanfragehistory`
 * `tredirectreferer`
 * `tsitemaptracker`
 * `tsuchanfragencache`
 * `ttagkunde`
 * `tumfragedurchfuehrung`
 * `tverfuegbarkeitsbenachrichtigung`
 * `tvergleichsliste`
 */
class AnonymizeIps extends Method implements MethodInterface
{
    /**
     * @var string
     */
    protected $szReason = 'anonymize_all_IPs';

    /**
     * @var array
     */
    private $vTablesUpdate = [
        'tbestellung' => [
            'ColKey'      => 'kBestellung',
            'ColIp'       => 'cIP',
            'ColCreated'  => 'dErstellt',
            'saveInJournal' => 1
        ],
        'tbesucher' => [
            'ColKey'     => 'kBesucher',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dLetzteAktivitaet',
            'saveInJournal' => 1
        ],
        'tbesucherarchiv' => [
            'ColKey'     => 'kBesucher',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit'
        ],
        'tkontakthistory' => [
            'ColKey'     => 'kKontaktHistory',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt'
        ],
        'tproduktanfragehistory' => [
            'ColKey'     => 'kProduktanfrageHistory',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt'
        ],
        'tredirectreferer' => [
            'ColKey'     => 'kRedirectReferer',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDate',
            'saveInJournal' => 1
        ],
        'tsitemaptracker' => [
            'ColKey'     => 'kSitemapTracker',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'saveInJournal' => 1
        ],
        'tsuchanfragencache' => [
            'ColKey'     => 'kSuchanfrageCache',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit'
        ],
        'ttagkunde' => [
            'ColKey'     => 'kTagKunde',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit',
            'saveInJournal' => 1
        ],
        'tumfragedurchfuehrung' => [
            'ColKey'     => 'kUmfrageDurchfuehrung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDurchgefuehrt',
            'saveInJournal' => 1
        ],
        'tverfuegbarkeitsbenachrichtigung' => [
            'ColKey'     => 'kVerfuegbarkeitsbenachrichtigung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'saveInJournal' => 1
        ],
        'tvergleichsliste' => [
            'ColKey'     => 'kVergleichsliste',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDate',
            'saveInJournal' => 1
        ]
    ];

    // tables to truncate   (not yet implemented)
    /**
     * @var array
     */
    private $vTablesClear = [
        'tfsession'
    ];

    public function execute()
    {
        $this->anon_all_ips();
    }

    /**
     * anonymize IPs in various tables
     */
    public function anon_all_ips()
    {
        $oAnonymizer = new IpAnonymizer('', true); // anonymize "beautified"
        $szIpMaskV4  = \Shop::getSettings([CONF_GLOBAL])['global']['anonymize_ip_mask_v4'];
        $szIpMaskV6  = \Shop::getSettings([CONF_GLOBAL])['global']['anonymize_ip_mask_v6'];
        $szIpMaskV4  = substr($szIpMaskV4, strpos($szIpMaskV4, '.0'), strlen($szIpMaskV4)-1);
        $szIpMaskV6  = substr($szIpMaskV6, strpos($szIpMaskV6, ':0000'), strlen($szIpMaskV6)-1);

        foreach ($this->vTablesUpdate as $szTableName => $vTable) {
            // select maximum 10,000 rows in one step!
            // (if this script is running each day, we need some days
            // to anonymize more than 10,000 data sets)
            $vResult    = \Shop::Container()->getDB()->query(
                'SELECT
                    ' . $vTable['ColKey'] . ',
                    ' . $vTable['ColIp'] . ',
                    ' . $vTable['ColCreated'] . '
                FROM
                    ' . $szTableName . '
                WHERE
                    NOT INSTR(cIP, ".*") > 0
                    AND NOT INSTR(cIP, "' . $szIpMaskV4 . '") > 0
                    AND NOT INSTR(cIP, "' . $szIpMaskV6 . '") > 0
                    AND (case ceil(' . $vTable['ColCreated'] . ') = ' . $vTable['ColCreated'] . '
                        when 0 then date(' . $vTable['ColCreated'] . ') <= NOW() - INTERVAL ' . $this->iInterval . ' DAY
                        when 1 then from_unixtime(' . $vTable['ColCreated'] . ') <= NOW() - INTERVAL ' . $this->iInterval . ' DAY
                    end)
                ORDER BY
                    ' . $vTable['ColCreated'] . ' ASC
                LIMIT 10000',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (\is_array($vResult) && 0 < \count($vResult)) {
                if (isset($vTable['saveInJournal']) && $vTable['saveInJournal'] !== null) {
                    $this->saveToJournal($szTableName, get_object_vars($vResult[0]), $vResult);
                }
                foreach ($vResult as $oRow) {
                    $oRow->cIP = $oAnonymizer->setIp($oRow->cIP)->anonymize();
                    $szKeyColName = $vTable['ColKey'];
                    \Shop::Container()->getDB()->update($szTableName, $vTable['ColKey'], (int)$oRow->$szKeyColName, $oRow);
                }
            }
        }

    }

}

