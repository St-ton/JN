<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Portlets
 * @package Plugin\Admin\Validation\Items
 */
class Portlets extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['Portlets']) || !\is_array($node['Portlets'])) {
            return InstallCode::OK;
        }
        if (!isset($node['Portlets'][0]['Portlet'])
            || !\is_array($node['Portlets'][0]['Portlet'])
            || \count($node['Portlets'][0]['Portlet']) === 0
        ) {
            return InstallCode::MISSING_PORTLETS;
        }
        foreach ($node['Portlets'][0]['Portlet'] as $i => $portlet) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\strlen($hits2[0]) === \strlen($i)) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-äÄüÜöÖß' . \utf8_decode('äÄüÜöÖß') . '\(\) ]+/',
                    $portlet['Title'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($portlet['Title'])) {
                    return InstallCode::INVALID_PORTLET_TITLE;
                }
                \preg_match('/[a-zA-Z0-9\/_\-.]+/', $portlet['Class'], $hits1);
                if (\strlen($hits1[0]) === \strlen($portlet['Class'])) {
                    if (!\file_exists($dir .
                        \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_PORTLETS . $portlet['Class'] . '/' .
                        $portlet['Class'] . '.php')
                    ) {
                        return InstallCode::INVALID_PORTLET_CLASS_FILE;
                    }
                } else {
                    return InstallCode::INVALID_PORTLET_CLASS;
                }
                \preg_match(
                    '/[a-zA-Z0-9\/_\-äÄüÜöÖß' . \utf8_decode('äÄüÜöÖß') . '\(\) ]+/',
                    $portlet['Group'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($portlet['Group'])) {
                    return InstallCode::INVALID_PORTLET_GROUP;
                }
                \preg_match('/[0-1]{1}/', $portlet['Active'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($portlet['Active'])) {
                    return InstallCode::INVALID_PORTLET_ACTIVE;
                }
            }
        }

        return InstallCode::OK;
    }
}
