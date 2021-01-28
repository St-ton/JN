<?php

/**
 * Add bots to tbesucherbot
 *
 * @author je
 * @created Wed, 29 Apr 2020 09:19:00 +0200
 */

/**
 * Class Migration_20200429091900
 */
class Migration_20200429091900 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Add bots to tbesucherbot';

    /**
     * @return mixed|void
     */
    public function up()
    {
        $this->execute("INSERT INTO tbesucherbot (cUserAgent, cBeschreibung) VALUES ('bingbot','Bing.com')");
        $this->execute("INSERT INTO tbesucherbot (cUserAgent, cBeschreibung) VALUES ('semrush','SemRush Bot')");
        $this->execute("INSERT INTO tbesucherbot (cUserAgent, cBeschreibung) VALUES ('qwantify','qwant.com')");
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->execute("DELETE FROM tbesucherbot WHERE cUserAgent IN ('bingbot','semrush','qwantify')");
    }
}
