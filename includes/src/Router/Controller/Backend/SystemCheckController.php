<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\phpQuery\phpQuery;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Systemcheck\Environment;
use Systemcheck\Platform\Hosting;

/**
 * Class SystemCheckController
 * @package JTL\Router\Controller\Backend
 */
class SystemCheckController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('DIAGNOSTIC_VIEW');
        $this->getText->loadAdminLocale('pages/systemcheck');

        $phpInfo = '';
        if (isset($_GET['phpinfo']) && !\in_array('phpinfo', \explode(',', \ini_get('disable_functions')), true)) {
            \ob_start();
            \phpinfo();
            $content = \ob_get_contents();
            \ob_end_clean();
            $phpInfo = \pq('body', phpQuery::newDocumentHTML($content, \JTL_CHARSET))->html();
        }

        $systemcheck = new Environment();
        $platform    = new Hosting();

        return $smarty->assign('tests', $systemcheck->executeTestGroup('Shop5'))
            ->assign('platform', $platform)
            ->assign('passed', $systemcheck->getIsPassed())
            ->assign('phpinfo', $phpInfo)
            ->getResponse('systemcheck.tpl');
    }
}
