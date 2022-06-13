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
use JTL\Link\LinkGroupList;
use JTL\Link\LinkInterface;
use JTL\Media\Image;
use JTL\PlausiCMS;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class LinkController
 * @package JTL\Router\Controller\Backend
 */
class LinkController extends AbstractBackendController
{
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
        if ($action !== '' && Form::validateToken()) {
            $this->handleAction($action, $linkGroupID, $linkID);
        }

        if ($this->step === 'loesch_linkgruppe' && $linkGroupID > 0) {
            $this->step = 'uebersicht';
            if ($this->linkAdmin->deleteLinkGroup($linkGroupID) > 0) {
                $this->alertService->addSuccess(\__('successLinkGroupDelete'), 'successLinkGroupDelete');
                $this->clearCache = true;
                $this->step       = 'uebersicht';
            } else {
                $this->alertService->addError(\__('errorLinkGroupDelete'), 'errorLinkGroupDelete');
            }
            $_POST = [];
        } elseif ($this->step === 'edit-link') {
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
        if ($this->clearCache === true) {
            $this->linkAdmin->clearCache();
        }
        if ($this->step === 'uebersicht') {
            foreach ($this->linkAdmin->getDuplicateSpecialLinks()->groupBy(static function (LinkInterface $l) {
                return $l->getLinkType();
            }) as $specialLinks) {
                /** @var Collection $specialLinks */
                $this->alertService->addError(
                    \sprintf(
                        \__('hasDuplicateSpecialLink'),
                        ' ' . $specialLinks->map(static function (LinkInterface $l) {
                            return $l->getName();
                        })->implode('/')
                    ),
                    'hasDuplicateSpecialLink-' . $specialLinks->first()->getLinkType()
                );
            }
            $this->smarty->assign('linkGroupCountByLinkID', $this->linkAdmin->getLinkGroupCountForLinkIDs())
                ->assign('missingSystemPages', $this->linkAdmin->getMissingSystemPages())
                ->assign('linkgruppen', $this->linkAdmin->getLinkGroups());
        }
        if ($this->step === 'neuer Link') {
            $cgroups = $this->db->getObjects('SELECT * FROM tkundengruppe ORDER BY cName');
            $lgl     = new LinkGroupList($this->db, $this->cache);
            $lgl->loadAll();
            $this->smarty->assign('specialPages', $this->linkAdmin->getSpecialPageTypes())
                ->assign('kundengruppen', $cgroups);
        }

        return $this->smarty->assign('step', $this->step)
            ->assign('kPlugin', Request::verifyGPCDataInt('kPlugin'))
            ->assign('linkAdmin', $this->linkAdmin)
            ->assign('route', $this->route)
            ->getResponse('links.tpl');
    }

    private function handleAction(string $action, int $linkGroupID, int $linkID): void
    {
        switch ($action) {
            case 'add-link-to-linkgroup':
                $this->step = 'neuer Link';
                $link       = new Link($this->db);
                $link->setLinkGroupID($linkGroupID);
                $link->setLinkGroups([$linkGroupID]);
                $this->smarty->assign('Link', $link);
                break;
            case 'remove-link-from-linkgroup':
                $res = $this->linkAdmin->removeLinkFromLinkGroup($linkID, $linkGroupID);
                if ($res > 0) {
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
                break;
            case 'delete-link':
                if ($this->linkAdmin->deleteLink($linkID) > 0) {
                    $this->alertService->addSuccess(\__('successLinkDelete'), 'successLinkDelete');
                } else {
                    $this->alertService->addError(\__('errorLinkDelete'), 'errorLinkDelete');
                }
                $this->clearCache = true;
                $this->step       = 'uebersicht';
                $_POST            = [];
                break;
            case 'confirm-delete':
                if (Request::verifyGPCDataInt('confirmation') === 1) {
                    $this->step = 'loesch_linkgruppe';
                } else {
                    $this->step = 'uebersicht';
                    $_POST      = [];
                }
                break;
            case 'delete-linkgroup':
                $this->step = 'linkgruppe_loeschen_confirm';
                $group      = new LinkGroup($this->db);
                $this->smarty->assign('linkGroup', $group->load($linkGroupID))
                    ->assign('affectedLinkNames', $this->linkAdmin->getPreDeletionLinks($linkGroupID));
                break;
            case 'edit-linkgroup':
            case 'create-linkgroup':
                $this->step = 'neue Linkgruppe';
                $linkGroup  = null;
                if ($linkGroupID > 0) {
                    $linkGroup = new LinkGroup($this->db);
                    $linkGroup = $linkGroup->load($linkGroupID);
                }
                $this->smarty->assign('linkGroup', $linkGroup);
                break;
            case 'save-linkgroup':
                $checks = new PlausiCMS();
                $checks->setPostVar($_POST);
                $checks->doPlausi('grp');
                if (\count($checks->getPlausiVar()) === 0) {
                    $linkGroupTemplateExists = $this->db->select(
                        'tlinkgruppe',
                        'cTemplatename',
                        $_POST['cTemplatename']
                    );
                    if ($linkGroupTemplateExists !== null
                        && $linkGroupID !== (int)$linkGroupTemplateExists->kLinkgruppe
                    ) {
                        $this->step = 'neue Linkgruppe';
                        $linkGroup  = null;
                        if ($linkGroupID > 0) {
                            $linkGroup = new LinkGroup($this->db);
                            $linkGroup = $linkGroup->load($linkGroupID);
                        }
                        $this->alertService->addError(
                            \__('errorTemplateNameDuplicate'),
                            'errorTemplateNameDuplicate'
                        );
                        $this->smarty->assign('xPlausiVar_arr', $checks->getPlausiVar())
                            ->assign('xPostVar_arr', $checks->getPostVar())
                            ->assign('linkGroup', $linkGroup);
                    } else {
                        if ($linkGroupID === 0) {
                            $this->linkAdmin->createOrUpdateLinkGroup(0, $_POST);
                            $this->alertService->addSuccess(
                                \__('successLinkGroupCreate'),
                                'successLinkGroupCreate'
                            );
                        } else {
                            $linkgruppe = $this->linkAdmin->createOrUpdateLinkGroup($linkGroupID, $_POST);
                            $this->alertService->addSuccess(
                                \sprintf(\__('successLinkGroupEdit'), $linkgruppe->cName),
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
                break;
            case 'move-to-linkgroup':
                $res = $this->linkAdmin->updateLinkGroup(
                    $linkID,
                    Request::postInt('kLinkgruppeAlt'),
                    $linkGroupID
                );
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
                break;
            case 'copy-to-linkgroup':
                $res = $this->linkAdmin->createReference($linkID, $linkGroupID);
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
                break;
            case 'change-parent':
                $parentID = (int)($_POST['kVaterLink'] ?? 0);
                if ($parentID >= 0 && ($link = $this->linkAdmin->updateParentID($linkID, $parentID)) !== false) {
                    $this->alertService->addSuccess(
                        \sprintf(\__('successLinkMove'), $link->cName),
                        'successLinkMove'
                    );
                    $this->step       = 'uebersicht';
                    $this->clearCache = true;
                } else {
                    $this->alertService->addError(\__('errorLinkMove'), 'errorLinkMove');
                }
                break;
            case 'edit-link':
                $this->step = 'edit-link';
                break;
            case 'create-or-update-link':
                $hasHTML = [];
                foreach (LanguageHelper::getAllLanguages(0, true) as $lang) {
                    $hasHTML[] = 'cContent_' . $lang->getIso();
                }
                $checks = new PlausiCMS();
                $checks->setPostVar($_POST, $hasHTML, true);
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
                    $kLink            = $link->getID();
                    $this->step       = 'uebersicht';
                    $continue         = Request::postInt('continue') === 1;
                    if ($continue) {
                        $this->step     = 'neuer Link';
                        $_POST['kLink'] = $kLink;
                    }
                    // Bilder hochladen
                    if (!\is_dir($this->uploadDir . $kLink)) {
                        \mkdir($this->uploadDir . $kLink);
                    }
                    if (\is_array($_FILES['Bilder']['name']) && \count($_FILES['Bilder']['name']) > 0) {
                        $lastImage = $this->linkAdmin->getLastImageNumber($kLink);
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
                                $uploadedFile = $this->uploadDir . $kLink . '/Bild' . ($i + 1) . '.' .
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
                break;
            default:
                break;
        }
    }
}
