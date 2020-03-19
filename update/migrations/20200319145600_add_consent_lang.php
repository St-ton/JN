<?php declare(strict_types=1);

/**
 * @author mh
 * @created Thu, 19 Mar 2020 19:12:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200319145600
 */
class Migration_20200319145600 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add consent lang';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute("INSERT INTO tsprachsektion (cName) VALUES ('consent');");

        $this->setLocalization('ger', 'consent', 'howWeUseCookies', 'Wie wir Cookies nutzen');
        $this->setLocalization('ger', 'consent', 'cookieSettings', 'Ihre Cookie-Einstelungen');
        $this->setLocalization('ger', 'consent', 'selectAll', 'Alle ab-/auswählen');
        $this->setLocalization('ger', 'consent', 'moreInformation', 'Weitere Informationen');
        $this->setLocalization('ger', 'consent', 'description', 'Beschreibung');
        $this->setLocalization('ger', 'consent', 'company', 'Verarbeitende Firma');
        $this->setLocalization('ger', 'consent', 'terms', 'Nutzungsbedingungen');
        $this->setLocalization('ger', 'consent', 'link', 'Link');
        $this->setLocalization('ger', 'consent', 'dataProtection', 'Datenschutz Einstellungen');
        $this->setLocalization('ger', 'consent', 'consentOnce', 'Einmalig zustimmen');
        $this->setLocalization('ger', 'consent', 'consentAlways', 'Dauerhaft zustimmen');
        $this->setLocalization('ger', 'consent', 'consentAll', 'Allen zustimmen');
        $this->setLocalization('ger', 'consent', 'close', 'Schließen');
        $this->setLocalization('ger', 'consent', 'configure', 'Konfigurieren');
        $this->setLocalization('ger', 'consent', 'consentDescription', 'Durch Klicken auf <i>Alle akzeptieren</i> ' .
            'gestatten Sie den Einsatz folgender Dienste auf unserer Website: %s. Sie können die Einstellung jederzeit ' .
            'ändern (Fingerabdruck-Icon links unten). Details unter <i>Konfigurieren</i> und in unserer Datenschutzerklärung.');
        $this->setLocalization('ger', 'consent', 'dataProtectionDescription', 'Ihre derzeitigen Datenschutz Einstellungen' .
            'verhindern, dass Sie diesen Inhalt sehen können. Sie können den Inhalt einmalig oder dauerhaft aktivieren.');
        $this->setLocalization('ger', 'consent', 'cookieSettingsDescription', 'Einstellungen, die Sie hier vornehmen, ' .
            'werden auf Ihrem Endgerät gespeichert. Sie können diese Einstellungen jederzeit über das ' .
            'Fingerabdruck-Icon links unten ändern.');
    }

    /**
     * @return mixed|void
     */
    public function down()
    {
        $this->execute("DELETE FROM tsprachsektion WHERE cName = 'consent';");

        $this->removeLocalization('howWeUseCookies');
        $this->removeLocalization('cookieSettings');
        $this->removeLocalization('selectAll');
        $this->removeLocalization('moreInformation');
        $this->removeLocalization('description');
        $this->removeLocalization('company');
        $this->removeLocalization('terms');
        $this->removeLocalization('link');
        $this->removeLocalization('dataProtection');
        $this->removeLocalization('consentOnce');
        $this->removeLocalization('consentAlways');
        $this->removeLocalization('consentAll');
        $this->removeLocalization('close');
        $this->removeLocalization('configure');
        $this->removeLocalization('consentDescription');
        $this->removeLocalization('dataProtectionDescription');
        $this->removeLocalization('cookieSettingsDescription');
    }
}
