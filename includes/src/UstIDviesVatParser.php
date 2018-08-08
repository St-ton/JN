<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * class UstIDviesVatParser
 */
class UstIDviesVatParser
{
    /**
     * @var array
     * pattern of the country-specitfic VAT-IDs
     * MODIFY ONLY THIS ARRAY TO COVER NEW CIRCUMSTANCES!
     *
     * original source:
     * http://ec.europa.eu/taxation_customs/vies/faq.html
     *
     * additional:
     * (http://www.die-mehrwertsteuer.de/de/aufbau-umsatzsteuer-identifikationsnummern-in-der-eu.html)
     *
     * pattern-modifiers:
     * "XX" - the first two letters are the country-code (e.g. "ES" for Spain)
     * "9"  - represents "any integer digit"
     * "X"  - any other letter is a fixed given letter and has to match
     * " "  - spaces has to match too - but can't handled by the VIES-system, so we have to left out them here
     * "_"  - wildcard for any character
     */
    private $vCountryPattern = [

        // AT-Oesterreich                ATU99999999          1 Block mit 9 Ziffern    (comment: 8 !?)
        'AT' => ['ATU99999999'],         // example: ATU48075808(ok)

        // BE-Belgien                    BE0999999999         1 Block mit 10 Ziffern
        'BE' => ['BE0999999999'],        // example: BE0428759497(ok)

        // BG-Bulgarien                  BG999999999 oder
        //                               BG9999999999         1 Block mit 9 Ziffern oder 1 Block mit 10 Ziffern
        'BG' => [
            'BG999999999',              // example: BG175074752(ok)
            'BG9999999999'
        ],

        // CY-Zypern                     CY99999999L          1 Block mit 9 Ziffern    (comment: 8 with 1 char!?)
        'CY' => ['CY99999999_'],         //example: CY10259033P(ok)

        // CZ-Tschechische Republik      CZ99999999 oder
        //                               CZ999999999 oder
        //                               CZ9999999999         1 Block mit 8, 9 oder 10 Ziffern
        'CZ' => [
            'CZ99999999',                // example: CZ25123891(ok)
            'CZ999999999',
            'CZ9999999999'               // example: CZ7103192745(ok)
        ],

        // DE-Deutschland                DE999999999          1 Block mit 9 Ziffern
        'DE' => ['DE999999999'],         // example: DE122779245(ok)

        // DK-Dänemark                   DK99 99 99 99        4 Blöcke mit 2 Ziffern
        //, 'DK' => ['DK99 99 99 99']
        // modification in place of original documentation, because the VIES can not handle spaces
        'DK' => ['DK99999999'],          // example: DK13585628(ok)

        // EE-Estland                    EE999999999          1 Block mit 9 Ziffern
        'EE' => ['EE999999999'],         // example: EE100594102(ok)

        // EL-Griechenland               EL999999999          1 Block mit 9 Ziffern
        'EL' => ['EL999999999'],         // example: EL094259216(ok)

        // ES-Spanien                    ESX9999999X          1 Block mit 9 Ziffern    (comment: 8 with 1 char!?)
        'ES' => ['ES_9999999_'],         // example: ESX2482300W(ok), ESB58378431(ok)

        // FI-Finnland                   FI99999999           1 Block mit 8 Ziffern
        'FI' => ['FI99999999'],          // example: FI20774740(ok)

        // FR-Frankreich                 FRXX 999999999       1 Block mit 2 Ziffern und 1 Block mit 9 Ziffern
        //'FR' => ['FRXX 999999999'],
        // modification in place of original documentation, because the VIES can not handle spaces
        'FR' => ['FR__999999999'],       // example: FR40303265045(ok), FRK7399859412(ok)

        // GB-Vereinigtes Königreich     GB999 9999 99 oder
        //                               GB999 9999 99 999 oder
        //                               GBGD999 oder
        //                               GBHA999              1 Block mit 3 Ziffern, 1 Block mit 4 Ziffern
        // und 1 Block mit 2 Ziffern; oder wie oben, gefolgt von einem Block mit 3 Ziffern; oder 1 Block mit 5 Ziffern
        //, 'GB' => [
        //      'GB999 9999 99'
        //    , 'GB999 9999 99 999'
        //    , 'GBGD999'
        //    , 'GBHA999'
        //]
        // modification in place of original documentation, because the VIES can not handle spaces
        'GB' => [
            'GB999999999',               // example: GB862906405(ok), 'GB 117 8490 96'(ok, spaces are removed before parsing)
            'GB999999999999',
            'GBGD999',
            'GBHA999'
        ],

        // HR-Kroatien                   HR99999999999        1 Block mit 11 Ziffern
        'HR' => ['HR99999999999'],       // example: HR33392005961(ok)

        // HU-Ungarn                     HU99999999           1 Block mit 8 Ziffern
        'HU' => ['HU99999999'],

        // IE-Irland                     IE9S99999L oder
        //                               IE9999999WI          1 Block mit 8 Ziffern oder 1 Block mit 9 Ziffern
        //, 'IE' => [
        //      'IE9S99999L'
        //    , 'IE9999999WI'
        //]
        // modification in place of original EU-documentation
        'IE' => [
            'IE9_99999_',                // example: IE6433435F(ok), IE8D79739I(ok)
            'IE9_99999__'                // example: IE3333510LH(ok)
        ],

        // IT-Italien                    IT99999999999        1 Block mit 11 Ziffern
        'IT' => ['IT99999999999'],      // example: IT00743110157(ok)

        // LT-Litauen                    LT999999999 oder
        //                               LT999999999999       1 Block mit 9 Ziffern oder 1 Block mit 12 Ziffern
        'LT' => [
            'LT999999999',               // example: LT119511515(ok)
            'LT999999999999'             // example: LT100001919017(ok)
        ],

        // LU-Luxemburg                  LU99999999           1 Block mit 8 Ziffern
        'LU' => ['LU99999999'],          // example: LU15027442(ok)

        // LV-Lettland                   LV99999999999        1 Block mit 11 Ziffern
        'LV' => ['LV99999999999'],       // example: LV40003737497(ok)

        // MT-Malta                      MT99999999           1 Block mit 8 Ziffern
        'MT' => ['MT99999999'],          // example: MT10047516(ok)

        // NL-Niederlande                NL999999999B99       1 Block mit 12 Ziffern
        'NL' => ['NL999999999B99'],      // example: NL004495445B01(ok)

        // PL-Polen                      PL9999999999         1 Block mit 10 Ziffern
        'PL' => ['PL9999999999'],        // example: PL8290001028(ok)

        // PT-Portugal                   PT999999999          1 Block mit 9 Ziffern
        'PT' => ['PT999999999'],         // example: PT501964843(ok)

        // RO-Rumänien                   RO999999999          1 Block mit mindestens 2 und höchstens 10 Ziffern
        'RO' => [
            'RO999999999',
            'RO99999999'                 // example: RO27079589(ok), RO33315358(ok)
        ],

        // SE-Schweden                   SE999999999999       1 Block mit 12 Ziffern
        'SE' => ['SE999999999901'],      // example: SE556857280301(ok), SE556789180801(ok)

        // SI-Slowenien                  SI99999999           1 Block mit 8 Ziffern
        'SI' => ['SI99999999'],          // example: SI50223054(ok)

        // SK-Slowakei                   SK9999999999         1 Block mit 10 Ziffern
        'SK' => ['SK9999999999']         // example: SK7120000019(ok), SK2021254631(ok)
    ];

    /**
     * @var string
     */
    private $szVATid;

    /**
     * @var array
     */
    private $vIdParts = [];

    /**
     * @var integer
     */
    private $nErrorCode = 0;

    /**
     * @var string
     */
    private $szErrorInfo = '';

    /**
     * @var int
     */
    private $nErrorPos = -1;

    /**
     * @param string $szVATid
     */
    public function __construct($szVATid)
    {
        $this->szVATid = $szVATid;
    }

    /**
     * parses the VAT-ID-string.
     * returns the position of a possigble check-interrupt or 0 if all was fine.
     *
     * @param string $szVATid
     * @param string $szPattern
     * @return int
     */
    private function isIdPatternValid($szVATid, $szPattern): int
    {
        $len = strlen($szVATid);
        for ($i = 0; $i < $len; $i++) {
            // each character and white-space is compared exactly, while digits can be [1..9]
            switch (true) {
                case ctype_alpha($szPattern[$i]) :
                case ctype_space($szPattern[$i]) :
                    if ($szPattern[$i] === $szVATid[$i]) {
                        continue 2; // check-space OK
                    }
                    break 2; // check-space FAIL
                case is_numeric($szPattern[$i]) :
                    if (is_numeric($szVATid[$i])) {
                        continue 2; // check-num OK
                    }
                    break 2; // check-num FAIL
                default :
                    if ('_' === $szPattern[$i]) {
                        continue 2;
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
     * controls the parsing of the VAT-ID
     * ("comparing against multiple patterns of one country")
     * returns "true" = VAT-ID is correct, "false" = not correct.
     *
     * @return bool
     */
    public function parseVatId(): bool
    {
        // guess a country - the first 2 characters should allways be the country-code
        // (store the result-array in this object, $this->vIdParts)
        $nResult = preg_match('/([A-Z]{2})(.*)/', $this->szVATid, $this->vIdParts);
        if (0 === $nResult) {
            $this->nErrorCode = 100; // error: the ID did not start with 2 big letters

            return false;
        }
        // there is no country starting with this 2 letters
        if (! isset($this->vCountryPattern[$this->vIdParts[1]])) {
            $this->nErrorCode  = 130; // error: no pattern for such a country
            $this->szErrorInfo = $this->vIdParts[1];

            return false;
        }

        // compare our VAT-ID to all pattern of the guessed country
        foreach ($this->vCountryPattern[$this->vIdParts[1]] as $szPattern) {
            // length-check (and go back, if nothing matches)
            if (strlen($this->szVATid) !== strlen($szPattern)) {
                continue; // skipt this pattern, if the length did not match. try the next one
            }
            // checking the given pattern (return a possible interrupt-position)
            $nParseResult = $this->isIdPatternValid($this->szVATid, $szPattern);
            if (0 === $nParseResult) {

                return true; // if we found a valid pattern-match, we've done our job here
            }

            $this->nErrorCode  = 120; // error: id did not match any pattern of this country
            $this->szErrorInfo = $nParseResult; // interrupt-/error-position

            return false;
        }
        $this->nErrorCode = 110; // error: no length was matching

        return false;
    }

    /**
     * return the ID splitted into the two pieces:
     * - 2 (big) letters of country code
     * - n letters or digits as the rest of the ID
     *
     * NOTE: should called after '->parseVatId()'
     *
     * @return array
     */
    public function getIdAsParams(): array
    {
        return [$this->vIdParts[1], $this->vIdParts[2]];
    }

    /**
     * returns a descriptive string of the last ocurred error
     *
     * @return string
     */
    public function getErrorCode(): int
    {
        return $this->nErrorCode;
    }

    /**
     * return additional informations of the occurred error
     *
     * @return string
     */
    public function getErrorInfo(): string
    {
        return $this->szErrorInfo;
    }

    /**
     * returns the position, in the VAT-ID-string, at which the last error was ocurred
     *
     * @return int
     */
    public function getErrorPos(): int
    {
        return $this->nErrorPos;
    }
}

