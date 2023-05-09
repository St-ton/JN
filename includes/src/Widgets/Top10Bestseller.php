<?php declare(strict_types=1);

namespace JTL\Widgets;


/**
 * Class Top10Bestseller
 * @package Plugin\jtl_widgets
 */
class Top10Bestseller extends AbstractWidget
{
    /**
     * @inheritDoc
     */
    public function init(): void
    {
        if (\method_exists($this, 'setPermission')) {
            $this->setPermission('ORDER_VIEW');
        }

        $bestsellers = $this->getDB()->getObjects(
            'SELECT tbestseller.*, twarenkorbpos.cName
                FROM tbestseller
                JOIN twarenkorbpos 
                    ON twarenkorbpos.kArtikel = tbestseller.kArtikel
                    AND twarenkorbpos.nPosTyp = :tp
                JOIN tbestellung 
                    ON tbestellung.kWarenkorb = twarenkorbpos.kWarenkorb
                    AND DATE_SUB(NOW(), INTERVAL 7 DAY) < tbestellung.dErstellt
                GROUP BY tbestseller.kArtikel
                ORDER BY tbestseller.fAnzahl DESC
                LIMIT 10',
            ['tp' => \C_WARENKORBPOS_TYP_ARTIKEL]
        );
        $this->getSmarty()->assign('bestsellers', $bestsellers);
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->getSmarty()->fetch('tpl_inc/widgets/widgetTop10Bestseller.tpl');
    }
}
