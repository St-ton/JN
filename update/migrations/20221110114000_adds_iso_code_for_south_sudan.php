<?php declare(strict_types=1);

use JTL\Services\JTL\CountryService;
use JTL\Shop;
use JTL\Template\Config;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221110114000
 */
class Migration_20221110114000 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'adds iso code for south sudan';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->db->executeQuery("INSERT IGNORE INTO  
            tland 
                (cISO, cDeutsch, cEnglisch, nEU, cKontinent, bPermitRegistration, bRequireStateDefinition) 
            VALUES ,
                ('SS', 'Südsudan', 'South Sudan', 0, 'Afrika', 0, 0)");
        Shop::Container()->getCache()->flush(CountryService::CACHE_ID);

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->db->delete('tland', 'cISO', 'SS');
        Shop::Container()->getCache()->flush(CountryService::CACHE_ID);
    }
}