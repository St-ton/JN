<?php declare(strict_types=1);

namespace JTL\Country;

use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Services\JTL\CountryService;
use JTL\Services\JTL\CountryServiceInterface;
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
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * @var AlertServiceInterface
     */
    protected $alertService;

    /**
     * Manager constructor.
     * @param DbInterface $db
     * @param JTLSmarty $smarty
     * @param CountryServiceInterface $countryService
     * @param JTLCacheInterface $cache
     * @param AlertServiceInterface $alertService
     */
    public function __construct(
        DbInterface $db,
        JTLSmarty $smarty,
        CountryServiceInterface $countryService,
        JTLCacheInterface $cache,
        AlertServiceInterface $alertService
    ) {
        $this->db             = $db;
        $this->smarty         = $smarty;
        $this->countryService = $countryService;
        $this->cache          = $cache;
        $this->alertService   = $alertService;
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
            case 'update':
                $this->smarty->assign(
                    'country',
                    $this->countryService->getCountry(Request::verifyGPDataString('cISO'))
                );
                break;
            default:
                break;
        }

        $this->smarty->assign('step', $step)
            ->assign('countries', $this->countryService->getCountrylist())
            ->assign('continents', $this->countryService->getContinents())
            ->display('countrymanager.tpl');
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        $action = 'overview';
        if (Request::verifyGPDataString('action') !== '' && Form::validateToken()) {
            $action = Request::verifyGPDataString('action');
        }
        switch ($action) {
            case 'add':
                $action = $this->addCountry();
                break;
            case 'delete':
                $action = $this->deleteCountry();
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
        $iso = Request::verifyGPDataString('cISO');
        if ($this->countryService->getCountry($iso) !== null) {
            $this->alertService->addAlert(
                Alert::TYPE_DANGER,
                \sprintf(__('errorCountryIsoExists'), $iso),
                'errorCountryIsoExists'
            );
            return 'overview';
        }
        if (Request::postInt('save') === 1) {
            $country                          = new \stdClass();
            $country->cISO                    = \mb_strtoupper($iso);
            $country->cDeutsch                = Request::verifyGPDataString('cDeutsch');
            $country->cEnglisch               = Request::verifyGPDataString('cEnglisch');
            $country->nEU                     = Request::verifyGPDataString('nEU');
            $country->cKontinent              = Request::verifyGPDataString('cKontinent');
            $country->bPermitRegistration     = Request::verifyGPDataString('bPermitRegistration');
            $country->bRequireStateDefinition = Request::verifyGPDataString('bRequireStateDefinition');

            $this->db->insert('tland', $country);
            $this->cache->flush(CountryService::CACHE_ID);
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                \sprintf(__('successCountryAdd'), $iso),
                'successCountryAdd',
                ['saveInSession' => true]
            );

            $this->refreshPage();
        }

        return 'add';
    }

    /**
     * @return string
     */
    private function deleteCountry(): string
    {
        if ($this->db->delete('tland', 'cISO', Request::verifyGPDataString('cISO')) > 0) {
            $this->cache->flush(CountryService::CACHE_ID);
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                \sprintf(__('successCountryDelete'), Request::verifyGPDataString('cISO')),
                'successCountryDelete',
                ['saveInSession' => true]
            );

            $this->refreshPage();
        }

        return 'delete';
    }

    /**
     * @return string
     */
    private function updateCountry(): string
    {
        if (Request::postInt('save') === 1) {
            $country                          = new \stdClass();
            $country->cDeutsch                = Request::verifyGPDataString('cDeutsch');
            $country->cEnglisch               = Request::verifyGPDataString('cEnglisch');
            $country->nEU                     = Request::verifyGPDataString('nEU');
            $country->cKontinent              = Request::verifyGPDataString('cKontinent');
            $country->bPermitRegistration     = Request::verifyGPDataString('bPermitRegistration') === '' ? 0 : 1;
            $country->bRequireStateDefinition = Request::verifyGPDataString('bRequireStateDefinition') === '' ? 0 : 1;

            $this->db->update(
                'tland',
                'cISO',
                Request::verifyGPDataString('cISO'),
                $country
            );
            $this->cache->flush(CountryService::CACHE_ID);
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                \sprintf(__('successCountryUpdate'), Request::verifyGPDataString('cISO')),
                'successCountryUpdate',
                ['saveInSession' => true]
            );

            $this->refreshPage();
        }

        return 'update';
    }

    /**
     * refresh for CountryService
     */
    private function refreshPage(): void
    {
        header('Refresh:0');
        exit;
    }
}
