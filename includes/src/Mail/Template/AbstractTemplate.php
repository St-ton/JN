<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\DB\DbInterface;
use JTL\Helpers\Text;
use JTL\Mail\Renderer\RendererInterface;
use JTL\Smarty\JTLSmarty;
use stdClass;
use function Functional\first;

/**
 * Class AbstractTemplate
 * @package JTL\Mail\Template
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected string $settingsTable = 'temailvorlageeinstellungen';

    /**
     * @var string|null
     */
    protected ?string $overrideSubject = null;

    /**
     * @var string|null
     */
    protected ?string $overrideFromName = null;

    /**
     * @var string|null
     */
    protected ?string $overrideFromMail = null;

    /**
     * @var array
     */
    protected array $overrideCopyTo = [];

    /**
     * @var array
     */
    protected array $legalData = [];

    /**
     * @var Model|null
     */
    protected ?Model $model = null;

    /**
     * @var string|null
     */
    protected ?string $html = null;

    /**
     * @var string|null
     */
    protected ?string $text = null;

    /**
     * @var int
     */
    protected int $languageID = 0;

    /**
     * @var int
     */
    protected int $customerGroupID = 0;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * AbstractTemplate constructor.
     * @param DbInterface $db
     */
    public function __construct(protected DbInterface $db)
    {
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
        if ($this->model !== null && $languageID === $this->languageID && $customerGroupID === $this->customerGroupID) {
            return $this->model;
        }
        $this->languageID      = $languageID;
        $this->customerGroupID = $customerGroupID;
        $this->model           = new Model($this->db);
        $this->model           = $this->model->load($this->getID());
        if ($this->model === null) {
            return null;
        }
        $this->getAdditionalData($this->model->getID());
        $this->initLegalData();

        return $this->model;
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
        $data                  = $this->db->selectAll(
            'ttext',
            ['kKundengruppe'],
            [$this->customerGroupID]
        );
        $data                  = first(
            $data,
            function (stdClass $item): bool {
                return (int)$item->kSprache === $this->languageID;
            }
        ) ?? first($data);
        $agb                   = new stdClass();
        $wrb                   = new stdClass();
        $wrbForm               = new stdClass();
        $dse                   = new stdClass();
        $agb->cContentText     = $this->sanitizeText($data->cAGBContentText ?? '');
        $agb->cContentHtml     = $this->sanitizeText($data->cAGBContentHtml ?? '');
        $wrb->cContentText     = $this->sanitizeText($data->cWRBContentText ?? '');
        $wrb->cContentHtml     = $this->sanitizeText($data->cWRBContentHtml ?? '');
        $dse->cContentText     = $this->sanitizeText($data->cDSEContentText ?? '');
        $dse->cContentHtml     = $this->sanitizeText($data->cDSEContentHtml ?? '');
        $wrbForm->cContentHtml = $this->sanitizeText($data->cWRBFormContentHtml ?? '');
        $wrbForm->cContentText = $this->sanitizeText($data->cWRBFormContentText ?? '');

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
        return $text === null || \mb_strlen(\strip_tags($text)) === 0 ? '' : $text;
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
