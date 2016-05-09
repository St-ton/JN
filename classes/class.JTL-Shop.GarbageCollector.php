<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class GarbageCollector
 */
class GarbageCollector
{
    /**
     * @var array
     */
    protected $cTable_arr;

    /**
     *
     */
    public function __construct()
    {
        // cInterval = Days
        $this->cTable_arr = array(
            'tbesucherarchiv'                  => array(
                'cDate'     => 'dZeit',
                'cSubTable' => array(
                    'tbesuchersuchausdruecke' => 'kBesucher'
                ),
                'cInterval' => '180'
            ),
            'tcheckboxlogging'                 => array(
                'cDate'     => 'dErstellt',
                'cSubTable' => null,
                'cInterval' => '365'
            ),
            'texportformatqueuebearbeitet'     => array(
                'cDate'     => 'dZuletztGelaufen',
                'cSubTable' => null,
                'cInterval' => '60'
            ),
            'tkampagnevorgang'                 => array(
                'cDate'     => 'dErstellt',
                'cSubTable' => null,
                'cInterval' => '365'
            ),
            'tpreisverlauf'                    => array(
                'cDate'     => 'dDate',
                'cSubTable' => null,
                'cInterval' => '120'
            ),
            'tredirectreferer'                 => array(
                'cDate'     => 'dDate',
                'cSubTable' => null,
                'cInterval' => '60'
            ),
            'tsitemapreport'                   => array(
                'cDate'     => 'dErstellt',
                'cSubTable' => array(
                    'tsitemapreportfile' => 'kSitemapReport'
                ),
                'cInterval' => '120'
            ),
            'tsuchanfrage'                     => array(
                'cDate'     => 'dZuletztGesucht',
                'cSubTable' => array(
                    'tsuchanfrageerfolglos' => 'cSuche',
                    'tsuchanfrageblacklist' => 'cSuche',
                    'tsuchanfragencache'    => 'cSuche'
                ),
                'cInterval' => '120'
            ),
            'tsuchcache'                       => array(
                'cDate'     => 'dGueltigBis',
                'cSubTable' => array(
                    'tsuchcachetreffer' => 'kSuchCache'
                ),
                'cInterval' => '30'
            ),
            'tverfuegbarkeitsbenachrichtigung' => array(
                'cDate'     => 'dBenachrichtigtAm',
                'cSubTable' => null,
                'cInterval' => '90'
            )
        );
    }

    /**
     * @return $this
     */
    public function run()
    {
        foreach ($this->cTable_arr as $cTable => $cMainTable_arr) {
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
                Shop::DB()->query("DELETE {$cFrom} FROM {$cTable} {$cJoin} WHERE DATE_SUB(now(), INTERVAL {$cInterval} DAY) >= {$cTable}.{$cDateField}", 3);
            } else {
                Shop::DB()->query("DELETE FROM {$cTable} WHERE DATE_SUB(now(), INTERVAL {$cInterval} DAY) >= {$cDateField}", 3);
            }
        }

        return $this;
    }
}
