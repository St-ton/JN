<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Media\Image;
use JTL\Media\IMedia;
use JTL\Media\Media;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ImagesController
 * @package JTL\Router\Controller\Backend
 */
class ImagesController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $this->getText->loadAdminLocale('pages/bilder');
        $this->smarty = $smarty;
        $this->checkPermissions('SETTINGS_SITEMAP_VIEW');

        if (isset($_POST['speichern']) && Form::validateToken()) {
            $this->actionSaveConfig();
        }

        $indices = [
            'kategorien'    => \__('categories'),
            'variationen'   => \__('variations'),
            'artikel'       => \__('product'),
            'hersteller'    => \__('manufacturer'),
            'merkmal'       => \__('attributes'),
            'merkmalwert'   => \__('attributeValues'),
            'opc'           => 'OPC',
            'konfiggruppe'  => \__('configGroups'),
            'news'          => \__('news'),
            'newskategorie' => \__('newscategory')
        ];
        $this->getAdminSectionSettings(\CONF_BILDER);

        return $smarty->assign('indices', $indices)
            ->assign('imgConf', Shop::getSettingSection(\CONF_BILDER))
            ->assign('sizes', ['mini', 'klein', 'normal', 'gross'])
            ->assign('dims', ['breite', 'hoehe'])
            ->assign('route', $this->route)
            ->getResponse('bilder.tpl');
    }

    private function actionSaveConfig(): void
    {
        $shopSettings = Shopsetting::getInstance();
        $oldConfig    = $shopSettings->getSettings([\CONF_BILDER])['bilder'];
        $this->saveAdminSectionSettings(
            \CONF_BILDER,
            Text::filterXSS($_POST),
            [\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE, \CACHING_GROUP_CATEGORY]
        );
        $shopSettings->reset();
        $newConfig     = $shopSettings->getSettings([\CONF_BILDER])['bilder'];
        $confDiff      = \array_diff_assoc($oldConfig, $newConfig);
        $cachesToClear = [];
        $media         = Media::getInstance();
        foreach (\array_keys($confDiff) as $item) {
            if (\strpos($item, 'hersteller') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_MANUFACTURER);
                continue;
            }
            if (\strpos($item, 'variation') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_VARIATION);
                continue;
            }
            if (\strpos($item, 'kategorie') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CATEGORY);
                continue;
            }
            if (\strpos($item, 'merkmalwert') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CHARACTERISTIC_VALUE);
                continue;
            }
            if (\strpos($item, 'merkmal_') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CHARACTERISTIC);
                continue;
            }
            if (\strpos($item, 'opc') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_OPC);
                continue;
            }
            if (\strpos($item, 'konfiggruppe') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CONFIGGROUP);
                continue;
            }
            if (\strpos($item, 'artikel') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_PRODUCT);
                continue;
            }
            if (\strpos($item, 'news') !== false) {
                $cachesToClear[] = $media::getClass(Image::TYPE_NEWS);
                $cachesToClear[] = $media::getClass(Image::TYPE_NEWSCATEGORY);
                continue;
            }
            if (\strpos($item, 'quali') !== false
                || \strpos($item, 'container') !== false
                || \strpos($item, 'skalieren') !== false
                || \strpos($item, 'hintergrundfarbe') !== false
            ) {
                $cachesToClear = $media->getRegisteredClassNames();
                break;
            }
        }
        foreach (\array_unique($cachesToClear) as $class) {
            /** @var IMedia $class */
            $class::clearCache();
        }
    }
}
