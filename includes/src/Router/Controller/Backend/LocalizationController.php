<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\LocalizationCheck\LocalizationCheckFactory;
use JTL\Backend\Permissions;
use JTL\Backend\Status;
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
        $this->checkPermissions(Permissions::DIAGNOSTIC_VIEW);
        $this->getText->loadAdminLocale('pages/localizationcheck');
        $this->getText->loadAdminLocale('pages/categorycheck');

        $type      = $this->request->post('type');
        $languages = \collect(LanguageHelper::getAllLanguages(0, true, true));
        if ($this->tokenIsValid && $type !== null && $this->request->post('action') === 'deleteExcess') {
            $check = (new LocalizationCheckFactory($this->db, $languages))->getCheckByClassName($type);
            if ($check === null) {
                $this->alertService->addWarning('No check found', 'clearerr');
            }
            $deleted = $check->deleteExcessLocalizations();
            $this->alertService->addSuccess(\sprintf(\__('Deleted %d item(s).'), $deleted), 'clearsuccess');
        }

        return $this->smarty->assign('passed', false)
            ->assign('safe_mode', \SAFE_MODE === true)
            ->assign('checkResults', Status::getInstance($this->db, $this->cache)->getLocalizationProblems(false))
            ->assign('languagesById', $languages->keyBy('id')->toArray())
            ->getResponse('localizationcheck.tpl');
    }
}
