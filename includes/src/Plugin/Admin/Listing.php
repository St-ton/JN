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
                    $mapper          = new PluginValidation();
                    $plugin->cFehler = $mapper->map($code, $plugin);
                }
            }
            $plugins->push($plugin);
        }

        return $plugins;
    }

    /**
     * Läuft im Ordner PFAD_ROOT/includes/plugins/ alle Verzeichnisse durch und gibt korrekte Plugins zurück
     *
     * @param Collection $installed
     * @return object - {installiert[], verfuegbar[], fehlerhaft[]}
     * @former gibAllePlugins()
     */
    public function getAll(Collection $installed)
    {
        $path    = \PFAD_ROOT . \PFAD_PLUGIN;
        $plugins = (object)[
            'index'       => [],
            'installiert' => [],
            'verfuegbar'  => [],
            'fehlerhaft'  => [],
        ];

        if (!\is_dir($path)) {
            return $plugins;
        }
        $cInstalledPlugins = $installed->map(function ($item) {
            return $item->cVerzeichnis;
        });
        $iterator          = new \DirectoryIterator($path);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $cXML = $fileinfo->getPathname() . '/' . \PLUGIN_INFO_FILE;
            if (\file_exists($cXML)) {
                $dir     = $fileinfo->getBasename();
                $xml     = \file_get_contents($cXML);
                $xmlData = \XML_unserialize($xml);
                $xmlData = \getArrangedArray($xmlData);
                $code    = $this->validator->validateByPath($path . $dir);
                if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $cInstalledPlugins->contains($dir)) {
                    $xmlData['cVerzeichnis']    = $dir;
                    $xmlData['shop4compatible'] = isset($xmlData['jtlshop3plugin'][0]['Shop4Version']);
                    $plugins->index[$dir]       = \makeXMLToObj($xmlData);
                    $plugins->installiert[]     =& $plugins->index[$dir];
                } elseif ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK) {
                    $xmlData['cVerzeichnis']    = $dir;
                    $xmlData['shop4compatible'] = ($code === 1);
                    $plugins->index[$dir]       = \makeXMLToObj($xmlData);
                    $plugins->verfuegbar[]      =& $plugins->index[$dir];
                } elseif ($code !== InstallCode::OK && $code !== InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
                    $xmlData['cVerzeichnis'] = $dir;
                    $xmlData['cFehlercode']  = $code;
                    $plugins->index[$dir]    = \makeXMLToObj($xmlData);
                    $plugins->fehlerhaft[]   = &$plugins->index[$dir];
                }
            }
        }
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
