<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Catalog\Separator;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\PlausiTrennzeichen;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
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
        $this->checkPermissions('SETTINGS_SEPARATOR_VIEW');
        $this->getText->loadAdminLocale('pages/trennzeichen');
        $this->setzeSprache();

        $step = 'trennzeichen_uebersicht';
        if (Request::verifyGPCDataInt('save') === 1 && Form::validateToken()) {
            $checks = new PlausiTrennzeichen();
            $checks->setPostVar($_POST);
            $checks->doPlausi();
            $checkItems = $checks->getPlausiVar();
            if (\count($checkItems) === 0) {
                if ($this->save($_POST)) {
                    $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
                    $this->cache->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);
                } else {
                    $this->alertService->addError(\__('errorConfigSave'), 'errorConfigSave');
                    $smarty->assign('xPostVar_arr', $checks->getPostVar());
                }
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
                $idx = 'nDezimal_' . JTL_SEPARATOR_WEIGHT;
                if (isset($checkItems[$idx]) && $checkItems[$idx] === 2) {
                    $this->alertService->addError(\__('errorWeightDecimals'), 'errorWeightDecimals');
                }
                $idx = 'nDezimal_' . JTL_SEPARATOR_AMOUNT;
                if (isset($checkItems[$idx]) && $checkItems[$idx] === 2) {
                    $this->alertService->addError(\__('errorAmountDecimals'), 'errorAmountDecimals');
                }
                $smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                    ->assign('xPostVar_arr', $checks->getPostVar());
            }
        }

        return $smarty->assign('step', $step)
            ->assign('oTrennzeichenAssoc_arr', Separator::getAll($_SESSION['editLanguageID']))
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
        foreach ([JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_AMOUNT, JTL_SEPARATOR_LENGTH] as $unit) {
            if (!isset($post['nDezimal_' . $unit], $post['cDezZeichen_' . $unit], $post['cTausenderZeichen_' . $unit])) {
                continue;
            }
            $trennzeichen = new Separator();
            $trennzeichen->setSprache((int)$_SESSION['editLanguageID'])
                ->setEinheit($unit)
                ->setDezimalstellen((int)$post['nDezimal_' . $unit])
                ->setDezimalZeichen($post['cDezZeichen_' . $unit])
                ->setTausenderZeichen($post['cTausenderZeichen_' . $unit]);
            $idx = 'kTrennzeichen_' . $unit;
            if (isset($post[$idx])) {
                $trennzeichen->setTrennzeichen((int)$post[$idx])
                    ->update();
            } elseif (!$trennzeichen->save()) {
                return false;
            }
        }

        $this->cache->flushTags(
            [CACHING_GROUP_CORE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]
        );

        return true;
    }
}
