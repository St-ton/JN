<?php
/**
 * Change delivery workdays to days
 *
 * @author Falk Prüfer
 * @created Fri, 16 Dec 2016 11:02:37 +0100
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
class Migration_20161216110237 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Change delivery workdays to days';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# Tage');
        $this->setLocalization('eng', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# days');
        $this->setLocalization('ger', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# Tage');
        $this->setLocalization('eng', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# days');

        $this->setConfig('addDeliveryDayOnSaturday', '0', 7, 'Wochenende bei Lieferzeitanzeige ber&uuml;cksichtigen', 'selectbox', 435, (object)[
            'cBeschreibung' => 'Soll f&uuml;r die Anzeige der Lieferzeit das Wochenende bei einer Bestellung am Freitag/Samstag ber&uuml;cksichtigt werden?',
            'inputOptions'  => [
                '0' => 'nicht ber&uuml;cksichtigen',
                '1' => 'Sonntag ber&uuml;cksichtigen',
                '2' => 'Samstag und Sonntag ber&uuml;cksichtigen',
            ],
        ]);
    }

    public function down()
    {
        $this->removeConfig('addDeliveryDayOnSaturday');

        $this->setLocalization('ger', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# Werktage');
        $this->setLocalization('eng', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# workdays');
        $this->setLocalization('ger', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# Werktage');
        $this->setLocalization('eng', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# workdays');
    }
}
