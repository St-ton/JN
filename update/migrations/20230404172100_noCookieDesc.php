<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230404172100
 */
class Migration_20230404172100 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Remove index.php url from noCookieDesc alert';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'errorMessages',
            'noCookieDesc',
            'Zur Nutzung unserer Seite müssen Sie im Browser Cookies aktivieren.<br>' .
            'Rufen Sie dann noch einmal unsere <a href="%s">Startseite</a> auf.'
        );

        $this->setLocalization(
            'eng',
            'errorMessages',
            'noCookieDesc',
            'To use our site you have to activate cookies in your browser.<br>' .
            'After activation, please try to open our <a href="%s">homepage</a> again.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
