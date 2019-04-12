<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use function Functional\first;
use function Functional\map;
use function Functional\tail;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
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
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $moduleID;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @var bool
     */
    private $showAKZ = true;

    /**
     * @var bool
     */
    private $showAGB = true;

    /**
     * @var bool
     */
    private $showWRB = true;

    /**
     * @var bool
     */
    private $showWRBForm = true;

    /**
     * @var bool
     */
    private $showDSE = true;

    /**
     * @var bool
     */
    private $hasError = false;

    /**
     * @var int
     */
    private $languageID = 0;

    /**
     * @var int
     */
    private $pluginID = 0;

    /**
     * @var array
     */
    private $subject;

    /**
     * @var array
     */
    private $html = [];

    /**
     * @var array
     */
    private $text = [];

    /**
     * @var array
     */
    private $attachments = [];

    /**
     * @var array
     */
    private $attachmentNames = [];

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private static $mapping = [
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
        'cPDFNames'     => 'AttachmentNames',
        'kPlugin'       => 'PluginID',
    ];

    /**
     * Model constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param string|null $type
     * @return string|array|null
     */
    public function getMapping(string $type = null)
    {
        return $type === null
            ? self::$mapping
            : self::$mapping[$type] ?? null;
    }

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
    public function setID($id): void
    {
        $this->id = (int)$id;
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
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName ?? '';
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
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
    public function setLanguageID($languageID): void
    {
        $this->languageID = (int)$languageID;
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
    public function setPluginID($pluginID): void
    {
        $this->pluginID = (int)$pluginID;
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
     * @param int|null $languageID
     * @return array|null
     */
    public function getAttachmentNames(int $languageID = null): ?array
    {
        return $this->attachmentNames[$languageID ?? Shop::getLanguageID()] ?? [];
    }

    /**
     * @return array
     */
    public function getAllAttachmentNames(): array
    {
        return $this->attachmentNames;
    }

    /**
     * @param string|array|null $names
     * @param int               $languageID
     */
    public function setAttachmentNames($names, int $languageID): void
    {
        $this->attachmentNames[$languageID] = \is_string($names)
            ? Text::parseSSK($names)
            : $names;
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
     * @return int
     */
    public function save(): int
    {
        $res = 0;
        foreach ($this->text as $langID => $text) {
            $updates = ['id' => $this->getID(), 'lid' => $langID];
            $sets    = [];
            foreach (self::$mapping as $field => $method) {
                $method          = 'get' . $method;
                $data            = $this->$method($langID);
                $updates[$field] = \is_array($data) ? Text::createSSK($data) : $data;
                $sets[$field]    = $field . ' = :' . $field;
            }
            if ($this->getPluginID() === 0) {
                unset($updates['kPlugin'], $sets['kPlugin']);
            }
            unset($updates['kEmailvorlage'], $updates['kSprache'], $sets['kEmailvorlage'], $sets['kSprache']);
            $res += $this->db->queryPrepared(
                'UPDATE temailvorlage a
                    LEFT JOIN temailvorlagesprache b
                        ON a.kEmailvorlage = b.kEmailvorlage
                    SET ' . \implode(', ', $sets) . '
                    WHERE a.kEmailvorlage = :id
                        AND b.kSprache = :lid',
                $updates,
                ReturnType::AFFECTED_ROWS
            );
        }

        return $res;
    }

    /**
     * @param string $templateID
     * @return $this|null
     */
    public function load(string $templateID): ?self
    {
        $data = $this->loadFromDB($templateID);
        if ($data === null) {
            return null;
        }
        $arrayRows = ['cBetreff', 'cContentHtml', 'cContentText', 'cPDFS', 'cPDFNames'];
        $res       = first($data);
        foreach (tail($data) as $item) {
            $keys = \get_object_vars($item);
            foreach ($keys as $k => $v) {
                if (\in_array($k, $arrayRows, true)) {
                    if (!\is_array($res->$k)) {
                        $res->$k = [$res->kSprache => $res->$k];
                    }
                    $res->$k[$item->kSprache] = $v;
                }
            }
        }
        foreach (\get_object_vars($res) as $key => $value) {
            if (($mapping = $this->getMapping($key)) === null) {
                continue;
            }
            $method = 'set' . $mapping;
            if (\is_array($value)) {
                // setter with language ID
                foreach ($value as $langID => $content) {
                    $this->$method($content, $langID);
                }
            } else {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @param string $templateID
     * @return array|null
     */
    private function loadFromDB(string $templateID): ?array
    {
        if (\strpos($templateID, 'kPlugin') === 0) {
            // @todo: tpluginemailvorlageeinstellungen?
            [, $pluginID, $moduleID] = \explode('_', $templateID);
            $data                    = $this->db->queryPrepared(
                'SELECT *, 0 AS nFehlerhaft
                    FROM tpluginemailvorlage
                    LEFT JOIN tpluginemailvorlagesprache
                        ON tpluginemailvorlage.kEmailvorlage = tpluginemailvorlagesprache.kEmailvorlage
                    WHERE tpluginemailvorlage.kPlugin = :pid
                        AND cModulId = :mid',
                ['pid' => $pluginID, 'mid' => $moduleID],
                ReturnType::ARRAY_OF_OBJECTS
            );
        } else {
            $data = $this->db->queryPrepared(
                'SELECT *, 0 AS kPlugin
                    FROM temailvorlage
                    LEFT JOIN temailvorlagesprache
                        ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                    WHERE cModulId = :mid',
                ['mid' => $templateID],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }

        return \count($data) === 0
            ? null
            : map(
                $data,
                function ($e) {
                    $e->kSprache      = (int)$e->kSprache;
                    $e->kPlugin       = (int)$e->kPlugin;
                    $e->kEmailvorlage = (int)$e->kEmailvorlage;
                    $e->nAKZ          = (int)$e->nAKZ;
                    $e->nAGB          = (int)$e->nAGB;
                    $e->nWRB          = (int)$e->nWRB;
                    $e->nWRBForm      = (int)$e->nWRBForm;
                    $e->nDSE          = (int)$e->nDSE;
                    $e->nFehlerhaft   = (int)$e->nFehlerhaft;

                    return $e;
                }
            );
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
