<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use Exception;
use GuzzleHttp\Psr7\Response;
use JTL\Media\Image;
use JTL\Media\Image\Category;
use JTL\Media\Image\Characteristic;
use JTL\Media\Image\CharacteristicValue;
use JTL\Media\Image\ConfigGroup;
use JTL\Media\Image\Manufacturer;
use JTL\Media\Image\News;
use JTL\Media\Image\NewsCategory;
use JTL\Media\Image\OPC;
use JTL\Media\Image\Product;
use JTL\Media\Image\Variation;
use JTL\Media\IMedia;
use JTL\Media\MediaImageRequest;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MediaImageController
 * @package JTL\Router\Controller
 */
class MediaImageController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $types = '{type:category|characteristic|characteristicvalue|configgroup|'
            . 'manufacturer|news|newscategory|product|variation}';
        $id    = '{id:\d+}';
        $size  = '{size:xs|sm|md|lg|xl|os}';
        $name  = '{name:[a-zA-Z0-9 äööüÄÖÜß\@\$\-\_\.\+\!\*\\\'\(\)\,]+}';
        $no    = '{number:\d+}';
        $ext   = '{ext:jpg|jpeg|png|gif|webp}';
        $route->get(
            \sprintf('/media/image/%s/%s/%s/%s~%s.%s', $types, $id, $size, $name, $no, $ext),
            $this->getResponse(...)
        )
            ->setName('mediaImageNumbered' . $dynName);
        $route->get(
            \sprintf('/media/image/%s/%s/%s/%s.%s', $types, $id, $size, $name, $ext),
            $this->getResponse(...)
        )
            ->setName('mediaImage' . $dynName);
        $route->get(
            \sprintf('/media/image/{type:opc}/%s/%s.%s', $size, $name, $ext),
            $this->getResponse(...)
        )
            ->setName('mediaImageOPC' . $dynName);
    }

    /**
     * @param string $type
     * @return IMedia
     */
    private function getMappedClassName(string $type): IMedia
    {
        return match ($type) {
            'category'            => new Category($this->db),
            'characteristic'      => new Characteristic($this->db),
            'characteristicvalue' => new CharacteristicValue($this->db),
            'configgroup'         => new ConfigGroup($this->db),
            'manufacturer'        => new Manufacturer($this->db),
            'news'                => new News($this->db),
            'newscategory'        => new NewsCategory($this->db),
            'product'             => new Product($this->db),
            'variation'           => new Variation($this->db),
            'opc'                 => new OPC($this->db),
        };
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        try {
            $requestURL   = '/' . \ltrim($request->getUri()->getPath(), '/');
            $instance     = $this->getMappedClassName($args['type']);
            $mediaReq     = MediaImageRequest::create($args);
            $allowedNames = $instance->getImageNames($mediaReq);
            if (\count($allowedNames) === 0) {
                throw new Exception('No such image id: ' . (int)$mediaReq->id);
            }
            $imgPath      = null;
            $matchFound   = false;
            $allowedFiles = [];
            foreach ($allowedNames as $allowedName) {
                $mediaReq->path   = $allowedName . '.' . $mediaReq->ext;
                $mediaReq->name   = $allowedName;
                $mediaReq->number = (int)$mediaReq->number;
                $imgPath          = $instance::getThumbByRequest($mediaReq);
                $allowedFiles[]   = $imgPath;
                if ('/' . $imgPath === $requestURL) {
                    $matchFound = true;
                    break;
                }
            }
            if ($matchFound === false) {
                return new RedirectResponse(Shop::getImageBaseURL() . $allowedFiles[0], 301);
            }
            if ($imgPath === null || !\is_file(\PFAD_ROOT . $imgPath)) {
                return Image::render($mediaReq, false, true);
            }
            throw new Exception('File not found: ' . $imgPath);
        } catch (Exception $e) {
            $display = \strtolower(\ini_get('display_errors'));
            if (\in_array($display, ['on', '1', 'true'], true)) {
                echo $e->getMessage();
            }
        }

        return (new Response())->withStatus(404);
    }
}
