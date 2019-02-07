<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class MailTemplates
 * @package Plugin\Admin\Validation\Items
 */
class MailTemplates extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        if (!isset($node['Emailtemplate']) || !\is_array($node['Emailtemplate'])) {
            return InstallCode::OK;
        }
        if (!isset($node['Emailtemplate'][0]['Template'])
            || !\is_array($node['Emailtemplate'][0]['Template'])
            || \count($node['Emailtemplate'][0]['Template']) === 0
        ) {
            return InstallCode::MISSING_EMAIL_TEMPLATES;
        }
        foreach ($node['Emailtemplate'][0]['Template'] as $i => $tpl) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            \preg_match(
                '/[a-zA-Z0-9\/_\-äÄüÜöÖß' . ' ]+/',
                $tpl['Name'],
                $hits1
            );
            if (\mb_strlen($hits1[0]) !== \mb_strlen($tpl['Name'])) {
                return InstallCode::INVALID_TEMPLATE_NAME;
            }
            if ($tpl['Type'] !== 'text/html' && $tpl['Type'] !== 'text') {
                return InstallCode::INVALID_TEMPLATE_TYPE;
            }
            if (!isset($tpl['ModulId']) || \mb_strlen($tpl['ModulId']) === 0) {
                return InstallCode::INVALID_TEMPLATE_MODULE_ID;
            }
            if (!isset($tpl['Active']) || \mb_strlen($tpl['Active']) === 0) {
                return InstallCode::INVALID_TEMPLATE_ACTIVE;
            }
            if (!isset($tpl['AKZ']) || \mb_strlen($tpl['AKZ']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AKZ;
            }
            if (!isset($tpl['AGB']) || \mb_strlen($tpl['AGB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AGB;
            }
            if (!isset($tpl['WRB']) || \mb_strlen($tpl['WRB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_WRB;
            }
            if (!isset($tpl['TemplateLanguage'])
                || !\is_array($tpl['TemplateLanguage'])
                || \count($tpl['TemplateLanguage']) === 0
            ) {
                return InstallCode::MISSING_EMAIL_TEMPLATE_LANGUAGE;
            }
            foreach ($tpl['TemplateLanguage'] as $l => $localized) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                    \preg_match('/[A-Z]{3}/', $localized['iso'], $hits);
                    $len = \mb_strlen($localized['iso']);
                    if ($len === 0 || \mb_strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_EMAIL_TEMPLATE_ISO;
                    }
                } elseif (\mb_strlen($hits2[0]) === \mb_strlen($l)) {
                    \preg_match('/[a-zA-Z0-9\/_\-.#: ]+/', $localized['Subject'], $hits1);
                    $len = \mb_strlen($localized['Subject']);
                    if ($len === 0 || \mb_strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_EMAIL_TEMPLATE_SUBJECT;
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
