<?php
/**
 * cleanup newsletter optin messages
 *
 * @author Clemens Rudolph
 * @created Wed, 24 Apr 2019 09:35:17 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190424093517
 */
class Migration_20190424093517 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'cleanup newsletter optin messages';

    public function up()
    {
        $this->removeLocalization('newsletterExists');
        $this->removeLocalization('newsletterDelete');

        $this->setLocalization('ger', 'messages', 'optinSucceededMailSent', 'Die Mail mit Ihrem Freischalt-Code wurde bereits an Sie verschickt');
        $this->setLocalization('eng', 'messages', 'optinSucceededMailSent', 'The mail with your activation-code was already sent.');
    }

    public function down()
    {
        $this->removeLocalization('optinSucceededMailSent');

        $this->setLocalization('ger', 'errorMessages', 'newsletterDelete', 'Sie wurden erfolgreich aus unserem Newsletterverteiler ausgetragen.');
        $this->setLocalization('eng', 'errorMessages', 'newsletterDelete', 'You have been successfully deleted from our News list.');
        $this->setLocalization('ger', 'errorMessages', 'newsletterExists', 'Fehler: Ihre E-Mail-Adresse ist bereits vorhanden.');
        $this->setLocalization('eng', 'errorMessages', 'newsletterExists', 'Error: It appears that your E-Mail already exists.');
    }
}
