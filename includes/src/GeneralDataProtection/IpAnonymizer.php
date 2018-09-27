<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * class IpAnonymizer
 *
 * v4
 * anonymize()       : 255.255.255.34 -> 255.255.255.0
 * anonymizeLegacy() : 255.255.255.34 -> 255.255.255.*
 *
 * v6
 * anonymize()       : 2001:0db8:85a3:08d3:1319:8a2e:0370:7347 -> 2001:db8:85a3:8d3:0:0:0:0   (also cuts leading zeros!)
 * anonymizeLegacy() : 2001:0db8:85a3:08d3:1319:8a2e:0370:7347 -> 2001:0db8:85a3:08d3:*:*:*:*
 *
 */
class IpAnonymizer
{
    /**
     * IP-string, human readable
     *
     * @var string
     */
    private $szIP;

    /**
     * binary "packed" IP
     *
     * @var binary
     */
    private $bRawIp = null;

    /**
     * current IP-anonymization-mask
     *
     * @var string
     */
    private $szIpMask;

    /**
     * @var string
     */
    private $szIpMaskv4;

    /**
     * @var string
     */
    private $szIpMaskv6;

    /**
     * current placholder (if the given IP was invalid)
     *
     * @var string
     */
    private $szPlaceholderIP;

    /**
     * flag for old fashioned anonymization ("do not anonymize again")
     *
     * @var bool
     */
    private $bOldFashionedAnon = false;

    /**
     * flag to get "0:0:0:0:0:0:0:0" instead of "::" ("::" is a valid IPv6-notation too!)
     *
     * @var bool
     */
    private $bBeautifyFlag = false;

    /**
     * @var object Monolog\Logger
     */
    private $oLogger;


    /**
     * @param bool
     * @param string
     */
    public function __construct(string $szIP = '', bool $bBeautify = false)
    {
        try {
            $this->oLogger = \Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->oLogger = null;
        }
        $this->szIpMaskv4 = \Shop::getSettings([CONF_GLOBAL])['global']['anonymize_ip_mask_v4'];
        $this->szIpMaskv6 = \Shop::getSettings([CONF_GLOBAL])['global']['anonymize_ip_mask_v6'];

        if ($szIP !== '') {
            $this->szIP = $szIP;
            try {
                $this->init();
            } catch(\Exception $e) {
                // The current PHP-version did not support IPv6 addresses!
                ($this->oLogger !== null) ?: $this->oLogger->log(JTLLOG_LEVEL_NOTICE, $e->getMessage());
                return;
            }
        }
        if ($bBeautify !== false) {
            $this->bBeautifyFlag = true;
        }
    }

    /**
     * analyze the given IP and set the object-values
     *
     * @throws \RuntimeException
     */
    private function init()
    {
        if ($this->szIP === '' || strpos($this->szIP, '*') !== false) {
            // if there is an old fashioned anonymization or
            // an empty string, we do nothing (but set a flag)
            $this->bOldFashionedAnon = true;
            return;
        }
        // any ':' means, we got an IPv6-address
        // ("::127.0.0.1" or "::ffff:127.0.0.3" is valid too!)
        if (strpos($this->szIP, ':') !== false) {
            $this->bRawIp = inet_pton($this->szIP);
        } else {
            $this->bRawIp = inet_pton($this->rmLeadingZero($this->szIP));
        }
        switch (\strlen($this->bRawIp)) {
            case 4:
                $this->szPlaceholderIP = '0.0.0.0';
                $this->szIpMask        = $this->getMaskV4();
                break;
            case 16:
                if (\defined('AF_INET6')) {
                    $this->szPlaceholderIP = '0000:0000:0000:0000:0000:0000:0000:0000';
                    $this->szIpMask        = $this->getMaskV6();
                } else {
                    // this should normally never happen! (wrong compile-time setting of PHP)
                    throw new \RuntimeException('PHP wurde mit der Option "--disable-ipv6" compiliert!');
                }
                break;
            default:
        }
    }

    /**
     * delivers am valid IP-string,
     * (by conventions, with "0 summerized", for IPv6 addresses
     * use the "beautify-flag", during object construction, to get "0")
     *
     * @return string
     */
    public function anonymize(): string
    {
        if ($this->bOldFashionedAnon !== false) {
            return $this->szIP;
        }
        $szReadableIp = inet_ntop(inet_pton($this->szIpMask) & $this->bRawIp);

        if ($this->bBeautifyFlag === true && strpos($szReadableIp, '::') !== false) {
            $iColonPos = strpos($szReadableIp, '::');
            $iStrEnd   = \strlen($szReadableIp) - 2;

            $iBlockCount = \count(
                preg_split('/:/', str_replace('::', ':', $szReadableIp), -1, PREG_SPLIT_NO_EMPTY)
            );
            $szReplacement = '';
            $iDiff = 8 - $iBlockCount;
            for ($i = 0; $i < $iDiff; $i++) {
                ($szReplacement === '') ? $szReplacement .= '0' : $szReplacement .= ':0';
            }
            if (($iColonPos | $iStrEnd) === 0) { // for pure "::"
                $szReadableIp = $szReplacement;
            } elseif ($iColonPos === 0) {
                $szReadableIp = str_replace('::', $szReplacement.':', $szReadableIp);
            } elseif ($iColonPos === $iStrEnd) {
                $szReadableIp = str_replace('::', ':'.$szReplacement, $szReadableIp);
            } else {
                $szReadableIp = str_replace('::', ':'.$szReplacement.':', $szReadableIp);
            }
        }

        return $szReadableIp;
    }

    /**
     * delivers an IP the legacy way:
     * not optimized (zeros summerized) and with atseriscs as obvuscation
     *
     * @return string
     */
    public function anonymizeLegacy(): string
    {
        $vMaskParts = preg_split('/[\.:]/', $this->szIpMask);
        $vIpParts   = preg_split('/[\.:]/', $this->szIP);
        $nLen = \count($vIpParts);
        (4 === $nLen) ? $szGlue = '.' : $szGlue = ':';
        for ($i = 0; $i < $nLen; $i++) {
            (hexdec($vMaskParts[$i]) !== 0) ?: $vIpParts{$i} = '*';
        }
        return implode($szGlue, $vIpParts);
    }

    /**
     * @return string
     */
    public function getMaskV4(): string
    {
        return $this->szIpMaskv4;
    }

    /**
     * @return string
     */
    public function getMaskV6(): string
    {
        return $this->szIpMaskv6;
    }

    /**
     * @param string
     * @return self
     * @throws \Exception
     */
    public function setIp(string $szIP = ''): self
    {
        if ($szIP !== '') {
            $this->szIP = $szIP;
            $this->init();
        }
        return $this;
    }

    /**
     * remove leading zeros from the ip-string
     * (by converting each part to integer)
     *
     * @param string
     * @return string
     */
    private function rmLeadingZero(string $szIpString): string
    {
        $vIpParts = preg_split('/[\.:]/', $szIpString);
        $szGlue   = strpos($szIpString, '.') !== false ? '.' : ':';
        return implode($szGlue, array_map(function($e) {return (int)$e;}, $vIpParts));
    }
}

