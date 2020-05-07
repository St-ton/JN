<?php declare(strict_types=1);

namespace Template\NOVA;

use JTL\Shop;
use JTL\Template\Bootstrapper;
use scc\DefaultComponentRegistrator;
use scc\Renderer;
use Smarty;

/**
 * Class Bootstrap
 * @package Template\NOVA
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        parent::boot();
        $this->registerPlugins();
    }

    private function registerPlugins(): void
    {
        $smarty = $this->getSmarty();
        $plugins = new Plugins($this->getDB(), $this->getCache());
        $scc     = new DefaultComponentRegistrator(new Renderer($smarty));
        $scc->registerComponents();

        if (isset($_GET['scc-demo']) && Shop::isAdmin()) {
            $smarty->display('demo.tpl');
            die();
        }

        $smarty->registerPlugin(Smarty::PLUGIN_FUNCTION, 'gibPreisStringLocalizedSmarty', [$plugins, 'getLocalizedPrice'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getBoxesByPosition', [$plugins, 'getBoxesByPosition'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'has_boxes', [$plugins, 'hasBoxes'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'imageTag', [$plugins, 'getImgTag'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getCheckBoxForLocation', [$plugins, 'getCheckBoxForLocation'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'hasCheckBoxForLocation', [$plugins, 'hasCheckBoxForLocation'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'aaURLEncode', [$plugins, 'aaURLEncode'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_navigation', [$plugins, 'getNavigation'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_category_array', [$plugins, 'getCategoryArray'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_category_parents', [$plugins, 'getCategoryParents'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'prepare_image_details', [$plugins, 'prepareImageDetails'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_manufacturers', [$plugins, 'getManufacturers'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_cms_content', [$plugins, 'getCMSContent'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_static_route', [$plugins, 'getStaticRoute'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'hasOnlyListableVariations', [$plugins, 'hasOnlyListableVariations'])
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'has_trans', [$plugins, 'hasTranslation'])
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'trans', [$plugins, 'getTranslation'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_product_list', [$plugins, 'getProductList'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'captchaMarkup', [$plugins, 'captchaMarkup'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getStates', [$plugins, 'getStates'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getDecimalLength', [$plugins, 'getDecimalLength'])
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'seofy', [$plugins, 'seofy'])
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getUploaderLang', [$plugins, 'getUploaderLang']);

    }

    /**
     * @inheritdoc
     */
    public function installed(): void
    {
        parent::installed();
        echo '<br>NOVA installed...';
    }

    /**
     * @inheritDoc
     */
    public function enabled(): void
    {
        echo '<br>NOVA enabled...';
        parent::enabled();
    }

    /**
     * @inheritDoc
     */
    public function disabled(): void
    {
        echo '<br>NOVA disabled...';
        parent::enabled();
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion): void
    {
        echo 'NOVA updated from ' . $oldVersion . ' to ' . $newVersion;
        \error_log('NOVA updated from ' . $oldVersion . ' to ' . $newVersion);
    }

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
        echo '<br>NOVA uninstalled';
        parent::uninstalled($deleteData);
    }
}
