<?php declare(strict_types=1);

namespace JTL\Reset;

use JTL\DB\DbInterface;

class Reset
{
    /** @var DbInterface */
    private $db;
    
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param ResetContentType $contentType
     *
     * @return $this
     */
    public function doReset(ResetContentType $contentType): self
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        switch ($contentType) {
            case $contentType::PRODUCTS():
                $this->resetProducts();
                break;
            case $contentType::TAXES():
                $this->resetTaxes();
                break;
            case $contentType::REVISIONS():
                $this->db->query('TRUNCATE trevisions');
                break;
            case $contentType::NEWS():
                $this->resetNews();
                break;
            case $contentType::BESTSELLER():
                $this->db->query('TRUNCATE tbestseller');
                break;
            case $contentType::STATS_VISITOR():
                $this->resetVisitorStats();
                break;
            case $contentType::STATS_PRICES():
                $this->db->query('TRUNCATE tpreisverlauf');
                break;
            case $contentType::MESSAGES_AVAILABILITY():
                $this->db->query('TRUNCATE tverfuegbarkeitsbenachrichtigung');
                break;
            case $contentType::SEARCH_REQUESTS():
                $this->resetSearchRequests();
                break;
            case $contentType::RAITINGS():
                $this->resetRaitings();
                break;
            case $contentType::WISHLIST():
                $this->resetWishList();
                break;
            case $contentType::COMPARELIST():
                $this->db->query('TRUNCATE tvergleichsliste');
                $this->db->query('TRUNCATE tvergleichslistepos');
                break;
            case $contentType::CUSTOMERS():
                $this->resetCustomers();
                break;
            case $contentType::ORDERS():
                $this->resetOrders();
                break;
            case $contentType::COUPONS():
                $this->resetCoupons();
                break;
            case $contentType::SETTINGS():
                $this->resetSettings();
                break;
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');
        
        return $this;
    }
    
    private function resetProducts(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tartikel');
        $db->query('TRUNCATE tartikelabnahme');
        $db->query('TRUNCATE tartikelattribut');
        $db->query('TRUNCATE tartikelkategorierabatt');
        $db->query('TRUNCATE tartikelkonfiggruppe');
        $db->query('TRUNCATE tartikelmerkmal');
        $db->query('TRUNCATE tartikelpict');
        $db->query('TRUNCATE tartikelsichtbarkeit');
        $db->query('TRUNCATE tartikelsonderpreis');
        $db->query('TRUNCATE tartikelsprache');
        $db->query('TRUNCATE tartikelwarenlager');
        $db->query('TRUNCATE tattribut');
        $db->query('TRUNCATE tattributsprache');
        $db->query('TRUNCATE tbild');
        $db->query('TRUNCATE teigenschaft');
        $db->query('TRUNCATE teigenschaftkombiwert');
        $db->query('TRUNCATE teigenschaftsichtbarkeit');
        $db->query('TRUNCATE teigenschaftsprache');
        $db->query('TRUNCATE teigenschaftwert');
        $db->query('TRUNCATE teigenschaftwertabhaengigkeit');
        $db->query('TRUNCATE teigenschaftwertaufpreis');
        $db->query('TRUNCATE teigenschaftwertpict');
        $db->query('TRUNCATE teigenschaftwertsichtbarkeit');
        $db->query('TRUNCATE teigenschaftwertsprache');
        $db->query('TRUNCATE teinheit');
        $db->query('TRUNCATE tkategorie');
        $db->query('TRUNCATE tkategorieartikel');
        $db->query('TRUNCATE tkategorieattribut');
        $db->query('TRUNCATE tkategorieattributsprache');
        $db->query('TRUNCATE tkategoriekundengruppe');
        $db->query('TRUNCATE tkategoriemapping');
        $db->query('TRUNCATE tkategoriepict');
        $db->query('TRUNCATE tkategoriesichtbarkeit');
        $db->query('TRUNCATE tkategoriesprache');
        $db->query('TRUNCATE tmediendatei');
        $db->query('TRUNCATE tmediendateiattribut');
        $db->query('TRUNCATE tmediendateisprache');
        $db->query('TRUNCATE tmerkmal');
        $db->query('TRUNCATE tmerkmalsprache');
        $db->query('TRUNCATE tmerkmalwert');
        $db->query('TRUNCATE tmerkmalwertbild');
        $db->query('TRUNCATE tmerkmalwertsprache');
        $db->query('TRUNCATE tpreis');
        $db->query('TRUNCATE tpreisdetail');
        $db->query('TRUNCATE tsonderpreise');
        $db->query('TRUNCATE txsell');
        $db->query('TRUNCATE txsellgruppe');
        $db->query('TRUNCATE thersteller');
        $db->query('TRUNCATE therstellersprache');
        $db->query('TRUNCATE tlieferstatus');
        $db->query('TRUNCATE tkonfiggruppe');
        $db->query('TRUNCATE tkonfigitem');
        $db->query('TRUNCATE tkonfiggruppesprache');
        $db->query('TRUNCATE tkonfigitempreis');
        $db->query('TRUNCATE tkonfigitemsprache');
        $db->query('TRUNCATE twarenlager');
        $db->query('TRUNCATE twarenlagersprache');
        $db->query('TRUNCATE tuploadschema');
        $db->query('TRUNCATE tuploadschemasprache');
        $db->query('TRUNCATE tmasseinheit');
        $db->query('TRUNCATE tmasseinheitsprache');
        $db->query(
            "DELETE FROM tseo
                            WHERE cKey = 'kArtikel'
                            OR cKey = 'kKategorie'
                            OR cKey = 'kMerkmalWert'
                            OR cKey = 'kHersteller'"
        );
    }
    
    private function resetTaxes(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tsteuerklasse');
        $db->query('TRUNCATE tsteuersatz');
        $db->query('TRUNCATE tsteuerzone');
        $db->query('TRUNCATE tsteuerzoneland');
    }
    
    private function resetNews(): void
    {
        $db = $this->db;
        foreach ($db->getObjects('SELECT kNews FROM tnews') as $i) {
            \loescheNewsBilderDir($i->kNews, \PFAD_ROOT . \PFAD_NEWSBILDER);
        }
        $db->query('TRUNCATE tnews');
        $db->delete('trevisions', 'type', 'news');
        $db->query('TRUNCATE tnewskategorie');
        $db->query('TRUNCATE tnewskategorienews');
        $db->query('TRUNCATE tnewskommentar');
        $db->query('TRUNCATE tnewsmonatsuebersicht');
        $db->query(
            "DELETE FROM tseo
                            WHERE cKey = 'kNews'
                              OR cKey = 'kNewsKategorie'
                              OR cKey = 'kNewsMonatsUebersicht'"
        );
    }
    
    private function resetVisitorStats(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tbesucher');
        $db->query('TRUNCATE tbesucherarchiv');
        $db->query('TRUNCATE tbesuchteseiten');
    }
    
    private function resetSearchRequests(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tsuchanfrage');
        $db->query('TRUNCATE tsuchanfrageerfolglos');
        $db->query('TRUNCATE tsuchanfragemapping');
        $db->query('TRUNCATE tsuchanfragencache');
        $db->query('TRUNCATE tsuchcache');
        $db->query('TRUNCATE tsuchcachetreffer');
        $db->delete('tseo', 'cKey', 'kSuchanfrage');
    }
    
    private function resetRaitings(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tartikelext');
        $db->query('TRUNCATE tbewertung');
        $db->query('TRUNCATE tbewertungguthabenbonus');
        $db->query('TRUNCATE tbewertunghilfreich');
    }
    
    private function resetWishList(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE twunschliste');
        $db->query('TRUNCATE twunschlistepos');
        $db->query('TRUNCATE twunschlisteposeigenschaft');
        $db->query('TRUNCATE twunschlisteversand');
    }
    
    private function resetCustomers(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tkunde');
        $db->query('TRUNCATE tkundenattribut');
        $db->query('TRUNCATE tkundendatenhistory');
        $db->query('TRUNCATE tkundenfeld');
        $db->query('TRUNCATE tkundenfeldwert');
        $db->query('TRUNCATE tkundenherkunft');
        $db->query('TRUNCATE tkundenkontodaten');
        $db->query('TRUNCATE tlieferadresse');
        $db->query('TRUNCATE twarenkorbpers');
        $db->query('TRUNCATE twarenkorbperspos');
        $db->query('TRUNCATE twarenkorbpersposeigenschaft');
        $this->resetWishList();
        $this->resetOrders();
    }
    
    private function resetOrders(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tbestellid');
        $db->query('TRUNCATE tbestellstatus');
        $db->query('TRUNCATE tbestellung');
        $db->query('TRUNCATE tlieferschein');
        $db->query('TRUNCATE tlieferscheinpos');
        $db->query('TRUNCATE tlieferscheinposinfo');
        $db->query('TRUNCATE twarenkorb');
        $db->query('TRUNCATE twarenkorbpers');
        $db->query('TRUNCATE twarenkorbperspos');
        $db->query('TRUNCATE twarenkorbpersposeigenschaft');
        $db->query('TRUNCATE twarenkorbpos');
        $db->query('TRUNCATE twarenkorbposeigenschaft');
        $db->query('TRUNCATE tuploaddatei');
        $db->query('TRUNCATE tuploadqueue');
        
        $this->resetUploadFiles();
    }
    
    private function resetUploadFiles(): void
    {
        $uploadfiles = \glob(\PFAD_UPLOADS . '*');

        foreach ($uploadfiles as $file) {
            if (\is_file($file) && \mb_strpos($file, '.') !== 0) {
                \unlink($file);
            }
        }
    }
    
    private function resetCoupons(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE tkupon');
        $db->query('TRUNCATE tkuponbestellung');
        $db->query('TRUNCATE tkuponkunde');
        $db->query('TRUNCATE tkuponsprache');
    }
    
    private function resetSettings(): void
    {
        $db = $this->db;
        $db->query('TRUNCATE teinstellungenlog');
        $db->query(
            'UPDATE teinstellungen
                          INNER JOIN teinstellungen_default
                            USING(cName)
                          SET teinstellungen.cWert = teinstellungen_default.cWert'
        );
    }
}
