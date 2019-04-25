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
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190424093517 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'cleanup newsletter optin messages';

    public function up()
    {
        $this->removeLocalization('newsletterExists');
        $this->removeLocalization('newsletterDelete');

        $this->removeLocalization('optinSucceded');
        $this->removeLocalization('optinSuccededAgain');
        $this->setLocalization('ger', 'messages', 'optinSucceeded', 'Ihre Freischaltung ist erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSucceeded', 'Your confirmation was successfull.');
        $this->setLocalization('ger', 'messages', 'optinSucceededAgain', 'Ihre Freischaltung ist bereits erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSucceededAgain', 'Your confirmation is already active.');

        $this->setLocalization('ger', 'messages', 'optinSucceededMailSent', 'Die Mail mit Ihrem Freischalt-Code wurde bereits an Sie verschickt');
        $this->setLocalization('eng', 'messages', 'optinSucceededMailSent', 'The mail with your activation-code was already sent.');
    }

    public function down()
    {
        $this->removeLocalization('optinSucceededMailSent');

        $this->removeLocalization('optinSucceeded');
        $this->removeLocalization('optinSucceededAgain');
        $this->setLocalization('ger', 'messages', 'optinSucceded', 'Ihre Freischaltung ist erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSucceded', 'Your confirmation was successfull.');
        $this->setLocalization('ger', 'messages', 'optinSuccededAgain', 'Ihre Freischaltung ist bereits erfolgt.');
        $this->setLocalization('eng', 'messages', 'optinSuccededAgain', 'Your confirmation is already active.');

        $this->setLocalization('ger', 'errorMessages', 'newsletterDelete', 'Sie wurden erfolgreich aus unserem Newsletterverteiler ausgetragen.');
        $this->setLocalization('eng', 'errorMessages', 'newsletterDelete', 'You have been successfully deleted from our News list.');
        $this->setLocalization('ger', 'errorMessages', 'newsletterExists', 'Fehler: Ihre E-Mail-Adresse ist bereits vorhanden.');
        $this->setLocalization('eng', 'errorMessages', 'newsletterExists', 'Error: It appears that your E-Mail already exists.');
    }
}
