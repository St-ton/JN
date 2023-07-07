<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Catalog\Separator;
use JTL\Helpers\Text;
use JTL\PlausiTrennzeichen;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::SETTINGS_SEPARATOR_VIEW);
        $this->getText->loadAdminLocale('pages/trennzeichen');
        $this->setLanguage();

        $step = 'trennzeichen_uebersicht';
        if ($this->tokenIsValid && $this->request->requestInt('save') === 1) {
            $checks = new PlausiTrennzeichen();
            $checks->setPostVar($this->request->getBody());
            $checks->doPlausi();
            $checkItems = $checks->getPlausiVar();
            if (\count($checkItems) === 0) {
                if ($this->save($this->request->getBody())) {
                    $this->alertService->addSuccess(\__('successConfigSave'), 'successConfigSave');
                    $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_CORE]);
                } else {
                    $this->alertService->addError(\__('errorConfigSave'), 'errorConfigSave');
                    $this->smarty->assign('xPostVar_arr', $checks->getPostVar());
                }
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
                $this->smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                    ->assign('xPostVar_arr', $checks->getPostVar());
            }
        }

        return $this->smarty->assign('step', $step)
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
        $filtered = Text::filterXSS($post);
        foreach ([\JTL_SEPARATOR_WEIGHT, \JTL_SEPARATOR_AMOUNT, \JTL_SEPARATOR_LENGTH] as $unt) {
            if (!isset(
                $filtered['nDezimal_' . $unt],
                $filtered['cDezZeichen_' . $unt],
                $filtered['cTausenderZeichen_' . $unt]
            )) {
                continue;
            }
            $separator = new Separator();
            $separator->setSprache($this->currentLanguageID)
                ->setEinheit($unt)
                ->setDezimalstellen((int)$filtered['nDezimal_' . $unt])
                ->setDezimalZeichen($filtered['cDezZeichen_' . $unt])
                ->setTausenderZeichen($filtered['cTausenderZeichen_' . $unt]);
            $idx = 'kTrennzeichen_' . $unt;
            if (isset($filtered[$idx])) {
                $separator->setTrennzeichen((int)$filtered[$idx])->update();
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
