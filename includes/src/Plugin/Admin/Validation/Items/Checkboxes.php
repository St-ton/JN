<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Checkboxes
 * @package Plugin\Admin\Validation\Items
 */
class Checkboxes extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        if (!isset($node['CheckBoxFunction'][0]['Function'])
            || !\is_array($node['CheckBoxFunction'][0]['Function'])
            || \count($node['CheckBoxFunction'][0]['Function']) === 0
        ) {
            return InstallCode::OK;
        }
        foreach ($node['CheckBoxFunction'][0]['Function'] as $t => $cb) {
            \preg_match('/[0-9]+/', $t, $hits2);
            if (\strlen($hits2[0]) === \strlen($t)) {
                if (\strlen($cb['Name']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_NAME;
                }
                if (\strlen($cb['ID']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_ID;
                }
            }
        }

        return InstallCode::OK;
    }
}
