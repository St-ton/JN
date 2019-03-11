<?php
/**
 * ftp settings
 *
 * @author aj
 * @created Mon, 17 Nov 2018 13:33:00 +0100
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
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20181117133301 extends Migration implements IMigration
{
    protected $author      = 'aj';
    protected $description = 'ftp settings';

    public function up()
    {
        $this->setConfig(
            'ftp_header',
            'FTP Verbindung',
            \CONF_FTP,
            'FTP Verbindung',
            null,
            100,
            (object)[ 'cConf' => 'N' ],
            true
        );
        $this->setConfig('ftp_hostname', 'localhost', \CONF_FTP, 'FTP Hostname', 'text', 101, null, true);
        $this->setConfig('ftp_port', '21', \CONF_FTP, 'FTP Port', 'number', 102, null, true);
        $this->setConfig('ftp_user', '', \CONF_FTP, 'FTP Benutzer', 'text', 103, null, true);
        $this->setConfig('ftp_pass', '', \CONF_FTP, 'FTP Passwort', 'pass', 104, null, true);
        $this->setConfig(
            'ftp_ssl',
            'N',
            \CONF_FTP,
            'FTP SSL',
            'selectbox',
            105,
            (object)[
                'cBeschreibung' => 'Verschlüsselte Verbindung aktivieren?',
                'inputOptions'  => [
                    '1' => 'Ja',
                    '0' => 'Nein',
                ],
            ],
            true
        );
        $this->setConfig(
            'ftp_path',
            '/',
            \CONF_FTP,
            'FTP Pfad',
            'text',
            106,
            (object)[ 'cBeschreibung' => 'Pfad zum Shop Hauptverzeichnis?' ],
            true
        );
    }

    public function down()
    {
        $this->removeConfig('ftp_header');
        $this->removeConfig('ftp_hostname');
        $this->removeConfig('ftp_port');
        $this->removeConfig('ftp_user');
        $this->removeConfig('ftp_pass');
        $this->removeConfig('ftp_ssl');
        $this->removeConfig('ftp_path');
    }
}
