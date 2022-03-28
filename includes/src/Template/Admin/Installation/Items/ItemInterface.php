<?php declare(strict_types=1);

namespace JTL\Template\Admin\Installation\Items;

use JTL\DB\DbInterface;
use JTL\Template\Model;
use SimpleXMLElement;

/**
 * Interface ItemInterface
 * @package JTL\Plugin\Admin\Installation\Items
 */
interface ItemInterface
{
    /**
     * @param DbInterface           $db
     * @param SimpleXMLElement      $xml
     * @param SimpleXMLElement|null $parentXML
     * @param Model|null            $model
     */
    public function __construct(
        DbInterface $db,
        SimpleXMLElement $xml,
        ?SimpleXMLElement $parentXML,
        ?Model $model = null
    );

    /**
     * @return SimpleXMLElement|null
     */
    public function getNode(): ?SimpleXMLElement;

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
     * @return Model|null
     */
    public function getModel(): ?Model;

    /**
     * @param Model $model
     */
    public function setModel(Model $model): void;

    /**
     * @return SimpleXMLElement
     */
    public function getXML(): SimpleXMLElement;

    /**
     * @param SimpleXMLElement $xml
     */
    public function setXML(SimpleXMLElement $xml): void;
}
