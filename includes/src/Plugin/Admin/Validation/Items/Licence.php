<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Licence
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Licence extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $requiresMissingIoncube = false;
        $node                   = $this->getBaseNode();
        $dir                    = $this->getDir();
        if (isset($node['LicenceClassFile'])
            && !\extension_loaded('ionCube Loader')
            && \file_exists($dir . \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile'])
        ) {
            $content = \file_get_contents($dir . \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile']);
            // ioncube encoded files usually have a header that checks loaded extions itself
            // but it can also be in short form, where there are no opening php tags
            $requiresMissingIoncube = ((\str_contains($content, 'ionCube')
                    && \str_contains($content, 'extension_loaded'))
                || !\str_contains($content, '<?php'));
        }
        if (isset($node['LicenceClassFile']) && \mb_strlen($node['LicenceClassFile']) > 0) {
            if (!\file_exists($dir . \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile'])) {
                return InstallCode::MISSING_LICENCE_FILE;
            }
            if (empty($node['LicenceClass']) || $node['LicenceClass'] !== $node['PluginID'] . \PLUGIN_LICENCE_CLASS) {
                return InstallCode::INVALID_LICENCE_FILE_NAME;
            }
            if ($requiresMissingIoncube) {
                return InstallCode::IONCUBE_REQUIRED;
            }
            require_once $dir . \PFAD_PLUGIN_LICENCE . $node['LicenceClassFile'];
            if (!\class_exists($node['LicenceClass'])) {
                return InstallCode::MISSING_LICENCE;
            }
            $classMethods = \get_class_methods($node['LicenceClass']);
            if (!\in_array(\PLUGIN_LICENCE_METHODE, $classMethods, true)) {
                return InstallCode::MISSING_LICENCE_CHECKLICENCE_METHOD;
            }
        }

        return InstallCode::OK;
    }
}
