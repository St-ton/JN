<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

use DB\DbInterface;
use DB\ReturnType;

/**
 * Class DB
 * @package OPC
 */
class DB
{
    /**
     * @var null|DbInterface
     */
    protected $shopDB;

    /**
     * DB constructor.
     * @param \DB\DbInterface $shopDB
     */
    public function __construct(DbInterface $shopDB)
    {
        $this->shopDB = $shopDB;
    }

    /**
     * @param bool $withInactive
     * @return int[]
     */
    public function getAllBlueprintIds(bool $withInactive = false): array
    {
        $blueprintsDB = $this->shopDB->selectAll(
            'topcblueprint',
            $withInactive ? [] : 'bActive',
            $withInactive ? [] : 1,
            'kBlueprint'
        );

        $blueprintIds = [];

        foreach ($blueprintsDB as $blueprintDB) {
            $blueprintIds[] = $blueprintDB->kBlueprint;
        }

        return $blueprintIds;
    }

    /**
     * @param Blueprint $blueprint
     * @return bool
     */
    public function blueprintExists(Blueprint $blueprint): bool
    {
        return \is_object($this->shopDB->select('topcblueprint', 'kBlueprint', $blueprint->getId()));
    }

    /**
     * @param Blueprint $blueprint
     * @return $this
     */
    public function deleteBlueprint(Blueprint $blueprint): self
    {
        $this->shopDB->delete('topcblueprint', 'kBlueprint', $blueprint->getId());

        return $this;
    }

    /**
     * @param Blueprint $blueprint
     * @throws \Exception
     */
    public function loadBlueprint(Blueprint $blueprint)
    {
        $blueprintDB = $this->shopDB->select('topcblueprint', 'kBlueprint', $blueprint->getId());

        if (!\is_object($blueprintDB)) {
            throw new \Exception("The OPC blueprint with the id '{$blueprint->getId()}' could not be found.");
        }

        $content = \json_decode($blueprintDB->cJson, true);

        $blueprint
            ->setId($blueprintDB->kBlueprint)
            ->setName($blueprintDB->cName)
            ->deserialize(['name' => $blueprintDB->cName, 'content' => $content]);
    }

    /**
     * @param Blueprint $blueprint
     * @return $this
     * @throws \Exception
     */
    public function saveBlueprint(Blueprint $blueprint): self
    {
        if ($blueprint->getName() === '') {
            throw new \Exception('The OPC blueprint data to be saved is incomplete or invalid.');
        }

        $blueprintDB = (object)[
            'kBlueprint' => $blueprint->getId(),
            'cName'      => $blueprint->getName(),
            'cJson'      => \json_encode($blueprint->getInstance()),
        ];

        if ($this->blueprintExists($blueprint)) {
            $res = $this->shopDB->update('topcblueprint', 'kBlueprint', $blueprint->getId(), $blueprintDB);

            if ($res === -1) {
                throw new \Exception('The OPC blueprint could not be updated in the DB.');
            }
        } else {
            $key = $this->shopDB->insert('topcblueprint', $blueprintDB);

            if ($key === 0) {
                throw new \Exception('The OPC blueprint could not be inserted into the DB.');
            }

            $blueprint->setId($key);
        }

        return $this;
    }

    /**
     * @param bool $withInactive
     * @return array
     * @throws \Exception
     */
    public function getPortletGroups(bool $withInactive = false): array
    {
        $groupNames = $this->shopDB->query(
            'SELECT DISTINCT(cGroup) FROM topcportlet ORDER BY cGroup ASC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $groups     = [];
        foreach ($groupNames as $groupName) {
            $groups[] = $this->getPortletGroup($groupName->cGroup, $withInactive);
        }

        return $groups;
    }

    /**
     * @param string $groupName
     * @param bool   $withInactive
     * @return PortletGroup
     * @throws \Exception
     */
    public function getPortletGroup(string $groupName, bool $withInactive = false): PortletGroup
    {
        $portletsDB = $this->shopDB->selectAll(
            'topcportlet',
            $withInactive ? 'cGroup' : ['cGroup', 'bActive'],
            $withInactive ? $groupName : [$groupName, 1],
            'cClass',
            'cTitle'
        );

        $portletGroup = new PortletGroup($groupName);

        foreach ($portletsDB as $portletDB) {
            $portlet = $this->getPortlet($portletDB->cClass);
            $portletGroup->addPortlet($portlet);
        }

        return $portletGroup;
    }

    /**
     * @return Portlet[]
     * @throws \Exception
     */
    public function getAllPortlets(): array
    {
        $portlets   = [];
        $portletsDB = $this->shopDB->selectAll('topcportlet', [], [], 'cClass', 'cTitle');

        foreach ($portletsDB as $portletDB) {
            $portlets[] = $this->getPortlet($portletDB->cClass);
        }

        return $portlets;
    }

    /**
     * @return int
     */
    public function getPortletCount(): int
    {
        return (int)$this->shopDB->query(
            'SELECT COUNT(kPortlet) AS count FROM topcportlet',
            ReturnType::SINGLE_OBJECT
        )->count;
    }

    /**
     * @param string $class
     * @return Portlet
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function getPortlet(string $class): Portlet
    {
        if ($class === '') {
            throw new \InvalidArgumentException("The OPC portlet class name '$class' is invalid.");
        }

        $portletDB = $this->shopDB->select('topcportlet', 'cClass', $class);

        if (!\is_object($portletDB)) {
            throw new \Exception("The OPC portlet with class name '$class' could not be found.");
        }

        if ((int)$portletDB->bActive !== 1) {
            throw new \Exception("The OPC portlet with class name '$class' is inactive.");
        }

        if ($portletDB->kPlugin > 0) {
            $plugin  = new \Plugin($portletDB->kPlugin);
            $include = PFAD_ROOT . \PFAD_PLUGIN . $plugin->cVerzeichnis . '/' . \PFAD_PLUGIN_VERSION
                . $plugin->getCurrentVersion() . '/' . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_PORTLETS
                . $portletDB->cClass . '/' . $portletDB->cClass . '.php';
            require_once $include;
        }

        /** @var Portlet $portlet */
        $fullClass = "\\OPC\\Portlets\\$class";
        $portlet   = new $fullClass();

        return $portlet
            ->setId($portletDB->kPortlet)
            ->setPluginId($portletDB->kPlugin)
            ->setTitle($portletDB->cTitle)
            ->setClass($portletDB->cClass)
            ->setGroup($portletDB->cGroup)
            ->setActive((int)$portletDB->bActive === 1);
    }

    /**
     * @return bool
     */
    public function isOPCInstalled(): bool
    {
        try {
            $this->shopDB->selectAll('topcportlet', [], []);
            $this->shopDB->selectAll('topcblueprint', [], []);
            $this->shopDB->selectAll('topcpage', [], []);

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
