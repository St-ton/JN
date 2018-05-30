<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxContainer
 * @package Boxes
 */
class BoxContainer extends AbstractBox
{
    /**
     * @var string
     */
    private $html = '';

    /**
     * @var BoxInterface[]
     */
    public $children = [];

    /**
     * BoxContainer constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('innerHTML', 'HTML');
        parent::addMapping('oContainer_arr', 'Children');
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param BoxInterface[] $chilren
     */
    public function setChildren(array $chilren)
    {
        $this->children = $chilren[$this->getID()] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function render($smarty, int $pageType = 0, int $pageID = 0): string
    {
        foreach ($this->children as $child) {
            $this->html .= trim($child->render($smarty, $pageType, $pageID));
        }

        return parent::render($smarty, $pageType, $pageID);
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHTML(string $html)
    {
        $this->html = $html;
    }
}
