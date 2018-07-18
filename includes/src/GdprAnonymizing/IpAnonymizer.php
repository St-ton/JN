<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
     * @var array
     */
    private $vShopConf = [];

    /**
     * IP-string, human readable
     * @var string
     */
    private $szIP = '';

    /**
     * binary "packed" IP
     * @var binary
     */
    private $bRawIp = null;

    /**
     * current IP-anonymization-mask
     * @var string
     */
    private $szIpMask  = '';

    /**
     * current placholder (if the given IP was invalid)
     * @var string
     */
    private $szPlaceholderIP = '';

    /**
     * @var string
     */
    private $szPlaceholderIPv4 = '0.0.0.0';

    /**
     * @var string
     */
    private $szPlaceholderIPv6 = '0000:0000:0000:0000:0000:0000:0000:0000';

    /**
     * flag for old fashioned anonymization ("do not anonymize again")
     * @var bool
     */
    private $bOldFashionedAnon = false;


    public function __construct(string $szIP)
    {
        $this->szIP = $szIP;

        if (false !== strpos($this->szIP, '*')) {
            // if there is an old fashioned anonymization, we do nothing (but set a flag)
            $this->bOldFashionedAnon = true;
            return;
        }
        $this->bRawIp    = inet_pton($this->szIP);
        $this->vShopConf = Shop::getSettings([CONF_GLOBAL]);

        switch (strlen($this->bRawIp)) {
            case 4:
                $this->szPlaceholderIP = $this->szPlaceholderIPv4;
                $this->szIP     = $this->rmLeadingZero($szIP); // possible leading zeros produce errors in inet_XtoY()-functions
                $this->szIpMask = $this->getMaskV4();
                break;
            case 16:
                if (defined('AF_INET6')) {
                    $this->szPlaceholderIP = $this->szPlaceholderIPv6;
                    $this->szIpMask        = $this->getMaskV6();
                } else {
                    // this should normally never happen! (wrong compile-time setting of PHP)
                    throw new Exception('PHP wurde mit der Option "--disable-ipv6" compiliert!');
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
    public function anonymize()
    {
        if (false !== $this->bOldFashionedAnon) {
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
    public function anonymizeLegacy()
    {
        $vMaskParts = preg_split('/[\.:]/', $this->szIpMask);
        $vIpParts   = preg_split('/[\.:]/', $this->szIP);
        $nLen = count($vIpParts);
        (4 === $nLen) ? $szGlue = '.' : $szGlue = ':';
        for ($i = 0; $i < $nLen; $i++) {
            (0 !== hexdec($vMaskParts[$i])) ?: $vIpParts{$i} = '*';
        }
        return implode($szGlue, $vIpParts);
    }

    /**
     * remove leading zeros from the ip-string
     * (by converting each part to integer)
     *
     * @param string
     * @return string
     */
    private function rmLeadingZero(string $szIpString)
    {
        $vIpParts = preg_split('/[\.:]/', $szIpString);
        $szGlue   = (strstr($szIpString, '.')) ? '.' : ':';
        return implode($szGlue, (array_map(function($e) {return (int)$e;}, $vIpParts)));
    }

    /**
     * get the v4-mask from shop-config
     *
     * @return string
     */
    private function getMaskV4()
    {
        return $this->vShopConf['global']['anonymize_ip_mask_v4'];
    }

    /**
     * get the v6-mask from shop-config
     *
     * @return string
     */
    private function getMaskV6()
    {
        return $this->vShopConf['global']['anonymize_ip_mask_v6'];
    }


    /**
     * return a corresponding placeholder for "do not save any IP"
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->szPlaceholderIP;
    }
}
