<?php
/**
 * syntax checks
 *
 * @author fm
 * @created Thu, 18 Apr 2019 14:47:00 +0200
 */

use JTL\Exportformat;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\SyntaxChecker;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200522000000
 */
class Migration_20200522000000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Syntax checks';

    /**
     * @inheritDoc
     */
    public function up()
    {
        if (\PHP_SAPI === 'cli') {
            // removed in cli environment due to runtime reasons
            return;
        }

        // fix for cli: SHOP-4321
        Shop::Container()->getGetText();

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

    /**
     * @inheritDoc
     */
    public function down()
    {
        unset($_SESSION['emailSyntaxErrorCount'], $_SESSION['exportSyntaxErrorCount']);
    }
}
