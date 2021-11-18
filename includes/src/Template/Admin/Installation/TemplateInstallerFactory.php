<?php declare(strict_types=1);

namespace JTL\Template\Admin\Installation;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Plugin\InstallCode;
use JTL\Template\Admin\Installation\Items\Consent;
use JTL\Template\Admin\Installation\Items\ItemInterface;
use JTL\Template\Model;
use SimpleXMLElement;

/**
 * Class TemplateInstallerFactory
 * @package JTL\Template\Admin\Installation
 */
class TemplateInstallerFactory
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var SimpleXMLElement
     */
    protected $xml;

    /**
     * @var SimpleXMLElement|null
     */
    protected $parentXml;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @param DbInterface           $db
     * @param SimpleXMLElement      $xml
     * @param SimpleXMLElement|null $parentXml
     * @param Model                 $model
     */
    public function __construct(
        DbInterface $db,
        SimpleXMLElement $xml,
        ?SimpleXMLElement $parentXml,
        Model $model
    ) {
        $this->db        = $db;
        $this->xml       = $xml;
        $this->parentXml = $parentXml;
        $this->model     = $model;
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        $items = new Collection();
        $items->push(new Consent($this->db, $this->xml, $this->parentXml, $this->model));

        return $items;
    }

    /**
     * @return int
     */
    public function install(): int
    {
        foreach ($this->getItems() as $installationItem) {
            /** @var ItemInterface $installationItem */
            if (($code = $installationItem->install()) !== InstallCode::OK) {
                return $code;
            }
        }

        return InstallCode::OK;
    }
}
