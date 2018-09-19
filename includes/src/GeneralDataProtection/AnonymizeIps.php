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
    protected $szReason = 'anonymize_IPs_older_than_one_year';

    /**
     * @var array
     */
    private $vTablesUpdate = [
          'tbestellung' => [
            'ColKey'     => 'kBestellung',
            'ColIp'      => 'cIP',
            'ColCreated' => 'dErstellt'
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
     * anonymize IPs in various tables
     */
    public function execute()
    {
        foreach ($this->vTablesUpdate as $szTableName => $vTable) {
            $voTableData = $this->readOlderThanOneYaer($szTableName, $vTable['ColKey'], $vTable['ColIp'], $vTable['ColCreated']);

            if (\is_array($voTableData) && 0 < \count($voTableData)) {
                $this->saveToJournal($szTableName, get_object_vars($voTableData[0]), $voTableData);
                foreach ($voTableData as $oRow) {
                    $oRow->cIP = (new IpAnonymizer($oRow->cIP))->anonymize();
                    \Shop::Container()->getDB()->update($szTableName, $vTable['ColKey'], (int)$oRow->kBestellung, $oRow);
                }
            }
        }

    }



    /**
     * looking for IPs in various tables,
     * which has to be anonymized the end of the next year after there creation
     *
     * @param string $szTableName
     * @param string $szColKey
     * @param string $szColIp
     * @param string $szColCreated
     * @return array
     */
    private function readOlderThanOneYaer(string $szTableName, string $szColKey, string $szColIp, string $szColCreated)
    {
        // NOTE: queryPrepared() is not possible here!
        // (a RDBMS can not prepare a execution-plan with variable table-names)
        return \Shop::Container()->getDB()->query('SELECT
                      `' . $szColKey . '`
                    , `' . $szColIp . '`
                    , `' . $szColCreated . '`
                FROM
                    `' . $szTableName . '`
                WHERE
                    `' . $szColCreated . '` <= LAST_DAY(DATE_ADD(NOW() - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(NOW()) MONTH))'
            , \DB\ReturnType::ARRAY_OF_OBJECTS);
    }

}
