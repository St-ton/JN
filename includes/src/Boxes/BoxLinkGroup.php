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
     * @inheritdoc
     */
    public function map(array $boxData)
    {
        parent::map($boxData);
        $this->setShow(false);
        $this->linkGroup = \Shop::Container()->getLinkService()->getLinkGroupByID($this->getCustomID());
        if ($this->linkGroup !== null) {
            $this->setShow($this->linkGroup->getLinks()->count() > 0);
            $this->setLinkGroupTemplate($this->linkGroup->getTemplate());
        } else {
            throw new \InvalidArgumentException('Cannot find link group id ' . $this->getCustomID());
        }
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
}
