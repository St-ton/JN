<?php

class IpAnonymizer
{
    /**
     * @var array
     */
    private $vShopConf = [];

    /**
     * @var string
     */
    private $szIP = '';   // IP-string, human readable

    /**
     * @var binary
     */
    private $bRawIp = null; // binary "packed"  IP

    /**
     * @var string
     */
    private $szIpMask  = '';


    public function __construct(string $szIP)
    {
        $this->szIP      = $szIP;
        $this->bRawIp    = inet_pton($this->szIP);

        $this->vShopConf = Shop::getSettings([CONF_GLOBAL]);

        switch (strlen($this->bRawIp)) {
            case 4:
                 $this->szIP = $this->rmLeadingZero($szIP);
                 $this->szIpMask = $this->getMaskV4();
                break;
            case 16:
                 $this->szIpMask = $this->getMaskV6();
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
        return $this->vShopConf['global']['ip_anonymize_mask_v4'];
    }

    /**
     * get the v6-mask from shop-config
     *
     * @return string
     */
    private function getMaskV6()
    {
        return $this->vShopConf['global']['ip_anonymize_mask_v6'];
    }
}

