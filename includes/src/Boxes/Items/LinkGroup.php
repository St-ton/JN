<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

/**
 * Class LinkGroup
 * @package Boxes\Items
 */
final class LinkGroup extends AbstractBox
{
    /**
     * @var LinkGroup|null
     */
    private $linkGroup;

    /**
     * @var string|null
     */
    public $linkGroupTemplate;

    /**
     * LinkGroup constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('oLinkGruppe', 'LinkGroup');
        $this->addMapping('oLinkGruppeTemplate', 'LinkGroupTemplate');
    }

    /**
     * @inheritdoc
     */
    public function map(array $boxData): void
    {
        parent::map($boxData);
        $this->setShow(false);
        $this->linkGroup = \Shop::Container()->getLinkService()->getLinkGroupByID($this->getCustomID());
        if ($this->linkGroup !== null) {
            $this->setShow($this->linkGroup->getLinks()->count() > 0);
            $this->setLinkGroupTemplate($this->linkGroup->getTemplate());
//        } else {
//            throw new \InvalidArgumentException('Cannot find link group id ' . $this->getCustomID());
        }
    }

    /**
     * @return LinkGroup|null
     */
    public function getLinkGroup(): ?LinkGroup
    {
        return $this->linkGroup;
    }

    /**
     * @param LinkGroup|null $linkGroup
     */
    public function setLinkGroup($linkGroup): void
    {
        $this->linkGroup = $linkGroup;
    }

    /**
     * @return null|string
     */
    public function getLinkGroupTemplate(): string
    {
        return $this->linkGroupTemplate;
    }

    /**
     * @param null|string $linkGroupTemplate
     */
    public function setLinkGroupTemplate(string $linkGroupTemplate): void
    {
        $this->linkGroupTemplate = $linkGroupTemplate;
    }
}
