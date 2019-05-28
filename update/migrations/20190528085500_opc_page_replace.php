<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Remove replace column from topcpage
 *
 * @author Danny Raufeisen
 */

class Migration_20190528085500 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Remove replace column from topcpage';

    public function up()
    {
        $this->execute('ALTER TABLE topcpage DROP COLUMN bReplace');
    }

    public function down()
    {
        $this->execute('ALTER TABLE topcpage ADD COLUMN bReplace BOOL NOT NULL DEFAULT 0');
    }
}
