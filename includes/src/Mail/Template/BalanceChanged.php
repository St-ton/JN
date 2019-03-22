<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class BalanceChanged
 * @package JTL\Mail\Template
 */
class BalanceChanged extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_GUTSCHEIN;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('Gutschein', $data->tgutschein);
    }
}
