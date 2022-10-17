<?php declare(strict_types=1);

/**
 * Fix typo in subject of english version of mail template for delete customer account
 *
 * @author Stefan Langkau
 * @created Mon, 17 Oct 2022 08:22:22 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221017082222
 */
class Migration_20221017082222 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Fix typo in subject of english version of mail template for delete customer account';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $languageId      = (int)$this->fetchOne("SELECT kSprache FROM tsprache WHERE cISO = 'eng'")->kSprache;
        $emailTemplateId = (int)$this->fetchOne("SELECT kEmailvorlage FROM temailvorlage " .
            " WHERE cModulId = 'core_jtl_account_geloescht'")->kEmailvorlage;
        $where           = " WHERE cBetreff ='You account has been deleted' AND ksprache = " . $languageId .
            " AND kEmailvorlage = " . $emailTemplateId;

        $this->execute("UPDATE temailvorlagespracheoriginal SET cBetreff = 'Your account has been deleted'" . $where);
        $this->execute("UPDATE temailvorlagesprache SET cBetreff = 'Your account has been deleted'" . $where);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $languageId      = (int)$this->fetchOne("SELECT kSprache FROM tsprache WHERE cISO = 'eng'")->kSprache;
        $emailTemplateId = (int)$this->fetchOne("SELECT kEmailvorlage FROM temailvorlage " .
            " WHERE cModulId = 'core_jtl_account_geloescht'")->kEmailvorlage;
        $where           = " WHERE cBetreff ='Your account has been deleted' AND ksprache = " . $languageId .
            " AND kEmailvorlage = " . $emailTemplateId;

        $this->execute("UPDATE temailvorlagespracheoriginal SET cBetreff = 'You account has been deleted'" . $where);
        $this->execute("UPDATE temailvorlagesprache SET cBetreff = 'You account has been deleted'" . $where);
    }
}
