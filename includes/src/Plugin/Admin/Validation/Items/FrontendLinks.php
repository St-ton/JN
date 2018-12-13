<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\Admin\Validation\ValidationItemInterface;
use Plugin\InstallCode;

/**
 * Class FrontendLinks
 * @package Plugin\Admin\Validation\Items
 */
class FrontendLinks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['FrontendLink'][0])) {
            return InstallCode::OK;
        }
        $node = $node['FrontendLink'][0];
        if (!isset($node['Link']) || !\is_array($node['Link']) || \count($node['Link']) === 0) {
            return InstallCode::MISSING_FRONTEND_LINKS;
        }
        foreach ($node['Link'] as $i => $link) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);

            if (\strlen($hits2[0]) !== \strlen($i)) {
                continue;
            }
            if (\strlen($link['Filename']) === 0) {
                return InstallCode::INVALID_FRONTEND_LINK_FILENAME;
            }
            \preg_match(
                '/[a-zA-Z0-9äÄöÖüÜß' . '\_\- ]+/',
                $link['Name'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($link['Name'])) {
                return InstallCode::INVALID_FRONTEND_LINK_NAME;
            }
            // Templatename UND Fullscreen Templatename vorhanden?
            // Es darf nur entweder oder geben
            if (isset($link['Template'], $link['FullscreenTemplate'])
                && \strlen($link['Template']) > 0
                && \strlen($link['FullscreenTemplate']) > 0
            ) {
                return InstallCode::TOO_MANY_FULLSCREEN_TEMPLATE_NAMES;
            }
            if (!isset($link['FullscreenTemplate']) || \strlen($link['FullscreenTemplate']) === 0) {
                if (\strlen($link['Template']) === 0) {
                    return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                \preg_match('/[a-zA-Z0-9\/_\-.]+.tpl/', $link['Template'], $hits1);
                if (\strlen($hits1[0]) === \strlen($link['Template'])) {
                    if (!\file_exists($dir .
                        \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $link['Template'])
                    ) {
                        return InstallCode::MISSING_FRONTEND_LINK_TEMPLATE;
                    }
                } else {
                    return InstallCode::INVALID_FULLSCREEN_TEMPLATE;
                }
            }
            if (!isset($link['Template']) || \strlen($link['Template']) === 0) {
                if (\strlen($link['FullscreenTemplate']) === 0) {
                    return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                \preg_match('/[a-zA-Z0-9\/_\-.]+.tpl/', $link['FullscreenTemplate'], $hits1);
                if (\strlen($hits1[0]) === \strlen($link['FullscreenTemplate'])) {
                    if (!\file_exists($dir .
                        \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $link['FullscreenTemplate'])
                    ) {
                        return InstallCode::MISSING_FULLSCREEN_TEMPLATE_FILE;
                    }
                } else {
                    return InstallCode::INVALID_FULLSCREEN_TEMPLATE_NAME;
                }
            }
            \preg_match('/[NY]{1,1}/', $link['VisibleAfterLogin'], $hits2);
            if (\strlen($hits2[0]) !== \strlen($link['VisibleAfterLogin'])) {
                return InstallCode::INVALID_FRONEND_LINK_VISIBILITY;
            }
            \preg_match('/[NY]{1,1}/', $link['PrintButton'], $hits3);
            if (\strlen($hits3[0]) !== \strlen($link['PrintButton'])) {
                return InstallCode::INVALID_FRONEND_LINK_PRINT;
            }
            if (isset($link['NoFollow'])) {
                \preg_match('/[NY]{1,1}/', $link['NoFollow'], $hits3);
            } else {
                $hits3 = [];
            }
            if (isset($hits3[0]) && \strlen($hits3[0]) !== \strlen($link['NoFollow'])) {
                return InstallCode::INVALID_FRONTEND_LINK_NO_FOLLOW;
            }
            if (!isset($link['LinkLanguage'])
                || !\is_array($link['LinkLanguage'])
                || \count($link['LinkLanguage']) === 0
            ) {
                return InstallCode::INVALID_FRONEND_LINK_ISO;
            }
            foreach ($link['LinkLanguage'] as $l => $localized) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    \preg_match('/[A-Z]{3}/', $localized['iso'], $hits);
                    $len = \strlen($localized['iso']);
                    if ($len === 0 || \strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_ISO;
                    }
                } elseif (\strlen($hits2[0]) === \strlen($l)) {
                    \preg_match('/[a-zA-Z0-9- ]+/', $localized['Seo'], $hits1);
                    $len = \strlen($localized['Seo']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_SEO;
                    }
                    \preg_match(
                        '/[a-zA-Z0-9äÄüÜöÖß' . '\- ]+/',
                        $localized['Name'],
                        $hits1
                    );
                    $len = \strlen($localized['Name']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_NAME;
                    }
                    \preg_match(
                        '/[a-zA-Z0-9äÄüÜöÖß' . '\- ]+/',
                        $localized['Title'],
                        $hits1
                    );
                    $len = \strlen($localized['Title']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_TITLE;
                    }
                    \preg_match(
                        '/[a-zA-Z0-9äÄüÜöÖß' . '\,\.\- ]+/',
                        $localized['MetaTitle'],
                        $hits1
                    );
                    $len = \strlen($localized['MetaTitle']);
                    if ($len === 0 && \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_TITLE;
                    }
                    \preg_match(
                        '/[a-zA-Z0-9äÄüÜöÖß' . '\,\- ]+/',
                        $localized['MetaKeywords'],
                        $hits1
                    );
                    $len = \strlen($localized['MetaKeywords']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_KEYWORDS;
                    }
                    \preg_match(
                        '/[a-zA-Z0-9äÄüÜöÖß' . '\,\.\- ]+/',
                        $localized['MetaDescription'],
                        $hits1
                    );
                    $len = \strlen($localized['MetaDescription']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_DESCRIPTION;
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
