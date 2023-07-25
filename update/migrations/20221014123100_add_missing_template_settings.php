<?php declare(strict_types=1);

use JTL\Shop;
use JTL\Template\Config;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221014123100
 */
class Migration_20221014123100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missing template settings';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $template = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        if ($template->getName() === 'NOVA' || $template->getParent() === 'NOVA') {
            $config   = new Config($template->getDir(), $this->getDB());
            $settings = Shop::getSettings([CONF_TEMPLATE])['template'];
            foreach ($this->getMissingTemplateSettings() as $setting) {
                if (!isset($settings[$setting->section][$setting->name])) {
                    $config->updateConfigInDB($setting->section, $setting->name, $setting->value);
                }
            }
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $currentTemplate = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        if ($currentTemplate->getName() === 'NOVA' || $currentTemplate->getParent() === 'NOVA') {
            foreach ($this->getMissingTemplateSettings() as $setting) {
                $this->execute(
                    "DELETE FROM ttemplateeinstellungen
                        WHERE cTemplate = '" . $currentTemplate->getDir() . "'
                        AND cSektion = '" . $setting->section . "'
                        AND cName='" . $setting->name . "'"
                );
            }
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);
    }

    /**
     * @return array
     */
    private function getMissingTemplateSettings(): array
    {
        return [
            (object)['section' => 'header', 'name' => 'menu_template', 'value' => 'standard'],
            (object)['section' => 'header', 'name' => 'menu_single_row', 'value' => 'N'],
            (object)['section' => 'header', 'name' => 'menu_multiple_rows', 'value' => 'scroll'],
            (object)['section' => 'header', 'name' => 'menu_center', 'value' => 'center'],
            (object)['section' => 'header', 'name' => 'menu_scroll', 'value' => 'menu'],
            (object)['section' => 'header', 'name' => 'menu_logoheight', 'value' => '49'],
            (object)['section' => 'header', 'name' => 'menu_logo_centered', 'value' => 'N'],
            (object)['section' => 'header', 'name' => 'menu_show_topbar', 'value' => 'Y'],
            (object)['section' => 'header', 'name' => 'menu_search_width', 'value' => '0'],
            (object)['section' => 'header', 'name' => 'menu_search_position', 'value' => 'center'],
            (object)['section' => 'productlist', 'name' => 'variation_select_productlist_gallery', 'value' => '2'],
            (object)['section' => 'productlist', 'name' => 'variation_max_werte_productlist_gallery', 'value' => '3'],
            (object)['section' => 'productdetails', 'name' => 'swatch_slider', 'value' => '12'],
            (object)['section' => 'productdetails', 'name' => 'config_position', 'value' => 'details'],
            (object)['section' => 'productdetails', 'name' => 'config_layout', 'value' => 'list'],
            (object)['section' => 'colors', 'name' => 'primary', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'secondary', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'header-bg-color', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'header-color', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'header-bg-color-secondary', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'header-color-secondary', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'footer-bg-color', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'footer-color', 'value' => ''],
            (object)['section' => 'colors', 'name' => 'copyright-bg-color', 'value' => ''],
            (object)['section' => 'customsass', 'name' => 'customVariables', 'value' => ''],
            (object)['section' => 'customsass', 'name' => 'customContent', 'value' => '']
        ];
    }
}
