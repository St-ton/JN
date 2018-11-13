<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Name
 * @package Plugin\Admin\Validation\Items
 */
class Name extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['Name'])) {
            return InstallCode::INVALID_NAME;
        }
        \preg_match(
            '/[a-zA-Z0-9äÄüÜöÖß' . '\(\)_ -]+/',
            $baseNode['Name'],
            $hits
        );

        return !isset($hits[0]) || \strlen($hits[0]) !== \strlen($baseNode['Name'])
            ? InstallCode::INVALID_NAME
            : InstallCode::OK;
    }
}
