<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Installation;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Plugin\Admin\Installation\Items\AdminMenu;
use JTL\Plugin\Admin\Installation\Items\Blueprints;
use JTL\Plugin\Admin\Installation\Items\Boxes;
use JTL\Plugin\Admin\Installation\Items\Checkboxes;
use JTL\Plugin\Admin\Installation\Items\Consent;
use JTL\Plugin\Admin\Installation\Items\CSS;
use JTL\Plugin\Admin\Installation\Items\Exports;
use JTL\Plugin\Admin\Installation\Items\FrontendLinks;
use JTL\Plugin\Admin\Installation\Items\Hooks;
use JTL\Plugin\Admin\Installation\Items\ItemInterface;
use JTL\Plugin\Admin\Installation\Items\JS;
use JTL\Plugin\Admin\Installation\Items\LanguageVariables;
use JTL\Plugin\Admin\Installation\Items\MailTemplates;
use JTL\Plugin\Admin\Installation\Items\PaymentMethods;
use JTL\Plugin\Admin\Installation\Items\Portlets;
use JTL\Plugin\Admin\Installation\Items\SettingsLinks;
use JTL\Plugin\Admin\Installation\Items\Templates;
use JTL\Plugin\Admin\Installation\Items\Uninstall;
use JTL\Plugin\Admin\Installation\Items\Widgets;
use JTL\Plugin\InstallCode;

/**
 * Class AbstractInstallerFactory
 * @package JTL\Plugin\Admin\Installation
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
        $this->baseNode = $xml['jtlshopplugin'][0] ?? $xml['jtlshop3plugin'][0] ?? null;
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
        $items->push(new Consent());
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
