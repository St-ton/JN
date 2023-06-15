<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Illuminate\Support\Collection;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Link\Admin\LinkAdmin;
use JTL\Link\Link;
use JTL\Link\LinkGroup;
use JTL\Link\LinkGroupCollection;
use JTL\Link\LinkGroupInterface;
use JTL\Link\LinkGroupList;
use JTL\Link\LinkInterface;
use JTL\Media\Image;
use JTL\PlausiCMS;
use JTL\Services\JTL\LinkService;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\map;

/**
 * Class LinkController
 * @package JTL\Router\Controller\Backend
 */
class LinkController extends AbstractBackendController
{
    public const ERROR_LINK_ALREADY_EXISTS = 1;

    public const ERROR_LINK_NOT_FOUND = 2;

    public const ERROR_LINK_GROUP_NOT_FOUND = 3;

    /**
     * @var string
     */
    private string $uploadDir = \PFAD_ROOT . \PFAD_BILDER . \PFAD_LINKBILDER;

    /**
     * @var LinkAdmin
     */
    private LinkAdmin $linkAdmin;

    /**
     * @var bool
     */
    private bool $clearCache = false;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::CONTENT_PAGE_VIEW);
        $this->getText->loadAdminLocale('pages/links');

        $this->step      = 'uebersicht';
        $this->linkAdmin = new LinkAdmin($this->db, $this->cache);
        $action          = Request::verifyGPDataString('action');
        $linkID          = Request::verifyGPCDataInt('kLink');
        $linkGroupID     = Request::verifyGPCDataInt('kLinkgruppe');
        $this->linkAdmin->getMissingSystemPages();
        if (Form::validateToken()) {
            $this->handleAction($action, $linkGroupID, $linkID);
        }
        if ($this->step === 'loesch_linkgruppe' && $linkGroupID > 0) {
            $this->actionDeleteLinkGroup($linkGroupID);
        } elseif ($this->step === 'edit-link') {
            $this->actionEditLink($linkID);
        }
        if ($this->clearCache === true) {
            $this->clearCache();
        }
        if ($this->step === 'uebersicht') {
            $this->actionOverview();
        }
        if ($this->step === 'neuer Link') {
            $this->actionCreateLink();
        }

        return $this->smarty->assign('step', $this->step)
            ->assign('kPlugin', Request::verifyGPCDataInt('kPlugin'))
            ->assign('linkAdmin', $this->linkAdmin)
            ->assign('route', $this->route)
            ->getResponse('links.tpl');
    }

    /**
     * @param string $action
     * @param int    $linkGroupID
     * @param int    $linkID
     * @return void
     */
    private function handleAction(string $action, int $linkGroupID, int $linkID): void
    {
        switch ($action) {
            case 'add-link-to-linkgroup':
                $this->actionAddLinkToLinkGroup($linkGroupID);
                break;
            case 'remove-link-from-linkgroup':
                $this->actionRemoveLinkFromLinkGroup($linkGroupID, $linkID);
                break;
            case 'delete-link':
                $this->actionDeleteLink($linkID);
                break;
            case 'confirm-delete':
                $this->actionConfirm();
                break;
            case 'delete-linkgroup':
                $this->actionDeleteLinkGroupPre($linkGroupID);
                break;
            case 'edit-linkgroup':
            case 'create-linkgroup':
                $this->actionCreateEditLinkGroup($linkGroupID);
                break;
            case 'save-linkgroup':
                $this->actionSaveLinkGroup($linkGroupID);
                break;
            case 'move-to-linkgroup':
                $this->actionMoveToLinkGroup($linkID, Request::postInt('kLinkgruppeAlt'), $linkGroupID);
                break;
            case 'copy-to-linkgroup':
                $this->actionCopyToLinkGroup($linkGroupID, $linkID);
                break;
            case 'change-parent':
                $this->actionChangeParent($linkID, Request::postInt('kVaterLink'));
                break;
            case 'edit-link':
                $this->step = 'edit-link';
                break;
            case 'create-or-update-link':
                $this->actionCreateOrUpdateLink();
                break;
            default:
                break;
        }
    }

    /**
     * @return LinkGroupCollection
     */
    public function getLinkGroups(): LinkGroupCollection
    {
        $ls  = new LinkService($this->db, $this->cache);
        $lgl = new LinkGroupList($this->db, $this->cache);
        $lgl->loadAll();
        $linkGroups = $lgl->getLinkGroups()->filter(static function (LinkGroupInterface $e): bool {
            return $e->isSpecial() === false || $e->getTemplate() === 'unassigned';
        });
        foreach ($linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $filtered = $this->buildNavigation($linkGroup, $ls);
            $linkGroup->setLinks($filtered);
        }

        return $linkGroups;
    }

    /**
     * @param LinkGroupInterface   $linkGroup
     * @param LinkServiceInterface $service
     * @param int                  $parentID
     * @return Collection
     * @former build_navigation_subs_admin()
     */
    private function buildNavigation(
        LinkGroupInterface $linkGroup,
        LinkServiceInterface $service,
        int $parentID = 0
    ): Collection {
        $news = new Collection();
        foreach ($linkGroup->getLinks() as $link) {
            $link->setLevel(\count($service->getParentIDs($link->getID())));
            /** @var LinkInterface $link */
            if ($link->getParent() !== $parentID) {
                continue;
            }
            $link->setChildLinks($this->buildNavigation($linkGroup, $service, $link->getID()));
            $news->push($link);
        }

        return $news;
    }

    /**
     * @return array
     */
    public function getLinkGroupCountForLinkIDs(): array
    {
        $assocCount             = $this->db->getObjects(
            'SELECT tlink.kLink, COUNT(*) AS cnt 
                FROM tlink 
                JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                GROUP BY tlink.kLink
                HAVING COUNT(*) > 1'
        );
        $linkGroupCountByLinkID = [];
        foreach ($assocCount as $item) {
            $linkGroupCountByLinkID[(int)$item->kLink] = (int)$item->cnt;
        }

        return $linkGroupCountByLinkID;
    }

    /**
     * @param int $linkID
     * @param int $linkGroupID
     * @return int
     */
    public function removeLinkFromLinkGroup(int $linkID, int $linkGroupID): int
    {
        $link = (new Link($this->db))->load($linkID);
        foreach ($link->getChildLinks() as $childLink) {
            $this->removeLinkFromLinkGroup($childLink->getID(), $linkGroupID);
        }

        return $this->db->delete(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$linkGroupID, $linkID]
        );
    }

    /**
     * @param int $linkID
     * @param int $parentLinkID
     * @return bool|stdClass
     */
    public function updateParentID(int $linkID, int $parentLinkID): bool|stdClass
    {
        $link       = $this->db->select('tlink', 'kLink', $linkID);
        $parentLink = $this->db->select('tlink', 'kLink', $parentLinkID);

        if ($link !== null
            && $link->kLink > 0
            && (($parentLink !== null && $parentLink->kLink > 0) || $parentLinkID === 0)
        ) {
            $this->db->update('tlink', 'kLink', $linkID, (object)['kVaterLink' => $parentLinkID]);

            return $link;
        }

        return false;
    }

    /**
     * @param int $linkID
     * @return int
     */
    public function deleteLink(int $linkID): int
    {
        return $this->db->getAffectedRows(
            "DELETE tlink, tlinksprache, tseo, tlinkgroupassociations
                FROM tlink
                LEFT JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                LEFT JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                LEFT JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = :lid
                WHERE tlink.kLink = :lid
                    OR tlink.reference = :lid",
            ['lid' => $linkID]
        );
    }

    /**
     * @param int  $linkGroupID
     * @param bool $names
     * @return array
     */
    public function getPreDeletionLinks(int $linkGroupID, bool $names = true): array
    {
        $links = $this->db->getObjects(
            'SELECT tlink.cName
                FROM tlink
                JOIN tlinkgroupassociations A
                    ON tlink.kLink = A.linkID
                JOIN tlinkgroupassociations B
                    ON A.linkID = B.linkID
                WHERE A.linkGroupID = :lgid
                GROUP BY A.linkID
                HAVING COUNT(A.linkID) > 1',
            ['lgid' => $linkGroupID]
        );

        return $names === true
            ? map($links, static function ($l) {
                return $l->cName;
            })
            : $links;
    }

    /**
     * @param int $linkID
     * @param int $targetLinkGroupID
     * @return int|Link
     */
    public function createReference(int $linkID, int $targetLinkGroupID): Link|int
    {
        $link = new Link($this->db);
        $link->load($linkID);
        if ($link->getID() === 0) {
            return self::ERROR_LINK_NOT_FOUND;
        }
        if ($link->getReference() > 0) {
            $linkID = $link->getReference();
        }
        $targetLinkGroup = $this->db->select('tlinkgruppe', 'kLinkgruppe', $targetLinkGroupID);
        if ($targetLinkGroup === null || $targetLinkGroup->kLinkgruppe <= 0) {
            return self::ERROR_LINK_GROUP_NOT_FOUND;
        }
        $exists = $this->db->select(
            'tlinkgroupassociations',
            ['linkID', 'linkGroupID'],
            [$linkID, $targetLinkGroupID]
        );
        if ($exists !== null) {
            return self::ERROR_LINK_ALREADY_EXISTS;
        }
        $ref            = new stdClass();
        $ref->kPlugin   = $link->getPluginID();
        $ref->nLinkart  = \LINKTYP_REFERENZ;
        $ref->reference = $linkID;
        $ref->cName     = \__('Referenz') . ' ' . $linkID;
        $linkID         = $this->db->insert('tlink', $ref);

        $ins              = new stdClass();
        $ins->linkID      = $linkID;
        $ins->linkGroupID = $targetLinkGroupID;
        $this->db->insert('tlinkgroupassociations', $ins);
        $this->copyChildLinksToLinkGroup($link, $targetLinkGroupID);

        return $link;
    }

    /**
     * @param int $linkID
     * @param int $oldLinkGroupID
     * @param int $newLinkGroupID
     * @return int|Link
     */
    public function updateLinkGroup(int $linkID, int $oldLinkGroupID, int $newLinkGroupID): Link|int
    {
        $link = new Link($this->db);
        $link->load($linkID);
        if ($link->getID() === 0) {
            return self::ERROR_LINK_NOT_FOUND;
        }
        $linkgruppe = $this->db->select('tlinkgruppe', 'kLinkgruppe', $newLinkGroupID);
        if ($linkgruppe === null || $linkgruppe->kLinkgruppe <= 0) {
            return self::ERROR_LINK_GROUP_NOT_FOUND;
        }
        $exists = $this->db->select(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$newLinkGroupID, $link->getID()]
        );
        if ($exists !== null) {
            return self::ERROR_LINK_ALREADY_EXISTS;
        }
        $upd              = new stdClass();
        $upd->linkGroupID = $newLinkGroupID;
        $rows             = $this->db->update(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$oldLinkGroupID, $link->getID()],
            $upd
        );
        if ($rows === 0) {
            // previously unassigned link
            $upd              = new stdClass();
            $upd->linkGroupID = $newLinkGroupID;
            $upd->linkID      = $link->getID();
            $this->db->insert('tlinkgroupassociations', $upd);
        }
        unset($upd->linkID);
        $this->updateChildLinkGroups($link, $oldLinkGroupID, $newLinkGroupID);

        return $link;
    }

    /**
     * @param LinkInterface $link
     * @param int           $old
     * @param int           $new
     */
    private function updateChildLinkGroups(LinkInterface $link, int $old, int $new): void
    {
        $upd = (object)['linkGroupID' => $new];
        foreach ($link->getChildLinks() as $childLink) {
            if ($old < 0) {
                // previously unassigned
                $ins              = new stdClass();
                $ins->linkGroupID = $new;
                $ins->linkID      = $childLink->getID();
                $this->db->insert('tlinkgroupassociations', $ins);
            } else {
                $this->db->update(
                    'tlinkgroupassociations',
                    ['linkGroupID', 'linkID'],
                    [$old, $childLink->getID()],
                    $upd
                );
            }
            $this->updateChildLinkGroups($childLink, $old, $new);
        }
    }

    /**
     * @param LinkInterface $link
     * @param int           $linkGroupID
     */
    public function copyChildLinksToLinkGroup(LinkInterface $link, int $linkGroupID): void
    {
        $link->buildChildLinks();
        $ins = (object)['linkGroupID' => $linkGroupID];
        foreach ($link->getChildLinks() as $childLink) {
            $ins->linkID = $childLink->getID();
            $this->db->insert('tlinkgroupassociations', $ins);
            $this->copyChildLinksToLinkGroup($childLink, $linkGroupID);
        }
    }

    /**
     * @param int $linkGroupID
     * @return int
     */
    public function deleteLinkGroup(int $linkGroupID): int
    {
        $this->db->delete('tlinkgroupassociations', 'linkGroupID', $linkGroupID);
        $res = $this->db->delete('tlinkgruppe', 'kLinkgruppe', $linkGroupID);
        $this->db->delete('tlinkgruppesprache', 'kLinkgruppe', $linkGroupID);

        return $res;
    }

    /**
     * @return bool
     */
    public function clearCache(): bool
    {
        $this->cache->flushTags([\CACHING_GROUP_CORE]);
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');

        return true;
    }

    /**
     * @param int   $id
     * @param array $post
     * @return stdClass
     */
    public function createOrUpdateLinkGroup(int $id, array $post): stdClass
    {
        $linkGroup                = new stdClass();
        $linkGroup->kLinkgruppe   = (int)$post['kLinkgruppe'];
        $linkGroup->cName         = $this->specialChars($post['cName']);
        $linkGroup->cTemplatename = $this->specialChars($post['cTemplatename']);
        if ($id === 0) {
            $groupID = $this->db->insert('tlinkgruppe', $linkGroup);
        } else {
            $groupID = (int)$post['kLinkgruppe'];
            $this->db->update('tlinkgruppe', 'kLinkgruppe', $groupID, $linkGroup);
        }
        $localized              = new stdClass();
        $localized->kLinkgruppe = $groupID;
        foreach (LanguageHelper::getAllLanguages(0, true) as $language) {
            $localized->cISOSprache = $language->getIso();
            $localized->cName       = $linkGroup->cName;
            $idx                    = 'cName_' . $language->getIso();
            if (isset($post[$idx])) {
                $localized->cName = $this->specialChars($post[$idx]);
            }
            $this->db->delete(
                'tlinkgruppesprache',
                ['kLinkgruppe', 'cISOSprache'],
                [$groupID, $language->getIso()]
            );
            $this->db->insert('tlinkgruppesprache', $localized);
        }

        return $linkGroup;
    }

    /**
     * @param int $linkID
     * @return int
     */
    public function getLastImageNumber(int $linkID): int
    {
        $uploadDir = \PFAD_ROOT . \PFAD_BILDER . \PFAD_LINKBILDER;
        $images    = [];
        if (\is_dir($uploadDir . $linkID)) {
            $handle = \opendir($uploadDir . $linkID);
            while (($file = \readdir($handle)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $images[] = $file;
                }
            }
        }
        $max = 0;
        foreach ($images as $image) {
            $num = \mb_substr($image, 4, (\mb_strlen($image) - \mb_strpos($image, '.')) - 3);
            if ($num > $max) {
                $max = $num;
            }
        }

        return (int)$max;
    }

    /**
     * @param string $text
     * @return string
     */
    private function specialChars(string $text): string
    {
        return \htmlspecialchars($text, \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET, false);
    }

    /**
     * @param int $linkGroupID
     * @return void
     */
    private function actionAddLinkToLinkGroup(int $linkGroupID): void
    {
        $this->step = 'neuer Link';
        $link       = new Link($this->db);
        $link->setLinkGroupID($linkGroupID);
        $link->setLinkGroups([$linkGroupID]);
        $this->smarty->assign('Link', $link);
    }

    /**
     * @param int $linkGroupID
     * @param int $linkID
     * @return void
     */
    private function actionRemoveLinkFromLinkGroup(int $linkGroupID, int $linkID): void
    {
        if ($this->removeLinkFromLinkGroup($linkID, $linkGroupID) > 0) {
            $this->alertService->addSuccess(
                \__('successLinkFromLinkGroupDelete'),
                'successLinkFromLinkGroupDelete'
            );
        } else {
            $this->alertService->addError(
                \__('errorLinkFromLinkGroupDelete'),
                'errorLinkFromLinkGroupDelete'
            );
        }
        unset($_POST['kLinkgruppe']);
        $this->step       = 'uebersicht';
        $this->clearCache = true;
    }

    /**
     * @param int $linkID
     * @param int $parentID
     * @return void
     */
    private function actionChangeParent(int $linkID, int $parentID): void
    {
        if ($parentID >= 0 && ($link = $this->updateParentID($linkID, $parentID)) !== false) {
            $this->alertService->addSuccess(\sprintf(\__('successLinkMove'), $link->cName), 'successLinkMove');
            $this->step       = 'uebersicht';
            $this->clearCache = true;
            return;
        }
        $this->alertService->addError(\__('errorLinkMove'), 'errorLinkMove');
    }

    /**
     * @return void
     */
    private function actionCreateOrUpdateLink(): void
    {
        $htmlContent = [];
        foreach (LanguageHelper::getAllLanguages(0, true) as $lang) {
            $htmlContent[] = 'cContent_' . $lang->getIso();
        }
        $checks = new PlausiCMS();
        $checks->setPostVar($_POST, $htmlContent, true);
        $checks->doPlausi('lnk');
        if (\count($checks->getPlausiVar()) === 0) {
            $files = [];
            $link  = $this->linkAdmin->createOrUpdateLink($_POST);
            if (Request::postInt('kLink') === 0) {
                $this->alertService->addSuccess(\__('successLinkCreate'), 'successLinkCreate');
            } else {
                $this->alertService->addSuccess(
                    \sprintf(\__('successLinkEdit'), $link->getDisplayName()),
                    'successLinkEdit'
                );
            }
            $this->clearCache = true;
            $linkID           = $link->getID();
            $this->step       = 'uebersicht';
            $continue         = Request::postInt('continue') === 1;
            if ($continue) {
                $this->step     = 'neuer Link';
                $_POST['kLink'] = $linkID;
            }
            // Bilder hochladen
            if (!\is_dir($this->uploadDir . $linkID)
                && !\mkdir($concurrentDirectory = $this->uploadDir . $linkID)
                && !\is_dir($concurrentDirectory)
            ) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            if (\is_array($_FILES['Bilder']['name']) && \count($_FILES['Bilder']['name']) > 0) {
                $lastImage = $this->getLastImageNumber($linkID);
                $counter   = 0;
                if ($lastImage > 0) {
                    $counter = $lastImage;
                }
                $imageCount = (\count($_FILES['Bilder']['name']) + $counter);
                for ($i = $counter; $i < $imageCount; ++$i) {
                    $upload = [
                        'size'     => $_FILES['Bilder']['size'][$i - $counter],
                        'error'    => $_FILES['Bilder']['error'][$i - $counter],
                        'type'     => $_FILES['Bilder']['type'][$i - $counter],
                        'name'     => $_FILES['Bilder']['name'][$i - $counter],
                        'tmp_name' => $_FILES['Bilder']['tmp_name'][$i - $counter],
                    ];
                    if (Image::isImageUpload($upload)) {
                        $type         = $upload['type'];
                        $uploadedFile = $this->uploadDir . $linkID . '/Bild' . ($i + 1) . '.' .
                            \mb_substr(
                                $type,
                                \mb_strpos($type, '/') + 1,
                                \mb_strlen($type) - \mb_strpos($type, '/') + 1
                            );
                        \move_uploaded_file($upload['tmp_name'], $uploadedFile);
                    }
                }
            }
            $dirName = $this->uploadDir . $link->getID();
            if (\is_dir($dirName)) {
                $dirHandle = \opendir($dirName);
                $shopURL   = Shop::getImageBaseURL() . '/';
                while (($file = \readdir($dirHandle)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    $newFile            = new stdClass();
                    $newFile->cName     = \mb_substr($file, 0, \mb_strpos($file, '.'));
                    $newFile->cNameFull = $file;
                    $newFile->cURL      = '<img class="link_image" src="' .
                        $shopURL . \PFAD_BILDER . \PFAD_LINKBILDER . $link->getID() . '/' . $file . '" />';
                    $newFile->nBild     = (int)\mb_substr(
                        \str_replace('Bild', '', $file),
                        0,
                        \mb_strpos(\str_replace('Bild', '', $file), '.')
                    );
                    $files[]            = $newFile;
                }
                \usort($files, static function ($a, $b) {
                    return $a->nBild <=> $b->nBild;
                });
                $this->smarty->assign('cDatei_arr', $files);
            }
            $this->smarty->assign('Link', $link);
        } else {
            $this->step = 'neuer Link';
            $link       = new Link($this->db);
            $link->setLinkGroupID(Request::postInt('kLinkgruppe'));
            $link->setLinkGroups([Request::postInt('kLinkgruppe')]);
            $checkVars = $checks->getPlausiVar();
            if (isset($checkVars['nSpezialseite'])) {
                $this->alertService->addError(\__('isDuplicateSpecialLink'), 'isDuplicateSpecialLink');
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
            }
            $this->smarty->assign('xPlausiVar_arr', $checkVars)
                ->assign('Link', $link)
                ->assign('xPostVar_arr', $checks->getPostVar());
        }
    }

    /**
     * @param int $linkID
     * @return void
     */
    private function actionDeleteLink(int $linkID): void
    {
        if ($this->deleteLink($linkID) > 0) {
            $this->alertService->addSuccess(\__('successLinkDelete'), 'successLinkDelete');
        } else {
            $this->alertService->addError(\__('errorLinkDelete'), 'errorLinkDelete');
        }
        $this->clearCache = true;
        $this->step       = 'uebersicht';
        $_POST            = [];
    }

    /**
     * @param int $linkGroupID
     * @return void
     */
    private function actionDeleteLinkGroupPre(int $linkGroupID): void
    {
        $this->step = 'linkgruppe_loeschen_confirm';
        $this->smarty->assign('linkGroup', (new LinkGroup($this->db))->load($linkGroupID))
            ->assign('affectedLinkNames', $this->getPreDeletionLinks($linkGroupID));
    }

    /**
     * @param int $linkGroupID
     * @return void
     */
    private function actionDeleteLinkGroup(int $linkGroupID): void
    {
        $this->step = 'uebersicht';
        if ($this->deleteLinkGroup($linkGroupID) > 0) {
            $this->alertService->addSuccess(\__('successLinkGroupDelete'), 'successLinkGroupDelete');
            $this->clearCache = true;
            $this->step       = 'uebersicht';
        } else {
            $this->alertService->addError(\__('errorLinkGroupDelete'), 'errorLinkGroupDelete');
        }
        $_POST = [];
    }

    /**
     * @param int $linkGroupID
     * @return void
     */
    private function actionCreateEditLinkGroup(int $linkGroupID): void
    {
        $this->step = 'neue Linkgruppe';
        $linkGroup  = $linkGroupID > 0 ? (new LinkGroup($this->db))->load($linkGroupID) : null;
        $this->smarty->assign('linkGroup', $linkGroup);
    }

    /**
     * @param int $linkGroupID
     * @return void
     */
    private function actionSaveLinkGroup(int $linkGroupID): void
    {
        $checks = new PlausiCMS();
        $checks->setPostVar($_POST);
        $checks->doPlausi('grp');
        if (\count($checks->getPlausiVar()) === 0) {
            $tplExists = $this->db->select('tlinkgruppe', 'cTemplatename', $_POST['cTemplatename']);
            if ($tplExists !== null && $linkGroupID !== (int)$tplExists->kLinkgruppe) {
                $this->step = 'neue Linkgruppe';
                $linkGroup  = $linkGroupID > 0 ? (new LinkGroup($this->db))->load($linkGroupID) : null;
                $this->alertService->addError(\__('errorTemplateNameDuplicate'), 'errorTemplateNameDuplicate');
                $this->smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                    ->assign('xPostVar_arr', $checks->getPostVar())
                    ->assign('linkGroup', $linkGroup);
            } else {
                if ($linkGroupID === 0) {
                    $this->createOrUpdateLinkGroup(0, $_POST);
                    $this->alertService->addSuccess(\__('successLinkGroupCreate'), 'successLinkGroupCreate');
                } else {
                    $linkGroup = $this->createOrUpdateLinkGroup($linkGroupID, $_POST);
                    $this->alertService->addSuccess(
                        \sprintf(\__('successLinkGroupEdit'), $linkGroup->cName),
                        'successLinkGroupEdit'
                    );
                }
                $this->step = 'uebersicht';
            }
            $this->clearCache = true;
        } else {
            $this->step = 'neue Linkgruppe';
            $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
            $this->smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                ->assign('xPostVar_arr', $checks->getPostVar());
        }
    }

    /**
     * @return void
     */
    private function actionConfirm(): void
    {
        if (Request::verifyGPCDataInt('confirmation') === 1) {
            $this->step = 'loesch_linkgruppe';
        } else {
            $this->step = 'uebersicht';
            $_POST      = [];
        }
    }

    /**
     * @param int $linkID
     * @param int $oldLinkGroupID
     * @param int $linkGroupID
     * @return void
     */
    private function actionMoveToLinkGroup(int $linkID, int $oldLinkGroupID, int $linkGroupID): void
    {
        $res = $this->updateLinkGroup($linkID, $oldLinkGroupID, $linkGroupID);
        if ($res === LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
            $this->alertService->addError(\__('errorLinkMoveDuplicate'), 'errorLinkMoveDuplicate');
        } elseif ($res === LinkAdmin::ERROR_LINK_NOT_FOUND) {
            $this->alertService->addError(\__('errorLinkKeyNotFound'), 'errorLinkKeyNotFound');
        } elseif ($res === LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
            $this->alertService->addError(\__('errorLinkGroupKeyNotFound'), 'errorLinkGroupKeyNotFound');
        } elseif ($res instanceof LinkInterface) {
            $this->alertService->addSuccess(
                \sprintf(\__('successLinkMove'), $res->getDisplayName()),
                'successLinkMove'
            );
            $this->clearCache = true;
        } else {
            $this->alertService->addError(\__('errorUnknownLong'), 'errorUnknownLong');
        }
        $this->step = 'uebersicht';
    }

    /**
     * @param int $linkGroupID
     * @param int $linkID
     * @return void
     */
    private function actionCopyToLinkGroup(int $linkGroupID, int $linkID): void
    {
        $res = $this->createReference($linkID, $linkGroupID);
        if ($res === LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
            $this->alertService->addError(\__('errorLinkCopyDuplicate'), 'errorLinkCopyDuplicate');
        } elseif ($res === LinkAdmin::ERROR_LINK_NOT_FOUND) {
            $this->alertService->addError(\__('errorLinkKeyNotFound'), 'errorLinkKeyNotFound');
        } elseif ($res === LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
            $this->alertService->addError(\__('errorLinkGroupKeyNotFound'), 'errorLinkGroupKeyNotFound');
        } elseif ($res instanceof LinkInterface) {
            $this->alertService->addSuccess(
                \sprintf(\__('successLinkCopy'), $res->getDisplayName()),
                'successLinkCopy'
            );
            $this->step       = 'uebersicht';
            $this->clearCache = true;
        } else {
            $this->alertService->addError(\__('errorUnknownLong'), 'errorUnknownLong');
        }
    }

    /**
     * @param int $linkID
     * @return void
     */
    private function actionEditLink(int $linkID): void
    {
        $this->step = 'neuer Link';
        $link       = new Link($this->db);
        $link->load($linkID);
        $link->deref();
        $dirName = $this->uploadDir . $link->getID();
        $files   = [];
        if (Request::verifyGPCDataInt('delpic') === 1) {
            @\unlink($dirName . '/' . Request::verifyGPDataString('cName'));
        }
        if (\is_dir($dirName)) {
            $dirHandle = \opendir($dirName);
            $shopURL   = Shop::getURL() . '/';
            while (($file = \readdir($dirHandle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $newFile            = new stdClass();
                $newFile->cName     = \mb_substr($file, 0, \mb_strpos($file, '.'));
                $newFile->cNameFull = $file;
                $newFile->cURL      = '<img class="link_image" src="' .
                    $shopURL . \PFAD_BILDER . \PFAD_LINKBILDER . $link->getID() . '/' . $file . '" />';
                $newFile->nBild     = (int)mb_substr(
                    \str_replace('Bild', '', $file),
                    0,
                    \mb_strpos(\str_replace('Bild', '', $file), '.')
                );
                $files[]            = $newFile;
            }
            \usort($files, static function ($a, $b) {
                return $a->nBild <=> $b->nBild;
            });
            $this->smarty->assign('cDatei_arr', $files);
        }
        $this->smarty->assign('Link', $link);
    }

    /**
     * @return void
     */
    private function actionCreateLink(): void
    {
        (new LinkGroupList($this->db, $this->cache))->loadAll();
        $this->smarty->assign('specialPages', $this->linkAdmin->getSpecialPageTypes())
            ->assign('kundengruppen', $this->db->getObjects('SELECT * FROM tkundengruppe ORDER BY cName'));
    }

    /**
     * @return void
     */
    private function actionOverview(): void
    {
        foreach ($this->linkAdmin->getDuplicateSpecialLinks()->groupBy(static function (LinkInterface $l): int {
            return $l->getLinkType();
        }) as $specialLinks) {
            /** @var Collection $specialLinks */
            $this->alertService->addError(
                \sprintf(
                    \__('hasDuplicateSpecialLink'),
                    ' ' . $specialLinks->map(static function (LinkInterface $l): string {
                        return $l->getName();
                    })->implode('/')
                ),
                'hasDuplicateSpecialLink-' . $specialLinks->first()->getLinkType()
            );
        }
        $this->smarty->assign('linkGroupCountByLinkID', $this->getLinkGroupCountForLinkIDs())
            ->assign('missingSystemPages', $this->linkAdmin->getMissingSystemPages())
            ->assign('linkgruppen', $this->getLinkGroups());
    }
}
