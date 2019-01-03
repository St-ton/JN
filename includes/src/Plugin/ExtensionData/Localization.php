<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\ExtensionData;

use Tightenco\Collect\Support\Collection;
use function Functional\first;
use function Functional\group;

/**
 * Class Localization
 * @package Plugin\ExtensionData
 */
class Localization
{
    /**
     * @var Collection
     */
    private $langVars;

    /**
     * @var string
     */
    private $currentLanguageCode;

    /**
     * Localization constructor.
     * @param string $currentLanguageCode
     */
    public function __construct(string $currentLanguageCode)
    {
        $this->langVars            = new Collection();
        $this->currentLanguageCode = $currentLanguageCode;
    }

    /**
     * @param array $data
     * @return Localization
     */
    public function load(array $data): self
    {
        $grouped = group($data, function ($e) {
            return $e->kPluginSprachvariable;
        });
        foreach ($grouped as $group) {
            $lv                                    = first($group);
            $var                                   = new \stdClass();
            $var->kPluginSprachvariable            = (int)$lv->kPluginSprachvariable;
            $var->id                               = $var->kPluginSprachvariable;
            $var->kPlugin                          = (int)$lv->kPlugin;
            $var->pluginID                         = $var->kPlugin;
            $var->cName                            = $lv->cName;
            $var->name                             = $var->cName;
            $var->cBeschreibung                    = $lv->cBeschreibung;
            $var->description                      = $var->cBeschreibung;
            $var->oPluginSprachvariableSprache_arr = [$lv->cISO => $lv->customValue];
            $var->values                           = $var->oPluginSprachvariableSprache_arr;
            foreach ($group as $translation) {
                $var->oPluginSprachvariableSprache_arr[$translation->cISO] = $translation->customValue;
                $var->values[$translation->cISO]                           = $translation->customValue;
            }
            $this->langVars->push($var);
        }

        return $this;
    }

    /**
     * @param string      $name
     * @param string|null $iso
     * @return string|null
     */
    public function getTranslation(string $name, string $iso = null): ?string
    {
        $iso   = \strtoupper($iso ?? $this->currentLanguageCode);
        $first = $this->langVars->firstWhere('name', $name);

        return $first->values[$iso] ?? null;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        $iso = \strtoupper($this->currentLanguageCode);

        return $this->langVars->mapWithKeys(function ($item) use ($iso) {
            return [$item->name => $item->values[$iso] ?? null];
        })->toArray();
    }

    /**
     * compatability dummy
     */
    public function setTranslations(): void
    {
    }

    /**
     * @return array
     */
    public function getLangVarsCompat(): array
    {
        return $this->langVars->toArray();
    }

    /**
     * @return Collection
     */
    public function getLangVars(): Collection
    {
        return $this->langVars;
    }

    /**
     * @param Collection $langVars
     */
    public function setLangVars(Collection $langVars): void
    {
        $this->langVars = $langVars;
    }

    /**
     * @return string
     */
    public function getCurrentLanguageCode(): string
    {
        return $this->currentLanguageCode;
    }

    /**
     * @param string $currentLanguageCode
     */
    public function setCurrentLanguageCode(string $currentLanguageCode): void
    {
        $this->currentLanguageCode = $currentLanguageCode;
    }
}
