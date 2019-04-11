<?php
/**
 * add_nfehlerhaft_texportformat
 *
 * @author mh
 * @created Wed, 03 Apr 2019 11:55:19 +0200
 */

use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\SyntaxChecker;
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
        $this->execute('DELETE FROM texportformat WHERE nSpecial = 1 AND kPlugin = 0');
        $this->execute('ALTER TABLE texportformat ADD COLUMN nFehlerhaft TINYINT(1) DEFAULT 0');
        $this->execute('ALTER TABLE tpluginemailvorlage ADD COLUMN nFehlerhaft TINYINT(1) DEFAULT 0');

        $smarty   = new MailSmarty($this->getDB());
        $renderer = new SmartyRenderer($smarty);
        $checker  = new SyntaxChecker(
            $this->getDB(),
            new TemplateFactory($this->getDB()),
            $renderer,
            new TestHydrator($smarty, $this->getDB(), Shopsetting::getInstance())
        );
        $checker->checkAll();
        $ef = new \JTL\Exportformat(0, $this->getDB());
        $ef->checkAll();
    }

    public function down()
    {
        $this->execute('ALTER TABLE texportformat DROP COLUMN nFehlerhaft');
        $this->execute('ALTER TABLE tpluginemailvorlage DROP COLUMN nFehlerhaft');
    }
}
