<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Consent\ManagerInterface;
use JTL\Helpers\Form;
use JTL\Shop;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ConsentController
 * @package JTL\Router\Controller
 */
class ConsentController
{
    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @return array
     */
    public function handle(ServerRequestInterface $request, array $args): array
    {
        if (!Form::validateToken()) {
            return ['status' => 'FAILED', 'data' => 'Invalid token'];
        }
        $manager = Shop::Container()->get(ManagerInterface::class);

        return ['status' => 'OK', 'data' => $manager->save($request->getParsedBody()['data'] ?? '')];
    }
}
