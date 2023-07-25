<?php declare(strict_types=1);

use JTL\Shop;
use JTL\Template\Config;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211102141400
 */
class Migration_20211102141400 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add template setting mobile start category';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $template = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        $config   = new Config($template->getDir(), $this->getDB());
        $settings = Shop::getSettings([CONF_TEMPLATE])['template'];
        if (!isset($settings['megamenu']['mobile_start_category'])
            && ($template->getName() === 'NOVA' || $template->getParent() === 'NOVA')
        ) {
            $config->updateConfigInDB('megamenu', 'mobile_start_category', 'N');
        }
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $currentTemplate = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        $this->execute(
            "DELETE FROM ttemplateeinstellungen
                WHERE cTemplate = '" . $currentTemplate->getDir() . "' 
                AND cSektion = 'megamenu' 
                AND cName='mobile_start_category'"
        );
    }
}
