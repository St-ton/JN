<?php
/**
 * add_nfehlerhaft_texportformat
 *
 * @author mh
 * @created Wed, 03 Apr 2019 11:55:19 +0200
 */

use JTL\DB\ReturnType;
use JTL\Exportformat;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\SyntaxChecker;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190403115519
 */
class Migration_20190403115519 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Add nFehlerhaft to texportformat, tpluginemailvorlage';

    public function up()
    {
        unset($_SESSION['emailSyntaxErrorCount'], $_SESSION['exportSyntaxErrorCount']);
        $id = $this->getDB()->query(
            "SELECT kEmailvorlage 
                FROM temailvorlage 
                WHERE cModulId = 'core_jtl_rma_submitted'",
            ReturnType::SINGLE_OBJECT
        );
        if (isset($id->kEmailvorlage)) {
            $this->getDB()->delete('temailvorlage', 'kEmailvorlage', $id->kEmailvorlage);
            $this->getDB()->delete('temailvorlagesprache', 'kEmailvorlage', $id->kEmailvorlage);
            $this->getDB()->delete('temailvorlagespracheoriginal', 'kEmailvorlage', $id->kEmailvorlage);
        }
        $this->execute("UPDATE temailvorlagesprache SET cBetreff = '' WHERE kEmailvorlage > 0 AND cBetreff IS NULL");
        $this->execute("UPDATE temailvorlagespracheoriginal 
            SET cBetreff = '' WHERE kEmailvorlage > 0 AND cBetreff IS NULL"
        );
        $this->execute('DELETE FROM texportformat WHERE nSpecial = 1 AND kPlugin = 0');
        $this->execute('ALTER TABLE texportformat ADD COLUMN nFehlerhaft TINYINT(1) DEFAULT 0');
        $this->execute('ALTER TABLE tpluginemailvorlage ADD COLUMN nFehlerhaft TINYINT(1) DEFAULT 0');
        $this->execute('ALTER TABLE temailvorlagesprache 
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE temailvorlagespracheoriginal 
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagesprache 
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagespracheoriginal
            CHANGE COLUMN `cDateiname` `cPDFNames` VARCHAR(255) NULL DEFAULT NULL');

        $smarty   = new MailSmarty($this->getDB());
        $renderer = new SmartyRenderer($smarty);
        $checker  = new SyntaxChecker(
            $this->getDB(),
            new TemplateFactory($this->getDB()),
            $renderer,
            new TestHydrator($smarty, $this->getDB(), Shopsetting::getInstance())
        );
        $checker->checkAll();
        $ef = new Exportformat(0, $this->getDB());
        $ef->checkAll();
    }

    public function down()
    {
        unset($_SESSION['emailSyntaxErrorCount'], $_SESSION['exportSyntaxErrorCount']);
        $this->execute('ALTER TABLE texportformat DROP COLUMN nFehlerhaft');
        $this->execute('ALTER TABLE tpluginemailvorlage DROP COLUMN nFehlerhaft');
        $this->execute('ALTER TABLE temailvorlagesprache 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE temailvorlagespracheoriginal 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagesprache 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
        $this->execute('ALTER TABLE tpluginemailvorlagespracheoriginal 
            CHANGE COLUMN `cPDFNames` `cDateiname` VARCHAR(255) NULL DEFAULT NULL');
    }
}
