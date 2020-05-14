<?php
/**Added cAntwortKommentar column to tnewskommentar table*/

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200512132300
 */
class Migration_20200512132300 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Added cAntwortKommentar column to tnewskommentar table';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute(
            "ALTER TABLE `tnewskommentar`
        ADD `cAntwortKommentar` mediumtext COLLATE 'utf8_unicode_ci' NULL AFTER `cKommentar`;"
        );
        $this->setLocalization('ger', 'news', 'commentReply', 'Antwort');
        $this->setLocalization('eng', 'news', 'commentReply', 'Answer');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('commentReply','news');
        $this->execute(
            "ALTER TABLE `tnewskommentar`
            DROP `cAntwortKommentar`;"
        );
    }
}
