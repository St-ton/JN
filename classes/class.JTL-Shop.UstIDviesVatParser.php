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
     * "XX" - the first two chars are the country-code (e.g. "ES" for Spain)
     * "9"  - represents "any integer digit"
     * "X"  - any other char is a fixed given char and has to match
     * " "  - spaces has to match too
     */
    private $vCountryPattern = [
        'TE' => [
              'TELL99999999XL99B'
            , 'TELL9999 99 9XLB'
        ] // --DEBUG--

        // AT-Oesterreich                ATU99999999          1 Block mit 9 Ziffern
        , 'AT' => ['ATU99999999']

        // BE-Belgien                    BE0999999999         1 Block mit 10 Ziffern
        , 'BE' => ['BE0999999999']

        // BG-Bulgarien                  BG999999999 oder
        //                               BG9999999999         1 Block mit 9 Ziffern oder 1 Block mit 10 Ziffern
        , 'BG' => [
             'BG999999999'
            ,'BG9999999999'
        ]

        // CY-Zypern                     CY99999999L          1 Block mit 9 Ziffern
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

        // ES-Spanien                    ESX9999999X4         1 Block mit 9 Ziffern
        , 'ES' => ['ESX9999999X4']

        // FI-Finnland                   FI99999999           1 Block mit 8 Ziffern
        , 'FI' => ['FI99999999']

        // FR-Frankreich                 FRXX 999999999       1 Block mit 2 Ziffern und 1 Block mit 9 Ziffern
        , 'FR' => ['FRXX 999999999']

        // GB-Vereinigtes Königreich     GB999 9999 99 oder
        //                               GB999 9999 99 9995 oder
        //                               GBGD9996 oder
        //                               GBHA9997             1 Block mit 3 Ziffern, 1 Block mit 4 Ziffern und 1 Block mit 2 Ziffern; oder wie oben, gefolgt von einem Block mit 3 Ziffern; oder 1 Block mit 5 Ziffern
        , 'GB' => [
              'GB999 9999 99'
            , 'GB999 9999 99 9995'
            , 'GBGD9996'
            , 'GBHA9997'
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

        // NL-Niederlande                NL999999999B998      1 Block mit 12 Ziffern
        , 'NL' => ['NL999999999B998']

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

        $this->oLogger->debug('PATTERN: '.$szPattern); // --DEBUG--
        $this->oLogger->debug('VAT-ID : '.$szVATid); // --DEBUG--


        for($i=0; $i < strlen($szVATid); $i++) {
            //$this->oLogger->debug('char '.$i.'. '.$szVATid[$i] . ' cmp: '.strcmp($szVATid[$i], $szPattern[$i])); // --DEBUG--

            $nCmpVal = strcmp($szVATid[$i], $szPattern[$i]);
            if (0 < $nCmpVal || -10 > $nCmpVal) {
                $this->oLogger->debug('invalid'); // --DEBUG--
                return false;
            }

            /*
             *switch (true) {
             *    case (preg_match('/[A-Z]/', $szVATid[$i])):
             *        $this->oLogger->debug('A-Z'); // --DEBUG--
             *        break;
             *    case (preg_match('/\d/', $szVATid[$i])):
             *        $this->oLogger->debug('digit'); // --DEBUG--
             *        break;
             *    default:
             *        $this->oLogger->debug('other'); // --DEBUG--
             *        break;
             *}
             */
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
        $nResult = preg_match('/([A-Z]{2})(.*)/', $szVATid, $vGuessedCode);
        if (0 === $nResult) {
            return 110; // error: the number did not start with 2 big letters
        }


        $this->oLogger->debug('suggested country: '.print_r( $vGuessedCode[1] ,true )); // --DEBUG--

        foreach ($this->vCountryPattern[$vGuessedCode[1]] as $szPattern) {
            $this->oLogger->debug('szPattern: '.print_r( $szPattern ,true )); // --DEBUG--

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
                    return 130;
                }

            }
        }
        $this->oLogger->debug('no length was matching!'); // --DEBUG--
        return 120; // error: no length was matching

    }

}
