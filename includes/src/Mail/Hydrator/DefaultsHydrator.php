<?php declare(strict_types=1);

namespace JTL\Mail\Hydrator;

use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Firma;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageModel;
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
     * DefaultsHydrator constructor.
     * @param JTLSmarty   $smarty
     * @param DbInterface $db
     * @param Shopsetting $settings
     */
    public function __construct(protected JTLSmarty $smarty, protected DbInterface $db, protected Shopsetting $settings)
    {
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
    public function hydrate(?object $data, LanguageModel $language): void
    {
        $data         = $data ?? new stdClass();
        $data->tkunde = $data->tkunde ?? new Customer();

        if (!isset($data->tkunde->kKundengruppe) || !$data->tkunde->kKundengruppe) {
            $data->tkunde->kKundengruppe = CustomerGroup::getDefaultGroupID();
        }
        $data->tfirma        = new Firma(true, $this->db);
        $data->tkundengruppe = new CustomerGroup($data->tkunde->kKundengruppe);
        $customer            = $data->tkunde instanceof Customer
            ? $data->tkunde->localize($language)
            : $this->localizeCustomer($language, $data->tkunde);

        $this->smarty->assign('int_lang', $language)
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
     * @inheritdoc
     */
    public function getSmarty(): JTLSmarty
    {
        return $this->smarty;
    }

    /**
     * @inheritdoc
     */
    public function setSmarty(JTLSmarty $smarty): void
    {
        $this->smarty = $smarty;
    }

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): Shopsetting
    {
        return $this->settings;
    }

    /**
     * @inheritdoc
     */
    public function setSettings(Shopsetting $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @param LanguageModel     $lang
     * @param stdClass|Customer $customer
     * @return mixed
     */
    private function localizeCustomer(LanguageModel $lang, $customer)
    {
        $language = Shop::Lang();
        if ($language->gibISO() !== $lang->getCode()) {
            $language->setzeSprache($lang->getCode());
            $language->autoload();
        }
        if (isset($customer->cAnrede)) {
            if ($customer->cAnrede === 'w') {
                $customer->cAnredeLocalized = $language->get('salutationW');
            } elseif ($customer->cAnrede === 'm') {
                $customer->cAnredeLocalized = $language->get('salutationM');
            } else {
                $customer->cAnredeLocalized = $language->get('salutationGeneral');
            }
        }
        $customer = GeneralObject::deepCopy($customer);
        if (isset($customer->cLand)) {
            if (isset($_SESSION['Kunde'])) {
                $_SESSION['Kunde']->cLand = $customer->cLand;
            }
            if (($country = Shop::Container()->getCountryService()->getCountry($customer->cLand)) !== null) {
                $customer->angezeigtesLand = $country->getName($lang->getId());
            }
        }

        return $customer;
    }
}
