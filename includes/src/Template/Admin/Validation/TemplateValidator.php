<?php declare(strict_types=1);

namespace JTL\Template\Admin\Validation;

use JTL\DB\DbInterface;
use JTL\Plugin\InstallCode;
use JTL\XMLParser;

/**
 * Class TemplateValidator
 * @package JTL\Template\Admin\Validation
 */
class TemplateValidator implements ValidatorInterface
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
        $this->dir = \mb_strpos($dir, \PFAD_ROOT) === 0
            ? $dir
            : self::BASE_DIR . $dir;
    }

    /**
     * @inheritdoc
     */
    public function validateByPath(string $path, bool $forUpdate = false): int
    {
//        echo '<br>' . __CLASS__ . ':' . __METHOD__ . ': setting path to ' . $path;
        $this->setDir($path);
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        if (!\is_dir($this->dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        $infoXML = $this->dir . '/' . \TEMPLATE_XML;
        if (!\file_exists($infoXML)) {
            return InstallCode::INFO_XML_MISSING;
        }

        return InstallCode::OK;//$this->pluginPlausiIntern($this->parser->parse($infoXML), $forUpdate);
    }
}
