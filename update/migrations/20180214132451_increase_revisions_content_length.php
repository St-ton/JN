<?php
/**
 * Increase migration content length
 *
 * @author mscho
 * @created Wed, 14 Feb 2018 13:24:51 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180214132451
 */
class Migration_20180214132451 extends Migration implements IMigration
{
    protected $author      = 'mscho';
    protected $description = 'Increase revisions content length';

    public function up()
    {
        $this->execute('ALTER TABLE trevisions MODIFY content LONGTEXT');
    }

    public function down()
    {
        $this->execute('ALTER TABLE trevisions MODIFY content TEXT');
    }
}
