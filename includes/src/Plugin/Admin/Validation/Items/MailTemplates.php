<?php
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
        foreach ($node['Emailtemplate'][0]['Template'] as $u => $tpl) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            \preg_match(
                "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . " ]+/",
                $tpl['Name'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($tpl['Name'])) {
                return InstallCode::INVALID_TEMPLATE_NAME;
            }
            if ($tpl['Type'] !== 'text/html' && $tpl['Type'] !== 'text') {
                return InstallCode::INVALID_TEMPLATE_TYPE;
            }
            if (!isset($tpl['ModulId']) || \strlen($tpl['ModulId']) === 0) {
                return InstallCode::INVALID_TEMPLATE_MODULE_ID;
            }
            if (!isset($tpl['Active']) || \strlen($tpl['Active']) === 0) {
                return InstallCode::INVALID_TEMPLATE_ACTIVE;
            }
            if (!isset($tpl['AKZ']) || \strlen($tpl['AKZ']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AKZ;
            }
            if (!isset($tpl['AGB']) || \strlen($tpl['AGB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AGB;
            }
            if (!isset($tpl['WRB']) || \strlen($tpl['WRB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_WRB;
            }
            if (!isset($tpl['TemplateLanguage'])
                || !\is_array($tpl['TemplateLanguage'])
                || \count($tpl['TemplateLanguage']) === 0
            ) {
                return InstallCode::MISSING_EMAIL_TEMPLATE_LANGUAGE;
            }
            foreach ($tpl['TemplateLanguage'] as $l => $localized) {
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    \preg_match("/[A-Z]{3}/", $localized['iso'], $hits);
                    $len = \strlen($localized['iso']);
                    if ($len === 0 || \strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_EMAIL_TEMPLATE_ISO;
                    }
                } elseif (\strlen($hits2[0]) === \strlen($l)) {
                    \preg_match("/[a-zA-Z0-9\/_\-.#: ]+/", $localized['Subject'], $hits1);
                    $len = \strlen($localized['Subject']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_EMAIL_TEMPLATE_SUBJECT;
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
