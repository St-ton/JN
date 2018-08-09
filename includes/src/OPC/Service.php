<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

use Filter\AbstractFilter;
use Filter\FilterOption;
use Filter\Items\ItemAttribute;
use Filter\Type;

/**
 * Class Service
 * @package OPC
 */
class Service
{
    /**
     * @var string
     */
    protected $adminName = '';

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var null|Page
     */
    protected $curPage;

    /**
     * Service constructor.
     * @param DB $db
     */
    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * @return array list of the OPC service methods to be exposed for AJAX requests
     */
    public function getIOFunctionNames(): array
    {
        return [
            'getIOFunctionNames',
            'getBlueprints',
            'getBlueprint',
            'getBlueprintInstance',
            'getBlueprintPreview',
            'saveBlueprint',
            'deleteBlueprint',
            'getPortletInstance',
            'getPortletPreviewHtml',
            'getConfigPanelHtml',
            'getFilteredProductIds',
            'getFilterOptions',
        ];
    }

    /**
     * @param \AdminIO $io
     * @throws \Exception
     */
    public function registerAdminIOFunctions(\AdminIO $io)
    {
        $this->adminName = $io->getAccount()->account()->cLogin;

        foreach ($this->getIOFunctionNames() as $functionName) {
            $publicFunctionName = 'opc' . \ucfirst($functionName);
            $io->register($publicFunctionName, [$this, $functionName], null, 'CONTENT_PAGE_VIEW');
        }
    }

    /**
     * @param bool $withInactive
     * @return PortletGroup[]
     * @throws \Exception
     */
    public function getPortletGroups(bool $withInactive = false): array
    {
        return $this->db->getPortletGroups($withInactive);
    }

    /**
     * @param bool $withInactive
     * @return Blueprint[]
     * @throws \Exception
     */
    public function getBlueprints(bool $withInactive = false): array
    {
        $blueprints = [];
        foreach ($this->db->getAllBlueprintIds($withInactive) as $blueprintId) {
            $blueprints[] = $this->getBlueprint($blueprintId);
        }

        return $blueprints;
    }

    /**
     * @param int $id
     * @return Blueprint
     * @throws \Exception
     */
    public function getBlueprint(int $id): Blueprint
    {
        $blueprint = (new Blueprint())
            ->setId($id);

        $this->db->loadBlueprint($blueprint);

        return $blueprint;
    }

    /**
     * @param int $id
     * @return PortletInstance
     * @throws \Exception
     */
    public function getBlueprintInstance(int $id): PortletInstance
    {
        return $this->getBlueprint($id)->getInstance();
    }

    /**
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getBlueprintPreview(int $id): string
    {
        return $this->getBlueprintInstance($id)->getPreviewHtml();
    }

    /**
     * @param string $name
     * @param array $data
     * @throws \Exception
     */
    public function saveBlueprint($name, $data)
    {
        $blueprint = (new Blueprint())
            ->deserialize(['name' => $name, 'content' => $data]);

        $this->db->saveBlueprint($blueprint);
    }

    /**
     * @param int $id
     */
    public function deleteBlueprint($id)
    {
        $blueprint = (new Blueprint())
            ->setId($id);

        $this->db->deleteBlueprint($blueprint);
    }

    /**
     * @param string $class
     * @return PortletInstance
     * @throws \Exception
     */
    public function createPortletInstance($class): PortletInstance
    {
        return new PortletInstance($this->db->getPortlet($class));
    }

    /**
     * @param array $data
     * @return PortletInstance
     * @throws \Exception
     */
    public function getPortletInstance($data): PortletInstance
    {
        return $this->createPortletInstance($data['class'])
            ->deserialize($data);
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function getPortletPreviewHtml($data): string
    {
        return $this->getPortletInstance($data)->getPreviewHtml();
    }

    /**
     * @param string $portletClass
     * @param array $props
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml($portletClass, $props): string
    {
        return $this->getPortletInstance(['class' => $portletClass, 'properties' => $props])->getConfigPanelHtml();
    }

    /**
     * @return bool
     */
    public function isEditMode(): bool
    {
        return \RequestHelper::verifyGPDataString('opcEditMode') === 'yes';
    }

    /**
     * @return bool
     */
    public function isOPCInstalled(): bool
    {
        return $this->db->isOPCInstalled();
    }

    /**
     * @return int
     */
    public function getEditedPageKey(): int
    {
        return \RequestHelper::verifyGPCDataInt('opcEditedPageKey');
    }

    /**
     * @param array $enabledFilters
     * @return array
     */
    public function getFilterOptions(array $enabledFilters = []): array
    {
        $productFilter    = new \Filter\ProductFilter();
        $availableFilters = $productFilter->getAvailableFilters();
        $results          = [];
        $enabledMap       = [];

        foreach ($enabledFilters as $enabledFilter) {
            /** @var AbstractFilter $newFilter **/
            $newFilter = new $enabledFilter['class']($productFilter);
            $newFilter->setType(Type::AND);
            $productFilter->addActiveFilter($newFilter, $enabledFilter['value']);
            $enabledMap[$enabledFilter['class'] . ':' . $enabledFilter['value']] = true;
        }

        foreach ($availableFilters as $availableFilter) {
            $class   = $availableFilter->getClassName();
            $name    = $availableFilter->getFrontendName();
            $options = [];

            if ($class === ItemAttribute::class) {
                $name = 'Merkmale';

                foreach ($availableFilter->getOptions() as $option) {
                    foreach ($option->getOptions() as $suboption) {
                        /** @var FilterOption $suboption */
                        $value    = $suboption->kMerkmalWert;
                        $mapindex = $class . ':' . $value;

                        if (!isset($enabledMap[$mapindex])) {
                            $options[] = [
                                'name'  => $suboption->getName(),
                                'value' => $value,
                                'count' => $suboption->getCount(),
                                'class' => $class,
                            ];
                        }
                    }
                }
            } else {
                foreach ($availableFilter->getOptions() as $option) {
                    $value    = $option->getValue();
                    $mapindex = $class . ':' . $value;

                    if (!isset($enabledMap[$mapindex])) {
                        $options[] = [
                            'name'  => $option->getName(),
                            'value' => $value,
                            'count' => $option->getCount(),
                            'class' => $class,
                        ];
                    }
                }
            }

            if (\count($options) > 0) {
                $results[] = [
                    'name'    => $name,
                    'class'   => $class,
                    'options' => $options,
                ];
            }
        }

        return $results;
    }
}
