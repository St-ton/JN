<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\DB\DbInterface;
use JTL\Plugin\LegacyPlugin;
use stdClass;

/**
 * Class AbstractItem
 * @package JTL\Plugin\Admin\Installation\Items
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var stdClass
     */
    protected $plugin;

    /**
     * @var stdClass|LegacyPlugin|null
     */
    protected $oldPlugin;

    /**
     * @var array
     */
    protected $baseNode;

    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db = null, array $baseNode = null, $plugin = null, $oldPlugin = null)
    {
        $this->db        = $db;
        $this->baseNode  = $baseNode;
        $this->plugin    = $plugin;
        $this->oldPlugin = $oldPlugin;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return stdClass
     */
    public function getPlugin(): stdClass
    {
        return $this->plugin;
    }

    /**
     * @param stdClass $plugin
     */
    public function setPlugin(stdClass $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @return LegacyPlugin|stdClass|null
     */
    public function getOldPlugin()
    {
        return $this->oldPlugin;
    }

    /**
     * @param LegacyPlugin|stdClass|null $oldPlugin
     */
    public function setOldPlugin($oldPlugin): void
    {
        $this->oldPlugin = $oldPlugin;
    }

    /**
     * @return array
     */
    public function getBaseNode(): array
    {
        return $this->baseNode;
    }

    /**
     * @param array $baseNode
     */
    public function setBaseNode(array $baseNode): void
    {
        $this->baseNode = $baseNode;
    }
}
