<?php declare(strict_types=1);

namespace JTL\Backend;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Template\XMLReader;
use SimpleXMLElement;

/**
 * Class AdminTemplate
 * @package JTL\Backend
 */
class AdminTemplate
{
    /**
     * @var string
     */
    public static $cTemplate = 'bootstrap';

    /**
     * @var AdminTemplate|null
     */
    private static ?AdminTemplate $instance = null;

    /**
     * @var bool
     */
    private static bool $isAdmin = true;

    /**
     * @var string
     */
    public string $version = '1.0.0';

    /**
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(private readonly DbInterface $db, private readonly JTLCacheInterface $cache)
    {
        $this->init();
        self::$instance = $this;
    }

    /**
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     * @return self
     */
    public static function getInstance(?DbInterface $db = null, ?JTLCacheInterface $cache = null): self
    {
        return self::$instance ?? new self($db ?? Shop::Container()->getDB(), $cache ?? Shop::Container()->getCache());
    }

    /**
     * get template configuration
     *
     * @return bool
     */
    public function getConfig(): bool
    {
        return false;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getDir(bool $absolute = false): string
    {
        return $absolute
            ? (\PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . self::$cTemplate)
            : self::$cTemplate;
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        $cacheID = 'crnt_tpl_adm';
        if (($template = $this->cache->get($cacheID)) !== false) {
            self::$cTemplate = $template->cTemplate;
        } else {
            $template = $this->db->select('ttemplate', 'eTyp', 'admin');
            //dump('$oTemplate', $oTemplate);
            if ($template) {
                self::$cTemplate = $template->cTemplate;
                $this->cache->set($cacheID, $template, [\CACHING_GROUP_TEMPLATE]);

                return $this;
            }
        }

        return $this;
    }

    /**
     * get array of static resources in minify compatible format
     *
     * @param bool $absolute
     * @return array
     */
    public function getMinifyArray(bool $absolute = false): array
    {
        $dir       = $this->getDir();
        $folders   = [];
        $folders[] = $dir;
        $cacheID   = 'tpl_mnfy_dta_adm_' . $dir . (($absolute === true) ? '_a' : '');
        if (($tplGroups = $this->cache->get($cacheID)) === false) {
            $tplGroups = [
                'admin_css' => [],
                'admin_js'  => []
            ];
            $reader    = new XMLReader();
            foreach ($folders as $dir) {
                $xml = $reader->getXML($dir, true);
                if ($xml === null) {
                    continue;
                }
                $cssSource = $xml->Minify->CSS ?? [];
                $jsSource  = $xml->Minify->JS ?? [];
                /** @var SimpleXMLElement $css */
                foreach ($cssSource as $css) {
                    $name = (string)$css->attributes()->Name;
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
                    }
                    foreach ($css->File as $cssFile) {
                        $file     = (string)$cssFile->attributes()->Path;
                        $filePath = self::$isAdmin === false
                            ? \PFAD_ROOT . \PFAD_TEMPLATES . $xml->dir . '/' . $file
                            : \PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $xml->dir . '/' . $file;
                        if (\file_exists($filePath)) {
                            $tplGroups[$name][] = ($absolute === true ? \PFAD_ROOT : '') .
                                (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                                \PFAD_TEMPLATES . $dir . '/' . $cssFile->attributes()->Path;
                            $customFilePath     = \str_replace('.css', '_custom.css', $filePath);
                            if (\file_exists($customFilePath)) {
                                $tplGroups[$name][] = \str_replace(
                                    '.css',
                                    '_custom.css',
                                    ($absolute === true ? \PFAD_ROOT : '') .
                                    (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                                    \PFAD_TEMPLATES . $dir . '/' . $cssFile->attributes()->Path
                                );
                            }
                        }
                    }
                    // assign custom.css
                    $customFilePath = \PFAD_ROOT . 'templates/' . $xml->dir . '/themes/custom.css';
                    if (\file_exists($customFilePath)) {
                        $tplGroups[$name][] = (($absolute === true) ? \PFAD_ROOT : '') .
                            (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                            \PFAD_TEMPLATES . $dir . '/' . 'themes/custom.css';
                    }
                }
                foreach ($jsSource as $js) {
                    $name = (string)$js->attributes()->Name;
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
                    }
                    foreach ($js->File as $jsFile) {
                        $tplGroups[$name][] = ($absolute === true ? \PFAD_ROOT : '') .
                            (self::$isAdmin === true ? \PFAD_ADMIN : '') .
                            \PFAD_TEMPLATES . $dir . '/' . $jsFile->attributes()->Path;
                    }
                }
            }
            $cacheTags = [\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE, \CACHING_GROUP_PLUGIN];
            if (!self::$isAdmin) {
                \executeHook(\HOOK_CSS_JS_LIST, ['groups' => &$tplGroups, 'cache_tags' => &$cacheTags]);
            }
            $this->cache->set($cacheID, $tplGroups, $cacheTags);
        }

        return $tplGroups;
    }

    /**
     * build string to serve minified files or direct head includes
     *
     * @param bool $minify - generates absolute links for minify when true
     * @return array - list of js/css resources
     */
    public function getResources(bool $minify = true): array
    {
        self::$isAdmin = true;
        $outputCSS     = '';
        $outputJS      = '';
        $baseURL       = Shop::getURL();
        $files         = $this->getMinifyArray($minify);
        if ($minify === false) {
            $fileSuffix = '?v=' . $this->version;
            foreach ($files['admin_js'] as $_file) {
                $outputJS .= '<script type="text/javascript" src="'
                    . $baseURL . '/'
                    . $_file
                    . $fileSuffix
                    . '"></script>'
                    . "\n";
            }
            foreach ($files['admin_css'] as $_file) {
                $outputCSS .= '<link rel="stylesheet" type="text/css" href="'
                    . $baseURL . '/'
                    . $_file
                    . $fileSuffix
                    . '" media="screen" />'
                    . "\n";
            }
        } else {
            $tplString  = $this->getDir(); // add tpl string to avoid caching
            $fileSuffix = '&v=' . $this->version;
            $outputCSS  = '<link rel="stylesheet" type="text/css" href="'
                . $baseURL . '/'
                . \PFAD_MINIFY . '/index.php?g=admin_css&tpl='
                . $tplString
                . $fileSuffix
                . '" media="screen" />';
            $outputJS   = '<script type="text/javascript" src="'
                . $baseURL . '/'
                . \PFAD_MINIFY
                . '/index.php?g=admin_js&tpl='
                . $tplString
                . $fileSuffix
                . '"></script>';
        }

        return ['js' => $outputJS, 'css' => $outputCSS];
    }
}
