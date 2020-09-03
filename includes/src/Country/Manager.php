<?php declare(strict_types=1);

namespace JTL\Country;

use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Services\JTL\CountryServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class Manager
 * @package JTL\Manager
 */
class Manager
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * @var CountryServiceInterface
     */
    protected $countryService;

    /**
     * Manager constructor.
     * @param DbInterface $db
     * @param JTLSmarty $smarty
     * @param CountryServiceInterface $countryService
     */
    public function __construct(DbInterface $db, JTLSmarty $smarty, CountryServiceInterface $countryService)
    {
        $this->db             = $db;
        $this->smarty         = $smarty;
        $this->countryService = $countryService;
    }

    /**
     * @param string $step
     * @throws \SmartyException
     */
    public function finalize(string $step): void
    {
        switch ($step) {
            case 'add':
                break;
            default:
                $this->smarty->assign('countries', $this->countryService->getCountrylist())
                    ->assign('continents', $this->countryService->getContinents());
                break;
        }

        $this->smarty->assign('step', $step)
            ->display('countrymanager.tpl');
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        $action = 'overview';
        if (isset($_REQUEST['action']) && Form::validateToken()) {
            $action = $_REQUEST['action'];
        }
        switch ($action) {
            case 'add':
                $action = $this->addCountry();
                break;
            case 'remove':
                $action = $this->removeCountry();
                break;
            case 'update':
                $action = $this->updateCountry();
                break;
            default:
                break;
        }

        return $action;
    }

    /**
     * @return string
     */
    private function addCountry(): string
    {
        if (Request::postInt('save') !== 0) {
            $country             = new \stdClass();
            $country->cISO       = Request::verifyGPDataString('ISO');
            $country->cDeustch   = Request::verifyGPDataString('cDeustch');
            $country->cEnglisch  = Request::verifyGPDataString('cEnglisch');
            $country->nEU        = Request::verifyGPDataString('nEU');
            $country->cKontinent = Request::verifyGPDataString('cKontinent');

            $this->db->insert('tland', $country);
        }

        return 'add';
    }

    /**
     * @return string
     */
    private function removeCountry(): string
    {
        $this->db->delete('tland', 'cISO', $_REQUEST['action']);

        return 'remove';
    }

    /**
     * @return string
     */
    private function updateCountry(): string
    {
        if (Request::postInt('save') !== 0) {
            $country             = new \stdClass();
            $country->cDeustch   = Request::verifyGPDataString('cDeustch');
            $country->cEnglisch  = Request::verifyGPDataString('cEnglisch');
            $country->nEU        = Request::verifyGPDataString('nEU');
            $country->cKontinent = Request::verifyGPDataString('cKontinent');

            $this->db->update(
                'tland',
                'cISO',
                Request::verifyGPDataString('ISO'),
                $country
            );
        }

        return 'update';
    }
}
