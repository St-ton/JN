<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace DB\Services;


use DB\DbInterface;

/**
 * Class GcService
 * @package DB\Services
 */
class GcService implements GcServiceInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var array
     */
    protected $definition = [
        'tbesucherarchiv'                  => [
            'cDate'     => 'dZeit',
            'cSubTable' => [
                'tbesuchersuchausdruecke' => 'kBesucher'
            ],
            'cInterval' => '180'
        ],
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
        'tredirectreferer'                 => [
            'cDate'     => 'dDate',
            'cSubTable' => null,
            'cInterval' => '60'
        ],
        'tsitemapreport'                   => [
            'cDate'     => 'dErstellt',
            'cSubTable' => [
                'tsitemapreportfile' => 'kSitemapReport'
            ],
            'cInterval' => '120'
        ],
        'tsuchanfrage'                     => [
            'cDate'     => 'dZuletztGesucht',
            'cSubTable' => [
                'tsuchanfrageerfolglos' => 'cSuche',
                'tsuchanfrageblacklist' => 'cSuche',
                'tsuchanfragencache'    => 'cSuche'
            ],
            'cInterval' => '120'
        ],
        'tsuchcache'                       => [
            'cDate'     => 'dGueltigBis',
            'cSubTable' => [
                'tsuchcachetreffer' => 'kSuchCache'
            ],
            'cInterval' => '30'
        ],
        'tverfuegbarkeitsbenachrichtigung' => [
            'cDate'     => 'dBenachrichtigtAm',
            'cSubTable' => null,
            'cInterval' => '90'
        ]
    ];

    /**
     * GcService constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return $this
     */
    public function run(): GcServiceInterface
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
                $this->db->query(
                    "DELETE {$cFrom} 
                        FROM {$cTable} {$cJoin} 
                        WHERE DATE_SUB(now(), INTERVAL {$cInterval} DAY) >= {$cTable}.{$cDateField}",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            } else {
                $this->db->query(
                    "DELETE FROM {$cTable} 
                        WHERE DATE_SUB(now(), INTERVAL {$cInterval} DAY) >= {$cDateField}",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }
        }

        return $this;
    }

}
