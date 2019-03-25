<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Hydrator;

use JTL\Customer\Kunde;
use JTL\Customer\Kundengruppe;
use JTL\DB\DbInterface;
use JTL\Firma;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use stdClass;

/**
 * Class DefaultsHydrator
 * @package JTL\Mail\Hydrator
 */
class DefaultsHydrator implements HydratorInterface
{
    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var Shopsetting
     */
    protected $settings;

    /**
     * DefaultsHydrator constructor.
     * @param JTLSmarty   $smarty
     * @param DbInterface $db
     * @param Shopsetting $settings
     */
    public function __construct(JTLSmarty $smarty, DbInterface $db, Shopsetting $settings)
    {
        $this->smarty   = $smarty;
        $this->db       = $db;
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public function add(string $variable, $content): void
    {
        $this->smarty->assign($variable, $content);
    }

    /**
     * @inheritdoc
     */
    public function hydrate(?object $data, object $lang): void
    {
        $data         = $data ?? new stdClass();
        $data->tkunde = $data->tkunde ?? new Kunde();

        if (!isset($data->tkunde->kKundengruppe) || !$data->tkunde->kKundengruppe) {
            $data->tkunde->kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $data->tfirma        = new Firma();
        $data->tkundengruppe = new Kundengruppe($data->tkunde->kKundengruppe);
        $customer            = $data->tkunde instanceof Kunde
            ? $data->tkunde->localize($lang)
            : $this->localizeCustomer($lang, $data->tkunde);

        $this->smarty->assign('int_lang', $lang)
            ->assign('Firma', $data->tfirma)
            ->assign('Kunde', $customer)
            ->assign('Kundengruppe', $data->tkundengruppe)
            ->assign('NettoPreise', $data->tkundengruppe->isMerchant())
            ->assign('ShopLogoURL', Shop::getLogo(true))
            ->assign('ShopURL', Shop::getURL())
            ->assign('Einstellungen', $this->settings)
            ->assign('IP', Text::htmlentities(Text::filterXSS(Request::getRealIP())));
    }

    /**
     * @param object         $lang
     * @param stdClass|Kunde $customer
     * @return mixed
     */
    private function localizeCustomer($lang, $customer)
    {
        $language = Shop::Lang();
        if ($language->gibISO() !== $lang->cISO) {
            $language->setzeSprache($lang->cISO);
            $language->autoload();
        }
        if (isset($customer->cAnrede)) {
            if ($customer->cAnrede === 'w') {
                $customer->cAnredeLocalized = Shop::Lang()->get('salutationW');
            } elseif ($customer->cAnrede === 'm') {
                $customer->cAnredeLocalized = Shop::Lang()->get('salutationM');
            } else {
                $customer->cAnredeLocalized = Shop::Lang()->get('salutationGeneral');
            }
        }
        $customer = GeneralObject::deepCopy($customer);
        if (isset($customer->cLand)) {
            $cISOLand = $customer->cLand;
            $sel_var  = 'cDeutsch';
            if (mb_convert_case($lang->cISO, MB_CASE_LOWER) !== 'ger') {
                $sel_var = 'cEnglisch';
            }
            $land = $this->db->select(
                'tland',
                'cISO',
                $customer->cLand,
                null,
                null,
                null,
                null,
                false,
                $sel_var . ' AS cName, cISO'
            );
            if (isset($land->cName)) {
                $customer->cLand = $land->cName;
            }
        }
        if (isset($_SESSION['Kunde'], $cISOLand)) {
            $_SESSION['Kunde']->cLand = $cISOLand;
        }

        return $customer;
    }
}
