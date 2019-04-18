<?php
/**
 * correct_selection_wizard_permission
 *
 * @author mh
 * @created Fri, 12 Apr 2019 12:41:20 +0200
 */

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
 * Class Migration_20190418144700
 */
class Migration_20190418144700 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Unify mail template tables';

    public function up()
    {
        unset($_SESSION['emailSyntaxErrorCount'], $_SESSION['exportSyntaxErrorCount']);
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
    }
}
