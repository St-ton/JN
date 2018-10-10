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
    protected $szReasonName;

    /**
     * @var array
     */
    private $vTablesUpdate = [
        'tbestellung' => [
            'ColKey'      => 'kBestellung',
            'ColIp'       => 'cIP',
            'ColCreated'  => 'dErstellt'
        ],
        'tbesucher' => [
            'ColKey'     => 'kBesucher',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dLetzteAktivitaet'
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
            'ColCreated' => 'dDate'
        ],
        'tsitemaptracker' => [
            'ColKey'     => 'kSitemapTracker',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt'
        ],
        'tsuchanfragencache' => [
            'ColKey'     => 'kSuchanfrageCache',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit'
        ],
        'ttagkunde' => [
            'ColKey'     => 'kTagKunde',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit'
        ],
        'tumfragedurchfuehrung' => [
            'ColKey'     => 'kUmfrageDurchfuehrung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDurchgefuehrt'
        ],
        'tverfuegbarkeitsbenachrichtigung' => [
            'ColKey'     => 'kVerfuegbarkeitsbenachrichtigung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt'
        ],
        'tvergleichsliste' => [
            'ColKey'     => 'kVergleichsliste',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDate'
        ]
    ];

    // tables to truncate   (not yet implemented)
    /**
     * @var array
     */
    private $vTablesClear = [
        'tfsession'
    ];

    /**
     * AnonymizeDeletedCustomer constructor
     *
     * @param $oNow
     * @param $iInterval
     */
    public function __construct($oNow, $iInterval)
    {
        parent::__construct($oNow, $iInterval);
        $this->szReasonName = substr(__CLASS__, strrpos(__CLASS__, '\\')) . ': ';
    }

    /**
     * run all anonymize processes
     */
    public function execute(): void
    {
        $this->anon_all_ips();
    }

    /**
     * anonymize IPs in various tables
     */
    public function anon_all_ips()
    {
        $oAnonymizer = new IpAnonymizer('', true); // anonymize "beautified"
        $szIpMaskV4  = $oAnonymizer->getMaskV4();
        $szIpMaskV6  = $oAnonymizer->getMaskV6();
        $szIpMaskV4  = substr($szIpMaskV4, strpos($szIpMaskV4, '.0'), \strlen($szIpMaskV4)-1);
        $szIpMaskV6  = substr($szIpMaskV6, strpos($szIpMaskV6, ':0000'), \strlen($szIpMaskV6)-1);

        $this->szReason = $this->szReasonName . 'anonymize IPs in general';
        foreach ($this->vTablesUpdate as $szTableName => $vTable) {
            // select maximum 10,000 rows in one step!
            // (if this script is running each day, we need some days
            // to anonymize more than 10,000 data sets)
            $vResult = \Shop::Container()->getDB()->query('SELECT
                    ' . $vTable['ColKey'] . ',
                    ' . $vTable['ColIp'] . ',
                    ' . $vTable['ColCreated'] . '
                FROM
                    ' . $szTableName . '
                WHERE
                    NOT INSTR(cIP, ".*") > 0
                    AND NOT INSTR(cIP, "' . $szIpMaskV4 . '") > 0
                    AND NOT INSTR(cIP, "' . $szIpMaskV6 . '") > 0
                    AND (CASE ' . $vTable['ColCreated'] . ' * 1 = ' . $vTable['ColCreated'] . '
                        WHEN 1 THEN ' . $vTable['ColCreated'] . ' <= "' . $this->oNow->format('Y-m-d H:i:s') . '" - INTERVAL ' . $this->iInterval . ' DAY
                        WHEN 0 THEN FROM_UNIXTIME(' . $vTable['ColCreated'] . ') <= "' . $this->oNow->format('Y-m-d H:i:s') . '" - INTERVAL ' . $this->iInterval . ' DAY
                    END)
                ORDER BY
                    ' . $vTable['ColCreated'] . ' ASC
                LIMIT ' . $this->iWorkLimit,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (\is_array($vResult) && 0 < \count($vResult)) {
                foreach ($vResult as $oRow) {
                    $oRow->cIP = $oAnonymizer->setIp($oRow->cIP)->anonymize();
                    $szKeyColName = $vTable['ColKey'];
                    \Shop::Container()->getDB()->update($szTableName, $vTable['ColKey'], (int)$oRow->$szKeyColName, $oRow);
                }
                ($this->oLogger === null) ?: $this->oLogger->log(JTLLOG_LEVEL_NOTICE, 'Rows updated: ' . $iRowCount);
            }
        }

    }

}

