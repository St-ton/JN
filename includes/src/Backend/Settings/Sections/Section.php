<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

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
     * @param object $conf
     * @return string
     */
    public function getValueMarkup($conf): string;

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool;

    /**
     * @return array
     */
    public function getConfigData(): array;

    /**
     * @param array $data
     */
    public function setConfigData(array $data): void;
}
