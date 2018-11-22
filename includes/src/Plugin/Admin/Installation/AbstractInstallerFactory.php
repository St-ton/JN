<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation;

use DB\DbInterface;
use Plugin\Admin\Installation\Items\AdminMenu;
use Plugin\Admin\Installation\Items\Blueprints;
use Plugin\Admin\Installation\Items\Boxes;
use Plugin\Admin\Installation\Items\Checkboxes;
use Plugin\Admin\Installation\Items\CSS;
use Plugin\Admin\Installation\Items\Exports;
use Plugin\Admin\Installation\Items\FrontendLinks;
use Plugin\Admin\Installation\Items\Hooks;
use Plugin\Admin\Installation\Items\ItemInterface;
use Plugin\Admin\Installation\Items\JS;
use Plugin\Admin\Installation\Items\LanguageVariables;
use Plugin\Admin\Installation\Items\MailTemplates;
use Plugin\Admin\Installation\Items\PaymentMethods;
use Plugin\Admin\Installation\Items\Portlets;
use Plugin\Admin\Installation\Items\SettingsLinks;
use Plugin\Admin\Installation\Items\Templates;
use Plugin\Admin\Installation\Items\Uninstall;
use Plugin\Admin\Installation\Items\Widgets;
use Plugin\InstallCode;
use Tightenco\Collect\Support\Collection;

/**
 * Class AbstractInstallerFactory
 * @package Plugin\Admin\Installation
 */
abstract class AbstractInstallerFactory
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var \stdClass
     */
    protected $plugin;

    /**
     * @var array
     */
    protected $baseNode;

    /**
     * AbstractInstallerFactory constructor.
     * @param DbInterface $db
     * @param array       $xml
     * @param             $plugin
     */
    public function __construct(DbInterface $db, array $xml, $plugin)
    {
        $this->db       = $db;
        $this->baseNode = $xml['jtlshopplugin'][0] ?? $xml['jtlshop3plugin'][0];
        $this->plugin   = $plugin;
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        $items = new Collection();
        $items->push(new Hooks());
        $items->push(new Uninstall());
        $items->push(new AdminMenu());
        $items->push(new SettingsLinks());
        $items->push(new FrontendLinks());
        $items->push(new PaymentMethods());

        $items->push(new Boxes());//@todo: extension check

        $items->push(new Templates());
        $items->push(new MailTemplates());
        $items->push(new LanguageVariables());
        $items->push(new Checkboxes());//@todo: extension check
        $items->push(new Widgets());//@todo: extension check
        $items->push(new Portlets());//@todo: extension check
        $items->push(new Blueprints());//@todo: extension check
        $items->push(new Exports());
        $items->push(new CSS());
        $items->push(new JS());

        $items->each(function (ItemInterface $e) {
            $e->setDB($this->db);
            $e->setPlugin($this->plugin);
            $e->setBaseNode($this->baseNode);
        });

        return $items;
    }

    /**
     * @return int
     */
    public function install(): int
    {
        foreach ($this->getItems() as $installationItem) {
            /** @var ItemInterface $installationItem */
            if (($code = $installationItem->install()) !== InstallCode::OK) {
                return $code;
            }
        }

        return InstallCode::OK;
    }
}
