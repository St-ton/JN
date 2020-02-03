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
            'tutStepText_0_0'  => 'In dieser kurzen Einführung wollen wir dir einen Überblick vom
                <em>OnPage Composer</em> geben, ein Editor mit dem schnell neue Inhalte auf deiner Shop-Seite
                entstehen können.',

            'tutStepTitle_0_1' => 'Aufteilung',
            'tutStepText_0_1'  => '<p>Grundsätzlich ist der Editor in die zwei Bereich aufgeteilt.</p><p>Hier links
                siehst du die Sidebar.</p>',

            'tutStepTitle_0_2' => 'Aufteilung',
            'tutStepText_0_2'  => 'Im rechten Fenster siehst du deine aktuelle Seite im Bearbeitungsmodus.',

            'tutStepTitle_0_3' => 'Portlets',
            'tutStepText_0_3'  => 'Das sind unsere <em>Portlets</em>, fertige Bausteine um deine Seiten mit neuen
                Inhalten zu ergänzen.',

            'tutStepTitle_0_4' => 'Portlets',
            'tutStepText_0_4'  => 'Die hellblauen Bereiche auf der Seite zeigen dir wo du Portlets ablegen kannst.',

            'tutStepTitle_0_5' => 'Portlets',
            'tutStepText_0_5'  => 'Ziehe nun das Portlet <em>Überschrift</em> in einen der hellblauen Bereiche und
                schau was passiert!',

            'tutStepTitle_0_6' => 'Portlets',
            'tutStepText_0_6'  => 'Alle Portlets bieten verschiedene Einstellungen. In diesem Fenster kannst du dein
                Portlet konfigurieren.',

            'tutStepTitle_0_7' => 'Einstellungen',
            'tutStepText_0_7'  => 'Gib einen eigenen Text für die Überschrift ein und klicke auf <em>Speichern</em>!',

            'tutStepTitle_0_8' => 'Einstellungen',
            'tutStepText_0_8'  => 'An dem neuen Portlet siehst du eine Leiste mit verschiedenen Buttons. Über den Stift
                kannst du das Portlet jederzeit erneut bearbeiten.',

            'tutStepTitle_0_9' => 'Seite Veröffentlichen',
            'tutStepText_0_9'  => '<p>Nur veröffentlichte Entwürfe sind für deine Shop-Besucher sichtbar.</p>
                <p>Um deinen Entwurf jetzt zu veröffentlichen klicke auf diesen Button.</p>',

            'tutStepTitle_0_10' => 'Seite Veröffentlichen',
            'tutStepText_0_10'  => 'Für jede Seite kannst mehrere Entwürfe pflegen, deren Sichtbarkeit du
                außerdem zeitlich planen kannst. (Z.B. einen allgemeinen und einen Entwurf für die Weihnachtszeit)',

            'tutStepTitle_0_11' => 'Seite Veröffentlichen',
            'tutStepText_0_11'  => 'Die Voreinstellung macht den Entwurf ab sofort und bis auf unbestimmte
                Zeit sichtbar. Klicke jetzt auf <em>Übernehmen</em> und der Entwurf ist online!',

            'tutStepTitle_0_12' => 'Fertig!',
            'tutStepText_0_12'  => '<p>Du kannst den Editor nun beenden und deine Seite anschauen.</p>
                <p>Das waren die Basics. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!</p>',

            'tutStepTitle_1_0' => 'Animationen',
            'tutStepText_1_0'  => 'Einige Portlets verfügen über Einstellungen, um <em>Animationen zu erstellen.
                Hier lernst du, wie du diese nutzt.',

            'tutStepTitle_1_1' => 'Animationen',
            'tutStepText_1_1'  => 'Ziehe zunächst einen <em>Button</em> in deine Seite!',

            'tutStepTitle_1_2' => 'Animationen',
            'tutStepText_1_2'  => 'Wechsel nun zu dem Reiter <em>Animation</em>.',

            'tutStepTitle_1_3' => 'Animationen',
            'tutStepText_1_3'  => '<p>Unter <em>Animationstyp</em> kannst du einen von verschiedenen Animations-Stilen
                auswählen. (z.B. <em>"bounce"</em> lässt das Portlet hüpfen)</p>
                <p>Speichere dann die Einstellungen!</p>',

            'tutStepTitle_1_4' => 'Animationen',
            'tutStepText_1_4'  => '<p>Sofort siehst du deine erste Animation im Editorfenster.</p>
                <p>Versuchen wir nun was komplexeres!</p>',

            'tutStepTitle_1_5' => 'Animation',
            'tutStepText_1_5'  => 'Öffne erneut die Einstellungen des Buttons. (Doppelklick auf das Portlet oder
                klicke den Stift-Button)',

            'tutStepTitle_1_6' => 'Animationen',
            'tutStepText_1_6'  => 'Hier im Reiter "Stile" kannst du dem Button einen unteren Abstand von 350 (Pixeln)
                zuweisen. Wechsle dann noch mal zum Animations-Reiter!',

            'tutStepTitle_1_7' => 'Animationen',
            'tutStepText_1_7'  => 'Als Animationstyp eignet sich <em>"fade-in"</em> ganz gut. Gib außerdem bei
                <em>Abstand</em> 350 ein! Nun Kannst du die Einstellungen speichern!',

            'tutStepTitle_1_8' => 'Animationen',
            'tutStepText_1_8'  => 'Um zu sehen wie sich die Einstellung auswirkt, lass uns den Button, so wie er ist
                ein paar mal klonen. Klicke dazu auf den <em>Kopieren</em>-Button!',

            'tutStepTitle_1_9' => 'Animationen',
            'tutStepText_1_9'  => 'Und noch einmal!',

            'tutStepTitle_1_10' => 'Animationen',
            'tutStepText_1_10'  => 'Und noch ein letztes mal, so dass wir 4 Exemplare des Buttons
                untereinander haben!',

            'tutStepTitle_1_11' => 'Animationen',
            'tutStepText_1_11'  => 'Mit dem <em>Vorschau</em>-Schalter kannst du das Ergebnis gleich mal testen!',

            'tutStepTitle_1_12' => 'Animationen',
            'tutStepText_1_12'  => '<p>Scroll die Seite nach unten und beachte dabei, dass die Animation nicht eher
                startet, ehe der Button mindesten 350 Pixel über die Viewport-Untergrenze gescrollt wurde.</p>
                <p>Man kann dieses Konzept auch weiter ausbauen und die Bereiche z.B. abwechselnd von rechts und links
                in die Seite einfahren lassen.</p>',

            'tutStepTitle_1_13' => 'Animationen',
            'tutStepText_1_13'  => '<p>Bedenke aber bitte, dass "weniger oft mehr ist". Soll heißen: geh sparsam mit
                Animationen um, damit deine Kunden nicht abgelenkt oder gar verschreckt werden.</p>
                <p>Damit sind wir mit dem Tutorial "Animationen" durch. Wir wünschen dir weiterhin viel Spaß mit dem
                <em>OnPage Composer</em>!</p>',

            'tutStepTitle_2_0' => 'Vorlagen',
            'tutStepText_2_0'  => '<p>Lerne hier wie du Vorlagen anlegst und wiederverwendest.</p>
                <p>Ziehe zunächst ein <em>Grid-Layout</em> in die Seite und fülle die Spalten nach Herzenslust mit
                beliebigen Inhalten.</p>',

            'tutStepTitle_2_1' => 'Vorlagen anlegen',
            'tutStepText_2_1'  => 'Willst du deine Kreation zu einem späteren Zeitpunkt wiederverwenden, so kannst du
                einfach eine <em>Vorlage</em> daraus machen. Wähle das gesamte Grid-Layout mit einem Klick aus!',

            'tutStepTitle_2_2' => 'Vorlagen anlegen',
            'tutStepText_2_2'  => 'Klicke jetzt in der Toolbar auf den <em>Stern</em>-Button!',

            'tutStepTitle_2_3' => 'Vorlagen anlegen',
            'tutStepText_2_3'  => 'Trage hier einen aussagekräftigen Namen ein! Mit diesem Namen findest du später
                deine Vorlage schneller wieder.<br>
                (Gute Beispiele sind <em>"Produkttabelle", "Video mit Beschreibungstext"</em> oder
                <em>"3-spaltiger Text"</em>)',

            'tutStepTitle_2_4' => 'Vorlagen wiederverwenden',
            'tutStepText_2_4'  => 'Alle gespeicherten Vorlagen findest du über den entsprechenden Tab in der Sidebar.',

            'tutStepTitle_2_5' => 'Vorlagen wiederverwenden',
            'tutStepText_2_5'  => 'Du kannst jede Vorlage wie ein normales Portlet einfach in die Seite ziehen.',

            'tutStepTitle_2_6' => 'Vorlagen wiederverwenden',
            'tutStepText_2_6'  => '<p>Das Plugin <em>"JTL-Portlets"</em> bietet übrigens eine kleine Auswahl an
                Vorlagen, die, wenn installiert, hier mit zur Verfügung gestellt werden.</p>
                <p>Das war es auch schon mit den <em>Vorlagen</em>. Wir wünschen dir weiterhin viel Spaß mit dem 
                <em>OnPage Composer</em>!</p>',
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
