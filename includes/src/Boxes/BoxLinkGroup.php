<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Link\LinkGroup;

/**
 * Class BoxLinkGroup
 * @package Boxes
 */
final class BoxLinkGroup extends AbstractBox
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
     * BoxLinkGroup constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('oLinkGruppe', 'LinkGroup');
        parent::addMapping('oLinkGruppeTemplate', 'LinkGroupTemplate');
    }

    /**
     * @return LinkGroup|null
     */
    public function getLinkGroup()
    {
        return $this->linkGroup;
    }

    /**
     * @param LinkGroup|null $linkGroup
     */
    public function setLinkGroup($linkGroup)
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
    public function setLinkGroupTemplate(string $linkGroupTemplate)
    {
        $this->linkGroupTemplate = $linkGroupTemplate;
    }

    /**
     * @inheritdoc
     */
    public function render($smarty, int $pageType = 0, int $pageID = 0): string
    {
        $this->setShow(false);
        $this->linkGroup = \Shop::Container()->getLinkService()->getLinkGroupByID($this->customID);
        if ($this->linkGroup !== null) {
            $this->setShow($this->linkGroup->getLinks()->count() > 0);
            $this->setLinkGroupTemplate($this->linkGroup->getTemplate());
        } else {
            throw new \InvalidArgumentException('Cannot find link group id ' . $this->customID);
        }

        return parent::render($smarty, $pageType, $pageID);
    }
}
