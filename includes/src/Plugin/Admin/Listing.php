<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use JTL\XMLParser;
use Mapper\PluginValidation;
use Plugin\InstallCode;
use Plugin\Plugin;
use Tightenco\Collect\Support\Collection;
use function Functional\map;

/**
 * Class Listing
 * @package Plugin\Admin
 */
final class Listing
{
    private const PLUGINS_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

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
        $mapper    = new PluginValidation();
        $plugins   = new Collection();
        $pluginIDs = map(
            $this->db->selectAll('tplugin', [], [], 'kPlugin', 'cName, cAutor, nPrio'),
            function (\stdClass $e) {
                return (int)$e->kPlugin;
            }
        );
        foreach ($pluginIDs as $pluginID) {
            $plugin = new Plugin($pluginID, true);
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
            $info = $fileinfo->getPathname() . '/' . \PLUGIN_INFO_FILE;
            if (!\file_exists($info)) {
                continue;
            }
            $parser = new XMLParser();
            $xml    = $parser->parse($info);
            $code   = $this->validator->validateByPath(self::PLUGINS_DIR . $dir);
            if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $installedPlugins->contains($dir)) {
                $xml['cVerzeichnis']    = $dir;
                $xml['shop4compatible'] = isset($xml['jtlshop3plugin'][0]['Shop4Version']);
                $plugins->index[$dir]       = $this->makeXMLToObj($xml);
                $plugins->installiert[]     = &$plugins->index[$dir];
            } elseif ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK) {
                $xml['cVerzeichnis']    = $dir;
                $xml['shop4compatible'] = ($code === 1);
                $plugins->index[$dir]       = $this->makeXMLToObj($xml);
                $plugins->verfuegbar[]      = &$plugins->index[$dir];
            } else {
                $xml['cVerzeichnis'] = $dir;
                $xml['cFehlercode']  = $code;
                $plugins->index[$dir]    = $this->makeXMLToObj($xml);
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

    /**
     * Baut aus einer XML ein Objekt
     *
     * @param array $XML
     * @return \stdClass
     */
    private function makeXMLToObj($XML): \stdClass
    {
        $res = new \stdClass();
        if (isset($XML['jtlshop3plugin']) && \is_array($XML['jtlshop3plugin'])) {
            if (!isset($XML['jtlshop3plugin'][0]['Install'][0]['Version'])) {
                return $res;
            }
            if (!isset($XML['jtlshop3plugin'][0]['Name'])) {
                return $res;
            }
            $node        = $XML['jtlshop3plugin'][0];
            $lastVersion = \count($node['Install'][0]['Version']) / 2 - 1;

            $res->cName           = $node['Name'];
            $res->cDescription    = $node['Description'] ?? '';
            $res->cAuthor         = $node['Author'] ?? '';
            $res->cPluginID       = $node['PluginID'];
            $res->cIcon           = $node['Icon'] ?? null;
            $res->cVerzeichnis    = $XML['cVerzeichnis'];
            $res->shop4compatible = !empty($XML['shop4compatible'])
                ? $XML['shop4compatible']
                : false;
            $res->nVersion        = $lastVersion >= 0
            && isset($node['Install'][0]['Version'][$lastVersion . ' attr']['nr'])
                ? (int)$node['Install'][0]['Version'][$lastVersion . ' attr']['nr']
                : 0;
            $res->cVersion        = \number_format($res->nVersion / 100, 2);
        }

        if (empty($res->cName) && empty($res->cDescription) && !empty($XML['cVerzeichnis'])) {
            $res->cName        = $XML['cVerzeichnis'];
            $res->cDescription = '';
            $res->cVerzeichnis = $XML['cVerzeichnis'];
        }
        if (isset($XML['cFehlercode']) && \strlen($XML['cFehlercode']) > 0) {
            $mapper                   = new PluginValidation();
            $res->cFehlercode         = $XML['cFehlercode'];
            $res->cFehlerBeschreibung = $mapper->map($XML['cFehlercode'], $res);
        }

        return $res;
    }
}
