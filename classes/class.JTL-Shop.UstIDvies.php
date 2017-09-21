<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * ToDos:
 *
 * - prüfen auf
 *     - Anzahl Zeichen
 *     - korrekte Länge
 *     - korrektes Länderkürzel
 * - Verfügbarkeitszeiten der Steuerverwaltungen in den Ländern berücksichtigen
 *   (ggf. entsprechende Hinweise ausgeben)
 *
 */

/**
 * Class UstIDvies
 *
 * External documentation
 *
 * @link http://ec.europa.eu/taxation_customs/vies/faq.html
 *
 * European Commission
 * VIES (VAT Information Exchange System)
 * @link https://ec.europa.eu/taxation_customs/business/vat/eu-vat-rules-topic/vies-vat-information-exchange-system-enquiries_en
 */
class UstIDvies
{
    /**
     * string zero-terminated, URL of the MIAS
     */
    private $szViesWSDL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * string zero-terminated
     */
    private $szVATid;

    /**
     * array, answers of the MIAS-system
     * --TO-CHECK-- may it's not needed here this way
     */
    private $vAnswerStrings = [
           0 => "MwSt-Nummer gültig."
        , 10 => "MwSt-Nummer ungültig." // (D.h. die eingegebene Nummer ist zumindest an dem angegebenen Tag ungültig)
        , 20 => "Bearbeitung derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später." // (D.h. es gibt ein Problem mit dem Netz oder mit der Web-Anwendung)
        , 30 => "Bearbeitung im Mitgliedstaat derzeit nicht möglich. Bitte wiederholen Sie Ihre Anfrage später." // (D.h. die Anwendung ist in dem Mitgliedstaat, der die von Ihnen eingegebene MwSt-Nummer erteilt hat, derzeit nicht möglich)
        , 40 => "Unvollständige oder fehlerhafte Dateneingabe" // (MwSt-Nummer + Mitgliedstaat)
        , 50 => "Zeitüberschreitung. Bitte wiederholen Sie Ihre Anfrage später."
    ];


    /**
     * array, time-slots of the VAT-databases of the members of the MIAS-system
     */
    private $vTimeSlots = [
        'AT' => '' // Unavailable almost daily around 06:00 AM for a few minutes (Oesterreich)
        'BE' => '' // Available 24/7 (Belgien)
        'BG' => '' // Unknown (Bulgarien)
        'CY' => '' // Available 24/7 (Zypern)
        'CZ' => '' // Unavailable everyday around 07:00 AM for about 20 minutes (Tschechische Republik)
        'DE' => '' // Available from 05:00 AM to 11:00 PM (Deutschland)
        'DK' => '' // Available 24/7 (Daenemark)
        'EE' => '' // Available 24/7 (Estland)
        'EL' => '' // Available 24/7 (Griechenland)
        'ES' => '' // Unavailable daily around 11:00 PM for a few minutes (Spanien)
        'FI' => '' // Unavailable every Sunday between 05:40 AM and 05:50 AM (Finnland)
        'FR' => '' // Unavailable almost everyday between 01:30 AM and 01:40 AM (Frankreich)
        'GB' => '' // Unavailable every Saturday from 07:30 AM to 10:30 AM and almost daily from around 04:30 AM to 04:40 AM (Vereinigtes Königreich)
        'HU' => '' // Available 24/7 (Ungarn)
        'IE' => '' // Unavailable on Sunday nights for maximum 2 hours (Irland)
        'IT' => '' // Unavailable every Monday to Saturday from 08:00 PM for 30 to 60 minutes (Italien)
        'LT' => '' // Available 24/7 (Litauen)
        'LU' => '' // Available 24/7 (Luxemburg)
        'LV' => '' // Available 24/7 (Lettland)
        'MT' => '' // Unavailable every Thursday from 07:00 AM to 07:30 AM (Malta)
        'NL' => '' // Unavailable every weekend from Saturday 09:50 PM to Sunday 09:40 PM (Niederlande)
        'PL' => '' // Available 24/7 (Polen)
        'PT' => '' // Unavailable every Friday from around 23:30 for about 30 minutes or more (Portugal)
        'RO' => '' // Unavailable almost every weekend from Saturday 09:50 PM to Sunday 09:50 PM (Rumänien)
        'SE' => '' // Available 24/7 (Schweden)
        'SK' => '' // Available 24/7 (Slowakei)
    ];

    private $oLogger = null; // --DEBUG--


    public function __construct($szUstID = '')
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--


        $this->szVATid = $szUstID;
    }


    /**
     * parse and form the given UstID
     * and build a array, which can used to ask the remote APIs
     *
     * @param $szUstID
     * @return int
     */
    private function parseVATtoParams($szUstID)
    {
        //$vParams = ['countryCode' => 'DE', 'vatNumber' => '257864472']; // works (JTL Ust-ID)
        $vUstID = ''; // --TODO--
        return $vUstID;
    }


    /**
     * ask the remote APIs of the VIES-online-system
     *
     * @param $szUstID
     * @return bool
     */
    public function doCheckID($szUstID = '')
    {
        $szMWstID = '';
        if ('' === $szUstID && '' === $this->szVATid) {
            // ERROR: "no szUstID was given" --TODO--
        } else {
            $szMWstID = ('' === $szUstID)
                ? $this->szVATid
                : $szUstID
            ;
        }

        $this->oLogger->debug('internal MwStID: '.$szMWstID); // --DEBUG--

        // ask the remote service
        /*
         *$oSoapClient = new SoapClient($this->szViesWSDL);
         *$result = $oSoapClient->checkVat($this->parseVATtoParams($szMWstID)); // --TODO--
         */

        // return
    }

}
