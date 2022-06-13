<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Country\Manager;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CountryController
 * @package JTL\Router\Controller\Backend
 */
class CountryController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::COUNTRY_VIEW);
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

        return $smarty->assign('route', $this->route)
            ->getResponse('countrymanager.tpl');
    }
}
