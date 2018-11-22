<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\ExtensionData;

use Tightenco\Collect\Support\Collection;

/**
 * Class Widget
 * @package Plugin\ExtensionData
 */
class Widget
{
    /**
     * @var Collection
     */
    private $widgets;

    public function __construct()
    {
        $this->widgets = new Collection();
    }

    /**
     * @param array  $data
     * @param string $adminPath
     * @return $this
     */
    public function load(array $data, string $adminPath): self
    {
        foreach ($data as $widget) {
            $widget->nPos        = (int)$widget->nPos;
            $widget->bExpanded   = (int)$widget->bExpanded;
            $widget->bActive     = (int)$widget->bActive;
            $widget->kWidget     = (int)$widget->kWidget;
            $widget->id          = $widget->kWidget;
            $widget->kPlugin     = (int)$widget->kPlugin;
            $widget->isExtension = true;
            $widget->classFile   = $adminPath . \PFAD_PLUGIN_WIDGET . $widget->cClass . '.php';
            $widget->className   = $widget->namespace . $widget->cClass;
            $this->widgets->push($widget);
        }

        return $this;
    }

    /**
     * @param int $id
     * @return \stdClass|null
     */
    public function getWidgetByID(int $id): ?\stdClass
    {
        return $this->widgets->firstWhere('id', $id);
    }

    /**
     * @return Collection
     */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    /**
     * @param Collection $widgets
     */
    public function setWidgets(Collection $widgets): void
    {
        $this->widgets = $widgets;
    }

    /**
     * @param \stdClass $widget
     * @return Collection
     */
    public function addWidget(\stdClass $widget): Collection
    {
        $this->widgets[] = $widget;

        return $this->widgets;
    }
}
