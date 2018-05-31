<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Filter\FilterInterface;

/**
 * Interface BoxInterface
 * @package Boxes
 */
interface BoxInterface
{
    /**
     * @return bool
     */
    public function show(): bool;

    /**
     * @param bool $show
     */
    public function setShow(bool $show);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getURL(): string;

    /**
     * @param string $url
     */
    public function setURL(string $url);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getTemplateFile(): string;

    /**
     * @param string $templateFile
     */
    public function setTemplateFile(string $templateFile);

    /**
     * @return null|\Plugin
     */
    public function getPlugin();

    /**
     * @param null|\Plugin $plugin
     */
    public function setPlugin(\Plugin $plugin);

    /**
     * @return int
     */
    public function getContainerID(): int;

    /**
     * @param int $containerID
     */
    public function setContainerID(int $containerID);

    /**
     * @return string
     */
    public function getPosition(): string;

    /**
     * @param string $position
     */
    public function setPosition(string $position);

    /**
     * @param null|int $idx
     * @return string
     */
    public function getTitle($idx = null): string;

    /**
     * @param string|array $title
     */
    public function setTitle($title);

    /**
     * @param null|int $idx
     * @return string
     */
    public function getContent($idx = null): string;

    /**
     * @param string|array $content
     */
    public function setContent($content);

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id);

    /**
     * @return int
     */
    public function getBaseType(): int;

    /**
     * @param int $type
     */
    public function setBaseType(int $type);

    /**
     * @return int
     */
    public function getCustomID(): int;

    /**
     * @param int $id
     */
    public function setCustomID(int $id);

    /**
     * @return int
     */
    public function getSort(): int;

    /**
     * @param int $sort
     */
    public function setSort(int $sort);

    /**
     * @return int
     */
    public function getItemCount(): int;

    /**
     * @param int $count
     */
    public function setItemCount(int $count);

    /**
     * @return bool
     */
    public function supportsRevisions(): bool;

    /**
     * @param bool $supportsRevisions
     */
    public function setSupportsRevisions(bool $supportsRevisions);

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive);

    /**
     * @return array|\Artikel[]|\ArtikelListe
     */
    public function getProducts();

    /**
     * @param array|\ArtikelListe $products
     */
    public function setProducts($products);

    /**
     * @return array|FilterInterface
     */
    public function getItems();

    /**
     * @param array $items|FilterInterface
     */
    public function setItems($items);

    /**
     * @return array
     */
    public function getFilter(): array;

    /**
     * @param array $filter
     */
    public function setFilter(array $filter);

    /**
     * @return array
     */
    public function getConfig(): array;

    /**
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * @return null|string
     */
    public function getJSON(): string;

    /**
     * @param null|string $json
     */
    public function setJSON(string $json);

    /**
     * @param int $pageType
     * @param int $pageID
     * @return bool
     */
    public function isBoxVisible(int $pageType = 0, int $pageID = 0): bool;

    /**
     * @param \JTLSmarty $smarty
     * @param int        $pageType
     * @param int        $pageID
     * @return string
     */
    public function render(\JTLSmarty $smarty, int $pageType = 0, int $pageID = 0): string;

    /**
     * @param array $boxData
     */
    public function map(array $boxData);
}
