<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
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
    /**
     * @inheritdoc
     */
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $this->getText->loadAdminLocale('pages/bilder');
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_IMAGES_VIEW);
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

    /**
     * @return void
     */
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
            if (\str_contains($item, 'hersteller')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_MANUFACTURER);
                continue;
            }
            if (str_contains($item, 'variation')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_VARIATION);
                continue;
            }
            if (\str_contains($item, 'kategorie')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CATEGORY);
                continue;
            }
            if (\str_contains($item, 'merkmalwert')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CHARACTERISTIC_VALUE);
                continue;
            }
            if (\str_contains($item, 'merkmal_')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CHARACTERISTIC);
                continue;
            }
            if (\str_contains($item, 'opc')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_OPC);
                continue;
            }
            if (\str_contains($item, 'konfiggruppe')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_CONFIGGROUP);
                continue;
            }
            if (\str_contains($item, 'artikel')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_PRODUCT);
                continue;
            }
            if (\str_contains($item, 'news')) {
                $cachesToClear[] = $media::getClass(Image::TYPE_NEWS);
                $cachesToClear[] = $media::getClass(Image::TYPE_NEWSCATEGORY);
                continue;
            }
            if (\str_contains($item, 'quali')
                || \str_contains($item, 'container')
                || \str_contains($item, 'skalieren')
                || \str_contains($item, 'hintergrundfarbe')
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
