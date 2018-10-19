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
     * @var array
     */
    private $vTablesUpdate = [
        'tbestellung' => [
            'ColKey'     => 'kBestellung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tbesucherarchiv' => [
            'ColKey'     => 'kBesucher',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit',
            'ColType'    => 'DATETIME'
        ],
        'tkontakthistory' => [
            'ColKey'     => 'kKontaktHistory',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tproduktanfragehistory' => [
            'ColKey'     => 'kProduktanfrageHistory',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tredirectreferer' => [
            'ColKey'     => 'kRedirectReferer',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDate',
            'ColType'    => 'TIMESTAMP'
        ],
        'tsitemaptracker' => [
            'ColKey'     => 'kSitemapTracker',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tsuchanfragencache' => [
            'ColKey'     => 'kSuchanfrageCache',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit',
            'ColType'    => 'DATETIME'
        ],
        'ttagkunde' => [
            'ColKey'     => 'kTagKunde',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dZeit',
            'ColType'    => 'DATETIME'
        ],
        'tumfragedurchfuehrung' => [
            'ColKey'     => 'kUmfrageDurchfuehrung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDurchgefuehrt',
            'ColType'    => 'DATETIME'
        ],
        'tverfuegbarkeitsbenachrichtigung' => [
            'ColKey'     => 'kVerfuegbarkeitsbenachrichtigung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt',
            'ColType'    => 'DATETIME'
        ],
        'tvergleichsliste' => [
            'ColKey'     => 'kVergleichsliste',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dDate',
            'ColType'    => 'DATETIME'
        ]
    ];


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
        $szObjectNow = $this->oNow->format('Y-m-d H:i:s');

        foreach ($this->vTablesUpdate as $szTableName => $vTable) {
            $szSql = "SELECT
                    {$vTable['ColKey']},
                    {$vTable['ColIp']},
                    {$vTable['ColCreated']}
                FROM
                    {$szTableName}
                WHERE
                    NOT INSTR(cIP, '.*') > 0
                    AND NOT INSTR(cIP, '{$szIpMaskV4}') > 0
                    AND NOT INSTR(cIP, '{$szIpMaskV6}') > 0";

            if ($vTable['ColType'] !== 'TIMESTAMP') {
                $szSql .= " AND {$vTable['ColCreated']} <= '{$szObjectNow}' - INTERVAL {$this->iInterval} DAY";
            } else {
                $szSql .= " AND FROM_UNIXTIME({$vTable['ColCreated']}) <= '{$szObjectNow}' - INTERVAL {$this->iInterval} DAY";
            }

            $szSql .= " ORDER BY {$vTable['ColCreated']} ASC
                LIMIT {$this->iWorkLimit}";

            $vResult = \Shop::Container()->getDB()->query($szSql,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (\is_array($vResult) && 0 < $iRowCount = \count($vResult)) {
                foreach ($vResult as $oRow) {
                    try {
                        $oRow->cIP = $oAnonymizer->setIp($oRow->cIP)->anonymize();
                    } catch (\Exception $e) {
                        ($this->oLogger === null) ?: $this->oLogger->log(JTLLOG_LEVEL_WARNING, $e->getMessage());
                    }
                    $szKeyColName = $vTable['ColKey'];
                    \Shop::Container()->getDB()->update($szTableName, $vTable['ColKey'], (int)$oRow->$szKeyColName, $oRow);
                }
            }
        }
    }
}

