<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

use JTL\DB\DbInterface;
use JTL\MagicCompatibilityTrait;
use JTL\Plugin\PluginInterface;
use stdClass;

/**
 * Class PaymentMethod
 * @package JTL\Plugin\Data
 */
class PaymentMethod
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private int $methodID = 0;

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var string
     */
    private string $moduleID = '';

    /**
     * @var array
     */
    private array $customerGroups = [];

    /**
     * @var string
     */
    private string $template = '';

    /**
     * @var string
     */
    private string $templateFilePath = '';

    /**
     * @var string
     */
    private string $additionalTemplate = '';

    /**
     * @var string
     */
    private string $image = '';

    /**
     * @var int
     */
    private int $sort = 0;

    /**
     * @var bool
     */
    private bool $sendMail = false;

    /**
     * @var bool
     */
    private bool $active = false;

    /**
     * @var string
     */
    private string $provider = '';

    /**
     * @var string
     */
    private string $tsCode = '';

    /**
     * @var bool
     */
    private bool $duringOrder = false;

    /**
     * @var bool
     */
    private bool $useCurl = false;

    /**
     * @var bool
     */
    private bool $useSoap = false;

    /**
     * @var bool
     */
    private bool $useSockets = false;

    /**
     * @var bool
     */
    private bool $usable = false;

    /**
     * @var int
     */
    private int $pluginID = 0;

    /**
     * @var string
     */
    private string $classFile = '';

    /**
     * @var string
     */
    private string $className = '';

    /**
     * @var string
     */
    private string $templatePath = '';

    /**
     * @var array
     */
    private array $config = [];

    /**
     * @var array
     */
    private array $localization = [];

    /**
     * @var string
     */
    private string $classFilePath = '';

    /**
     * @var array
     */
    public static array $mapping = [
        'kZahlungsart'                    => 'MethodID',
        'cName'                           => 'Name',
        'cModulId'                        => 'ModuleID',
        'cKundengruppen'                  => 'CustomerGroups',
        'cPluginTemplate'                 => 'Template',
        'cZusatzschrittTemplate'          => 'AdditionalTemplate',
        'cBild'                           => 'Image',
        'nSort'                           => 'Sort',
        'nMailSenden'                     => 'SendMail',
        'nActive'                         => 'Active',
        'cAnbieter'                       => 'Provider',
        'cTSCode'                         => 'TsCode',
        'nWaehrendBestellung'             => 'DuringOrder',
        'nCURL'                           => 'UseCurl',
        'nSOAP'                           => 'UseSoap',
        'nSOCKETS'                        => 'UseSockets',
        'nNutzbar'                        => 'Usable',
        'kPlugin'                         => 'PluginID',
        'cClassPfad'                      => 'ClassFile',
        'cClassName'                      => 'ClassName',
        'cTemplatePfad'                   => 'TemplatePath',
        'oZahlungsmethodeEinstellung_arr' => 'Config',
        'oZahlungsmethodeSprache_arr'     => 'Localization',
        'cTemplateFileURL'                => 'TemplateFilePath',
    ];

    /**
     * PaymentMethod constructor.
     * @param stdClass|null        $data
     * @param PluginInterface|null $plugin
     */
    public function __construct(stdClass $data = null, PluginInterface $plugin = null)
    {
        if ($data !== null && \SAFE_MODE === false) {
            $this->mapData($data, $plugin);
        }
    }

    /**
     * @param DbInterface $db
     * @param string      $moduleId
     * @return PaymentMethod
     */
    public static function load(DbInterface $db, string $moduleId): self
    {
        $data = $db->selectSingleRow('tzahlungsart', 'cModulId', $moduleId);
        if ($data !== null) {
            $data->kZahlungsart = (int)$data->kZahlungsart;
            $data->nSort        = (int)$data->nSort;
            $data->nMailSenden  = (int)$data->nMailSenden;
            $data->nActive      = (int)$data->nActive;
            $data->nCURL        = (int)$data->nCURL;
            $data->nSOAP        = (int)$data->nSOAP;
            $data->nSOCKETS     = (int)$data->nSOCKETS;
            $data->nNutzbar     = (int)$data->nNutzbar;
        }

        return new self($data);
    }

    /**
     * @param stdClass             $data
     * @param PluginInterface|null $plugin
     */
    public function mapData(stdClass $data, PluginInterface $plugin = null): void
    {
        foreach (\get_object_vars($data) as $item => $value) {
            $method = self::$mapping[$item] ?? null;
            if ($method === null) {
                continue;
            }
            $method = 'set' . $method;
            $this->$method($value);
        }
        if ($plugin === null) {
            return;
        }
        $this->classFilePath = $plugin->getPaths()->getVersionedPath() . \PFAD_PLUGIN_PAYMENTMETHOD . $this->classFile;
        if (\file_exists($this->classFilePath)) {
            global $oPlugin;
            $oPlugin = $plugin;
            require_once $this->classFilePath;
            if (!\class_exists($this->className)) {
                $class = \sprintf(
                    'Plugin\\%s\\%s\\%s',
                    $plugin->getPluginID(),
                    \rtrim(\PFAD_PLUGIN_PAYMENTMETHOD, '/'),
                    $this->className
                );
                if (\class_exists($class)) {
                    $this->className = $class;
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getMethodID(): int
    {
        return $this->methodID;
    }

    /**
     * @param int $methodID
     */
    public function setMethodID(int $methodID): void
    {
        $this->methodID = $methodID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getModuleID(): string
    {
        return $this->moduleID;
    }

    /**
     * @param string $moduleID
     */
    public function setModuleID(string $moduleID): void
    {
        $this->moduleID = $moduleID;
    }

    /**
     * @return array
     */
    public function getCustomerGroups(): array
    {
        return $this->customerGroups;
    }

    /**
     * @param array|string $customerGroups
     */
    public function setCustomerGroups($customerGroups): void
    {
        if (\is_array($customerGroups)) {
            $this->customerGroups = $customerGroups;

            return;
        }

        $this->customerGroups = \array_map(static function ($item): int {
            return (int)$item;
        }, \array_filter(\explode(';', $customerGroups)));
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplateFilePath(): string
    {
        return $this->templateFilePath;
    }

    /**
     * @param string $templateFilePath
     */
    public function setTemplateFilePath(string $templateFilePath): void
    {
        $this->templateFilePath = $templateFilePath;
    }

    /**
     * @return string
     */
    public function getAdditionalTemplate(): string
    {
        return $this->additionalTemplate;
    }

    /**
     * @param string $additionalTemplate
     */
    public function setAdditionalTemplate(string $additionalTemplate): void
    {
        $this->additionalTemplate = $additionalTemplate;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return bool
     */
    public function getSendMail(): bool
    {
        return $this->sendMail;
    }

    /**
     * @param bool|int $sendMail
     */
    public function setSendMail($sendMail): void
    {
        $this->sendMail = (bool)$sendMail;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool|int $active
     */
    public function setActive($active): void
    {
        $this->active = (bool)$active;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getTsCode(): string
    {
        return $this->tsCode;
    }

    /**
     * @param string $tsCode
     */
    public function setTsCode(string $tsCode): void
    {
        $this->tsCode = $tsCode;
    }

    /**
     * @return bool
     */
    public function getDuringOrder(): bool
    {
        return $this->duringOrder;
    }

    /**
     * @param bool|int $duringOrder
     */
    public function setDuringOrder($duringOrder): void
    {
        $this->duringOrder = (bool)$duringOrder;
    }

    /**
     * @return bool
     */
    public function getUseCurl(): bool
    {
        return $this->useCurl;
    }

    /**
     * @param bool|int $useCurl
     */
    public function setUseCurl($useCurl): void
    {
        $this->useCurl = (bool)$useCurl;
    }

    /**
     * @return bool
     */
    public function getUseSoap(): bool
    {
        return $this->useSoap;
    }

    /**
     * @param bool|int $useSoap
     */
    public function setUseSoap($useSoap): void
    {
        $this->useSoap = (bool)$useSoap;
    }

    /**
     * @return bool
     */
    public function getUseSockets(): bool
    {
        return $this->useSockets;
    }

    /**
     * @param bool|int $useSockets
     */
    public function setUseSockets($useSockets): void
    {
        $this->useSockets = (bool)$useSockets;
    }

    /**
     * @return bool
     */
    public function getUsable(): bool
    {
        return $this->usable;
    }

    /**
     * @param bool|int $usable
     */
    public function setUsable($usable): void
    {
        $this->usable = (bool)$usable;
    }

    /**
     * @return int
     */
    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    /**
     * @param int $pluginID
     */
    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @return string
     */
    public function getClassFile(): string
    {
        return $this->classFile;
    }

    /**
     * @param string $classFile
     */
    public function setClassFile(string $classFile): void
    {
        $this->classFile = $classFile;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getLocalization(): array
    {
        return $this->localization;
    }

    /**
     * @param array $localization
     */
    public function setLocalization(array $localization): void
    {
        $this->localization = $localization;
    }

    /**
     * @return string
     */
    public function getClassFilePath(): string
    {
        return $this->classFilePath;
    }

    /**
     * @param string $classFilePath
     */
    public function setClassFilePath(string $classFilePath): void
    {
        $this->classFilePath = $classFilePath;
    }
}
