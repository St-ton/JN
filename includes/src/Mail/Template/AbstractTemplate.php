<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use function Functional\first;
use function Functional\tail;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\map;
use function Functional\reindex;

/**
 * Class AbstractTemplate
 * @package JTL\Mail\Template
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $settingsTable = 'temailvorlageeinstellungen';

    /**
     * @var string|null
     */
    protected $overrideSubject;

    /**
     * @var string|null
     */
    protected $overrideFromName;

    /**
     * @var string|null
     */
    protected $overrideFromMail;

    /**
     * @var array
     */
    protected $overrideCopyTo = [];

    /**
     * @var array
     */
    protected $legalData = [];

    /**
     * @var Model|null
     */
    protected $model;

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var string|null
     */
    protected $html;

    /**
     * @var string|null
     */
    protected $text;

    /**
     * @var int
     */
    protected $languageID;

    /**
     * @var int
     */
    protected $customerGroupID;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected static $mapping = [
        'kEmailvorlage' => 'ID',
        'cName'         => 'Name',
        'cBeschreibung' => 'Description',
        'cMailTyp'      => 'Type',
        'cModulId'      => 'ModuleID',
        'cDateiname'    => 'FileNames',
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
        'kPlugin'       => 'PluginID',
    ];

    /**
     * AbstractTemplate constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->init();
    }

    protected function init(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function load(int $languageID, int $customerGroupID): ?Model
    {
        $this->model           = null;
        $this->languageID      = $languageID;
        $this->customerGroupID = $customerGroupID;
        $data                  = $this->getData();
        if ($data === null) {
            return null;
        }
        $this->getAdditionalData($data->kEmailvorlage);
        $this->initLegalData();
        $this->model = new Model();
        foreach (\get_object_vars($data) as $key => $value) {
            if (($mapping = $this->getMapping($key)) === null) {
                continue;
            }
            $method = 'set' . $mapping;
            if (\is_array($value)) {
                // setter with language ID
                foreach ($value as $langID => $content) {
                    $this->model->$method($content, $langID);
                }
            } else {
                $this->model->$method($value);
            }
        }

        return $this->model;
    }

    /**
     * @param string $type
     * @return string|null
     */
    private function getMapping(string $type): ?string
    {
        return self::$mapping[$type] ?? null;
    }

    /**
     * @return stdClass|null
     */
    protected function getData(): ?stdClass
    {
        if (\strpos($this->getID(), 'kPlugin') === 0) {
            // @todo: tpluginemailvorlageeinstellungen?
            [, $pluginID, $moduleID] = \explode('_', $this->getID());
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
                ['mid' => $this->getID()],
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if (\count($data) === 0) {
            return null;
        }
        $data              = map(
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
        $res               = first($data);
        $res->cBetreff     = [$res->kSprache => $res->cBetreff];
        $res->cContentHtml = [$res->kSprache => $res->cContentHtml];
        $res->cContentText = [$res->kSprache => $res->cContentText];
        $res->cPDFS        = [$res->kSprache => $res->cPDFS];
        $res->cDateiname   = [$res->kSprache => $res->cDateiname];
        foreach (tail($data) as $item) {
            $keys = \get_object_vars($item);
            foreach ($keys as $k => $v) {
                if ($k === 'cBetreff'
                    || $k === 'cContentHtml'
                    || $k === 'cContentText'
                    || $k === 'cPDFS'
                    || $k === 'cDateiname'
                ) {
                    $res->$k[$item->kSprache] = $v;
                }
            }
        }

        return $res;
    }

    /**
     * @param int $tplID
     */
    protected function getAdditionalData(int $tplID): void
    {
        $data = $this->db->selectAll(
            $this->settingsTable,
            'kEmailvorlage',
            $tplID
        );
        foreach ($data as $item) {
            if ($item->cKey === 'cEmailSenderName') {
                $this->overrideFromName = $item->cValue;
            } elseif ($item->cKey === 'cEmailOut') {
                $this->overrideFromMail = $item->cValue;
            } elseif ($item->cKey === 'cEmailCopyTo') {
                $this->overrideCopyTo = Text::parseSSK($item->cValue);
            }
        }
    }

    /**
     * @return array
     */
    protected function initLegalData(): array
    {
        $agb                   = new stdClass();
        $wrb                   = new stdClass();
        $wrbForm               = new stdClass();
        $dse                   = new stdClass();
        $data                  = $this->db->select(
            'ttext',
            ['kSprache', 'kKundengruppe'],
            [$this->languageID, $this->customerGroupID]
        );
        $agb->cContentText     = $this->sanitizeText($data->cAGBContentText);
        $agb->cContentHtml     = $this->sanitizeText($data->cAGBContentHtml);
        $wrb->cContentText     = $this->sanitizeText($data->cWRBContentText);
        $wrb->cContentHtml     = $this->sanitizeText($data->cWRBContentHtml);
        $dse->cContentText     = $this->sanitizeText($data->cDSEContentText);
        $dse->cContentHtml     = $this->sanitizeText($data->cDSEContentHtml);
        $wrbForm->cContentHtml = $this->sanitizeText($data->cWRBFormContentHtml);
        $wrbForm->cContentText = $this->sanitizeText($data->cWRBFormContentText);

        $this->legalData = [
            'agb'     => $agb,
            'wrb'     => $wrb,
            'wrbform' => $wrbForm,
            'dse'     => $dse
        ];

        return $this->legalData;
    }

    /**
     * @param string|null $text
     * @return string
     */
    private function sanitizeText(?string $text): string
    {
        if ($text === null || \mb_strlen(\strip_tags($text)) === 0) {
            return '';
        }

        return $text;
    }

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
    }

    /**
     * @inheritdoc
     */
    public function render(RendererInterface $renderer, int $languageID, int $customerGroupID): void
    {
        $this->load($languageID, $customerGroupID);
        $renderer->renderTemplate($this, $languageID);
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
     * @inheritdoc
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getFromMail(): ?string
    {
        return $this->overrideFromMail;
    }

    /**
     * @inheritdoc
     */
    public function setFromMail(?string $mail): void
    {
        $this->overrideFromMail = $mail;
    }

    /**
     * @inheritdoc
     */
    public function getFromName(): ?string
    {
        return $this->overrideFromName;
    }

    /**
     * @inheritdoc
     */
    public function setFromName(?string $name): void
    {
        $this->overrideFromName = $name;
    }

    /**
     * @inheritdoc
     */
    public function getCopyTo(): array
    {
        return $this->overrideCopyTo;
    }

    /**
     * @inheritdoc
     */
    public function setCopyTo(array $copy): void
    {
        $this->overrideCopyTo = $copy;
    }

    /**
     * @inheritdoc
     */
    public function getLegalData(): array
    {
        return $this->legalData;
    }

    /**
     * @inheritdoc
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @inheritdoc
     */
    public function getHTML(): ?string
    {
        return $this->html;
    }

    /**
     * @inheritdoc
     */
    public function setHTML(?string $html): void
    {
        $this->html = $html;
    }

    /**
     * @inheritdoc
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    /**
     * @inheritdoc
     */
    public function getSubject(): ?string
    {
        return $this->overrideSubject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject(?string $overrideSubject): void
    {
        $this->overrideSubject = $overrideSubject;
    }

    /**
     * @inheritDoc
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @inheritDoc
     */
    public function setLanguageID(int $languageID): void
    {
        $this->languageID = $languageID;
    }
}
