<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use JTL\Smarty\JTLSmarty;

/**
 * Class CustomerPromotionBonus
 * @package JTL\Mail\Template
 */
class CustomerPromotionBonus extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_KUNDENWERBENKUNDENBONI;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('BestandskundenBoni', $data->BestandskundenBoni)
               ->assign('Neukunde', $data->oNeukunde)
               ->assign('Bestandskunde', $data->oBestandskunde);
    }
}
