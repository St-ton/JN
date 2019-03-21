<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

use JTL\XMLParser;

/**
 * Class Plugin
 * @package JTL\Plugin
 */
class Plugin extends AbstractPlugin
{
    /**
     * @return string
     */
    public function getCurrentVersion(): string
    {
        $path = $this->getPaths()->getBasePath();
        if (!\is_dir($path) || !\file_exists($path . '/' . \PLUGIN_INFO_FILE)) {
            return '0';
        }
        $parser = new XMLParser();
        $xml    = $parser->parse($path . '/' . \PLUGIN_INFO_FILE);

        return $xml['jtlshopplugin'][0]['Version'] ?? '0';
    }

    /**
     * @return void
     */
    protected function translate()
    {
        $descKey     = $this->getPluginID() . '_desc';
        $description = __($descKey);

        if ($description !== $descKey) {
            $this->meta->setDescription($description);
        }

        foreach ($this->getAdminMenu()->getItems() as $adminMenu) {
            $adminMenu->cName = __($adminMenu->cName);
            $adminMenu->name  = __($adminMenu->name);
        }
    }
}
