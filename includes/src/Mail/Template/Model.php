<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use JTL\Helpers\Text;
use JTL\Shop;
use stdClass;

/**
 * Class Model
 * @package JTL\Mail\Template
 */
final class Model
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $moduleID;

    /**
     * @var array[]
     */
    protected $fileNames = [];

    /**
     * @var bool
     */
    protected $active = true;

    /**
     * @var bool
     */
    protected $showAKZ = true;

    /**
     * @var bool
     */
    protected $showAGB = true;

    /**
     * @var bool
     */
    protected $showWRB = true;

    /**
     * @var bool
     */
    protected $showWRBForm = true;

    /**
     * @var bool
     */
    protected $showDSE = true;

    /**
     * @var bool
     */
    protected $hasError = false;

    /**
     * @var int
     */
    protected $languageID = 0;

    /**
     * @var int
     */
    protected $pluginID = 0;

    /**
     * @var array
     */
    protected $subject;

    /**
     * @var array
     */
    protected $html = [];

    /**
     * @var array
     */
    protected $text = [];

    /**
     * @var array
     */
    protected $attachments = [];

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected static $mapping = [
        'kEmailvorlage' => 'ID',
        'cName'         => 'Name',
        'cBeschreibung' => 'Description',
        'cMailTyp'      => 'Type',
        'cModulId'      => 'ModuleID',
        'cDateiname'    => 'FileName',
        'cAktiv'        => 'Active',
        'nAKZ'          => 'ShowAKZ',
        'nAGB'          => 'ShowAGB',
        'nWRB'          => 'ShowWRB',
        'nWRBForm'      => 'ShowWRBForm',
        'nFehlerhaft'   => 'HasError',
        'nDSE'          => 'ShowDSE',
        'kSprache'      => 'LanguageID',
        'cBetreff'      => 'Subject',
        'cContentHtml'  => 'HTML',
        'cContentText'  => 'Text',
        'cPDFS'         => 'Attachments',
    ];

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
     * @param int $languageID
     * @return array
     */
    public function getFileNames(int $languageID = null): array
    {
        return $this->fileNames[$languageID ?? Shop::getLanguageID()] ?? [];
    }

    /**
     * @return array
     */
    public function getAllFileNames(): array
    {
        return $this->fileNames;
    }

    /**
     * @param array|string $fileNames
     * @param int          $languageID
     */
    public function setFileNames($fileNames, int $languageID): void
    {
        $this->fileNames[$languageID] = \is_string($fileNames)
            ? Text::parseSSK($fileNames)
            : $fileNames;
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
        if ($active === 'N') {
            $active = false;
        } elseif ($active === 'Y') {
            $active = true;
        }
        $this->active = (bool)$active;
    }

    /**
     * @return bool
     */
    public function getShowAKZ(): bool
    {
        return $this->showAKZ;
    }

    /**
     * @param bool|int $showAKZ
     */
    public function setShowAKZ($showAKZ): void
    {
        $this->showAKZ = (bool)$showAKZ;
    }

    /**
     * @return bool
     */
    public function getShowAGB(): bool
    {
        return $this->showAGB;
    }

    /**
     * @param bool|int $show
     */
    public function setShowAGB($show): void
    {
        $this->showAGB = (bool)$show;
    }

    /**
     * @return bool
     */
    public function getShowWRB(): bool
    {
        return $this->showWRB;
    }

    /**
     * @param bool|int $show
     */
    public function setShowWRB($show): void
    {
        $this->showWRB = (bool)$show;
    }

    /**
     * @return bool
     */
    public function getShowWRBForm(): bool
    {
        return $this->showWRBForm;
    }

    /**
     * @param bool|int $show
     */
    public function setShowWRBForm($show): void
    {
        $this->showWRBForm = (bool)$show;
    }

    /**
     * @return bool
     */
    public function getShowDSE(): bool
    {
        return $this->showDSE;
    }

    /**
     * @param bool|int $show
     */
    public function setShowDSE($show): void
    {
        $this->showDSE = (bool)$show;
    }

    /**
     * @return bool
     */
    public function getHasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @param bool|int $hasError
     */
    public function setHasError($hasError): void
    {
        $this->hasError = (bool)$hasError;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int $languageID
     */
    public function setLanguageID(int $languageID): void
    {
        $this->languageID = $languageID;
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
     * @return array
     */
    public function getSubjects(): array
    {
        return $this->subject;
    }

    /**
     * @param int|null $languageID
     * @return string
     */
    public function getSubject(int $languageID = null): string
    {
        return $this->subject[$languageID ?? Shop::getLanguageID()] ?? '';
    }

    /**
     * @param string $subject
     * @param int    $languageID
     */
    public function setSubject(string $subject, int $languageID): void
    {
        $this->subject[$languageID] = $subject;
    }

    /**
     * @return array
     */
    public function getAllHTML(): array
    {
        return $this->html;
    }

    /**
     * @param int|null $languageID
     * @return string
     */
    public function getHTML(int $languageID = null): string
    {
        return $this->html[$languageID ?? Shop::getLanguageID()] ?? '';
    }

    /**
     * @param string $html
     * @param int    $languageID
     */
    public function setHTML(string $html, int $languageID): void
    {
        $this->html[$languageID] = $html;
    }

    /**
     * @return array
     */
    public function getAllText(): array
    {
        return $this->text;
    }

    /**
     * @param int|null $languageID
     * @return string
     */
    public function getText(int $languageID = null): string
    {
        return $this->text[$languageID ?? Shop::getLanguageID()] ?? '';
    }

    /**
     * @param string $text
     * @param int    $languageID
     */
    public function setText(string $text, int $languageID): void
    {
        $this->text[$languageID] = $text;
    }

    /**
     * @param int|null $languageID
     * @return array|null
     */
    public function getAttachments(int $languageID = null): ?array
    {
        return $this->attachments[$languageID ?? Shop::getLanguageID()] ?? [];
    }

    /**
     * @return array
     */
    public function getAllAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param string|array|null $attachments
     * @param int               $languageID
     */
    public function setAttachments($attachments, int $languageID): void
    {
        $this->attachments[$languageID] = \is_string($attachments)
            ? Text::parseSSK($attachments)
            : $attachments;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * this is only useful for revisions
     *
     * @return array
     */
    public function viewCompat(): array
    {
        $res = [];
        foreach ($this->html as $langID => $data) {
            $item                = new stdClass();
            $item->kEmailvorlage = $this->getID();
            $item->cBetreff      = $this->getSubject($langID);
            $item->cContentHtml  = $this->getHTML($langID);
            $item->cContentText  = $this->getText($langID);
            $res[$langID]        = $item;
        }

        return $res;
    }
}
