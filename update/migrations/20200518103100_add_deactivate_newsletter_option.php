<?php declare(strict_types=1);

/**
 * @author mh
 * @created Mon, 18 May 2020 10:31:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200518103100
 */
class Migration_20200518103100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add newsletter active option';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'newsletter_active',
            'Y',
            \CONF_NEWSLETTER,
            'Newsletter aktivieren',
            'selectbox',
            15,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, ob der Newsletter genutzt werden soll.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->removeConfig('newsletter_active');
    }
}
