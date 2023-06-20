<?php declare(strict_types=1);
/**
 * Change cConf of redirect_save_404 to Y
 *
 * @author sl
 * @created Fri, 14 Apr 2023 11:14:19 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230414111419
 */
class Migration_20230414111419 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Change cConf of redirect_save_404 to Y';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $settingConf = new stdClass();
        $settingConf->cConf = 'Y';

        $this->getDB()->updateRow('teinstellungenconf','cWertName','redirect_save_404', $settingConf);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $settingConf = new stdClass();
        $settingConf->cConf = 'N';

        $this->getDB()->updateRow('teinstellungenconf','cWertName','redirect_save_404', $settingConf);
    }
}
