<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class DateCreated
 * @package Plugin\Admin\Validation\Items
 */
class DateCreated extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['Install'][0]['CreateDate'])) {
            return InstallCode::INVALID_DATE;
        }
        \preg_match(
            '/[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/',
            $baseNode['Install'][0]['CreateDate'],
            $hits
        );

        return !isset($hits[0]) || \strlen($hits[0]) !== \strlen($baseNode['Install'][0]['CreateDate'])
            ? InstallCode::INVALID_DATE
            : InstallCode::OK;
    }
}
