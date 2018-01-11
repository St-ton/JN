<?php
/**
 * Add product filter config
 */

/**
 * Class Migration_20180109100600
 */
class Migration_20180109100600 extends Migration implements IMigration
{
    protected $author      = 'Felix Moche';
    protected $description = 'Add product filter config';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'tag_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Tagfilters',
            'selectbox',
            176,
            (object) [
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'category_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Tagfilters',
            'selectbox',
            148,
            (object) [
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'price_range_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Preisspannenfilters',
            'selectbox',
            201,
            (object) [
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'manufacturer_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Herstellerfilters',
            'selectbox',
            121,
            (object) [
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
        $this->setConfig(
            'search_special_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Suchspezialfilters',
            'selectbox',
            141,
            (object) [
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'Verundung',
                    'O' => 'Veroderung'
                ]
            ]
        );
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->removeConfig('tag_filter_type');
        $this->removeConfig('category_filter_type');
        $this->removeConfig('price_range_filter_type');
        $this->removeConfig('manufacturer_filter_type');
        $this->removeConfig('search_special_filter_type');
    }
}
