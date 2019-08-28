<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Add default value for topcblueprint.kPlugin
 * Remove List Portlet
 *
 * @author Danny Raufeisen
 */

class Migration_20190807150900 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Remove List Portlet';

    public function up()
    {
        $this->execute("DELETE FROM topcportlet WHERE cClass = 'ListPortlet'");
    }

    public function down()
    {
        $this->execute("
            INSERT INTO topcportlet (kPlugin, cTitle, cClass, cGroup, bActive)
              VALUES (0, 'List', 'ListPortlet', 'layout', 1)
        ");
    }
}
