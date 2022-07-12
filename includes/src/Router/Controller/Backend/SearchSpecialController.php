<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SearchSpecialController
 * @package JTL\Router\Controller\Backend
 */
class SearchSpecialController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_SPECIALPRODUCTS_VIEW);
        $this->getText->loadAdminLocale('pages/suchspecials');
        $this->setLanguage();
        if (Request::verifyGPCDataInt('einstellungen') === 1) {
            $this->saveAdminSectionSettings(\CONF_SUCHSPECIAL, $_POST);
        } elseif (Request::postInt('suchspecials') === 1 && Form::validateToken()) {
            $this->actionSave(Text::filterXSS($_POST));
        }

        $ssSeoData      = $this->db->selectAll(
            'tseo',
            ['cKey', 'kSprache'],
            ['suchspecial', $this->currentLanguageID],
            '*',
            'kKey'
        );
        $searchSpecials = [];
        foreach ($ssSeoData as $searchSpecial) {
            $searchSpecials[$searchSpecial->kKey] = $searchSpecial->cSeo;
        }
        $this->getAdminSectionSettings(\CONF_SUCHSPECIAL);

        return $smarty->assign('oSuchSpecials_arr', $searchSpecials)
            ->assign('step', 'suchspecials')
            ->assign('route', $this->route)
            ->getResponse('suchspecials.tpl');
    }

    /**
     * @param array $postData
     * @return void
     */
    private function actionSave(array $postData): void
    {
        $searchSpecials   = $this->db->selectAll(
            'tseo',
            ['cKey', 'kSprache'],
            ['suchspecial', $this->currentLanguageID],
            '*',
            'kKey'
        );
        $ssTmp            = [];
        $ssToDelete       = [];
        $bestSellerSeo    = \strip_tags($this->db->escape($postData['bestseller']));
        $specialOffersSeo = $this->db->escape($postData['sonderangebote']);
        $newProductsSeo   = \strip_tags($this->db->escape($postData['neu_im_sortiment']));
        $topOffersSeo     = \strip_tags($this->db->escape($postData['top_angebote']));
        $releaseSeo       = \strip_tags($this->db->escape($postData['in_kuerze_verfuegbar']));
        $topRatedSeo      = \strip_tags($this->db->escape($postData['top_bewertet']));
        if (\mb_strlen($bestSellerSeo) > 0 && !$this->checkSeo(
            $searchSpecials,
            $bestSellerSeo,
            \SEARCHSPECIALS_BESTSELLER
        )) {
            $bestSellerSeo = Seo::checkSeo(Seo::getSeo($bestSellerSeo, true));

            if ($bestSellerSeo !== $postData['bestseller']) {
                $this->alertService->addNotice(
                    \sprintf(
                        \__('errorBestsellerExistRename'),
                        $postData['bestseller'],
                        $bestSellerSeo
                    ),
                    'errorBestsellerExistRename'
                );
            }
            $bestSeller       = new stdClass();
            $bestSeller->kKey = \SEARCHSPECIALS_BESTSELLER;
            $bestSeller->cSeo = $bestSellerSeo;

            $ssTmp[] = $bestSeller;
        } elseif (\mb_strlen($bestSellerSeo) === 0) {
            $ssToDelete[] = \SEARCHSPECIALS_BESTSELLER;
        }
        // Pruefe Sonderangebote
        if (\mb_strlen($specialOffersSeo) > 0 && !$this->checkSeo(
            $searchSpecials,
            $specialOffersSeo,
            \SEARCHSPECIALS_SPECIALOFFERS
        )) {
            $specialOffersSeo = Seo::checkSeo(Seo::getSeo($specialOffersSeo, true));

            if ($specialOffersSeo !== $postData['sonderangebote']) {
                $this->alertService->addNotice(
                    \sprintf(
                        \__('errorSpecialExistRename'),
                        $postData['sonderangebote'],
                        $specialOffersSeo
                    ),
                    'errorSpecialExistRename'
                );
            }
            $specialOffer       = new stdClass();
            $specialOffer->kKey = \SEARCHSPECIALS_SPECIALOFFERS;
            $specialOffer->cSeo = $specialOffersSeo;

            $ssTmp[] = $specialOffer;
        } elseif (\mb_strlen($specialOffersSeo) === 0) {
            // cSeo loeschen
            $ssToDelete[] = \SEARCHSPECIALS_SPECIALOFFERS;
        }
        // Pruefe Neu im Sortiment
        if (\mb_strlen($newProductsSeo) > 0 && !$this->checkSeo(
            $searchSpecials,
            $newProductsSeo,
            \SEARCHSPECIALS_NEWPRODUCTS
        )) {
            $newProductsSeo = Seo::checkSeo(Seo::getSeo($newProductsSeo, true));

            if ($newProductsSeo !== $postData['neu_im_sortiment']) {
                $this->alertService->addNotice(
                    \sprintf(
                        \__('errorNewExistRename'),
                        $postData['neu_im_sortiment'],
                        $newProductsSeo
                    ),
                    'errorNewExistRename'
                );
            }
            $newProducts       = new stdClass();
            $newProducts->kKey = \SEARCHSPECIALS_NEWPRODUCTS;
            $newProducts->cSeo = $newProductsSeo;

            $ssTmp[] = $newProducts;
        } elseif (\mb_strlen($newProductsSeo) === 0) {
            // cSeo leoschen
            $ssToDelete[] = \SEARCHSPECIALS_NEWPRODUCTS;
        }
        // Pruefe Top Angebote
        if (\mb_strlen($topOffersSeo) > 0 && !$this->checkSeo(
            $searchSpecials,
            $topOffersSeo,
            \SEARCHSPECIALS_TOPOFFERS
        )) {
            $topOffersSeo = Seo::checkSeo(Seo::getSeo($topOffersSeo, true));

            if ($topOffersSeo !== $postData['top_angebote']) {
                $this->alertService->addNotice(
                    \sprintf(
                        \__('errorTopProductsExistRename'),
                        $postData['top_angebote'],
                        $topOffersSeo
                    ),
                    'errorTopProductsExistRename'
                );
            }
            $topOffers       = new stdClass();
            $topOffers->kKey = \SEARCHSPECIALS_TOPOFFERS;
            $topOffers->cSeo = $topOffersSeo;

            $ssTmp[] = $topOffers;
        } elseif (\mb_strlen($topOffersSeo) === 0) {
            // cSeo loeschen
            $ssToDelete[] = \SEARCHSPECIALS_TOPOFFERS;
        }
        // Pruefe In kuerze Verfuegbar
        if (\mb_strlen($releaseSeo) > 0 && !$this->checkSeo(
            $searchSpecials,
            $releaseSeo,
            \SEARCHSPECIALS_UPCOMINGPRODUCTS
        )) {
            $releaseSeo = Seo::checkSeo(Seo::getSeo($releaseSeo, true));
            if ($releaseSeo !== $postData['in_kuerze_verfuegbar']) {
                $this->alertService->addNotice(
                    \sprintf(
                        \__('errorSoonExistRename'),
                        $postData['in_kuerze_verfuegbar'],
                        $releaseSeo
                    ),
                    'errorSoonExistRename'
                );
            }
            $release       = new stdClass();
            $release->kKey = \SEARCHSPECIALS_UPCOMINGPRODUCTS;
            $release->cSeo = $releaseSeo;

            $ssTmp[] = $release;
        } elseif (\mb_strlen($releaseSeo) === 0) {
            // cSeo loeschen
            $ssToDelete[] = \SEARCHSPECIALS_UPCOMINGPRODUCTS;
        }
        // Pruefe Top bewertet
        if (\mb_strlen($topRatedSeo) > 0 && !$this->checkSeo(
            $searchSpecials,
            $topRatedSeo,
            \SEARCHSPECIALS_TOPREVIEWS
        )) {
            $topRatedSeo = Seo::checkSeo(Seo::getSeo($topRatedSeo, true));

            if ($topRatedSeo !== $postData['top_bewertet']) {
                $this->alertService->addNotice(
                    \sprintf(
                        \__('errorTopRatingExistRename'),
                        $postData['top_bewertet'],
                        $topRatedSeo
                    ),
                    'errorTopRatingExistRename'
                );
            }
            $topRated       = new stdClass();
            $topRated->kKey = \SEARCHSPECIALS_TOPREVIEWS;
            $topRated->cSeo = $topRatedSeo;

            $ssTmp[] = $topRated;
        } elseif (\mb_strlen($topRatedSeo) === 0) {
            // cSeo loeschen
            $ssToDelete[] = \SEARCHSPECIALS_TOPREVIEWS;
        }
        // tseo speichern
        if (\count($ssTmp) > 0) {
            $ids = [];
            foreach ($ssTmp as $item) {
                $ids[] = (int)$item->kKey;
            }
            $this->db->queryPrepared(
                'DELETE FROM tseo
                    WHERE cKey = \'suchspecial\'
                        AND kSprache = :lid
                        AND kKey IN (' . \implode(',', $ids) . ')',
                ['lid' => $this->currentLanguageID]
            );
            foreach ($ssTmp as $item) {
                $seo           = new stdClass();
                $seo->cSeo     = $item->cSeo;
                $seo->cKey     = 'suchspecial';
                $seo->kKey     = $item->kKey;
                $seo->kSprache = $this->currentLanguageID;

                $this->db->insert('tseo', $seo);
            }
        }
        if (\count($ssToDelete) > 0) {
            $this->deleteSeoData($ssToDelete);
        }
        $this->alertService->addSuccess(\__('successSeoSave'), 'successSeoSave');
    }

    /**
     * @param int[] $items
     * @return void
     */
    private function deleteSeoData(array $items): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tseo
                WHERE cKey = \'suchspecial\'
                    AND kSprache = :lid
                    AND kKey IN (' . \implode(',', $items) . ')',
            ['lid' => $this->currentLanguageID]
        );
    }

    /**
     * Prueft ob ein bestimmtes Suchspecial Seo schon vorhanden ist
     *
     * @param array  $searchSpecials
     * @param string $seo
     * @param int    $key
     * @return bool
     * @former pruefeSuchspecialSeo()
     */
    private function checkSeo(array $searchSpecials, string $seo, int $key): bool
    {
        if ($key > 0 && \count($searchSpecials) > 0 && \mb_strlen($seo)) {
            foreach ($searchSpecials as $special) {
                if ((int)$special->kKey === $key && $special->cSeo === $seo) {
                    return true;
                }
            }
        }

        return false;
    }
}
