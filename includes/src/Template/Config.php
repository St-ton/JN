<?php declare(strict_types=1);

namespace JTL\Template;

use JTL\DB\DbInterface;
use stdClass;

/**
 * Class Config
 * @package JTL\Template\Admin
 */
class Config
{
    /**
     * @var string
     */
    private string $currentTemplateDir;

    /**
     * @var DbInterface|null - this class can be cached so $db will be NULL after __wakeup()
     */
    private ?DbInterface $db;

    /**
     * Config constructor.
     * @param string      $currentTemplateDir
     * @param DbInterface $db
     */
    public function __construct(string $currentTemplateDir, DbInterface $db)
    {
        $this->currentTemplateDir = $currentTemplateDir;
        $this->db                 = $db;
    }

    /**
     * @param XMLReader   $reader
     * @param string|null $parentFolder
     * @return array
     */
    public function getConfigXML(XMLReader $reader, ?string $parentFolder = null): array
    {
        $configValues = $this->loadConfigFromDB();
        $configXML    = $reader->getConfigXML($this->currentTemplateDir, $parentFolder);
        foreach ($configXML as $section) {
            foreach ($section->settings as $setting) {
                if ($setting->isEditable && isset($configValues[$section->key][$setting->key])) {
                    $setting->value = $configValues[$section->key][$setting->key];
                }
            }
        }

        return $configXML;
    }

    /**
     * @return array
     */
    public function loadConfigFromDB(): array
    {
        $settingsData = $this->db->selectAll('ttemplateeinstellungen', 'cTemplate', $this->currentTemplateDir);
        $settings     = [];
        foreach ($settingsData as $setting) {
            if (isset($settings[$setting->cSektion]) && !\is_array($settings[$setting->cSektion])) {
                $settings[$setting->cSektion] = [];
            }
            $settings[$setting->cSektion][$setting->cName] = $setting->cWert;
        }

        return $settings;
    }

    /**
     * set template configuration
     *
     * @param string $section
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function updateConfigInDB(string $section, string $name, $value): self
    {
        $config = $this->db->select(
            'ttemplateeinstellungen',
            'cTemplate',
            $this->currentTemplateDir,
            'cSektion',
            $section,
            'cName',
            $name
        );
        if ($config !== null && isset($config->cTemplate)) {
            $this->db->update(
                'ttemplateeinstellungen',
                ['cTemplate', 'cSektion', 'cName'],
                [$this->currentTemplateDir, $section, $name],
                (object)['cWert' => $value]
            );
        } else {
            $ins            = new stdClass();
            $ins->cTemplate = $this->currentTemplateDir;
            $ins->cSektion  = $section;
            $ins->cName     = $name;
            $ins->cWert     = $value;
            $this->db->insert('ttemplateeinstellungen', $ins);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentTemplateDir(): string
    {
        return $this->currentTemplateDir;
    }

    /**
     * @param string $currentTemplateDir
     */
    public function setCurrentTemplateDir(string $currentTemplateDir): void
    {
        $this->currentTemplateDir = $currentTemplateDir;
    }

    /**
     * @return DbInterface|null
     */
    public function getDB(): ?DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }
}
