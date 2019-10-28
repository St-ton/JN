<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class NewsletterRegistration
 * @package JTL\Mail\Template
 */
class NewsletterRegistration extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_NEWSLETTERANMELDEN;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('NewsletterEmpfaenger', $data->NewsletterEmpfaenger);
    }
}
