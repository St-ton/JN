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


    public function __construct(string $szIP = '')
    {
        $this->szIpMaskv4 = \Shop::getSettings([CONF_GLOBAL])['global']['anonymize_ip_mask_v4'];
        $this->szIpMaskv6 = \Shop::getSettings([CONF_GLOBAL])['global']['anonymize_ip_mask_v6'];

        if ($szIP !== '') {
            $this->szIP = $szIP;
            $this->init();
        }
    }

    /**
     * analyze the given IP and set the object-values
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
                    throw new \Exception('PHP wurde mit der Option "--disable-ipv6" compiliert!');
                }
                break;
            default:
        }
    }

    /**
     * delivers am valid IP-string,
     * be modern conventions, with "zeros summerized"
     * and ident-parts (according to the masks) with zeros replaced
     *
     * @return string
     */
    public function anonymize(): string
    {
        if ($this->bOldFashionedAnon !== false) {
            return $this->szIP;
        }
        return inet_ntop(inet_pton($this->szIpMask) & $this->bRawIp);
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
    public function setIp(string $szIP = '')
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

