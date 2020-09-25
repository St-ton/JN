<?php

/**
 * @author mh
 * @created Thu, 09 Aug 2020 09:33:00 +0200
 */

/**
 * Class Migration_20200924093300
 */
class Migration_20200924093300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missingToken, unknownError messages';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'messages', 'missingToken', 'Fehlerhafter Token.');
        $this->setLocalization('eng', 'messages', 'missingToken', 'Missing token.');
        $this->setLocalization('ger', 'messages', 'unknownError', 'Ein unbekannter Fehler trat auf.');
        $this->setLocalization('eng', 'messages', 'unknownError', 'An unknown error occured.');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeLocalization('missingToken');
        $this->removeLocalization('unknownError');
    }
}
