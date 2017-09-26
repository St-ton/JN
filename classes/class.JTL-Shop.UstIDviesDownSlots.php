<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */


class UstIDviesDownSlots
{
    /**
     * array, time-slots of the VAT-databases of the members of the MIAS-system
     * MODIFY ONLY THIS ARRAY TO COVER NEW CIRCUMSTANCES!
     */
    private $vDownTimeSlots = [
        // 'country' => [
        //       ['weekday', 'start-time', 'end-time']  // one day a week
        //       [       '', 'start-time', 'end-time']  // all days a week
        //     , [...]
        // ]
        'TE' => [
              ['Fri', '09:00', '12:00']
            , ['Tue', '13:00', '16:30']
        ]

        // Unavailable almost daily around 06:00 AM for a few minutes (Oesterreich)
        , 'AT' => [
            ['', '05:59', '06:15']
        ]

        // Available 24/7 (Belgien)
        , 'BE' => [ ]

        // Unknown (Bulgarien)
        , 'BG' => [ ]

        // Available 24/7 (Zypern)
        , 'CY' => [ ]

        // Unavailable everyday around 07:00 AM for about 20 minutes (Tschechische Republik)
        , 'CZ' => [
            ['', '07:00', '07:20']
        ]

        // Available from 05:00 AM to 11:00 PM (Deutschland)
        , 'DE' => [
            ['', '23:00', '05:00']
        ]

        // Available 24/7 (Daenemark)
        , 'DK' => [   ]

        // Available 24/7 (Estland)
        , 'EE' => [   ]

        // Available 24/7 (Griechenland)
        , 'EL' => [   ]

        // Unavailable daily around 11:00 PM for a few minutes (Spanien)
        , 'ES' => [   ]

        // Unavailable every Sunday between 05:40 AM and 05:50 AM (Finnland)
        , 'FI' => [
            ['Sun', '05:40', '05:50']
        ]

        // Unavailable almost everyday between 01:30 AM and 01:40 AM (Frankreich)
        , 'FR' => [
            ['', '01:30', '01:40']
        ]

        // Unavailable every Saturday from 07:30 AM to 10:30 AM and almost daily from around 04:30 AM to 04:40 AM (Vereinigtes Königreich)
        , 'GB' => [
              ['Sat', '07:30', '10:30']
            , [   '', '04:30', '04:40']
        ]

        // Available 24/7 (Ungarn)
        , 'HU' => [   ]

        // Unavailable on Sunday nights for maximum 2 hours (Irland)
        , 'IE' => [
            ['Sun', '', '']
        ]

        // Unavailable every Monday to Saturday from 08:00 PM for 30 to 60 minutes (Italien)
        , 'IT' => [
            ['Sun', '08:00', '09:00']
        ]

        // Available 24/7 (Litauen)
        , 'LT' => [   ]

        // Available 24/7 (Luxemburg)
        , 'LU' => [   ]

        // Available 24/7 (Lettland)
        , 'LV' => [   ]

        // Unavailable every Thursday from 07:00 AM to 07:30 AM (Malta)
        , 'MT' => [
            ['Thu', '07:00', '7:30']
        ]

        // Unavailable every weekend from Saturday 09:50 PM to Sunday 09:40 PM (Niederlande)
        , 'NL' => [
              ['Sat', '09:50', '00:00']
            , ['Sun', '00:00', '09:40']
        ]

        // Available 24/7 (Polen)
        , 'PL' => [   ]

        // Unavailable every Friday from around 23:30 for about 30 minutes or more (Portugal)
        , 'PT' => [
            ['Fri', '23:30', '00:00']
        ]

        // Unavailable almost every weekend from Saturday 09:50 PM to Sunday 09:50 PM (Rumänien)
        , 'RO' => [
              ['Sat', '09:50', '00:00']
            , ['Sun', '00:00', '09:50']
        ]

        // Available 24/7 (Schweden)
        , 'SE' => [   ]

        // Available 24/7 (Slowakei)
        , 'SK' => [   ]
    ];

    /**
     * object DateTime
     * current date and time, "now" at runtime
     */
    private $oNow = null;

    /**
     * string
     */
    private $szDownInfo = '';

    const WEEKDAY = 0;
    const START   = 1;
    const ENDING  = 2;


    public function __construct()
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--

        $this->oNow = new DateTime();
    }

    public function isDown($szCountryCode)
    {
          $this->oLogger->debug('checking country down-time: '.$szCountryCode); // --DEBUG--
/*
 *        $this->oLogger->debug('AT start: '.$this->vDownTimeSlots['AT'][0][1]); // --DEBUG--
 *        $this->oLogger->debug('AT end  : '.$this->vDownTimeSlots['AT'][0][2]); // --DEBUG--
 *
 *        $date = DateTime::createFromFormat('H:i', $this->vDownTimeSlots['AT'][0][1]);
 *        $this->oLogger->debug('AT start OOP: '.print_r( $date ,true )); // --DEBUG--
 *
 *           $oNow = new DateTime(); // --DEBUG--
 *           //$oLogger->debug('now? : '.print_r($oNow  ,true )); // --DEBUG--
 *           $oLogger->debug(''.print_r( $oNow->format('l') ,true )); // --DEBUG--
 *           $oLogger->debug(''.print_r( $oNow->format('D') ,true )); // --DEBUG--
 *           $oLogger->debug(''.print_r( $oNow->format('w') ,true )); // --DEBUG--
 *           //$oLogger->debug(''.print_r( new DateInterval('P2D') ,true )); // --DEBUG--
 */

        $date = DateTime::createFromFormat('H:i', $this->vDownTimeSlots['AT'][self::WEEKDAY][self::START]);
        //$this->oLogger->debug('AT start OOP: '.print_r( $date ,true )); // --DEBUG--

        foreach ($this->vDownTimeSlots[$szCountryCode] as $vCountryDownTimes) {
            $this->oLogger->debug('+++++++ checking time-slot '.print_r($vCountryDownTimes,true)); // --DEBUG--

            // if no weekday was given (which means "every weekday"), we replace the weekday in the check-array with the current weekday here
            if ('' === $vCountryDownTimes[self::WEEKDAY]) {
                $vCountryDownTimes[self::WEEKDAY] = $this->oNow->format('D');
            }

            $oStartTime = DateTime::createFromFormat('D:H:i', $vCountryDownTimes[self::WEEKDAY] . ':' . $vCountryDownTimes[self::START]);
            $oEndTime   = DateTime::createFromFormat('D:H:i', $vCountryDownTimes[self::WEEKDAY] . ':' . $vCountryDownTimes[self::ENDING]);

            if ($oStartTime <= $this->oNow && $this->oNow <= $oEndTime) {
                //$this->oLogger->debug('- - - - - - - - - - - - - - - - - - - - !!! IN DOWNTIME !!!'); // --DEBUG--
                //$this->oLogger->debug('start  : '.print_r($oStartTime,true)); // --DEBUG--
                //$this->oLogger->debug('NOW    : '.print_r($this->oNow,true)); // --DEBUG--
                //$this->oLogger->debug('ending : '.print_r($oEndTime,true)); // --DEBUG--
                //$this->oLogger->debug('- - - - - - - - - - - - - - - - - - - - '); // --DEBUG--

                // inform the user and/or log this event
                // --TODO--
                $this->oLogger->debug('service is down till '.$oEndTime->format('l y-m-d, H:i')); // --DEBUG--

                // if we see ANY VALID DOWNTIME, we go back with TRUE (what means "DOWN NOW")
                return true;
            }
        }

        return false;
    }

}
