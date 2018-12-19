<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation;

use DB\DbInterface;
use JTL\XMLParser;
use Plugin\InstallCode;

/**
 * Class AbstractValidator
 * @package Plugin\Admin\Validation
 */
abstract class AbstractValidator implements ValidatorInterface
{
    protected const BASE_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var string
     */
    protected $dir;

    /**
     * @var XMLParser
     */
    protected $parser;

    /**
     * AbstractValidator constructor.
     * @param DbInterface $db
     * @param XMLParser   $parser
     */
    public function __construct(DbInterface $db, XMLParser $parser)
    {
        $this->db     = $db;
        $this->parser = $parser;
    }

    /**
     * @inheritdoc
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @inheritdoc
     */
    public function setDir(string $dir): void
    {
        $this->dir = \strpos($dir, \PFAD_ROOT) === 0
            ? $dir
            : self::BASE_DIR . $dir;
    }

    /**
     * @inheritdoc
     */
    public function validateByPath(string $path, bool $forUpdate = false): int
    {
        $this->setDir($path);
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        if (!\is_dir($this->dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        $infoXML = $this->dir . '/' . \PLUGIN_INFO_FILE;
        if (!\file_exists($infoXML)) {
            return InstallCode::INFO_XML_MISSING;
        }

        return $this->pluginPlausiIntern($this->parser->parse($infoXML), $forUpdate);
    }

    /**
     * @inheritdoc
     */
    public function validateByPluginID(int $kPlugin, bool $forUpdate = false): int
    {
        $plugin = $this->db->select('tplugin', 'kPlugin', $kPlugin);
        if (empty($plugin->kPlugin)) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        $dir  = self::BASE_DIR . $plugin->cVerzeichnis;
        $info = $dir . '/' . \PLUGIN_INFO_FILE;
        $this->setDir($dir);
        if (!\is_dir($dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        if (!\file_exists($info)) {
            return InstallCode::INFO_XML_MISSING;
        }

        return $this->pluginPlausiIntern($this->parser->parse($info), $forUpdate);
    }
}
