<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Catalog\Separator;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\PlausiTrennzeichen;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SeparatorController
 * @package JTL\Router\Controller\Backend
 */
class SeparatorController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_SEPARATOR_VIEW);
        $this->getText->loadAdminLocale('pages/trennzeichen');
        $this->setLanguage();

        $step = 'trennzeichen_uebersicht';
        if (Request::verifyGPCDataInt('save') === 1 && Form::validateToken()) {
            $checks = new PlausiTrennzeichen();
            $checks->setPostVar($_POST);
            $checks->doPlausi();
            $checkItems = $checks->getPlausiVar();
            if (\count($checkItems) === 0) {
                if ($this->save($_POST)) {
                    $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
                    $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_CORE]);
                } else {
                    $this->alertService->addError(\__('errorConfigSave'), 'errorConfigSave');
                    $smarty->assign('xPostVar_arr', $checks->getPostVar());
                }
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
                $smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                    ->assign('xPostVar_arr', $checks->getPostVar());
            }
        }

        return $smarty->assign('step', $step)
            ->assign('route', $this->route)
            ->assign('oTrennzeichenAssoc_arr', Separator::getAll($this->currentLanguageID))
            ->getResponse('trennzeichen.tpl');
    }

    /**
     * @param array $post
     * @return bool
     * @former speicherTrennzeichen()
     */
    private function save(array $post): bool
    {
        $post = Text::filterXSS($post);
        foreach ([\JTL_SEPARATOR_WEIGHT, \JTL_SEPARATOR_AMOUNT, \JTL_SEPARATOR_LENGTH] as $unt) {
            if (!isset($post['nDezimal_' . $unt], $post['cDezZeichen_' . $unt], $post['cTausenderZeichen_' . $unt])) {
                continue;
            }
            $separator = new Separator();
            $separator->setSprache($this->currentLanguageID)
                ->setEinheit($unt)
                ->setDezimalstellen((int)$post['nDezimal_' . $unt])
                ->setDezimalZeichen($post['cDezZeichen_' . $unt])
                ->setTausenderZeichen($post['cTausenderZeichen_' . $unt]);
            $idx = 'kTrennzeichen_' . $unt;
            if (isset($post[$idx])) {
                $separator->setTrennzeichen((int)$post[$idx])->update();
            } elseif (!$separator->save()) {
                return false;
            }
        }
        $this->cache->flushTags([
            \CACHING_GROUP_CORE,
            \CACHING_GROUP_CATEGORY,
            \CACHING_GROUP_OPTION,
            \CACHING_GROUP_ARTICLE
        ]);

        return true;
    }
}
