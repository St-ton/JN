<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Change List Portlet class name
 *
 * @author Danny Raufeisen
 */

class Migration_20190130123800 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Change List Portlet class name';

    public function up()
    {
        $this->execute("UPDATE topcportlet SET cClass = 'ListPortlet' WHERE cClass = 'PList'");
    }

    public function down()
    {
        $this->execute("UPDATE topcportlet SET cClass = 'PList' WHERE cClass = 'ListPortlet'");
    }
}
