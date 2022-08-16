<?php declare(strict_types=1);

namespace JTL\Template\Admin\Installation\Items;

use JTL\DB\DbInterface;
use JTL\Template\Model;
use SimpleXMLElement;

/**
 * Class AbstractItem
 * @package JTL\Template\Admin\Installation\Items
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @param DbInterface           $db
     * @param SimpleXMLElement      $xml
     * @param SimpleXMLElement|null $parentXml
     * @param Model|null            $model
     */
    public function __construct(
        protected DbInterface $db,
        protected SimpleXMLElement $xml,
        protected ?SimpleXMLElement $parentXml,
        protected ?Model $model = null
    ) {
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
    }

    /**
     * @inheritDoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritDoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function getXML(): SimpleXMLElement
    {
        return $this->xml;
    }

    /**
     * @inheritDoc
     */
    public function setXML(SimpleXMLElement $xml): void
    {
        $this->xml = $xml;
    }
}
