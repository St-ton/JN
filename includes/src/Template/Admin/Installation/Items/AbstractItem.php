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
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @var SimpleXMLElement
     */
    protected SimpleXMLElement $xml;

    /**
     * @var SimpleXMLElement|null
     */
    protected ?SimpleXMLElement $parentXml;

    /**
     * @var Model|null
     */
    protected ?Model $model;

    /**
     * @inheritdoc
     */
    public function __construct(
        DbInterface $db,
        SimpleXMLElement $xml,
        ?SimpleXMLElement $parentXML,
        ?Model $model = null
    ) {
        $this->db        = $db;
        $this->xml       = $xml;
        $this->parentXml = $parentXML;
        $this->model     = $model;
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
