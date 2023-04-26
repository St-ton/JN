<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230221062100
 */
class Migration_20230221062100 extends Migration implements IMigration
{
    protected $author      = 'tt';
    protected $description = 'Create translated rma reasons and help text';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'rma', 'rma_qty_sent', 'Anzahl versendet');
        $this->setLocalization('eng', 'rma', 'rma_qty_sent', 'Amount sent');
        
        $this->setLocalization('ger', 'rma', 'rma_helptext', 'Bitte wählen Sie die Anzahl und einen 
        Grund für jeden Artikel den Sie retournieren möchten.');
        $this->setLocalization('eng', 'rma', 'rma_helptext', 'Please select the quantity and a reason 
        for each item you would like to return.');
        
        $this->setLocalization('ger', 'rma', 'rma_reason_other', 'Sonstiges');
        $this->setLocalization('eng', 'rma', 'rma_reason_other', 'Sonstiges');

        $this->setLocalization('ger', 'rma', 'rma_reason_defect', 'Der Artikel ist defekt');
        $this->setLocalization('eng', 'rma', 'rma_reason_defect', 'The product is defect');

        $this->setLocalization('ger', 'rma', 'rma_reason_dont_like', 'Der Artikel entspricht nicht meinen 
        Vorstellungen');
        $this->setLocalization('eng', 'rma', 'rma_reason_dont_like', 'The product does not meet my expectations');

        $this->setLocalization('ger', 'rma', 'rma_reason_missing_parts', 'Der Artikel oder Teile davon fehlen');
        $this->setLocalization('eng', 'rma', 'rma_reason_missing_parts', 'The product or parts of it are missing');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
    }
}
