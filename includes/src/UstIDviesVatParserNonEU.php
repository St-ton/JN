<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
class UstIDviesVatParserNonEU
{
    /**
     * @var $vCountryPattern
     */
    private $vCountryPattern = [
        // CH-Schweiz                    CHE-999.999.999 oder
        //                               CHE-999999999         1 Block mit 9 Ziffern,
        //                               CHE-999.999.999-99    with or without "Handelregister"-appendix ("-43")
        'CHE' => [
            'CHE-999_999_999',              // example: CHE-422.597.330 (valid)
            'CHE-999_999_999-9',
            'CHE-999_999_999-99',
            'CHE-999999999',
            'CHE-999999999-9',
            'CHE-999999999-99'
        ],
    ];

    /**
     * @var string
     */
    public $szVATid = '';

    /**
     * @var string
     */
    private $szErrorInfo = '';

    /**
     * @var integer
     */
    private $nErrorCode = 0;

    /**
     *
     */
    public function __construct(string $szVATid)
    {
        $this->szVATid = $szVATid;
    }

    /**
     * parses the VAT-ID-string.
     * returns the position of a possible check-interrupt or 0 if all was fine.
     *
     * @param string $szVATid
     * @param string $szPattern
     * @return int
     */
    private function isIdPatternValid($szVATid, $szPattern)
    {
        $len = strlen($szVATid);
        for ($i = 0; $i < $len; $i++) {
            // each character and white-space is compared exactly, while digits can be [1..9]
            switch (true) {
                case ctype_alpha($szPattern[$i]):
                case ctype_space($szPattern[$i]):
                    if ($szPattern[$i] === $szVATid[$i]) {
                        continue 2; // check-space OK
                    }

                    break 2; // check-space FAIL
                case is_numeric($szPattern[$i]):
                    if (is_numeric($szVATid[$i])) {
                        continue 2; // check-num OK
                    }

                    break 2; // check-num FAIL
                default:
                    if ('_' === $szPattern[$i] || '-' === $szPattern[$i]) {
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
     * @return bool
     */
    public function parseVatId()
    {
        // check, if there is a country, which matches the starting chars of the given ID
        $nLimit = 4; // first three(!) for now
        $bHit   = false;
        for ($i = 1; $nLimit > $i && !$bHit; $i++) {
            $szStartPattern = substr($this->szVATid, 0, $i);
            isset($this->vCountryPattern[$szStartPattern]) ? $bHit = true : $bHit = false;
        }

        // compare our VAT-ID to all pattern of the guessed country
        foreach ($this->vCountryPattern[$szStartPattern] as $szPattern) {
            // length-check (and go back, if nothing matches)
            if (strlen($this->szVATid) !== strlen($szPattern)) {
                continue; // skipt this pattern, if the length did not match. try the next one
            }
            // checking the given pattern (return a possible interrupt-position)
            $nParseResult = $this->isIdPatternValid($this->szVATid, $szPattern);
            if (0 === $nParseResult) {
                return true; // if we found a valid pattern-match, we've done our job here
            }
            $this->nErrorCode  = UstIDviesInterface::PATTERN_NOT_MATCH; // error 120: id did not match any pattern of this country
            $this->szErrorInfo = $nParseResult; // interrupt-/error-position

            return false;
        }
        $this->nErrorCode = UstIDviesInterface::PATTERNLENGTH_NOT_FOUND; // error 110: no length was matching

        return false;
    }

    /**
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->szErrorInfo;
    }

    /**
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->nErrorCode;
    }
}
