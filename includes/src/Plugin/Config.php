<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use function Functional\first;
use function Functional\group;

/**
 * Class Config
 * @package Plugin
 */
class Config
{
    /**
     * @var string
     */
    private $adminPath;

    /**
     * Config constructor.
     * @param string $adminPath
     */
    public function __construct(string $adminPath)
    {
        $this->adminPath = $adminPath;
    }

    /**
     * @param array $data
     * @return array
     */
    public function load(array $data): array
    {
        $grouped = group($data, function ($e) {
            return $e->id;
        });
        $options = [];
        foreach ($grouped as $values) {
            $base = first($values);
            $cfg              = new \stdClass();
            $cfg->id          = (int)$base->id;
            $cfg->menuID      = (int)$base->menuID;
            $cfg->name        = $base->name;
            $cfg->description = $base->description;
            $cfg->inputType   = $base->inputType;
            $cfg->sort        = (int)$base->nSort;
            $cfg->confType    = $base->confType;
            $cfg->sourceFile  = $base->sourceFile;
            $cfg->value       = $base->confType === 'M'
                ? \unserialize($base->currentValue, ['allowed_classes' => false])
                : $base->currentValue;
            $cfg->options     = [];
            if (!empty($cfg->sourceFile) && ($cfg->inputType === 'selectbox' || $cfg->inputType === 'radio')) {
                $cfg->options = $this->getDynamicOptions($cfg);
            } elseif (!($base->confValue === null && $base->confName === null)) {
                foreach ($values as $value) {
                    $opt        = new \stdClass();
                    $opt->name  = $value->confName;
                    $opt->value = $value->confValue;
                    $opt->sort  = (int)$value->confSort;

                    $cfg->options[] = $opt;
                }
            }
            $options[] = $cfg;
        }

        return $options;
    }

    /**
     * @param object $conf
     * @return null|array
     */
    public function getDynamicOptions($conf): ?array
    {
        $dynamicOptions = null;
        if (!empty($conf->cSourceFile) && \file_exists($this->adminPath . $conf->cSourceFile)) {
            $dynamicOptions = include $this->adminPath . $conf->cSourceFile;
            foreach ($dynamicOptions as $option) {
                $option->kPluginEinstellungenConf = $conf->kPluginEinstellungenConf;
                if (!isset($option->nSort)) {
                    $option->nSort = 0;
                }
            }
        }

        return $dynamicOptions;
    }
}
