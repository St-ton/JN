<?php declare(strict_types=1);

namespace JTL\Country;

use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
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
                $action = $this->addCountry(Text::filterXSS($_POST));
                break;
            case 'delete':
                $action = $this->deleteCountry();
                break;
            case 'update':
                $action = $this->updateCountry(Text::filterXSS($_POST));
                break;
            default:
                break;
        }

        return $action;
    }

    /**
     * @param array $postData
     * @return string
     */
    private function addCountry(array $postData): string
    {
        $iso = \mb_strtoupper($postData['cISO'] ?? '');
        if ($this->countryService->getCountry($iso) !== null) {
            $this->alertService->addAlert(
                Alert::TYPE_DANGER,
                \sprintf(__('errorCountryIsoExists'), $iso),
                'errorCountryIsoExists'
            );
            return 'overview';
        }
        if ($iso !== '' && Request::postInt('save') === 1) {
            $country                          = new \stdClass();
            $country->cISO                    = $iso;
            $country->cDeutsch                = $postData['cDeutsch'];
            $country->cEnglisch               = $postData['cEnglisch'];
            $country->nEU                     = $postData['nEU'];
            $country->cKontinent              = $postData['cKontinent'];
            $country->bPermitRegistration     = $postData['bPermitRegistration'];
            $country->bRequireStateDefinition = $postData['bRequireStateDefinition'];

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
        $iso = Text::filterXSS(Request::verifyGPDataString('cISO'));
        if ($this->db->delete('tland', 'cISO', $iso) > 0) {
            $this->cache->flush(CountryService::CACHE_ID);
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                \sprintf(__('successCountryDelete'), $iso),
                'successCountryDelete',
                ['saveInSession' => true]
            );

            $this->refreshPage();
        }

        return 'delete';
    }

    /**
     * @param array $postData
     * @return string
     */
    private function updateCountry(array $postData): string
    {
        if (Request::postInt('save') === 1) {
            $country                          = new \stdClass();
            $country->cDeutsch                = $postData['cDeutsch'];
            $country->cEnglisch               = $postData['cEnglisch'];
            $country->nEU                     = $postData['nEU'];
            $country->cKontinent              = $postData['cKontinent'];
            $country->bPermitRegistration     = $postData['bPermitRegistration'];
            $country->bRequireStateDefinition = $postData['bRequireStateDefinition'];

            $this->db->update(
                'tland',
                'cISO',
                $postData['cISO'],
                $country
            );
            $this->cache->flush(CountryService::CACHE_ID);
            $this->alertService->addAlert(
                Alert::TYPE_SUCCESS,
                \sprintf(__('successCountryUpdate'), $postData['cISO']),
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
