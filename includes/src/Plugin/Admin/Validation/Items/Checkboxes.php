<?php declare(strict_types=1);
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
        foreach ($node['CheckBoxFunction'][0]['Function'] as $i => $cb) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                if (\mb_strlen($cb['Name']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_NAME;
                }
                if (\mb_strlen($cb['ID']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_ID;
                }
            }
        }

        return InstallCode::OK;
    }
}
