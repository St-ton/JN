<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterKategorieFilter
 */
class FilterKategorieFilter extends FilterKategorie
{
    /**
     * @return string
     */
    public function getSQLCondition()
    {
        $conf = Shop::getSettings(array(CONF_NAVIGATIONSFILTER));
        if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
            return '(tkategorieartikelgesamt.kOberKategorie = ' . $this->getID() . ' OR tkategorieartikelgesamt.kKategorie = ' . $this->getID() . ') ';
        }

        return ' tkategorieartikel.kKategorie = ' . $this->getID();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        $conf = Shop::getSettings(array(CONF_NAVIGATIONSFILTER));
        $join = new FilterJoin();
        $join->setComment('join from FilterKategorieFilter')
            ->setType('JOIN');
        if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
            $join->setTable('tkategorieartikelgesamt')->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel');
//            return 'JOIN tkategorieartikelgesamt ON tartikel.kArtikel = tkategorieartikelgesamt.kArtikel';
        }
        $join->setTable('tkategorieartikel')->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');

        return [$join];
//        return 'JOIN tkategorieartikel ON tartikel.kArtikel = tkategorieartikel.kArtikel';
    }
}
