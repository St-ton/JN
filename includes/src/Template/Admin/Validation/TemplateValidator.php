<?php declare(strict_types=1);

namespace JTL\Template\Admin\Validation;

use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\Plugin\InstallCode;
use JTLShop\SemVer\Version;

/**
 * Class TemplateValidator
 * @package JTL\Template\Admin\Validation
 */
class TemplateValidator implements ValidatorInterface
{
    protected const BASE_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    public const RES_OK = 1;

    public const RES_XML_PARSE_ERROR = 2;

    public const RES_PARENT_NOT_FOUND = 3;

    public const RES_XML_NOT_FOUND = 4;

    public const RES_DIR_DOES_NOT_EXIST = 5;

    public const RES_SHOP_VERSION_NOT_FOUND = 6;

    public const RES_NAME_NOT_FOUND = 7;

    public const RES_INVALID_VERSION = 8;

    public const RES_INVALID_NAMESPACE = 9;

    /**
     * @var string
     */
    protected string $dir;

    /**
     * TemplateValidator constructor.
     * @param DbInterface $db
     */
    public function __construct(protected DbInterface $db)
    {
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
        $this->dir = \str_starts_with($dir, \PFAD_ROOT)
            ? $dir
            : self::BASE_DIR . $dir;
    }

    /**
     * @inheritdoc
     */
    public function validate(string $path, array $xml): int
    {
        $code = $this->validateByPath($path);
        if ($code === InstallCode::OK) {
            $code = $this->validateXML($xml);
        }

        return $code;
    }

    /**
     * @inheritdoc
     */
    public function validateByPath(string $path, bool $forUpdate = false): int
    {
        $this->setDir($path);
        if (empty($this->dir) || !\is_dir($this->dir)) {
            return self::RES_DIR_DOES_NOT_EXIST;
        }
        $infoXML = $this->dir . '/' . \TEMPLATE_XML;
        if (!\file_exists($infoXML)) {
            return self::RES_XML_NOT_FOUND;
        }
        if (\file_exists($this->dir . '/Bootstrap.php')) {
            $code  = \file_get_contents($this->dir . '/Bootstrap.php');
            $start = false;
            foreach (\token_get_all($code) as $token) {
                if (\is_array($token)) {
                    if ($token[0] === T_NAMESPACE) {
                        $start = true;
                    }
                    if ($start === true && $token[0] === T_NAME_QUALIFIED) {
                        $base = \pathinfo($path, \PATHINFO_BASENAME);
                        if ($token[1] !== 'Template\\' . $base) {
                            return self::RES_INVALID_NAMESPACE;
                        }
                        break;
                    }
                }
            }
        }

        return self::RES_OK;
    }

    /**
     * @param array $xml
     * @return int
     */
    public function validateXML(array $xml): int
    {
        $node = $xml['Template'][0] ?? null;
        if ($node === null) {
            return self::RES_XML_NOT_FOUND;
        }
        $parent = $node['Parent'] ?? null;
        if ($parent !== null) {
            $parent = \basename($parent);
            if (!\file_exists(\PFAD_ROOT . \PFAD_TEMPLATES . $parent . '/' . \TEMPLATE_XML)) {
                return self::RES_PARENT_NOT_FOUND;
            }
        }
        $minShopversion = $node['MinShopVersion'] ?? $node['ShopVersion'] ?? null;
        if ($minShopversion === null) {
            return self::RES_SHOP_VERSION_NOT_FOUND;
        }
        if (!isset($node['Name'])) {
            return self::RES_NAME_NOT_FOUND;
        }
        try {
            // all *version nodes have to be valid semver strings
            Version::parse($node['Version'] ?? '0');
            Version::parse($minShopversion);
            Version::parse($node['MaxShopVersion'] ?? '0.0.0');
        } catch (InvalidArgumentException) {
            return self::RES_INVALID_VERSION;
        }

        return self::RES_OK;
    }
}
