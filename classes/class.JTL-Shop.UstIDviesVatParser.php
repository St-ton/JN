<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class UstIDviesVatParser
{
    /**
     * pattern of the country-specitfic VAT-IDs
     * MODIFY ONLY THIS ARRAY TO COVER NEW CIRCUMSTANCES!
     *
     * original source:
     * http://ec.europa.eu/taxation_customs/vies/faq.html
     *
     * "XX" - the first two chars are the country-code (e.g. "ES" for Spain)
     * "9"  - represents "any integer digit"
     * "X"  - any other char is a fixed given char and has to match
     * " "  - spaces has to match too
     */
    private $vCountryPattern = [

        // ---------------- TEST ------------------
        'TE' => [
              'TELL99999999XL99B'
            , 'TELL9999 99 9XLB'
        ] // --DEBUG--
        // ---------------- TEST ------------------

        // AT-Oesterreich                ATU99999999          1 Block mit 9 Ziffern    (comment: 8 !?)
        , 'AT' => ['ATU99999999']

        // BE-Belgien                    BE0999999999         1 Block mit 10 Ziffern
        , 'BE' => ['BE0999999999']

        // BG-Bulgarien                  BG999999999 oder
        //                               BG9999999999         1 Block mit 9 Ziffern oder 1 Block mit 10 Ziffern
        , 'BG' => [
             'BG999999999'
            ,'BG9999999999'
        ]

        // CY-Zypern                     CY99999999L          1 Block mit 9 Ziffern    (comment: 8 with 1 char!?)
        , 'CY' => ['CY99999999L']

        // CZ-Tschechische Republik      CZ99999999 oder
        //                               CZ999999999 oder
        //                               CZ9999999999         1 Block mit 8, 9 oder 10 Ziffern
        , 'CZ' => [
              'CZ99999999'
            , 'CZ999999999'
            , 'CZ9999999999'
        ]

        // DE-Deutschland                DE999999999          1 Block mit 9 Ziffern
        , 'DE' => ['DE999999999']

        // DK-Dänemark                   DK99 99 99 99        4 Blöcke mit 2 Ziffern
        , 'DK' => ['DK99 99 99 99']

        // EE-Estland                    EE999999999          1 Block mit 9 Ziffern
        , 'EE' => ['EE999999999']

        // EL-Griechenland               EL999999999          1 Block mit 9 Ziffern
        , 'EL' => ['EL999999999']

        // ES-Spanien                    ESX9999999X          1 Block mit 9 Ziffern    (comment: 8 with 1 char!?)
        , 'ES' => ['ESX9999999X']

        // FI-Finnland                   FI99999999           1 Block mit 8 Ziffern
        , 'FI' => ['FI99999999']

        // FR-Frankreich                 FRXX 999999999       1 Block mit 2 Ziffern und 1 Block mit 9 Ziffern
        , 'FR' => ['FRXX 999999999']

        // GB-Vereinigtes Königreich     GB999 9999 99 oder
        //                               GB999 9999 99 999 oder
        //                               GBGD999 oder
        //                               GBHA999              1 Block mit 3 Ziffern, 1 Block mit 4 Ziffern und 1 Block mit 2 Ziffern; oder wie oben, gefolgt von einem Block mit 3 Ziffern; oder 1 Block mit 5 Ziffern
        , 'GB' => [
              'GB999 9999 99'
            , 'GB999 9999 99 999'
            , 'GBGD999'
            , 'GBHA999'
        ]

        // HR-Kroatien                   HR99999999999        1 Block mit 11 Ziffern
        , 'HR' => ['HR99999999999']

        // HU-Ungarn                     HU99999999           1 Block mit 8 Ziffern
        , 'HU' => ['HU99999999']

        // IE-Irland                     IE9S99999L oder
        //                               IE9999999WI          1 Block mit 8 Ziffern oder 1 Block mit 9 Ziffern
        , 'IE' => [
              'IE9S99999L'
            , 'IE9999999WI'
        ]

        // IT-Italien                    IT99999999999        1 Block mit 11 Ziffern
        , 'IT' => ['IT99999999999']

        // LT-Litauen                    LT999999999 oder
        //                               LT999999999999       1 Block mit 9 Ziffern oder 1 Block mit 12 Ziffern
        , 'LT' => [
              'LT999999999'
            , 'LT999999999999'
        ]

        // LU-Luxemburg                  LU99999999           1 Block mit 8 Ziffern
        , 'LU' => ['LU99999999']

        // LV-Lettland                   LV99999999999        1 Block mit 11 Ziffern
        , 'LV' => ['LV99999999999']

        // MT-Malta                      MT99999999           1 Block mit 8 Ziffern
        , 'MT' => ['MT99999999']

        // NL-Niederlande                NL999999999B99       1 Block mit 12 Ziffern
        , 'NL' => ['NL999999999B99']

        // PL-Polen                      PL9999999999         1 Block mit 10 Ziffern
        , 'PL' => ['PL9999999999']

        // PT-Portugal                   PT999999999          1 Block mit 9 Ziffern
        , 'PT' => ['PT999999999']

        // RO-Rumänien                   RO999999999          1 Block mit mindestens 2 und höchstens 10 Ziffern
        , 'RO' => ['RO999999999']

        // SE-Schweden                   SE999999999999       1 Block mit 12 Ziffern
        , 'SE' => ['SE999999999999']

        // SI-Slowenien                  SI99999999           1 Block mit 8 Ziffern
        , 'SI' => ['SI99999999']

        // SK-Slowakei                   SK9999999999         1 Block mit 10 Ziffern
        , 'SK' => ['SK9999999999']
    ];

    private $szVATid = '';


    public $oLogger = null; // --DEBUG--

    public function __construct()
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
    }


    private function isIdPatternValid($szVATid, $szPattern)
    {
        //preg_match_all('/([A-z]+)(\d+)(.*)/', $szVATid, $vMatches);
        //$this->oLogger->debug('+++++ VAT-parts: '.print_r($vMatches ,true )); // --DEBUG--

        $this->oLogger->debug('VAT-ID : '.$szVATid); // --DEBUG--
        $this->oLogger->debug('PATTERN: '.$szPattern); // --DEBUG--

        for($i=0; $i < strlen($szVATid); $i++) {

            // compare each id-character to the appropriated pattern-character
            /*
            $nCmpVal = strcmp($szVATid[$i], $szPattern[$i]);
            if (0 < $nCmpVal || -10 > $nCmpVal) {
                $this->oLogger->debug('invalid'); // --DEBUG--
                return false;
            }
             */

            // each character and white-space is compared exactly, while digits can be [1..9]
            switch (true) {
                case ctype_alpha($szPattern[$i]) :
                    if ($szPattern[$i] === $szVATid[$i]) {
                        $this->oLogger->debug('check ok        : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                        continue;
                    }
                    $this->oLogger->debug('check failed    : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                    return false;
                case ctype_space($szPattern[$i]) :
                    if ($szPattern[$i] === $szVATid[$i]) {
                        $this->oLogger->debug('check space ok  : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                        continue;
                    }
                    $this->oLogger->debug('check space f   : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                    return false;
                //case ctype_digit($szVATid[$i]) :
                case is_numeric($szPattern[$i]) :
                    $this->oLogger->debug('check num       : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                    continue;
                default :
                    $this->oLogger->debug('default hit!'); // --DEBUG--
                    return false;
            }
        }

        /*
         *return sscanf($szVATid, '%2s%9d'); // --DEBUG--
         *return false;
         */
         return true;
    }



    public function getIdAsParams($szVATid)
    {
        $this->szVATid = $szVATid; // optional

        // guess a country
        // the first 2 characters are allways the country
        $nResult = preg_match('/([A-Z]{2})(.*)/', $szVATid, $vGuessedCode);
        if (0 === $nResult) {
            return 110; // error: the id did not start with 2 big letters
        }

        $this->oLogger->debug('suggested country: '.print_r( $vGuessedCode[1] ,true )); // --DEBUG--

        foreach ($this->vCountryPattern[$vGuessedCode[1]] as $szPattern) {

            // length-check (and go back, if nothing matches)
            if (strlen($szVATid) !== strlen($szPattern)) {
                $this->oLogger->debug('skip pattern '.$szPattern); // --DEBUG--
                continue; // skipt this pattern, if the length did not match
            } else {
                // checking the given pattern
                if ($vIdAsParams = $this->isIdPatternValid($szVATid, $szPattern)) {
                    // if we found a valid pattern-match, we've done our job here
                    //return $vIdAsParams;
                    return [$vGuessedCode[1], $vGuessedCode[2]];
                } else {
                    return 130; // error: id matches not any pattern of this country
                }

            }
        }
        $this->oLogger->debug('no length was matching!'); // --DEBUG--
        //throw new ExceptionVies('wrong length!');
        return 120; // error: no length was matching
    }

}


/*
 *class ExceptionVies extends Exception {
 *
 *    protected $message = '';
 *
 *    public function __construct($message, $code = 0, Exception $previous = null)
 *    {
 *        parent::__construct($message, $code, $previous);
 *    }
 *
 *}
 */
