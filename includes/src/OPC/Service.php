<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

use Filter\AbstractFilter;
use Filter\IFilter;
use Filter\Type;

class Service
{
    /**
     * @var string
     */
    protected $adminName = '';

    /**
     * @var null|DB
     */
    protected $db = null;

    /**
     * @var null|Locker
     */
    protected $locker = null;

    /**
     * @var null|Page
     */
    protected $curPage = null;

    /**
     * Service constructor.
     * @param DB $db
     * @param Locker $locker
     */
    public function __construct(DB $db, Locker $locker)
    {
        $this->db     = $db;
        $this->locker = $locker;
    }

    /**
     * @return array list of the OPC service methods to be exposed for AJAX requests
     */
    public function getIOFunctionNames()
    {
        return [
            'getIOFunctionNames',
            'getBlueprints',
            'getBlueprint',
            'getBlueprintInstance',
            'getBlueprintPreview',
            'saveBlueprint',
            'deleteBlueprint',
            'getPageRevisions',
            'lockPage',
            'unlockPage',
            'savePage',
            'loadPagePreview',
            'createPagePreview',
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
    public function registerAdminIOFunctions($io)
    {
        $this->adminName = $io->getAccount()->account()->cLogin;

        foreach ($this->getIOFunctionNames() as $functionName) {
            $publicFunctionName = 'opc' . ucfirst($functionName);
            $io->register($publicFunctionName, [$this, $functionName], null, 'CONTENT_PAGE_VIEW');
        }
    }

    /**
     * @return Page[]
     */
    public function getPages()
    {
        $pageIds = $this->db->getAllPageIds();
        $pages   = [];

        foreach ($pageIds as $pageId) {
            $pages[] = $this->getPage($pageId);
        }

        return $pages;
    }

    /**
     * @param string $id
     * @param int $revId
     * @return Page
     * @throws \Exception
     */
    public function getPage($id, $revId = 0)
    {
        $page = (new Page())
            ->setId($id)
            ->setRevId($revId);

        if ($this->db->pageExists($page)) {
            $this->db->loadPage($page);
        }

        return $page;
    }

    /**
     * @return Page
     */
    public function getCurPage()
    {
        if ($this->curPage === null) {
            $curPageUrl                    = '/' . ltrim(\Shop::getRequestUri(), '/');
            $curPageParameters             = \Shop::getParameters();
            $curPageParameters['kSprache'] = \Shop::getLanguage();
            $curPageId                     = md5(serialize($curPageParameters));
            $this->curPage                 = $this->getPage($curPageId)->setUrl($curPageUrl);
        }

        return $this->curPage;
    }

    /**
     * @return bool
     */
    public function curPageExists()
    {
        return $this->db->pageExists($this->getCurPage());
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function lockPage($id)
    {
        return $this->locker->lock($this->adminName, $this->getPage($id));
    }

    /**
     * @param string $id
     * @throws \Exception
     */
    public function unlockPage($id)
    {
        $page = (new Page())
            ->setId($id);

        $this->locker->unlock($page);
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function savePage($data)
    {
        $page = $this->getPage($data['id'])
            ->deserialize($data);

        $this->db->savePage($page);
    }

    /**
     * @param string $id
     */
    public function deletePage($id)
    {
        $page = (new Page())
            ->setId($id);

        $this->db->deletePage($page);
    }

    /**
     * @param string $id
     * @return array
     */
    public function getPageRevisions($id)
    {
        $page = $this->getPage($id);

        return $this->db->getPageRevisions($page);
    }

    /**
     * @param int $id
     * @param int $revId
     * @return string[]
     * @throws \Exception
     */
    public function loadPagePreview($id, $revId = 0)
    {
        return $this->getPage($id, $revId)->getAreaList()->getPreviewHtml();
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function createPagePreview($data)
    {
        $page = new Page();
        $page->deserialize($data);

        return $page->getAreaList()->getPreviewHtml();
    }

    /**
     * @return PortletGroup[]
     * @throws \Exception
     */
    public function getPortletGroups($withInactive = false)
    {
        return $this->db->getPortletGroups($withInactive);
    }

    /**
     * @return Blueprint[]
     * @throws \Exception
     */
    public function getBlueprints($withInactive = false)
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
    public function getBlueprint($id)
    {
        $blueprint = (new Blueprint())
            ->setId($id);

        $this->db->loadBlueprint($blueprint);

        return $blueprint;
    }

    /**
     * @param int $id
     * @return PortletInstance
     */
    public function getBlueprintInstance($id)
    {
        return $this->getBlueprint($id)->getInstance();
    }

    /**
     * @param int $id
     * @return string
     */
    public function getBlueprintPreview($id)
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
    public function createPortletInstance($class)
    {
        return new PortletInstance($this->db->getPortlet($class));
    }

    /**
     * @param array $data
     * @return PortletInstance
     * @throws \Exception
     */
    public function getPortletInstance($data)
    {
        return $this->createPortletInstance($data['class'])
            ->deserialize($data);
    }

    /**
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function getPortletPreviewHtml($data)
    {
        return $this->getPortletInstance($data)->getPreviewHtml();
    }

    /**
     * @param string $portletClass
     * @param array $props
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml($portletClass, $props)
    {
        return $this->getPortletInstance(['class' => $portletClass, 'properties' => $props])->getConfigPanelHtml();
    }

    /**
     * @return bool
     */
    public function isEditMode()
    {
        return verifyGPDataString('opcEditMode') === 'yes';
    }

    /**
     * @return bool
     */
    public function isReplacePage()
    {
        return $this->getCurPage()->isReplace();
    }

    /**
     * @param array $enabledFilters
     * @return array
     */
    public function getFilterOptions($enabledFilters = [])
    {
        \Shop::setLanguage(1);

        $productFilter    = new \Filter\ProductFilter();
        $availableFilters = $productFilter->getAvailableFilters();
        $results          = [];
        $enabledMap       = [];

        foreach ($enabledFilters as $enabledFilter) {
            /** @var AbstractFilter $newFilter **/
            $newFilter = new $enabledFilter['class']($productFilter);
            $newFilter->setType(Type::AND());
            $productFilter->addActiveFilter($newFilter, $enabledFilter['value']);
            $enabledMap[$enabledFilter['class'] . ':' . $enabledFilter['value']] = true;
        }

        foreach ($availableFilters as $availableFilter) {
            $class   = $availableFilter->getClassName();
            $name    = $availableFilter->getFrontendName();
            $options = [];

            if (\StringHandler::endsWith($class, 'ItemAttribute')) {
                $name = 'Merkmale';

                foreach ($availableFilter->getOptions() as $option) {
                    foreach ($option->getOptions() as $suboption) {
                        /** @var \Filter\FilterOption $suboption */
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

            if (count($options) > 0) {
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
