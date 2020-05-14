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
    protected $description = 'Added isAdmin, parentCommentID columns to tnewskommentar table';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute(
            "ALTER TABLE `tnewskommentar`
            ADD `isAdmin` int(10) unsigned NULL COMMENT 'checks if comment was created by adminlogin' AFTER `cKommentar`,
            ADD `parentCommentID` int(10) unsigned NULL DEFAULT NULL COMMENT 'refers to the connected comment' AFTER `isAdmin`;"
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
            "ALTER TABLE `tnewskommentar` DROP `isAdmin`, DROP `parentCommentID`;"
        );
    }
}
