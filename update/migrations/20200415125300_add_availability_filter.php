<?php
/**
 * Add availability filter
 *
 * @author mh
 * @created Wed, 15 Apr 2020 12:53:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200415125300
 */
class Migration_20200415125300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add availability filter';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute(
            "INSERT INTO `tboxvorlage`
                VALUES (103, 0, 'tpl', 'Filter (Verfügbarkeit)', '2', 'box_filter_availability.tpl')"
        );

        $this->setConfig(
            'allgemein_availabilityfilter_benutzen',
            'Y',
            \CONF_NAVIGATIONSFILTER,
            'Verfügbarkeitsfilter benutzen',
            'selectbox',
            117,
            (object)[
                'cBeschreibung' => 'Soll die Verfügbarkeitsfilterung beim Filtern benutzt werden?',
                'inputOptions'  => [
                    'content' => 'Ja, im Contentbereich',
                    'box'     => 'Ja, in Navigationsbox',
                    'Y'       => 'Ja, im Contentbereich und der Navigationsbox',
                    'N'       => 'Nein'
                ]
            ]
        );
        $this->setConfig(
            'configgroup_110_availability_filter',
            'Verfügbarkeitsfilter',
            \CONF_NAVIGATIONSFILTER,
            'Verfügbarkeitsfilter',
            null,
            115,
            (object)['cConf' => 'N']
        );


        $this->setLocalization('ger', 'global', 'filterAvailability', 'Verfügbarkeit');
        $this->setLocalization('eng', 'global', 'filterAvailability', 'Availability');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute(
            'DELETE `tboxvorlage`, `tboxen`, `tboxensichtbar`
                  FROM `tboxvorlage`
                  LEFT JOIN `tboxen`
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                  LEFT JOIN `tboxensichtbar`
                    ON tboxen.kBox = tboxensichtbar.kBox
                  WHERE tboxvorlage.kBoxvorlage = 103'
        );

        $this->removeConfig('allgemein_availabilityfilter_benutzen');
        $this->removeConfig('configgroup_110_availability_filter');

        $this->removeLocalization('filterAvailability', 'global');
    }
}
