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
        'tbesucherarchiv' => [
            'cDate'     => 'dZeit',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tbesuchersuchausdruecke' => 'kBesucher'
            ],
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 180 days)
        ],
        'tcheckboxlogging' => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'texportformatqueuebearbeitet' => [
            'cDate'     => 'dZuletztGelaufen',
            'cDateType' => 'DATETIME',
            'cSubTable' => null,
            'cInterval' => '30'
        ],
        'tkampagnevorgang' => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
            'cSubTable' => null,
            'cInterval' => '365'
        ],
        'tpreisverlauf' => [
            'cDate'     => 'dDate',
            'cDateType' => 'DATE',
            'cSubTable' => null,
            'cInterval' => '730' // 2 years (former 120 days)
        ],
        'tredirectreferer' => [
            'cDate'     => 'dDate',
            'cDateType' => 'TIMESTAMP',
            'cSubTable' => null,
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 60 days)
        ],
        'tsitemapreport' => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tsitemapreportfile' => 'kSitemapReport'
            ],
            'cInterval' => '365' // (former 120 days)
        ],
        'tsuchanfrage' => [
            'cDate'     => 'dZuletztGesucht',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tsuchanfrageerfolglos' => 'cSuche',
                'tsuchanfrageblacklist' => 'cSuche',
                'tsuchanfragencache'    => 'cSuche' // (anonymized after 7 days)
            ],
            'cInterval' => '2920' // anonymized after 7 days, removed after 8 years (former 60 days)
        ],
        'tsuchcache' => [
            'cDate'     => 'dGueltigBis',
            'cDateType' => 'DATETIME',
            'cSubTable' => [
                'tsuchcachetreffer' => 'kSuchCache'
            ],
            'cInterval' => '30'
        ],
        'tfsession' => [
            'cDate'     => 'dErstellt',
            'cDateType' => 'DATETIME',
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
            $cObjectNow    = $this->oNow->format('Y-m-d H:i:s');

            if ($cSubTable_arr !== null) {
                $cFrom = $cTable;
                $cJoin = '';
                foreach ($cSubTable_arr as $cSubTable => $cKey) {
                    $cFrom .= ", {$cSubTable}";
                    $cJoin .= " LEFT JOIN {$cSubTable} ON {$cSubTable}.{$cKey} = {$cTable}.{$cKey}";
                }
                $szDateColumn = "{$cTable}.{$cDateField}";
                if ($cMainTable_arr['cDateType'] === 'TIMESTAMP') {
                    $szDateColumn = "FROM_UNIXTIME({$szDateColumn})";
                }
                $res = \Shop::Container()->getDB()->query(
                    "DELETE {$cFrom}
                        FROM {$cTable} {$cJoin}
                        WHERE DATE_SUB('{$cObjectNow}', INTERVAL {$cInterval} DAY) >= {$szDateColumn}",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            } else {
                $szDateColumn = $cDateField;
                if ($cMainTable_arr['cDateType'] === 'TIMESTAMP') {
                    $szDateColumn = "FROM_UNIXTIME({$szDateColumn})";
                }
                $res = \Shop::Container()->getDB()->query(
                    "DELETE FROM {$cTable}
                        WHERE DATE_SUB('{$cObjectNow}', INTERVAL {$cInterval} DAY) >= {$szDateColumn}",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }
        }
    }
}

