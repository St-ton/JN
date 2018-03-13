<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class PortletGroup
{
    /**
     * @var Portlet[]
     */
    protected $portlets = [];

    /**
     * PortletGroup constructor.
     * @param string $groupName
     * @throws \Exception
     */
    public function __construct($groupName = '')
    {
        if ($groupName !== '') {
            $portletsDB = \Shop::DB()->selectAll('topcportlet', 'cGroup', $groupName, 'kPortlet', 'kPortlet');
        } else {
            $portletsDB = \Shop::DB()->selectAll('topcportlet', [], [], 'kPortlet', 'kPortlet');
        }

        foreach ($portletsDB as $portletDB) {
            $this->portlets[] = Portlet::fromId($portletDB->kPortlet);
        }
    }

    /**
     * @return Portlet[]
     */
    public function getPortlets()
    {
        return $this->portlets;
    }
}
