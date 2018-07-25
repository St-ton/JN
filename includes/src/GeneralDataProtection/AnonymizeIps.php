<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

class AnonymizeIps implements MethodInterface
{
    private $vTablesUpdate = [
          'tbestellung' => [
              'ColKey'     => 'kBestellung'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dErstellt'
        ]
        , 'tbesucher' => [
              'ColKey'     => 'kBesucher'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dLetzteAktivitaet'
        ]
        , 'tbesucherarchiv' => [
              'ColKey'     => 'kBesucher'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dZeit'
        ]
        , 'tkontakthistory' => [
              'ColKey'     => 'kKontaktHistory'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dErstellt'
        ]
        , 'tproduktanfragehistory' => [
              'ColKey'     => 'kProduktanfrageHistory'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dErstellt'
        ]
        , 'tredirectreferer' => [
              'ColKey'     => 'kRedirectReferer'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dDate'
        ]
        , 'tsitemaptracker' => [
              'ColKey'     => 'kSitemapTracker'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dErstellt'
        ]
        , 'tsuchanfragencache' => [
              'ColKey'     => 'kSuchanfrageCache'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dZeit'
        ]
        , 'ttagkunde' => [
              'ColKey'     => 'kTagKunde'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dZeit'
        ]
        , 'tumfragedurchfuehrung' => [
              'ColKey'     => 'kUmfrageDurchfuehrung'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dDurchgefuehrt'
        ]
        , 'tverfuegbarkeitsbenachrichtigung' => [
              'ColKey'     => 'kVerfuegbarkeitsbenachrichtigung'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dErstellt'
        ]
        , 'tvergleichsliste' => [
              'ColKey'     => 'kVergleichsliste'
            , 'ColIp'      => 'cIP'
            , 'ColCreated' => 'dDate'
        ]
    ];

    // tables to truncate
    private $vTablesClear = [
        'tfsession'
    ];


    public function __construct()
    {
    }

    /**
     * anonymize IP in various tables
     */
    public function execute()
    {
        foreach ($this->vTablesUpdate as $szTableName => $vTable) {
            // --DEVELOPMENT-- NOTE: here we can do other things beside "look one year forward"
            $voTableData = $this->readOneYearForward($szTableName, $vTable['ColKey'], $vTable['ColIp'], $vTable['ColCreated']);

            if (is_array($voTableData) && 0 < count($voTableData)) {
                foreach ($voTableData as $oRow) {
                    $oRow->cIP = (new \GdprAnonymizing\IpAnonymizer($oRow->cIP))->anonymize();
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
    private function readOneYearForward(string $szTableName, string $szColKey, string $szColIp, string $szColCreated)
    {
        // NOTE: queryPrepared() is not possible here!
        // (a RDBMS canot prepare a execution-plan with variable table-names)
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
