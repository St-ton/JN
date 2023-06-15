<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response;
use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MarkdownController
 * @package JTL\Router\Controller\Backend
 */
class MarkdownController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $path         = $request->getParsedBody()['path'] ?? null;
        if ($path !== null && Form::validateToken()) {
            $path  = \realpath($path);
            $base1 = \realpath(\PFAD_ROOT . \PLUGIN_DIR);
            $base2 = \realpath(\PFAD_ROOT . \PFAD_PLUGIN);
            if ($path !== false && (\str_starts_with($path, $base1) || \str_starts_with($path, $base2))) {
                $extension = \pathinfo($path, \PATHINFO_EXTENSION);
                if (\mb_convert_case($extension, \MB_CASE_LOWER) === 'md') {
                    $parseDown      = new Parsedown();
                    $licenseContent = $parseDown->text(Text::convertUTF8(\file_get_contents($path)));
                    $response       = (new Response())->withStatus(200)->withAddedHeader('content-type', 'text/html');
                    $response->getBody()->write('<div class="markdown">' . $licenseContent . '</div>');

                    return $response;
                }
            }
        }

        return (new Response())->withStatus(404);
    }
}
