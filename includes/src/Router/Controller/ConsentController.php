<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Consent\ManagerInterface;
use JTL\Helpers\Form;
use JTL\Shop;
use Laminas\Diactoros\ResponseFactory;
use League\Route\RouteGroup;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConsentController
 * @package JTL\Router\Controller
 */
class ConsentController
{
    /**
     * @param RouteGroup $route
     * @param string     $dynName
     * @return void
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $route->post('/_updateconsent', $this->getResponse(...))
            ->setName('ROUTE_UPDATE_CONSENTPOST' . $dynName)
            ->setStrategy(new JsonStrategy(new ResponseFactory()));
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @return array
     */
    public function getResponse(ServerRequestInterface $request, array $args): array
    {
        if (!Form::validateToken()) {
            return ['status' => 'FAILED', 'data' => 'Invalid token'];
        }
        $manager = Shop::Container()->get(ManagerInterface::class);

        return ['status' => 'OK', 'data' => $manager->save($request->getParsedBody()['data'] ?? '')];
    }
}
