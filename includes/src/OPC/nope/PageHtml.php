<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class PageHtml
 * @package OPC
 */
abstract class PageHtml
{
    /**
     * @var PageModel
     */
    protected $model;

    /**
     * @var string[]
     */
    protected $html = [];

    /**
     * PageHtml constructor.
     * @param $model
     * @throws \Exception
     */
    public function __construct($model)
    {
        if ($model === null) {
            throw new \Exception('This page HTML has no valid page model associated.');
        }

        $this->model = $model;
    }

    /**
     * @return string[] the rendered HTML content of this page for each area id
     */
    public function getHtml()
    {
        if (!is_string($this->html)) {
            $this->html = $this->renderHtml();
        }

        return $this->html;
    }

    /**
     * Re-render the HTML of this page
     * @return string[] the rendered HTML content of this page for each area id
     */
    abstract protected function renderHtml();
}
