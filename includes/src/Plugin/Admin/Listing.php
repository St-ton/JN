<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use JTL\XMLParser;
use Mapper\PluginValidation;
use Plugin\Admin\Validation\ValidatorInterface;
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

    private const NEW_PLUGINS_DIR = \PFAD_ROOT . 'plugins' . \DIRECTORY_SEPARATOR;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ValidatorInterface
     */
    private $modernValidator;

    /**
     * Listing constructor.
     * @param DbInterface        $db
     * @param ValidatorInterface $validator
     * @param ValidatorInterface $modernValidator
     */
    public function __construct(DbInterface $db, ValidatorInterface $validator, ValidatorInterface $modernValidator)
    {
        $this->db              = $db;
        $this->validator       = $validator;
        $this->modernValidator = $modernValidator;
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
            $plugin                  = new Plugin($pluginID, true);
            $plugin->updateAvailable = $plugin->nVersion < $plugin->getCurrentVersion();
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
        $parser           = new XMLParser();
        $this->getOldPlugins($parser, $plugins, $installedPlugins);
        $this->getNewPlugins($parser, $plugins, $installedPlugins);

        return $this->sort($plugins);
    }

    /**
     * @param XMLParser  $parser
     * @param \stdClass  $plugins
     * @param Collection $installedPlugins
     * @return \stdClass
     */
    private function getOldPlugins(XMLParser $parser, \stdClass $plugins, $installedPlugins): \stdClass
    {
        foreach (new \DirectoryIterator(self::PLUGINS_DIR) as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $dir  = $fileinfo->getBasename();
            $info = $fileinfo->getPathname() . '/' . \PLUGIN_INFO_FILE;
            if (!\file_exists($info)) {
                continue;
            }
            $xml  = $parser->parse($info);
            $code = $this->validator->validateByPath(self::PLUGINS_DIR . $dir);
            if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $installedPlugins->contains($dir)) {
                $xml['cVerzeichnis']    = $dir;
                $xml['shop4compatible'] = isset($xml['jtlshop3plugin'][0]['Shop4Version']);
                $plugins->index[$dir]   = $this->makeXMLToObj($xml);
                $plugins->installiert[] = &$plugins->index[$dir];
            } elseif ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK) {
                $xml['cVerzeichnis']    = $dir;
                $xml['shop4compatible'] = ($code === 1);
                $plugins->index[$dir]   = $this->makeXMLToObj($xml);
                $plugins->verfuegbar[]  = &$plugins->index[$dir];
            } else {
                $xml['cVerzeichnis']   = $dir;
                $xml['cFehlercode']    = $code;
                $plugins->index[$dir]  = $this->makeXMLToObj($xml);
                $plugins->fehlerhaft[] = &$plugins->index[$dir];
            }
        }

        return $plugins;
    }

    /**
     * @param XMLParser  $parser
     * @param \stdClass  $plugins
     * @param Collection $installedPlugins
     * @return \stdClass
     */
    private function getNewPlugins(XMLParser $parser, \stdClass $plugins, $installedPlugins): \stdClass
    {
        foreach (new \DirectoryIterator(self::NEW_PLUGINS_DIR) as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $dir  = $fileinfo->getBasename();
            $info = $fileinfo->getPathname() . '/' . \PLUGIN_INFO_FILE;
            if (!\file_exists($info)) {
                continue;
            }
            $xml  = $parser->parse($info);
            $code = $this->modernValidator->validateByPath(self::NEW_PLUGINS_DIR . $dir);
            if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $installedPlugins->contains($dir)) {
                $xml['cVerzeichnis']    = $dir;
                $xml['shop4compatible'] = isset($xml['jtlshop3plugin'][0]['Shop4Version']);
                $xml['shop5compatible'] = isset($xml['jtlshopplugin'][0]['ShopVersion']);
                $plugins->index[$dir]   = $this->makeXMLToObj($xml);
                $plugins->installiert[] = &$plugins->index[$dir];
            } elseif ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK) {
                $xml['cVerzeichnis']    = $dir;
                $xml['shop4compatible'] = $code === InstallCode::OK;
                $xml['shop5compatible'] = isset($xml['jtlshopplugin'][0]['ShopVersion']);
                $plugins->index[$dir]   = $this->makeXMLToObj($xml);
                $plugins->verfuegbar[]  = &$plugins->index[$dir];
            } else {
                $xml['cVerzeichnis']    = $dir;
                $xml['cFehlercode']     = $code;
                $xml['shop4compatible'] = false;
                $xml['shop5compatible'] = false;
                $plugins->index[$dir]   = $this->makeXMLToObj($xml);
                $plugins->fehlerhaft[]  = &$plugins->index[$dir];
            }
        }

        return $plugins;
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
     * @param array $xml
     * @return \stdClass
     */
    private function makeXMLToObj($xml): \stdClass
    {
        $res                  = new \stdClass();
        $node                 = null;
        $res->shop4compatible = false;
        $res->shop5compatible = false;
        $res->cName           = $xml['cVerzeichnis'];
        $res->cDescription    = '';
        $res->cVerzeichnis    = $xml['cVerzeichnis'];
        if (isset($xml['jtlshopplugin']) && \is_array($xml['jtlshopplugin'])) {
            $node                 = $xml['jtlshopplugin'][0];
            $res->shop5compatible = true;
        } elseif (isset($xml['jtlshop3plugin']) && \is_array($xml['jtlshop3plugin'])) {
            $node = $xml['jtlshop3plugin'][0];
        }
        if ($node !== null) {
            if (!isset($node['Install'][0]['Version'])) {
                return $res;
            }
            if (!isset($node['Name'])) {
                return $res;
            }
            $res->cName           = $node['Name'];
            $res->cDescription    = $node['Description'] ?? '';
            $res->cAuthor         = $node['Author'] ?? '';
            $res->cPluginID       = $node['PluginID'];
            $res->cIcon           = $node['Icon'] ?? null;
            $res->cVerzeichnis    = $xml['cVerzeichnis'];
            $res->shop4compatible = !empty($xml['shop4compatible'])
                ? $xml['shop4compatible']
                : false;
            if (\is_array($node['Install'][0]['Version'])) {
                $lastVersion   = \count($node['Install'][0]['Version']) / 2 - 1;
                $res->nVersion = $lastVersion >= 0
                && isset($node['Install'][0]['Version'][$lastVersion . ' attr']['nr'])
                    ? (int)$node['Install'][0]['Version'][$lastVersion . ' attr']['nr']
                    : 0;
                $res->cVersion = \number_format($res->nVersion / 100, 2);
            } else {
                $res->cVersion = $node['Install'][0]['Version'];
            }
        }
        if (!empty($xml['cFehlercode'])) {
            $mapper                   = new PluginValidation();
            $res->cFehlercode         = $xml['cFehlercode'];
            $res->cFehlerBeschreibung = $mapper->map($xml['cFehlercode'], $res);
        }

        return $res;
    }
}
