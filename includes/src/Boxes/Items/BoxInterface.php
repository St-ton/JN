<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use Filter\FilterInterface;

/**
 * Interface BoxInterface
 * @package Boxes
 */
interface BoxInterface
{

    /**
     * BoxInterface constructor.
     *
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * @return bool
     */
    public function show(): bool;

    /**
     * @param bool $show
     */
    public function setShow(bool $show): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getURL(): string;

    /**
     * @param string $url
     */
    public function setURL(string $url): void;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * @return string
     */
    public function getTemplateFile(): string;

    /**
     * @param string $templateFile
     */
    public function setTemplateFile(string $templateFile): void;

    /**
     * @return null|\Plugin
     */
    public function getPlugin(): ?\Plugin;

    /**
     * @param null|\Plugin $plugin
     */
    public function setPlugin(\Plugin $plugin): void;

    /**
     * @return int
     */
    public function getContainerID(): int;

    /**
     * @param int $containerID
     */
    public function setContainerID(int $containerID): void;

    /**
     * @return string
     */
    public function getPosition(): string;

    /**
     * @param string $position
     */
    public function setPosition(string $position): void;

    /**
     * @param null|int $idx
     * @return string
     */
    public function getTitle($idx = null): string;

    /**
     * @param string|array $title
     */
    public function setTitle($title): void;

    /**
     * @param null|int $idx
     * @return string
     */
    public function getContent($idx = null): string;

    /**
     * @param string|array $content
     */
    public function setContent($content): void;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @return int
     */
    public function getBaseType(): int;

    /**
     * @param int $type
     */
    public function setBaseType(int $type): void;

    /**
     * @return int
     */
    public function getCustomID(): int;

    /**
     * @param int $id
     */
    public function setCustomID(int $id): void;

    /**
     * @return int
     */
    public function getSort(): int;

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void;

    /**
     * @return int
     */
    public function getItemCount(): int;

    /**
     * @param int $count
     */
    public function setItemCount(int $count): void;

    /**
     * @return bool
     */
    public function supportsRevisions(): bool;

    /**
     * @param bool $supportsRevisions
     */
    public function setSupportsRevisions(bool $supportsRevisions): void;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void;

    /**
     * @return array|\Artikel[]|\ArtikelListe
     */
    public function getProducts();

    /**
     * @param array|\ArtikelListe $products
     */
    public function setProducts($products): void;

    /**
     * @return array|FilterInterface
     */
    public function getItems();

    /**
     * @param array|FilterInterface $items
     */
    public function setItems($items): void;

    /**
     * @param int $idx
     * @return array|bool
     */
    public function getFilter(int $idx = null);

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void;

    /**
     * @return array
     */
    public function getConfig(): array;

    /**
     * @param array $config
     */
    public function setConfig(array $config): void;

    /**
     * @return null|string
     */
    public function getJSON(): string;

    /**
     * @param null|string $json
     */
    public function setJSON(string $json): void;

    /**
     * @param int $pageType
     * @param int $pageID
     * @return bool
     */
    public function isBoxVisible(int $pageType = 0, int $pageID = 0): bool;

    /**
     * @param array $boxData
     */
    public function map(array $boxData): void;

    /**
     * @return BoxInterface[]
     */
    public function getChildren(): array;

    /**
     * @param BoxInterface[] $chilren
     */
    public function setChildren(array $chilren): void;

    /**
     * @return string
     */
    public function getRenderer(): string;

    /**
     * @return string
     */
    public function getHTML(): string;

    /**
     * @param string $html
     */
    public function setHTML(string $html): void;

    /**
     * @return string
     */
    public function getRenderedContent(): string;

    /**
     * @param string $renderedContent
     */
    public function setRenderedContent(string $renderedContent): void;
}
