<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\Backend\Settings\Item;
use JTL\Backend\Settings\Manager;

/**
 * Interface Section
 * @package Backend\Settings\Sections
 */
interface Section
{
    /**
     * @param Manager $manager
     * @param int     $sectionID
     */
    public function __construct(Manager $manager, int $sectionID);

    public function load(): void;

    /**
     * @param object $conf
     * @param object $confValue
     * @return bool
     */
    public function validate($conf, &$confValue): bool;

    /**
     * @param object $conf
     * @param mixed  $value
     */
    public function setValue(&$conf, $value): void;

    /**
     * @return string
     */
    public function getSectionMarkup(): string;

    /**
     * @param string $markup
     */
    public function setSectionMarkup(string $markup): void;

    /**
     * @param array $data
     * @param bool  $filter
     * @return array
     */
    public function update(array $data, bool $filter = true): array;

    /**
     * @return Item[]
     */
    public function getConfigData(): array;

    /**
     * @param array $data
     */
    public function setConfigData(array $data): void;
}
