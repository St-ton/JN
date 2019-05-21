<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Change OPC page id type
 *
 * @author Danny Raufeisen
 */

class Migration_20181102102400 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Change OPC page id type';

    public function up()
    {
        $this->execute('ALTER TABLE topcpage DROP INDEX cPageId');
        $this->execute('ALTER TABLE topcpage MODIFY cPageId MEDIUMTEXT NOT NULL');
        $this->execute('ALTER TABLE topcpage ADD UNIQUE INDEX (cPageId(255), dPublishFrom)');
    }

    public function down()
    {
        $this->execute('ALTER TABLE topcpage DROP INDEX cPageId');
        $this->execute('ALTER TABLE topcpage MODIFY cPageId CHAR(32) NOT NULL');
        $this->execute('ALTER TABLE topcpage ADD UNIQUE INDEX (cPageId, dPublishFrom)');
    }
}
