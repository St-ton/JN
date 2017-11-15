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
     * "X"  - any other letter is a fixed given letter and has to match
     * " "  - spaces has to match too
     * "_"  - wildcard for any character
     */
    private $vCountryPattern = [

        // ---------------- TEST ------------------
        'TE' => [
              'TELL99999999XL99B'
            , 'TEL_9999 99 9XLB'
        ] // --DEBUG--
        // ---------------- TEST ------------------

        // AT-Oesterreich                ATU99999999          1 Block mit 9 Ziffern    (comment: 8 !?)
        , 'AT' => ['AT_99999999']

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

    private $vIdParts = [];

    private $nErrorCode = 0;

    private $szErrorInfo = '';


    public $oLogger = null; // --DEBUG--

    public function __construct($szVATid)
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--

        $this->szVATid = $szVATid;
    }


    /**
     * parses the VAT-ID
     *
     * @param string  VAT-ID
     * @param string  country-specific VAT-ID-pattern
     * @return int  the position of a possigble check-interrupt or 0 if all was fine
     */
    private function isIdPatternValid($szVATid, $szPattern)
    {
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
                    break 2;
                case ctype_space($szPattern[$i]) :
                    if ($szPattern[$i] === $szVATid[$i]) {
                        $this->oLogger->debug('check space ok  : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                        continue;
                    }
                    $this->oLogger->debug('check space f   : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                    break 2;
                //case ctype_digit($szVATid[$i]) :
                case is_numeric($szPattern[$i]) :
                    $this->oLogger->debug('check num       : '.$szPattern[$i].' => '.$szVATid[$i]); // --DEBUG--
                    if (is_numeric($szVATid[$i])) {
                        continue;
                    }
                    break 2;
                default :
                    $this->oLogger->debug('default hit!     ('.$szPattern[$i].' => '.$szVATid[$i].')'); // --DEBUG--
                    if ('_' === $szPattern[$i]) {
                        continue;
                    }
                    break 2;
            }
        }

        // check, if we iterate the whole given VAT-ID,
        // and if not, return the position, at which we sopped
        if (strlen($szVATid) !== $i) {
            $this->nErrorPos = $i; // store the error-position for later usage too
            return $i;
        }
        return 0;
    }


    /**
     * regulate the parsing of the VAT-ID
     * ("comparing against multiple patterns of one country")
     *
     * @param void
     * @return boolean  "true" = VAT-ID is correct, "false" = not correct
     */
    public function parseVatId()
    {
        // guess a country (the first 2 characters should allways be the country-code)
        // store the result in this object instance
        $nResult = preg_match('/([A-Z]{2})(.*)/', $this->szVATid, $this->vIdParts);
        if (0 === $nResult) {
            //$this->nErrorCode = $this->vParseErrors[100]; // error: the id did not start with 2 big letters
            $this->nErrorCode = 100; // error: the id did not start with 2 big letters
            return false;
        }
        $this->oLogger->debug('suggested country: '.print_r( $this->vIdParts[1] ,true )); // --DEBUG--

        // there is no country starting with this 2 letters
        if (! isset($this->vCountryPattern[$this->vIdParts[1]])) {
            //$this->nErrorCode = $this->vParseErrors[130].$this->vIdParts[1]; // error: no pattern for such a country
            $this->nErrorCode  = 130; // error: no pattern for such a country
            $this->szErrorInfo = $this->vIdParts[1];
            return false;
        }

        // compare our VAT-ID to all pattern of the guessed country
        foreach ($this->vCountryPattern[$this->vIdParts[1]] as $szPattern) {

            // length-check (and go back, if nothing matches)
            if (strlen($this->szVATid) !== strlen($szPattern)) {
                $this->oLogger->debug('skip pattern '.$szPattern); // --DEBUG--
                continue; // skipt this pattern, if the length did not match. try the next one
            } else {
                // checking the given pattern (return a possible interrupt-position)
                $nParseResult = $this->isIdPatternValid($this->szVATid, $szPattern);
                $this->oLogger->debug('interrupt-position: '.print_r( $nParseResult ,true )); // --DEBUG--
                if (0 === $nParseResult) {
                    // if we found a valid pattern-match, we've done our job here
                    return true;
                } else {

                    // --TODO-- build a nice string with the position of interrupt

                    //$this->nErrorCode = $this->vParseErrors[120]; // error: id did not match any pattern of this country
                    $this->nErrorCode  = 120; // error: id did not match any pattern of this country
                    $this->szErrorInfo = $nParseResult; // interrupt-/error-position
                    return false;
                }

            }
        }
        $this->oLogger->debug('no length was matching!'); // --DEBUG--
        //throw new ExceptionVies('wrong length!');
        //$this->nErrorCode = $this->vParseErrors[110]; // error: no length was matching
        $this->nErrorCode = 110; // error: no length was matching
        return false;
    }


    /**
     * return the ID splitted into the two pieces:
     * - 2 characters country code
     * - n characters or digits as the rest of the ID
     *
     * @param void
     * @return array  the pieces of the VAT-ID as described above
     */
    public function getIdAsParams()
    {
        return [ $this->vIdParts[1], $this->vIdParts[2] ];
    }


    /**
     * returns a descriptive string of the last ocurred error
     *
     * @param void
     * @return string  error-description
     */
    public function getErrorCode()
    {
        return $this->nErrorCode;
    }


    public function getErrorInfo()
    {
        return $this->szErrorInfo;
    }

    /**
     * returns the position, in the VAT-ID-string, at which the last error was ocurred
     *
     * @param void
     * @return string  error-position in the VAT-ID-string
     */
    public function getErrorPos()
    {
        return $this->nErrorPos;
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
