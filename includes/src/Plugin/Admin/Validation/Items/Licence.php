<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Licence
 * @package Plugin\Admin\Validation\Items
 */
class Licence extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $requiresMissingIoncube = false;
        $installNode            = $this->getInstallNode();
        $node                   = $this->getBaseNode();
        $dir                    = $this->getDir();
        if (isset($node['LicenceClassFile']) && !\extension_loaded('ionCube Loader')) {
            // ioncube is not loaded
            $nLastVersionKey    = \count($installNode['Version']) / 2 - 1;
            $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];
            if (\file_exists($dir . '/' . \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile'])
            ) {
                $content = \file_get_contents($dir . '/' .
                    \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                    \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile']);
                // ioncube encoded files usually have a header that checks loaded extions itself
                // but it can also be in short form, where there are no opening php tags
                $requiresMissingIoncube = ((\strpos($content, 'ionCube') !== false
                        && \strpos($content, 'extension_loaded') !== false)
                    || \strpos($content, '<?php') === false);
            }
        }
        $nLastVersionKey    = \count($installNode['Version']) / 2 - 1;
        $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];
        $versionedDir       = $dir . '/' . \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/';
        if (isset($node['LicenceClassFile']) && \strlen($node['LicenceClassFile']) > 0) {
            if (!\file_exists($versionedDir . \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile'])) {
                return InstallCode::MISSING_LICENCE_FILE;
            }
            if (empty($node['LicenceClass'])
                || $node['LicenceClass'] !== $node['PluginID'] . \PLUGIN_LICENCE_CLASS
            ) {
                return InstallCode::INVALID_LICENCE_FILE_NAME;
            }
            if ($requiresMissingIoncube) {
                return InstallCode::IONCUBE_REQUIRED;
            }
            require_once $versionedDir . \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile'];
            if (!\class_exists($node['LicenceClass'])) {
                return InstallCode::MISSING_LICENCE;
            }
            $classMethods = \get_class_methods($node['LicenceClass']);
            $bClassMethod = \is_array($classMethods) && \in_array(\PLUGIN_LICENCE_METHODE, $classMethods, true);
            if (!$bClassMethod) {
                return InstallCode::MISSING_LICENCE_CHECKLICENCE_METHOD;
            }
        }

        return InstallCode::OK;
    }
}
