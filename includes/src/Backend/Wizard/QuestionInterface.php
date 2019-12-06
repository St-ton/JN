<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend\Wizard;

use Exception;
use stdClass;

/**
 * Interface QuestionInterface
 * @package JTL\Backend\Wizard
 */
interface QuestionInterface
{
    /**
     * @param array $post
     * @return mixed
     */
    public function answerFromPost(array $post);

    /**
     * @param array $data
     */
    public function loadAnswer(array $data): void;

    /**
     * @param string $configName
     * @param mixed  $value
     * @return int
     */
    public function updateConfig(string $configName, $value): int;

    /**
     * Add or update a row in tsprachwerte
     *
     * @param string $locale  locale iso code e.g. "ger"
     * @param string $section section e.g. "global". See tsprachsektion for all sections
     * @param string $key     unique name to identify localization
     * @param string $value   localized text
     * @param bool   $system  optional flag for system-default.
     * @throws Exception if locale key or section is wrong
     */
    public function setLocalization($locale, $section, $key, $value, $system = true): void;

    /**
     *
     */
    public function save(): void;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @return string
     */
    public function getText(): string;

    /**
     * @param string $text
     */
    public function setText(string $text): void;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     */
    public function setDescription(string $description): void;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param int $type
     */
    public function setType(int $type): void;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     */
    public function setValue($value): void;

    /**
     * @return int|null
     */
    public function getDependency(): ?int;

    /**
     * @param int $dependency
     */
    public function setDependency(int $dependency): void;

    /**
     * @return callable|null
     */
    public function getOnSave(): ?callable;

    /**
     * @param callable $onSave
     */
    public function setOnSave(callable $onSave): void;

    /**
     * @return SelectOption[]
     */
    public function getOptions(): array;

    /**
     * @param SelectOption[] $options
     */
    public function setOptions(array $options): void;

    /**
     * @param SelectOption $option
     */
    public function addOption(SelectOption $option): void;

    /**
     * @return bool
     */
    public function isMultiSelect(): bool;

    /**
     * @param bool $multi
     */
    public function setIsMultiSelect(bool $multi): void;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @param bool $required
     */
    public function setIsRequired(bool $required): void;

    /**
     * @return stdClass
     */
    public function jsonSerialize(): stdClass;
}
