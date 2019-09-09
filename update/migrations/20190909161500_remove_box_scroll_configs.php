<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Remove box scroll configs
 *
 * @author mh
 */

class Migration_20190909161500_remove_box_scroll_configs extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove box scroll configs';

    public function up()
    {
        $this->removeConfig('box_bestseller_scrollen');
        $this->removeConfig('box_sonderangebote_scrollen');
        $this->removeConfig('box_neuimsortiment_scrollen');
        $this->removeConfig('box_topangebot_scrollen');
        $this->removeConfig('box_erscheinende_scrollen');
    }

    public function down()
    {

    }
}
