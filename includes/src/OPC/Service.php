<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

use Exception;
use JTL\Backend\AdminIO;
use JTL\Filter\AbstractFilter;
use JTL\Filter\Config;
use JTL\Filter\Items\Characteristic;
use JTL\Filter\Items\PriceRange;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\Type;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\OPC\Portlets\MissingPortlet\MissingPortlet;
use JTL\Shop;

/**
 * Class Service
 * @package JTL\OPC
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

        Shop::Container()->getGetText()
            ->setLanguage(Shop::getCurAdminLangTag())
            ->loadAdminLocale('pages/opc');
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
            'getFilterList',
        ];
    }

    /**
     * @return string[]
     */
    public function getEditorMessages(): array
    {
        $messageNames = [
            'opcImportSuccessTitle',
            'opcImportSuccess',
            'opcImportUnmappedS',
            'opcImportUnmappedP',
            'btnTitleCopyArea',
            'offscreenAreasDivider',
            'yesDeleteArea',
            'Cancel',
        ];

        $messages = [];

        foreach ($messageNames as $name) {
            $messages[$name] = __($name);
        }

        $messages += [
            'tutStepTitle_0_0' => 'Willkommen',
            'tutStepText_0_0'  => 'In dieser kurzen Einführung wollen wir dir einen Überblick vom OnPage Composer
                geben, ein Editor mit dem schnell neue Inhalte auf deiner Shop-Seite entstehen können.',

            'tutStepTitle_0_1' => 'Aufteilung',
            'tutStepText_0_1'  => 'Grundsätzlich ist der Editor in die zwei Bereich aufgeteilt. Hier links siehst du
                die Sidebar.',

            'tutStepTitle_0_2' => 'Aufteilung',
            'tutStepText_0_2'  => 'Im rechten Fenster siehst du deine aktuelle Seite im Bearbeitungsmodus.',

            'tutStepTitle_0_3' => 'Portlets',
            'tutStepText_0_3'  => 'Das sind unsere Portlets, fertige Bausteine um deine Seiten mit neuen Inhalten zu
                ergänzen.',

            'tutStepTitle_0_4' => 'Portlets',
            'tutStepText_0_4'  => 'Die grauen Bereiche auf der Seite zeigen dir wo du Portlets ablegen kannst.',

            'tutStepTitle_0_5' => 'Portlets',
            'tutStepText_0_5'  => 'Ziehe nun das Portlet "Überschrift" in einen der grauen Bereiche und schau was
                passiert!',

            'tutStepTitle_0_6' => 'Portlets',
            'tutStepText_0_6'  => 'Alle Portlets bieten verschiedene Einstellungen. In diesem Fenster kannst du dein
                Portlet konfigurieren.',

            'tutStepTitle_0_7' => 'Einstellungen',
            'tutStepText_0_7'  => 'Gib einen eigenen Text für diese Überschrift ein und klicke auf "Speichern"!',

            'tutStepTitle_0_8' => 'Einstellungen',
            'tutStepText_0_8'  => 'An dem neuen Portlet siehst du eine Leiste mit verschiedenen Buttons. Über den Stift
                kannst du das Portlet jederzeit erneut bearbeiten.',

            'tutStepTitle_0_9' => 'Seite Veröffentlichen',
            'tutStepText_0_9'  => 'Nur veröffentlichte Entwürfe sind für deine Shop-Besucher sichtbar. Um deinen
                Entwurf jetzt zu veröffentlichen klicke auf diesen Button.',

            'tutStepTitle_0_10' => 'Seite Veröffentlichen',
            'tutStepText_0_10'  => 'Für jede Seite kannst beliebig viele Entwürfe pflegen. Jeden Entwurf kannst du
                zeitlich planen. Z.B. einen allgemeinen und einen Entwurf für die Weihnachtszeit.',

            'tutStepTitle_0_11' => 'Seite Veröffentlichen',
            'tutStepText_0_11'  => 'Die Voreinstellung macht den Entwurf ab sofort und bis auf unbestimmte
                Zeit sichtbar. Klicke jetzt auf "Übernehmen" und der Entwurf ist online!',

            'tutStepTitle_0_12' => 'Fertig!',
            'tutStepText_0_12'  => 'Du kannst den Editor nun beenden und deine Seite anschauen
                 Das waren die Basics. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!',
        ];

        return $messages;
    }

    /**
     * @param AdminIO $io
     * @throws Exception
     */
    public function registerAdminIOFunctions(AdminIO $io): void
    {
        $adminAccount = $io->getAccount();

        if ($adminAccount === null) {
            throw new Exception('Admin account was not set on AdminIO.');
        }

        $this->adminName = $adminAccount->account()->cLogin;

        foreach ($this->getIOFunctionNames() as $functionName) {
            $publicFunctionName = 'opc' . \ucfirst($functionName);
            $io->register($publicFunctionName, [$this, $functionName], null, 'CONTENT_PAGE_VIEW');
        }
    }

    /**
     * @return null|string
     * @throws Exception
     */
    public function getAdminSessionToken(): ?string
    {
        return Shop::getAdminSessionToken();
    }

    /**
     * @param bool $withInactive
     * @return PortletGroup[]
     * @throws Exception
     */
    public function getPortletGroups(bool $withInactive = false): array
    {
        return $this->db->getPortletGroups($withInactive);
    }

    /**
     * @param bool $withInactive
     * @return Portlet[]
     * @throws Exception
     */
    public function getAllPortlets(bool $withInactive = false): array
    {
        return $this->db->getAllPortlets($withInactive);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPortletInitScriptUrls(): array
    {
        $scripts = [];
        foreach ($this->getAllPortlets() as $portlet) {
            foreach ($portlet->getEditorInitScripts() as $script) {
                $path = $portlet->getBasePath() . $script;
                $url  = $portlet->getBaseUrl() . $script;
                if (!\array_key_exists($url, $scripts) && \file_exists($path)) {
                    $scripts[$url] = $url;
                }
            }
        }

        return $scripts;
    }

    /**
     * @param bool $withInactive
     * @return Blueprint[]
     * @throws Exception
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
     * @throws Exception
     */
    public function getBlueprint(int $id): Blueprint
    {
        $blueprint = (new Blueprint())->setId($id);
        $this->db->loadBlueprint($blueprint);

        return $blueprint;
    }

    /**
     * @param int $id
     * @return PortletInstance
     * @throws Exception
     */
    public function getBlueprintInstance(int $id): PortletInstance
    {
        return $this->getBlueprint($id)->getInstance();
    }

    /**
     * @param int $id
     * @return string
     * @throws Exception
     */
    public function getBlueprintPreview(int $id): string
    {
        return $this->getBlueprintInstance($id)->getPreviewHtml();
    }

    /**
     * @param string $name
     * @param array $data
     * @throws Exception
     */
    public function saveBlueprint($name, $data): void
    {
        $blueprint = (new Blueprint())->deserialize(['name' => $name, 'content' => $data]);
        $this->db->saveBlueprint($blueprint);
    }

    /**
     * @param int $id
     */
    public function deleteBlueprint(int $id): void
    {
        $blueprint = (new Blueprint())->setId($id);
        $this->db->deleteBlueprint($blueprint);
    }

    /**
     * @param string $class
     * @return PortletInstance
     * @throws Exception
     */
    public function createPortletInstance($class): PortletInstance
    {
        $portlet = $this->db->getPortlet($class);

        if ($portlet instanceof MissingPortlet) {
            return new MissingPortletInstance($portlet, $portlet->getMissingClass());
        }

        return new PortletInstance($portlet);
    }

    /**
     * @param array $data
     * @return PortletInstance
     * @throws Exception
     */
    public function getPortletInstance($data): PortletInstance
    {
        if ($data['class'] === 'MissingPortlet') {
            return $this->createPortletInstance($data['missingClass'])
                ->deserialize($data);
        }

        return $this->createPortletInstance($data['class'])
            ->deserialize($data);
    }

    /**
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function getPortletPreviewHtml($data): string
    {
        return $this->getPortletInstance($data)->getPreviewHtml();
    }

    /**
     * @param string $portletClass
     * @param string $missingClass
     * @param array $props
     * @return string
     * @throws Exception
     */
    public function getConfigPanelHtml($portletClass, $missingClass, $props): string
    {
        return $this->getPortletInstance([
            'class'        => $portletClass,
            'missingClass' => $missingClass,
            'properties'   => $props,
        ])->getConfigPanelHtml();
    }

    /**
     * @return bool
     */
    public function isEditMode(): bool
    {
        return Request::verifyGPDataString('opcEditMode') === 'yes';
    }

    /**
     * @return bool
     */
    public function isOPCInstalled(): bool
    {
        return $this->db->isOPCInstalled();
    }

    /**
     * @return bool
     */
    public function isPreviewMode(): bool
    {
        return Request::verifyGPDataString('opcPreviewMode') === 'yes';
    }

    /**
     * @return int
     */
    public function getEditedPageKey(): int
    {
        return Request::verifyGPCDataInt('opcEditedPageKey');
    }

    /**
     * @param string $propname
     * @param array $enabledFilters
     * @return string
     * @throws \SmartyException
     */
    public function getFilterList(string $propname, array $enabledFilters = [])
    {
        $filters = $this->getFilterOptions($enabledFilters);
        $smarty  = Shop::Smarty();
        $html    = $smarty
            ->assign('propname', $propname)
            ->assign('filters', $filters)
            ->fetch(\PFAD_ROOT . \PFAD_ADMIN . 'opc/tpl/config/filter-list.tpl');

        return $html;
    }

    /**
     * @param array $enabledFilters
     * @return array
     */
    public function getFilterOptions(array $enabledFilters = []): array
    {
        Tax::setTaxRates();

        $productFilter    = new ProductFilter(
            Config::getDefault(),
            Shop::Container()->getDB(),
            Shop::Container()->getCache()
        );
        $availableFilters = $productFilter->getAvailableFilters();
        $results          = [];
        $enabledMap       = [];

        foreach ($enabledFilters as $enabledFilter) {
            /** @var AbstractFilter $newFilter **/
            $newFilter = new $enabledFilter['class']($productFilter);
            $newFilter->setType(Type::AND);
            if ($newFilter instanceof PriceRange) {
                $productFilter->addActiveFilter($newFilter, (string)$enabledFilter['value']);
            } else {
                $productFilter->addActiveFilter($newFilter, $enabledFilter['value']);
            }
            $enabledMap[$enabledFilter['class'] . ':' . $enabledFilter['value']] = true;
        }

        foreach ($availableFilters as $availableFilter) {
            $class   = $availableFilter->getClassName();
            $name    = $availableFilter->getFrontendName();
            $options = [];

            if ($class === Characteristic::class) {
                $name = 'Merkmale';

                foreach ($availableFilter->getOptions() as $option) {
                    foreach ($option->getOptions() as $suboption) {
                        /** @var Option $suboption */
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
