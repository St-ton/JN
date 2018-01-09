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
                    'A' => 'AND',
                    'O' => 'OR'
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
                    'A' => 'AND',
                    'O' => 'OR'
                ]
            ]
        );
        $this->setConfig(
            'rating_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Bewertungsfilters',
            'selectbox',
            163,
            (object) [
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'AND',
                    'O' => 'OR'
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
                    'A' => 'AND',
                    'O' => 'OR'
                ]
            ]
        );
        $this->setConfig(
            'manufacturer_filter_type',
            'A',
            CONF_NAVIGATIONSFILTER,
            'Typ des Herstellerfilters',
            'selectbox',
            231,
            (object) [
                'cBeschreibung' => 'Erlaubt Verorderung oder Verundung der Filterwerte',
                'inputOptions'  => [
                    'A' => 'AND',
                    'O' => 'OR'
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
        $this->removeConfig('rating_filter_type');
        $this->removeConfig('price_range_filter_type');
        $this->removeConfig('manufacturer_filter_type');
    }
}
