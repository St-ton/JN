<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\IO\IO;
use JTL\IO\IOMethods;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class IOController
 * @package JTL\Router\Controller
 */
class IOController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $route->get('/io', [$this, 'getResponse'])->setName('ROUTE_IO' . $dynName);
        $route->post('/io', [$this, 'getResponse'])->setName('ROUTE_IOPOST' . $dynName);
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        Shop::setPageType(\PAGE_IO);
        $io        = IO::getInstance();
        $ioMethods = new IOMethods($io, $this->db);
        $ioMethods->registerMethods();
        $smarty->setCaching(false)
            ->assign('nSeitenTyp', \PAGE_IO)
            ->assign('imageBaseURL', Shop::getImageBaseURL())
            ->assign('ShopURL', Shop::getURL())
            ->assignDeprecated('BILD_KEIN_KATEGORIEBILD_VORHANDEN', \BILD_KEIN_KATEGORIEBILD_VORHANDEN, '5.0.0')
            ->assignDeprecated('BILD_KEIN_ARTIKELBILD_VORHANDEN', \BILD_KEIN_ARTIKELBILD_VORHANDEN, '5.0.0')
            ->assignDeprecated('BILD_KEIN_HERSTELLERBILD_VORHANDEN', \BILD_KEIN_HERSTELLERBILD_VORHANDEN, '5.0.0')
            ->assignDeprecated('BILD_KEIN_MERKMALBILD_VORHANDEN', \BILD_KEIN_MERKMALBILD_VORHANDEN, '5.0.0')
            ->assignDeprecated('BILD_KEIN_MERKMALWERTBILD_VORHANDEN', \BILD_KEIN_MERKMALWERTBILD_VORHANDEN, '5.0.0');

        if (!isset($_REQUEST['io'])) {
            return (new Response())->withStatus(400);
        }

        $requestData = $_REQUEST['io'];

        \executeHook(\HOOK_IO_HANDLE_REQUEST, [
            'io'      => &$io,
            'request' => &$requestData
        ]);

        try {
            $data = $io->handleRequest($requestData);
        } catch (\Exception $e) {
            $response = new Response();
            $response->getBody()->write($e->getMessage());

            return $response->withStatus(500);
        }
        if (\ob_get_length() > 0) {
            \ob_end_clean();
        }

        return new Response\JsonResponse(
            $data ?? (object)[],
            200,
            [
                'Last-Modified' => [\gmdate('D, d M Y H:i:s') . ' GMT'],
                'Cache-Control' => ['no-cache, must-revalidate'],
                'Pragma'        => ['no-cache'],
                'Content-type'  => ['application/json'],
                'Expires'       => ['Mon, 26 Jul 1997 05:00:00 GMT']
            ]
        );
    }
}
