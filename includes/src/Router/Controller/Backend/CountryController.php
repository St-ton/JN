<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Country\Manager;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CountryController
 * @package JTL\Router\Controller\Backend
 */
class CountryController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('COUNTRY_VIEW');
        $this->getText->loadAdminLocale('pages/countrymanager');

        $manager = new Manager(
            $this->db,
            $smarty,
            Shop::Container()->getCountryService(),
            $this->cache,
            $this->alertService,
            $this->getText
        );

        $manager->finalize($manager->getAction());

        return $smarty->assign('route', $route->getPath())
            ->getResponse('countrymanager.tpl');
    }
}
