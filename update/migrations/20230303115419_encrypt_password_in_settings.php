<?php declare(strict_types=1);

/**
 * Encrypt password in settings
 *
 * @author sl
 * @created Fri, 03 Mar 2023 11:54:19 +0100
 */

use JTL\Shop;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230303115419
 */
class Migration_20230303115419 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Encrypt password in settings';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $settingsOfTypePassSQL = "SELECT
                conf.kEinstellungenSektion, setting.cName, cWert
            FROM
                teinstellungenconf conf
                    JOIN
                teinstellungen setting ON conf.cWertName = setting.cName
            WHERE
                cInputTyp = 'pass'";

        $settingsOfTypePass = $this->getDB()->getObjects($settingsOfTypePassSQL);
        foreach ($settingsOfTypePass as $settingOfTypePass) {
            $settingOfTypePass->cWert = Shop::Container()->getCryptoService()->encryptXTEA($settingOfTypePass->cWert);
            $stmt                     = "UPDATE teinstellungen SET cWert = :cWert WHERE kEinstellungenSektion = :section AND cName = :settingName";
            $this->getDB()->queryPrepared($stmt, [
                'cWert'       => $settingOfTypePass->cWert,
                'section'     => $settingOfTypePass->kEinstellungenSektion,
                'settingName' => $settingOfTypePass->cName]);
        }
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $settingsOfTypePassSQL = "SELECT
                conf.kEinstellungenSektion, setting.cName, cWert
            FROM
                teinstellungenconf conf
                    JOIN
                teinstellungen setting ON conf.cWertName = setting.cName
            WHERE
                cInputTyp = 'pass'";

        $settingsOfTypePass = $this->getDB()->getObjects($settingsOfTypePassSQL);
        foreach ($settingsOfTypePass as $settingOfTypePass) {
            $settingOfTypePass->cWert = Shop::Container()->getCryptoService()->decryptXTEA($settingOfTypePass->cWert);
            $stmt                     = "UPDATE teinstellungen SET cWert = :cWert WHERE kEinstellungenSektion = :section AND cName = :settingName";
            $this->getDB()->queryPrepared($stmt, [
                'cWert'       => $settingOfTypePass->cWert,
                'section'     => $settingOfTypePass->kEinstellungenSektion,
                'settingName' => $settingOfTypePass->cName]);
        }
    }
}
