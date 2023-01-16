<?php declare(strict_types=1);

use JTL\Services\JTL\CountryService;
use JTL\Shop;
use JTL\Template\Config;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221109110600
 */
class Migration_20221109110600 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'add country iso codes';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->db->executeQuery('Alter table tland ADD UNIQUE UC_cISO(cISO)');
        $this->db->executeQuery("INSERT IGNORE INTO  
            tland 
                (cISO, cDeutsch, cEnglisch, nEU, cKontinent, bPermitRegistration, bRequireStateDefinition) 
            VALUES 
                ('MF', 'Saint-Martin', 'Saint Martin', 1, 'Nordamerika', 0, 0),
                ('SX', 'Sint Maarten', 'Sint Maarten', 0, 'Nordamerika', 0, 0)");
        Shop::Container()->getCache()->flush(CountryService::CACHE_ID);

    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->db->executeQuery('ALTER TABLE tland DROP INDEX UC_cISO');
        $this->db->delete('tland', 'cISO', 'MF');
        $this->db->delete('tland', 'cISO', 'SX');
        Shop::Container()->getCache()->flush(CountryService::CACHE_ID);
    }
}
