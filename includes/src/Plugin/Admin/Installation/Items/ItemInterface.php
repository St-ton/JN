<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use DB\DbInterface;
use Plugin\Plugin;

/**
 * Interface ItemInterface
 * @package Plugin\Admin\Installation\Items
 */
interface ItemInterface
{
    /**
     * ItemInterface constructor.
     * @param DbInterface           $db
     * @param array|null            $baseNode
     * @param \stdClass             $plugin
     * @param \stdClass|Plugin|null $oldPlugin
     */
    public function __construct(DbInterface $db = null, array $baseNode = null, $plugin = null, $oldPlugin = null);

    /**
     * @return array
     */
    public function getNode(): array;

    /**
     * @return mixed
     */
    public function install();

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void;

    /**
     * @return \stdClass
     */
    public function getPlugin(): \stdClass;

    /**
     * @param \stdClass $plugin
     */
    public function setPlugin(\stdClass $plugin): void;

    /**
     * @return array
     */
    public function getBaseNode(): array;

    /**
     * @param array $baseNode
     */
    public function setBaseNode(array $baseNode): void;
}
