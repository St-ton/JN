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
    }
}
