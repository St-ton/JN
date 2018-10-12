<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Class GcService
 * @package DB\Services
 */
class CleanupService extends Method implements MethodInterface
{
    /**
     * @var array
     */
    protected $definition = [
        /*
         * anonymized (AnonymizeIps) and removed (CleanupCustomerRelicts) via GDP  in 0d (!)
         *
         *'tbesucherarchiv'                  => [
         *    'cDate'     => 'dZeit',
         *    'cSubTable' => [
         *        'tbesuchersuchausdruecke' => 'kBesucher'
         *    ],
         *    'cInterval' => '180'
         *],
         */
        'tcheckboxlogging'                 => [
            'cDate'     => 'dErstellt',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'texportformatqueuebearbeitet'     => [
            'cDate'     => 'dZuletztGelaufen',
            'cSubTable' => null,
            'cInterval' => '60'
        ],
        'tkampagnevorgang'                 => [
            'cDate'     => 'dErstellt',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'tpreisverlauf'                    => [
            'cDate'     => 'dDate',
            'cSubTable' => null,
            'cInterval' => '120'
        ],
        /*
         * anonymized via GDP (AnonymizeIps) in 7d (!)
         *
         *'tredirectreferer'                 => [
         *    'cDate'     => 'dDate',
         *    'cSubTable' => null,
         *    'cInterval' => '60'
         *],
         */
        'tsitemapreport'                   => [
            'cDate'     => 'dErstellt',
            'cSubTable' => [
                'tsitemapreportfile' => 'kSitemapReport'
            ],
            'cInterval' => '120'
        ],
        /*
         * partly in GDP
         * (AnonymizeIps) 'tsuchanfragencache' anonymized in 7d
         *
         *'tsuchanfrage'                     => [
         *    'cDate'     => 'dZuletztGesucht',
         *    'cSubTable' => [
         *        'tsuchanfrageerfolglos' => 'cSuche',
         *        'tsuchanfrageblacklist' => 'cSuche',
         *        'tsuchanfragencache'    => 'cSuche'
         *    ],
         *    'cInterval' => '120'
         *],
         */
        'tsuchcache'                       => [
            'cDate'     => 'dGueltigBis',
            'cSubTable' => [
                'tsuchcachetreffer' => 'kSuchCache'
            ],
            'cInterval' => '30'
        ],
    ];


    /**
     * remove data from various tables
     */
    public function execute()
    {
        foreach ($this->definition as $cTable => $cMainTable_arr) {
            $cDateField    = $cMainTable_arr['cDate'];
            $cSubTable_arr = $cMainTable_arr['cSubTable'];
            $cInterval     = $cMainTable_arr['cInterval'];

            if ((int)$cInterval !== $this->iInterval) {
                continue;
            }

            if ($cSubTable_arr !== null) {
                $cFrom = "{$cTable}";
                $cJoin = '';
                foreach ($cSubTable_arr as $cSubTable => $cKey) {
                    $cFrom .= ", {$cSubTable}";
                    $cJoin .= " LEFT JOIN {$cSubTable} ON {$cSubTable}.{$cKey} = {$cTable}.{$cKey}";
                }
                \Shop::Container()->getDB()->query(
                    "DELETE {$cFrom}
                        FROM {$cTable} {$cJoin}
                        WHERE DATE_SUB(NOW(), INTERVAL {$cInterval} DAY) >= {$cTable}.{$cDateField}",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            } else {
                \Shop::Container()->getDB()->query(
                    "DELETE FROM {$cTable}
                        WHERE DATE_SUB(NOW(), INTERVAL {$cInterval} DAY) >= {$cDateField}",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }
        }
    }

}

