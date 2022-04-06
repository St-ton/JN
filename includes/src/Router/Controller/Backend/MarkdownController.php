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
 * Class StatusController
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
        $this->account->redirectOnFailure();

        if (isset($_POST['path']) && Form::validateToken()) {
            $path  = \realpath($_POST['path']);
            $base1 = \realpath(PFAD_ROOT . \PLUGIN_DIR);
            $base2 = \realpath(PFAD_ROOT . \PFAD_PLUGIN);
            if ($path !== false && (\mb_strpos($path, $base1) === 0 || \mb_strpos($path, $base2) === 0)) {
                $info = \pathinfo($path);
                if (\mb_convert_case($info['extension'], \MB_CASE_LOWER) === 'md') {
                    $parseDown      = new Parsedown();
                    $licenseContent = \mb_convert_encoding(
                        $parseDown->text(Text::convertUTF8(\file_get_contents($path))),
                        'HTML-ENTITIES'
                    );
                    $response       = (new Response())->withStatus(200)->withAddedHeader('content-type', 'text/html');
                    $response->getBody()->write('<div class="markdown">' . $licenseContent . '</div>');

                    return $response;
                }
            }
        }

        return (new Response())->withStatus(404);
    }
}
