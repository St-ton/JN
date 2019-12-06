<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard;

use JsonSerializable;
use JTL\DB\DbInterface;
use JTL\Update\MigrationTableTrait;
use JTL\Update\MigrationTrait;
use stdClass;

/**
 * Class AbstractQuestion
 * @package JTL\Backend\Wizard
 */
abstract class AbstractQuestion implements JsonSerializable, QuestionInterface
{
    use MigrationTrait,
        MigrationTableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var int|null
     */
    protected $dependency;

    /**
     * @var callable
     */
    protected $onSave;

    /**
     * @var SelectOption[]
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $multiSelect = false;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * AbstractQuestion constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->setDB($db);
    }

    /**
     * @inheritDoc
     */
    public function answerFromPost(array $post)
    {
        $data = $post['question'][$this->getID()] ?? null;
        if ($this->getType() === QuestionType::BOOL) {
            $value = $data === 'on';
        } else {
            $value = $data ?? '';
        }
        $this->setValue($value);

        return $value;
    }

    /**
     * @param string $configName
     * @param mixed  $value
     * @return int
     */
    public function updateConfig(string $configName, $value): int
    {
        return $this->db->update('teinstellungen', 'cName', $configName, (object)['cWert' => $value]);
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        $cb = $this->getOnSave();
        if (\is_callable($cb)) {
            $cb($this);
        }
    }

    /**
     * @inheritDoc
     */
    public function loadAnswer(array $data): void
    {
        $value = $data[$this->getID()] ?? null;
        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * @inheritDoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @inheritDoc
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getDependency(): ?int
    {
        return $this->dependency;
    }

    /**
     * @inheritDoc
     */
    public function setDependency(int $dependency): void
    {
        $this->dependency = $dependency;
    }

    /**
     * @return callable
     */
    public function getOnSave(): callable
    {
        return $this->onSave;
    }

    /**
     * @param callable $onSave
     */
    public function setOnSave(callable $onSave): void
    {
        $this->onSave = $onSave;
    }

    /**
     * @return SelectOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param SelectOption[] $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param SelectOption $option
     */
    public function addOption(SelectOption $option): void
    {
        $this->options[] = $option;
    }

    /**
     * @inheritDoc
     */
    public function isMultiSelect(): bool
    {
        return $this->multiSelect;
    }

    /**
     * @inheritDoc
     */
    public function setIsMultiSelect(bool $multi): void
    {
        $this->multiSelect = $multi;
    }

    /**
     * @inheritDoc
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @inheritDoc
     */
    public function setIsRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): stdClass
    {
        $data = new stdClass();
        foreach (\get_object_vars($this) as $k => $v) {
            $data->$k = $v;
        }

        return $data;
    }
}
