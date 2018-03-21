<?php
/**
 * Add backend logging option
 */

use Monolog\Logger;

/**
 * Class Migration_20180319103900
 */
class Migration_20180319103900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add backend logging option';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'admin_login_logger_mode',
            '1',
            CONF_GLOBAL,
            'Adminloginversuche loggen?',
            'listbox',
            1503,
            (object) [
                'cBeschreibung' => 'Sollen Backend-Loginversuche geloggt werden?',
                'inputOptions'  => [
                    AdminLoginConfig::CONFIG_DB   => 'in Datenbank',
                    AdminLoginConfig::CONFIG_FILE => 'in Textdatei'
                ]
            ]
        );
        $this->execute("UPDATE tjtllog SET nLevel = " . Logger::ALERT . " WHERE nLevel = 1");
        $this->execute("UPDATE tjtllog SET nLevel = " . Logger::INFO . " WHERE nLevel = 2");
        $this->execute("UPDATE tjtllog SET nLevel = " . Logger::DEBUG . " WHERE nLevel = 4");
        $this->execute("UPDATE teinstellungen 
            SET cWert = " . Logger::DEBUG . " 
            WHERE cName = 'systemlog_flag' 
            AND (cWert = 4 OR cWert = 5 OR cWert = 6 OR cWert = 7)"
        );
        $this->execute("UPDATE teinstellungen 
            SET cWert = " . Logger::INFO . " 
            WHERE cName = 'systemlog_flag' 
            AND (cWert = 2 OR cWert = 3)"
        );
        $this->execute("UPDATE teinstellungen 
            SET cWert = " . Logger::ALERT . "
             WHERE cName = 'systemlog_flag' 
             AND cWert = 1"
        );
        $this->execute("UPDATE teinstellungenconf 
            SET cInputTyp = 'number', nStandardAnzeigen = 0 
            WHERE cName = 'systemlog_flag' 
            AND cInputTyp = 'text'
            AND kEinstellungenSektion = 1"
        );
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->removeConfig('admin_login_logger_mode');
    }
}
