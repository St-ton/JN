<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class ForgotAdminPassword
 * @package JTL\Mail\Template
 */
class ForgotAdminPassword extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('passwordResetLink', $data->passwordResetLink);
    }
}
