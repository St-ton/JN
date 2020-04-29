<?php
/**
 * Defaults for new template settings in productlist
 *
 * @author fp
 * @created Tue, 13 Jun 2017 14:48:59 +0200
 */

use JTL\Template;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170613144859
 */
class Migration_20170613144859 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Defaults for new template settings in productlist';

    public function up()
    {
        $template = Template::getInstance();
        $config   = $template->getConfig();

        if ($template->getModel()->getName() === 'Evo' || $template->getModel()->getParent() === 'Evo') {
            if (!isset($config['productlist']['variation_select_productlist'])) {
                $template->setConfig($template->getModel()->getDir(), 'productlist', 'variation_select_productlist', 'N');
            }
            if (!isset($config['productlist']['variation_select_productlist'])) {
                $template->setConfig($template->getModel()->getDir(), 'productlist', 'quickview_productlist', 'N');
            }
            if (!isset($config['productlist']['variation_select_productlist'])) {
                $template->setConfig($template->getModel()->getDir(), 'productlist', 'hover_productlist', 'N');
            }
        }
    }

    public function down()
    {
        $template = Template::getInstance();
        $this->execute("DELETE FROM ttemplateeinstellungen WHERE cTemplate = '" . $template->getModel()->getDir() . "' AND cSektion = 'productlist'");
    }
}
