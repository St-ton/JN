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
    protected $description = 'Syntax checks';

    public function up()
    {
        // moved to Migration_20190901000000 for sequence reasons
    }

    public function down()
    {
    }
}
