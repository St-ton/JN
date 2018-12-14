<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\ExtensionData;

use function Functional\reindex;

/**
 * Class PaymentMethods
 * @package Plugin\ExtensionData
 */
class PaymentMethods
{
    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var array
     */
    private $classes = [];

    /**
     * @param array  $data
     * @param string $path
     * @return $this
     */
    public function load(array $data, string $path): self
    {
        foreach ($data as $method) {
            $method->kZahlungsart           = (int)$method->kZahlungsart;
            $method->nSort                  = (int)$method->nSort;
            $method->nMailSenden            = (int)$method->nMailSenden;
            $method->nActive                = (int)$method->nActive;
            $method->nCURL                  = (int)$method->nCURL;
            $method->nSOAP                  = (int)$method->nSOAP;
            $method->nSOCKETS               = (int)$method->nSOCKETS;
            $method->nNutzbar               = (int)$method->nNutzbar;
            $method->kPlugin                = (int)$method->kPlugin;
            $method->cZusatzschrittTemplate = \strlen($method->cZusatzschrittTemplate)
                ? $path . \PFAD_PLUGIN_PAYMENTMETHOD . $method->cZusatzschrittTemplate
                : '';
            $method->cTemplateFileURL       = \strlen($method->cPluginTemplate)
                ? $path . \PFAD_PLUGIN_PAYMENTMETHOD . $method->cPluginTemplate
                : '';
            foreach ($method->oZahlungsmethodeEinstellung_arr as $conf) {
                $conf->kPluginEinstellungenConf = (int)$conf->kPluginEinstellungenConf;
                $conf->kPlugin                  = (int)$conf->kPlugin;
                $conf->kPluginAdminMenu         = (int)$conf->kPluginAdminMenu;
                $conf->nSort                    = (int)$conf->nSort;
            }
            foreach ($method->oZahlungsmethodeSprache_arr as $loc) {
                $loc->kZahlungsart = (int)$loc->kZahlungsart;
            }
            $class                           = new \stdClass();
            $class->cModulId                 = $method->cModulId;
            $class->kPlugin                  = $method->kPlugin;
            $class->cClassPfad               = $method->cClassPfad;
            $class->cClassName               = $method->cClassName;
            $class->cTemplatePfad            = $method->cTemplatePfad;
            $class->cZusatzschrittTemplate   = $method->cZusatzschrittTemplate;
            $this->classes[$class->cModulId] = $class;
        }
        $this->methods = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethodsAssoc(): array
    {
        return reindex($this->methods, function ($e) {
            return $e->cModulId;
        });
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     */
    public function setClasses(array $classes): void
    {
        $this->classes = $classes;
    }
}
