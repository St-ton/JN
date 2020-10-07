<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201070123500
 */
class Migration_20201070123500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add seo setting page';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'configgroup_' . \CONF_SEO . '_robot_meta_tags',
            'Robot-Meta-Tags',
            \CONF_SEO,
            'Robot-Meta-Tags',
            null,
            100,
            (object)['cConf' => 'N']
        );
        $this->setConfig(
            'seo_robots_pagination',
            'NF',
            \CONF_SEO,
            'Robots Pagination',
            'selectbox',
            110,
            (object)[
                'cBeschreibung' => 'Robots Pagination',
                'inputOptions'  => [
                    'I' => 'index',
                    'NF' => 'noindex, follow',
                ],
            ]
        );
        $this->setConfig(
            'seo_robots_filter',
            'NF',
            \CONF_SEO,
            'Robots Filter',
            'selectbox',
            120,
            (object)[
                'cBeschreibung' => 'Robots Filter',
                'inputOptions'  => [
                    'I' => 'index',
                    'NF' => 'noindex, follow',
                ],
            ]
        );
        $this->setConfig(
            'seo_robots_manufacturer',
            'I',
            \CONF_SEO,
            'Robots Manufacturer',
            'selectbox',
            130,
            (object)[
                'cBeschreibung' => 'Robots Manufacturer',
                'inputOptions'  => [
                    'I' => 'index',
                    'NF' => 'noindex, follow',
                ],
            ]
        );

        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`) VALUES ('SETTINGS_SEO_VIEW', 'SEO settings');");
        $this->execute("INSERT INTO `teinstellungensektion` VALUES (130, 'SEO', 0, 0, 'SETTINGS_SEO_VIEW');");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('configgroup_' . \CONF_SEO . '_robot_meta_tags');
        $this->removeConfig('seo_robots_pagination');
        $this->removeConfig('seo_robots_filter');
        $this->removeConfig('seo_robots_manufacturer');

        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='SETTINGS_SEO_VIEW';");
        $this->execute("DELETE FROM `teinstellungensektion` WHERE `cName`='SEO';");
    }
}
