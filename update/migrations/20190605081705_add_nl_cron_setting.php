<?php
/**
 * Add NL cron setting
 *
 * @author Clemens Rudolph
 * @created Wed, 05 Jun 2019 08:17:05 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190605081705
 */
class Migration_20190605081705 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'Add NL cron setting';

    public function up()
    {
        $this->setConfig(
            'newsletter_send_delay',
            '2',
            \CONF_NEWSLETTER,
            'Newsletter Sendeverzögerung',
            'number',
            130,
            (object)[
                'cBeschreibung'     => 'Legt die Wartezeit zwischen den Newsletter-Sendungen fest.',
                'nStandardAnzeigen' => 1
            ],
            true
        );
    }

    public function down()
    {
        $this->removeConfig('newsletter_send_delay');
    }
}
