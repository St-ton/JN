<?php
/**
 * implement fallback-payment
 *
 * @author Clemens Rudolph
 * @created Thu, 03 May 2018 09:56:34 +0200
 */

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
class Migration_20180503095634 extends Migration implements IMigration
{
    protected $author            = 'Clemens Rudolph';
    protected $description       = 'implement fallback-payment';

    protected $szPaymentModuleId = 'za_null_jtl';

    public function up()
    {
        // create the new "fallback"-payment
        $this->execute('INSERT INTO `tzahlungsart`(`kZahlungsart`, `cName`, `cModulId`, `cKundengruppen`, `cBild`, `nMailSenden`, `cAnbieter`, `cTSCode`, `nWaehrendBestellung`)
            VALUES(0, "Keine Zahlung erforderlich", "' . $this->szPaymentModuleId . '", "", "", 1, "", "", 0)'
        );
        // depending on the new payment, create the additional stuff
        $oPaymentEntry = $this->fetchOne('SELECT * FROM `tzahlungsart` WHERE `cModulId` = "' . $this->szPaymentModuleId . '"');

        $this->execute('INSERT INTO `tzahlungsartsprache`(`kZahlungsart`, `cISOSprache`, `cName`, `cGebuehrname`, `cHinweisText`, `cHinweisTextShop`)
            VALUES(' . $oPaymentEntry->kZahlungsart . ', "ger", "Keine Zahlung erforderlich", "Keine Zahlung erforderlich", "Es ist keine Zahlung erforderlich. Ihr Shop-Guthaben wurde entsprechend verrechenet.",
            "Es ist keine Zahlung erforderlich. Ihr Shop-Guthaben wurde entsprechend verrechenet.")'
        );
        $this->execute('INSERT INTO `tzahlungsartsprache`(`kZahlungsart`, `cISOSprache`, `cName`, `cGebuehrname`, `cHinweisText`, `cHinweisTextShop`)
            VALUES(' . $oPaymentEntry->kZahlungsart . ', "eng", "No payment needed", "No payment needed", "There is no further payment needed. Your shop-credit was billed.",
            "There is no further payment needed. Your shop-credit was billed.")'
        );
    }

    public function down()
    {
        // collect what we got, before remove anything!
        $oPaymentEntry = $this->fetchOne('SELECT * FROM `tzahlungsart` WHERE `cModulId` = "' . $this->szPaymentModuleId . '"');

        $this->execute('DELETE FROM `tzahlungsart` WHERE `cModulID` = "' . $this->szPaymentModuleId . '"');
        $this->execute('DELETE FROM `tzahlungsartsprache` WHERE `kZahlungsart` = ' . (int)$oPaymentEntry->kZahlungsart);
    }
}

