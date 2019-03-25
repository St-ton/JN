<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use JTL\TrustedShops;

/**
 * Class OrderShipped
 * @package JTL\Mail\Template
 */
class OrderShipped extends AbstractTemplate
{
    protected $id = \MAILTEMPLATE_BESTELLUNG_VERSANDT;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        if ($this->config['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
            $langCode = Text::convertISO2ISO639($_SESSION['cISOSprache'] ?? 'ger');
            $ts       = new TrustedShops(-1, $langCode);
            $tsRating = $ts->holeKundenbewertungsstatus($langCode);
            if ($tsRating !== false && (int)$tsRating->nStatus === 1 && mb_strlen($tsRating->cTSID) > 0) {
                $smarty->assign('oTrustedShopsBewertenButton', TrustedShops::getRatingButton(
                    $data->tbestellung->oRechnungsadresse->cMail,
                    $data->tbestellung->cBestellNr
                ));
            }
        }
        $smarty->assign('Bestellung', $data->tbestellung);
    }
}
