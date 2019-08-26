<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

use Exception;
use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\OPC\Portlets\MissingPortlet\MissingPortlet;
use JTL\Plugin\PluginLoader;
use JTL\Shop;

/**
 * Class DB
 * @package JTL\OPC
 */
class DB
{
    /**
     * @var DbInterface
     */
    protected $shopDB;

    /**
     * DB constructor.
     *
     * @param DbInterface $shopDB
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
     * @throws Exception
     */
    public function loadBlueprint(Blueprint $blueprint): void
    {
        $blueprintDB = $this->shopDB->select('topcblueprint', 'kBlueprint', $blueprint->getId());

        if (!\is_object($blueprintDB)) {
            throw new Exception("The OPC blueprint with the id '{$blueprint->getId()}' could not be found.");
        }

        $content = \json_decode($blueprintDB->cJson, true);

        $blueprint->setId($blueprintDB->kBlueprint)
                  ->setName($blueprintDB->cName)
                  ->deserialize(['name' => $blueprintDB->cName, 'content' => $content]);
    }

    /**
     * @param Blueprint $blueprint
     * @return $this
     * @throws Exception
     */
    public function saveBlueprint(Blueprint $blueprint): self
    {
        if ($blueprint->getName() === '') {
            throw new Exception('The OPC blueprint data to be saved is incomplete or invalid.');
        }

        $blueprintDB = (object)[
            'kBlueprint' => $blueprint->getId(),
            'cName'      => $blueprint->getName(),
            'cJson'      => \json_encode($blueprint->getInstance()),
        ];

        if ($this->blueprintExists($blueprint)) {
            $res = $this->shopDB->update('topcblueprint', 'kBlueprint', $blueprint->getId(), $blueprintDB);

            if ($res === -1) {
                throw new Exception('The OPC blueprint could not be updated in the DB.');
            }
        } else {
            $key = $this->shopDB->insert('topcblueprint', $blueprintDB);

            if ($key === 0) {
                throw new Exception('The OPC blueprint could not be inserted into the DB.');
            }

            $blueprint->setId($key);
        }

        return $this;
    }

    /**
     * @param bool $withInactive
     * @return array
     * @throws Exception
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
     * @throws Exception
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
     * @param bool $withInactive
     * @return Portlet[]
     * @throws Exception
     */
    public function getAllPortlets(bool $withInactive = false): array
    {
        $portlets = [];

        $portletsDB = $this->shopDB->selectAll(
            'topcportlet',
            $withInactive ? [] : 'bActive',
            $withInactive ? [] : 1,
            'cClass',
            'cTitle'
        );

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
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getPortlet(string $class): Portlet
    {
        if ($class === '') {
            throw new InvalidArgumentException('The OPC portlet class name "' . $class . '" is invalid.');
        }

        $plugin      = null;
        $portletDB   = $this->shopDB->select('topcportlet', 'cClass', $class);
        $isInstalled = \is_object($portletDB);
        $isActive    = $isInstalled && (int)$portletDB->bActive === 1;
        $fromPlugin  = $isInstalled && (int)$portletDB->kPlugin > 0;

        if ($fromPlugin) {
            $loader    = new PluginLoader($this->shopDB, Shop::Container()->getCache());
            $plugin    = $loader->init((int)$portletDB->kPlugin);
            $fullClass = '\Plugin\\' . $plugin->getPluginID() . '\Portlets\\' . $class . '\\' . $class;
        } else {
            $fullClass = '\JTL\OPC\Portlets\\' . $class . '\\'. $class;
        }

        if ($isInstalled && $isActive) {
            /** @var Portlet $portlet */
            if (\class_exists($fullClass)) {
                $portlet = new $fullClass($class, $portletDB->kPortlet, $portletDB->kPlugin);
            } else {
                $portlet = new Portlet($class, $portletDB->kPortlet, $portletDB->kPlugin);
            }

            return $portlet
                ->setTitle($portletDB->cTitle)
                ->setGroup($portletDB->cGroup)
                ->setActive((int)$portletDB->bActive === 1);
        }

        /** @var MissingPortlet $portlet */
        $portlet = (new MissingPortlet('MissingPortlet', 0, 0))
            ->setMissingClass($class)
            ->setTitle('Missing Portlet "' . $class . '"')
            ->setGroup('hidden')
            ->setActive(false);

        if ($fromPlugin) {
            $portlet->setInactivePlugin($plugin)
                    ->setTitle('Missing Portlet "' . $class . '" (' . $plugin->getPluginID() . ')');
        }

        return $portlet;
    }

    /**
     * @return bool
     */
    public function isOPCInstalled(): bool
    {
        return $this->shopDB->select('tmigration', 'kMigration', 20180507101900) !== null;
    }
}
