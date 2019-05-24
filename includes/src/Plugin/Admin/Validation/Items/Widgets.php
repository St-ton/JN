<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Widgets
 * @package JTL\Plugin\Admin\Validation\Items
 */
class Widgets extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node     = $this->getInstallNode();
        $dir      = $this->getDir();
        $pluginID = $this->getPluginID();
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
            if (!\is_array($widget)) {
                continue;
            }
            $i      = (string)$i;
            $widget = $this->sanitizeWidgget($widget);
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            \preg_match(
                '/[a-zA-Z0-9\/_\-äÄüÜöÖß' . '\(\) ]+/',
                $widget['Title'],
                $hits1
            );
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Title'])) {
                return InstallCode::INVALID_WIDGET_TITLE;
            }
            \preg_match('/[a-zA-Z0-9\/_\-.]+/', $widget['Class'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Class'])) {
                return InstallCode::INVALID_WIDGET_CLASS;
            }
            if (!\file_exists($base . 'class.Widget' . $widget['Class'] . '_' . $pluginID . '.php')) {
                return InstallCode::MISSING_WIDGET_CLASS_FILE;
            }
            if (!\in_array($widget['Container'], ['center', 'left', 'right'], true)) {
                return InstallCode::INVALID_WIDGET_CONTAINER;
            }
            \preg_match('/[0-9]+/', $widget['Pos'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Pos'])) {
                return InstallCode::INVALID_WIDGET_POS;
            }
            \preg_match('/[0-1]{1}/', $widget['Expanded'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Expanded'])) {
                return InstallCode::INVALID_WIDGET_EXPANDED;
            }
            \preg_match('/[0-1]{1}/', $widget['Active'], $hits1);
            if (!isset($hits1[0]) || \mb_strlen($hits1[0]) !== \mb_strlen($widget['Active'])) {
                return InstallCode::INVALID_WIDGET_ACTIVE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $widget
     * @return array
     */
    private function sanitizeWidgget(array $widget): array
    {
        $widget['Title']     = $widget['Title'] ?? '';
        $widget['Class']     = $widget['Class'] ?? '';
        $widget['Container'] = $widget['Container'] ?? '';
        $widget['Pos']       = $widget['Pos'] ?? '';
        $widget['Expanded']  = $widget['Expanded'] ?? '';
        $widget['Active']    = $widget['Active'] ?? '';

        return $widget;
    }
}
