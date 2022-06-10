<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\LocalizationCheck\LocalizationCheckFactory;
use JTL\Backend\Status;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LocalizationController
 * @package JTL\Router\Controller\Backend
 */
class LocalizationController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('DIAGNOSTIC_VIEW');
        $this->getText->loadAdminLocale('pages/localizationcheck');

        $action    = Request::postVar('action');
        $type      = Request::postVar('type');
        $languages = \collect(LanguageHelper::getAllLanguages(0, true, true));

        if ($action === 'deleteExcess' && $type !== null && Form::validateToken()) {
            $factory = new LocalizationCheckFactory($this->db, $languages);
            $check   = $factory->getCheckByClassName($type);
            if ($check === null) {
                $this->alertService->addWarning('No check found', 'clearerr');
            }
            $deleted = $check->deleteExcessLocalizations();
            $this->alertService->addSuccess(\sprintf(\__('Deleted %d item(s).'), $deleted), 'clearsuccess');
        }

        return $smarty->assign('passed', false)
            ->assign('checkResults', Status::getInstance($this->db, $this->cache)->getLocalizationProblems(false))
            ->assign('languagesById', $languages->keyBy('id')->toArray())
            ->getResponse('localizationcheck.tpl');
    }
}
