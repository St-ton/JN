<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Network;

/**
 * Class JTLApi
 * @package Network
 */
final class JTLApi
{
    const URI = 'https://api.jtl-software.de/shop';

    /**
     * @var array
     */
    private $session;

    /**
     * @var \Nice
     */
    private $nice;

    /**
     * @var \Shop
     */
    private $shop;

    /**
     * JTLApi constructor.
     * @param array $session
     * @param \Nice $nice
     * @param \Shop $shop
     */
    public function __construct(array &$session, \Nice $nice, \Shop $shop)
    {
        $this->session = $session;
        $this->nice    = $nice;
        $this->shop    = $shop;
    }

    /**
     *
     */
    private function init()
    {
        if (!isset($this->session['rs'])) {
            $this->session['rs'] = [];
        }
    }

    /**
     * @return mixed
     */
    public function getSubscription()
    {
        if (!isset($this->session['rs']['subscription'])) {

            $subscription = $this->call('check/subscription', [
                'key'    => $this->nice->getAPIKey(),
                'domain' => $this->nice->getDomain(),
            ]);

            $this->session['rs']['subscription'] = (isset($subscription->kShop) && $subscription->kShop > 0)
                ? $subscription : null;
        }

        return $this->session['rs']['subscription'];
    }

    /**
     * @return mixed
     */
    public function getAvailableVersions()
    {
        if (!isset($this->session['rs']['versions'])) {
            $this->session['rs']['versions'] = $this->call('v2/versions');
        }

        return $this->session['rs']['versions'];
    }

    /**
     * @return mixed
     */
    public function getLatestVersion()
    {
        $nVersion      = $this->shop->_getVersion();
        $nMinorVersion = (int)\JTL_MINOR_VERSION;
        $oVersions     = $this->getAvailableVersions();

        $oStableVersions = \array_filter((array)$oVersions, function ($v) use ($nVersion, $nMinorVersion) {
            return $v->channel === 'Stable' && (int)$v->version >= $nVersion;
        });

        if (\count($oStableVersions) > 0) {
            $oVersions = $oStableVersions;
        }

        return \end($oVersions);
    }

    /**
     * @return bool
     */
    public function hasNewerVersion(): bool
    {
        if (\JTL_MINOR_VERSION === '#JTL_MINOR_VERSION#') {
            return false;
        }

        $nVersion      = $this->shop->_getVersion();
        $nMinorVersion = (int)\JTL_MINOR_VERSION;
        $oVersion      = $this->getLatestVersion();

        return $oVersion
            && ((int)$oVersion->version > $nVersion
                || ((int)$oVersion->version == $nVersion && $oVersion->build > $nMinorVersion));
    }

    /**
     * @param string $uri
     * @param null   $data
     * @return mixed|null
     */
    private function call($uri, $data = null)
    {
        $uri     = self::URI . '/' . \ltrim($uri, '/');
        $content = \RequestHelper::http_get_contents($uri, 10, $data);

        return empty($content) ? null : \json_decode($content);
    }
}
