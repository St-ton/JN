<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseAttribute
 */
class FilterBaseAttribute extends AbstractFilter
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'kMerkmal'     => 'AttributeIDCompat',
        'kMerkmalWert' => 'ValueCompat',
        'cName'        => 'Name'
    ];

    /**
     * FilterBaseAttribute constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct($productFilter)
    {
        parent::__construct($productFilter);
        $this->isCustom = false;
        $this->urlParam = 'm';
    }

    /**
     * sets "kMerkmalWert"
     *
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->value = (int)$id;

        return $this;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_arr = Shop::DB()->selectAll(
            'tseo',
            ['cKey', 'kKey'],
            ['kMerkmalWert', $this->getValue()],
            'cSeo, kSprache',
            'kSprache'
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            foreach ($oSeo_arr as $oSeo) {
                if ($language->kSprache === (int)$oSeo->kSprache) {
                    $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                }
            }
        }
        $oSQL            = new stdClass();
        $oSQL->cMMSelect = 'tmerkmal.cName';
        $oSQL->cMMJOIN   = '';
        $oSQL->cMMWhere  = '';
        if (Shop::getLanguage() > 0 && !standardspracheAktiv()) {
            $oSQL->cMMSelect = 'tmerkmalsprache.cName, tmerkmal.cName AS cMMName';
            $oSQL->cMMJOIN   = ' JOIN tmerkmalsprache 
                                     ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                     AND tmerkmalsprache.kSprache = ' . Shop::getLanguage();
        }
        $oSQL->cMMWhere   = 'tmerkmalwert.kMerkmalWert = ' . $this->getValue();
        $oMerkmalWert_arr = Shop::DB()->query(
            'SELECT tmerkmalwertsprache.cWert, ' . $oSQL->cMMSelect . '
                FROM tmerkmalwert
                JOIN tmerkmalwertsprache 
                    ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND kSprache = ' . Shop::getLanguage() . '
                JOIN tmerkmal ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                ' . $oSQL->cMMJOIN . '
                WHERE ' . $oSQL->cMMWhere, 2
        );
        if (is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
            $oMerkmalWert = $oMerkmalWert_arr[0];
            unset($oMerkmalWert_arr[0]);
            if (isset($oMerkmalWert->cWert) && strlen($oMerkmalWert->cWert) > 0) {
                if (!empty($this->getName())) {
                    $this->setName($oMerkmalWert->cName . ': ' . $oMerkmalWert->cWert);
                } elseif (isset($oMerkmalWert->cMMName) && strlen($oMerkmalWert->cMMName) > 0) {
                    $this->setName($oMerkmalWert->cMMName . ': ' . $oMerkmalWert->cWert);
                }
                if (count($oMerkmalWert_arr) > 0) {
                    foreach ($oMerkmalWert_arr as $oTmpMerkmal) {
                        if (isset($oTmpMerkmal->cName) && strlen($oTmpMerkmal->cName) > 0) {
                            $this->setName($this->getName() . ', ' . $oTmpMerkmal->cName . ': ' . $oTmpMerkmal->cWert);
                        } elseif (isset($oTmpMerkmal->cMMName) && strlen($oTmpMerkmal->cMMName) > 0) {
                            $this->setName($this->getName() . ', ' . $oTmpMerkmal->cMMName . ': ' . $oTmpMerkmal->cWert);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kMerkmalWert';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tmerkmalwert';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        return (new FilterJoin())->setType('JOIN')
             ->setComment('join1 from FilterBaseAttribute')
             ->setTable('(SELECT kArtikel
                              FROM tartikelmerkmal
                              WHERE kMerkmalWert = ' . $this->getValue() . '
                              GROUP BY tartikelmerkmal.kArtikel
                              ) AS tmerkmaljoin')
             ->setOrigin(__CLASS__)
             ->setOn('tmerkmaljoin.kArtikel = tartikel.kArtikel');
    }
}
