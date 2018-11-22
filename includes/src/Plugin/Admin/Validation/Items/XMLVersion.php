<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class XMLVersion
 * @package Plugin\Admin\Validation\Items
 */
class XMLVersion extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (!isset($baseNode['XMLVersion'])) {
            return InstallCode::INVALID_XML_VERSION;
        }
        \preg_match('/[0-9]{3}/', $baseNode['XMLVersion'], $hits);
        if (\count($hits) === 0
            || (\strlen($hits[0]) !== \strlen($baseNode['XMLVersion']) && (int)$baseNode['XMLVersion'] >= 100)
        ) {
            return InstallCode::INVALID_XML_VERSION;
        }

        return InstallCode::OK;
    }
}
