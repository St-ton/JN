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
        'tbesucherarchiv'                  => [
            'cDate'     => 'dZeit',
            'cSubTable' => [
                'tbesuchersuchausdruecke' => 'kBesucher'
            ],
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 180 days)
        ],
        'tcheckboxlogging' => [
            'cDate'     => 'dErstellt',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'texportformatqueuebearbeitet' => [
            'cDate'     => 'dZuletztGelaufen',
            'cSubTable' => null,
            'cInterval' => '30'
        ],
        'tkampagnevorgang' => [
            'cDate'     => 'dErstellt',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'tpreisverlauf' => [
            'cDate'     => 'dDate',
            'cSubTable' => null,
            'cInterval' => '730' // 2 years (former 120 days)
        ],
        'tredirectreferer' => [
            'cDate'     => 'dDate',
            'cSubTable' => null,
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 60 days)
        ],
        'tsitemapreport' => [
            'cDate'     => 'dErstellt',
            'cSubTable' => [
                'tsitemapreportfile' => 'kSitemapReport'
            ],
            'cInterval' => '365' // (former 120 days)
        ],
        'tsuchanfrage' => [
            'cDate'     => 'dZuletztGesucht',
            'cSubTable' => [
                'tsuchanfrageerfolglos' => 'cSuche',
                'tsuchanfrageblacklist' => 'cSuche',
                'tsuchanfragencache'    => 'cSuche' // (anonymized after 7 days)
            ],
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 60 days)
        ],
        'tsuchcache' => [
            'cDate'     => 'dGueltigBis',
            'cSubTable' => [
                'tsuchcachetreffer' => 'kSuchCache'
            ],
            'cInterval' => '30'
        ],
        'tfsession' => [
            'cDate'     => 'dErstellt',
            'cSubTable' => null,
            'cInterval' => '7'
        ]
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

