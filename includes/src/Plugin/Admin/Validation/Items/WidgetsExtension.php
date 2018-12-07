<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class WidgetsExtension
 * @package Plugin\Admin\Validation\Items
 */
class WidgetsExtension extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['AdminWidget']) || !\is_array($node['AdminWidget'])) {
            return InstallCode::OK;
        }
        if (!isset($node['AdminWidget'][0]['Widget'])
            || !\is_array($node['AdminWidget'][0]['Widget'])
            || \count($node['AdminWidget'][0]['Widget']) === 0
        ) {
            return InstallCode::MISSING_WIDGETS;
        }
        $base = $dir . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_WIDGET;
        foreach ($node['AdminWidget'][0]['Widget'] as $i => $widget) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\strlen($hits2[0]) !== \strlen($i)) {
                continue;
            }
            \preg_match(
                '/[a-zA-Z0-9\/_\-äÄüÜöÖß' . '\(\) ]+/',
                $widget['Title'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($widget['Title'])) {
                return InstallCode::INVALID_WIDGET_TITLE;
            }
            \preg_match('/[a-zA-Z0-9\/_\-.]+/', $widget['Class'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Class'])) {
                return InstallCode::INVALID_WIDGET_CLASS;
            }
            if (!\file_exists($base . $widget['Class'] . '.php')) {
                return InstallCode::MISSING_WIDGET_CLASS_FILE;
            }
            if (!\in_array($widget['Container'], ['center', 'left', 'right'], true)) {
                return InstallCode::INVALID_WIDGET_CONTAINER;
            }
            \preg_match('/[0-9]+/', $widget['Pos'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Pos'])) {
                return InstallCode::INVALID_WIDGET_POS;
            }
            \preg_match('/[0-1]{1}/', $widget['Expanded'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Expanded'])) {
                return InstallCode::INVALID_WIDGET_EXPANDED;
            }
            \preg_match('/[0-1]{1}/', $widget['Active'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Active'])) {
                return InstallCode::INVALID_WIDGET_ACTIVE;
            }
        }

        return InstallCode::OK;
    }
}
