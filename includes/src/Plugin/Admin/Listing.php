<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use Mapper\PluginValidation;
use Plugin\InstallCode;
use Tightenco\Collect\Support\Collection;
use function Functional\map;

/**
 * Class Listing
 * @package Plugin\Admin
 */
final class Listing
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var Validator
     */
    private $validator;

    private const PLUGINS_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    /**
     * Listing constructor.
     * @param DbInterface $db
     * @param Validator   $validator
     */
    public function __construct(DbInterface $db, Validator $validator)
    {
        $this->db        = $db;
        $this->validator = $validator;
    }

    /**
     * @return Collection
     * @former gibInstalliertePlugins()
     */
    public function getInstalled(): Collection
    {
        $mapper    = new PluginValidation();
        $plugins   = new Collection();
        $pluginIDs = map(
            $this->db->selectAll('tplugin', [], [], 'kPlugin', 'cName, cAutor, nPrio'),
            function (\stdClass $e) {
                return (int)$e->kPlugin;
            }
        );
        foreach ($pluginIDs as $pluginID) {
            $plugin = new \Plugin($pluginID);
            if ($plugin->updateAvailable === true) {
                $code = $this->validator->validateByPluginID($pluginID, true);
                if ($code !== InstallCode::OK) {
                    $plugin->cFehler = $mapper->map($code, $plugin);
                }
            }
            $plugins->push($plugin);
        }

        return $plugins;
    }

    /**
     * @param Collection $installed
     * @return \stdClass - {installiert[], verfuegbar[], fehlerhaft[]}
     * @former gibAllePlugins()
     */
    public function getAll(Collection $installed): \stdClass
    {
        $plugins          = (object)[
            'index'       => [],
            'installiert' => [],
            'verfuegbar'  => [],
            'fehlerhaft'  => [],
        ];
        $installedPlugins = $installed->map(function ($item) {
            return $item->cVerzeichnis;
        });
        foreach (new \DirectoryIterator(self::PLUGINS_DIR) as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $dir  = $fileinfo->getBasename();
            $cXML = $dir . '/' . \PLUGIN_INFO_FILE;
            if (!\file_exists($cXML)) {
                continue;
            }
            $xml     = \file_get_contents($cXML);
            $xmlData = \getArrangedArray(\XML_unserialize($xml));
            $code    = $this->validator->validateByPath(self::PLUGINS_DIR . $dir);
            if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $installedPlugins->contains($dir)) {
                $xmlData['cVerzeichnis']    = $dir;
                $xmlData['shop4compatible'] = isset($xmlData['jtlshop3plugin'][0]['Shop4Version']);
                $plugins->index[$dir]       = \makeXMLToObj($xmlData);
                $plugins->installiert[]     = &$plugins->index[$dir];
            } elseif ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK) {
                $xmlData['cVerzeichnis']    = $dir;
                $xmlData['shop4compatible'] = ($code === 1);
                $plugins->index[$dir]       = \makeXMLToObj($xmlData);
                $plugins->verfuegbar[]      = &$plugins->index[$dir];
            } else {
                $xmlData['cVerzeichnis'] = $dir;
                $xmlData['cFehlercode']  = $code;
                $plugins->index[$dir]    = \makeXMLToObj($xmlData);
                $plugins->fehlerhaft[]   = &$plugins->index[$dir];
            }
        }

        return $this->sort($plugins);
    }

    /**
     * @param \stdClass $plugins
     * @return \stdClass
     */
    private function sort(\stdClass $plugins): \stdClass
    {
        \usort($plugins->installiert, function ($left, $right) {
            return \strcasecmp($left->cName, $right->cName);
        });
        \usort($plugins->verfuegbar, function ($left, $right) {
            return \strcasecmp($left->cName, $right->cName);
        });
        \usort($plugins->fehlerhaft, function ($left, $right) {
            return \strcasecmp($left->cName, $right->cName);
        });

        return $plugins;
    }
}
