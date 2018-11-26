<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use JTL\XMLParser;

/**
 * Class Extension
 * @package Plugin
 */
class Extension extends AbstractExtension
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
        $parser  = new XMLParser();
        $xml     = $parser->parse($path . '/' . \PLUGIN_INFO_FILE);

        return $xml['jtlshopplugin'][0]['Install'][0]['Version'] ?? '0';
    }
}
