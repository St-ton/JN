<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Data;

use Plugin\Admin\InputType;
use Tightenco\Collect\Support\Collection;
use function Functional\first;
use function Functional\group;

/**
 * Class Config
 * @package Plugin
 */
class Config
{
    public const TYPE_NOT_CONFIGURABLE = 'N';

    public const TYPE_CONFIGURABLE = 'Y';

    public const TYPE_DYNAMIC = 'M';

    /**
     * @var string
     */
    private $adminPath;

    /**
     * @var Collection
     */
    private $options;

    /**
     * Config constructor.
     * @param string $adminPath
     */
    public function __construct(string $adminPath)
    {
        $this->adminPath = $adminPath;
        $this->options   = new Collection();
    }

    /**
     * @param array $data
     * @return Config
     */
    public function load(array $data): self
    {
        $grouped = group($data, function ($e) {
            return $e->id;
        });
        foreach ($grouped as $values) {
            $base             = first($values);
            $cfg              = new \stdClass();
            $cfg->id          = (int)$base->id;
            $cfg->valueID     = $base->confName;
            $cfg->menuID      = (int)$base->menuID;
            $cfg->niceName    = $base->name;
            $cfg->name        = $base->confNicename;
            $cfg->description = $base->description;
            $cfg->inputType   = $base->inputType;
            $cfg->sort        = (int)$base->nSort;
            $cfg->confType    = $base->confType;
            $cfg->sourceFile  = $base->sourceFile;
            $cfg->value       = $base->confType === self::TYPE_DYNAMIC
                ? \unserialize($base->currentValue, ['allowed_classes' => false])
                : $base->currentValue;
//            $cfg->raw         = $base;
            $cfg->options = [];
            if (!empty($cfg->sourceFile)
                && ($cfg->inputType === InputType::SELECT || $cfg->inputType === InputType::RADIO)
            ) {
                $cfg->options = $this->getDynamicOptions($cfg);
            } elseif (!($base->confValue === null && $base->confNicename === null)) {
                foreach ($values as $value) {
                    $opt           = new \stdClass();
                    $opt->niceName = $value->confNicename;
                    $opt->value    = $value->confValue;
                    $opt->sort     = (int)$value->confSort;

                    $cfg->options[] = $opt;
                }
            }
            $this->options->push($cfg);
        }

        return $this;
    }

    /**
     * @param object $conf
     * @return null|array
     */
    public function getDynamicOptions($conf): ?array
    {
        $dynamicOptions = null;
        if (!empty($conf->sourceFile) && \file_exists($this->adminPath . $conf->sourceFile)) {
            $dynamicOptions = include $this->adminPath . $conf->sourceFile;
            foreach ($dynamicOptions as $option) {
                $option->kPluginEinstellungenConf = $conf->id;
                $option->id                       = $conf->id;
                $option->niceName                 = $option->cName;
                $option->value                    = $option->cWert;
                if (!isset($option->nSort)) {
                    $option->nSort = 0;
                }
                if (!isset($option->sort)) {
                    $option->sort = $option->nSort;
                }
            }
        }

        return $dynamicOptions;
    }

    /**
     * @param string $name
     * @return \stdClass|null
     */
    public function getOption(string $name): ?\stdClass
    {
        return $this->options->first(function (\stdClass $item) use ($name) {
            return $item->valueID === $name;
        });
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue(string $name)
    {
        $item = $this->options->first(function (\stdClass $item) use ($name) {
            return $item->valueID === $name;
        });

        return $item->value ?? null;
    }

    /**
     * @return string
     */
    public function getAdminPath(): string
    {
        return $this->adminPath;
    }

    /**
     * @param string $adminPath
     */
    public function setAdminPath(string $adminPath): void
    {
        $this->adminPath = $adminPath;
    }

    /**
     * @return Collection
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * @param Collection $options
     */
    public function setOptions(Collection $options): void
    {
        $this->options = $options;
    }
}
