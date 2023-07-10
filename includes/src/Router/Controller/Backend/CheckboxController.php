<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\CheckBox;
use JTL\Checkbox\CheckboxDataTableObject;
use JTL\Customer\CustomerGroup;
use JTL\Exceptions\PermissionException;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CheckboxController
 * @package JTL\Router\Controller\Backend
 */
class CheckboxController extends AbstractBackendController
{
    /**
     * @var array|array[]
     */
    private array $internalBoxDefaults = [
        CheckBox::CHECKBOX_DOWNLOAD_ORDER_COMPLETE => [
            'nInternal'         => true,
            'cAnzeigeOrt'       => ';2;',
            'nPflicht'          => true,
            'nLink'             => false,
            'nAktiv'            => true,
            'nLogging'          => true,
            'kCheckBoxFunktion' => 0,
        ],
    ];

    /**
     * @inheritdoc
     * @throws PermissionException
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::CHECKBOXES_VIEW);
        $this->getText->loadAdminLocale('pages/checkbox');
        $step     = 'uebersicht';
        $checkbox = new CheckBox(0, $this->db);
        $tab      = $step;
        if (\mb_strlen($this->request->request('tab')) > 0) {
            $tab = $this->request->request('tab');
        }
        if ($this->request->post('erstellenShowButton') !== null) {
            $tab = 'erstellen';
        } elseif ($this->tokenIsValid && $this->request->requestInt('uebersicht') === 1) {
            $checkboxIDs = $this->request->requestIntArray('kCheckBox');
            if ($this->request->post('checkboxAktivierenSubmit') !== null) {
                $checkbox->activate($checkboxIDs);
                $this->alertService->addSuccess(\__('successCheckboxActivate'), 'successCheckboxActivate');
            } elseif ($this->request->post('checkboxDeaktivierenSubmit') !== null) {
                $checkbox->deactivate($checkboxIDs);
                $this->alertService->addSuccess(\__('successCheckboxDeactivate'), 'successCheckboxDeactivate');
            } elseif ($this->request->post('checkboxLoeschenSubmit') !== null) {
                $checkbox->delete($checkboxIDs);
                $this->alertService->addSuccess(\__('successCheckboxDelete'), 'successCheckboxDelete');
            }
        } elseif ($this->request->requestInt('edit') > 0) {
            $checkboxID = $this->request->requestInt('edit');
            $step       = 'erstellen';
            $tab        = $step;
            $this->smarty->assign('oCheckBox', new CheckBox($checkboxID, $this->db));
        } elseif ($this->tokenIsValid && $this->request->requestInt('erstellen') === 1) {
            $post        = Text::filterXSS($request->getParsedBody());
            $step        = 'erstellen';
            $checkboxID  = $this->request->requestInt('kCheckBox');
            $languages   = LanguageHelper::getAllLanguages(0, true, true);
            $checkboxDTO = $this->getCheckboxDTO($post, $languages);
            $checks      = $this->validate($checkboxDTO, $languages);
            if (\count($checks) === 0) {
                $checkbox = $this->save($checkboxDTO);
                $step     = 'uebersicht';
                $this->alertService->addSuccess(\__('successCheckboxCreate'), 'successCheckboxCreate');
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');

                $this->smarty->assign('cPost_arr', $post)
                    ->assign('cPlausi_arr', $checks);
                if ($checkboxID > 0) {
                    $this->smarty->assign('kCheckBox', $checkboxID);

                    $customerGroupID = $post['kKundengruppe'] ?? ';;';
                    if ((int)$post['nInternal'] === 1) {
                        $postBox = [
                            'nInternal'         => $post['nInternal'],
                            'kCheckBox'         => $checkboxID,
                            'cAnzeigeOrt'       => ';' . $post['cAnzeigeOrt'][0] . ';',
                            'cName'             => $post['cName'],
                            'nPflicht'          => $post['nPflicht'],
                            'nLink'             => $post['nLink'],
                            'nAktiv'            => $post['nAktiv'],
                            'nLogging'          => $post['nLogging'],
                            'kCheckBoxFunktion' => $post['kCheckBoxFunktion'],
                            'kKundengruppe'     => $customerGroupID,
                        ];
                    } else {
                        $postBox = [
                            'nInternal'         => $post['nInternal'],
                            'kCheckBox'         => $checkboxID,
                            'cAnzeigeOrt'       => $post['cAnzeigeOrt'] ?? ';;',
                            'cName'             => $post['cName'],
                            'nPflicht'          => $post['nPflicht'],
                            'nLink'             => $post['nLink'],
                            'kLink'             => $post['kLink'],
                            'nAktiv'            => $post['nAktiv'],
                            'nLogging'          => $post['nLogging'],
                            'kCheckBoxFunktion' => $post['kCheckBoxFunktion'],
                            'kKundengruppe'     => $customerGroupID,
                        ];
                    }
                    $this->smarty->assign('oCheckBox', (object)$postBox);
                }
            }
            $tab = $step;
        }

        $pagination = (new Pagination())
            ->setItemCount($checkbox->getTotalCount())
            ->assemble();

        return $this->smarty->assign('oCheckBox_arr', $checkbox->getAll('LIMIT ' . $pagination->getLimitSQL()))
            ->assign('pagination', $pagination)
            ->assign('cAnzeigeOrt_arr', CheckBox::gibCheckBoxAnzeigeOrte())
            ->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('oLink_arr', $this->db->getObjects(
                'SELECT * 
                     FROM tlink 
                     ORDER BY cName'
            ))
            ->assign('oCheckBoxFunktion_arr', $checkbox->getCheckBoxFunctions())
            ->assign('step', $step)
            ->assign('cTab', $tab)
            ->getResponse('checkbox.tpl');
    }

    /**
     * @param CheckboxDataTableObject $checkboxDTO
     * @param array                   $languages
     * @return array
     * @former plausiCheckBox()
     */
    private function validate(CheckboxDataTableObject $checkboxDTO, array $languages): array
    {
        $checks = [];
        if (\count($languages) === 0) {
            $checks['oSprache_arr'] = 1;

            return $checks;
        }
        if (\mb_strlen($checkboxDTO->getName()) === 0) {
            $checks['cName'] = 1;
        }
        $text = false;
        $link = true;
        foreach ($languages as $language) {
            if (\mb_strlen($checkboxDTO->getLanguages()[$language->getIso()]['text']) > 0) {
                $text = true;
                break;
            }
        }
        if (!$text) {
            $checks['cText'] = 1;
        }
        if ($checkboxDTO->getHasLink() === true) {
            $link = $checkboxDTO->getLinkID() > 0;
        }
        if ($link === false) {
            $checks['kLink'] = 1;
        }
        if (\mb_strlen($checkboxDTO->getDisplayAt()) === 0) {
            $checks['cAnzeigeOrt'] = 1;
        } else {
            foreach (\explode(';', $checkboxDTO->getDisplayAt()) as $item) {
                if ((int)$item === 3 && $checkboxDTO->getCheckboxFunctionID() === 1) {
                    $checks['cAnzeigeOrt'] = 2;
                }
            }
        }
        if ($checkboxDTO->getSort() === 0) {
            $checks['nSort'] = 1;
        }
        if (\mb_strlen($checkboxDTO->getCustomerGroupsSelected()) === 0) {
            $checks['kKundengruppe'] = 1;
        }

        return $checks;
    }

    /**
     * @param CheckboxDataTableObject $checkboxDTO
     * @return CheckBox
     * @former speicherCheckBox()
     */
    private function save(CheckboxDataTableObject $checkboxDTO): CheckBox
    {
        return (new CheckBox(0, $this->db))->save($checkboxDTO);
    }

    /**
     * @param array $post
     * @param array $languages
     * @return CheckboxDataTableObject
     */
    private function getCheckboxDTO(array $post, array $languages): CheckboxDataTableObject
    {
        $checkBoxDTO = new CheckboxDataTableObject();
        if (isset($post['kCheckBox'])) {
            $checkBoxDTO->setCheckboxID((int)$post['kCheckBox']);
        }
        if (isset($post['nLink']) && (int)$post['nLink'] === -1) {
            $post['kLink'] = 0;
        }
        $checkBoxDTO->hydrate(
            \array_merge(
                $post,
                (isset($this->internalBoxDefaults[$post['cName']])
                    && \is_array($this->internalBoxDefaults[$post['cName']]))
                    ? $this->internalBoxDefaults[$post['cName']] : []
            )
        );
        $checkBoxDTO->setCreated('NOW()');

        return $this->addTranslationsToDTO($languages, $post, $checkBoxDTO);
    }

    /**
     * @param array                   $languages
     * @param array                   $post
     * @param CheckboxDataTableObject $checkboxDTO
     * @return CheckboxDataTableObject
     */
    private function addTranslationsToDTO(
        array                   $languages,
        array                   $post,
        CheckboxDataTableObject $checkboxDTO
    ): CheckboxDataTableObject {
        $texts = [];
        $descr = [];
        foreach ($languages as $language) {
            $code         = $language->getIso();
            $textCode     = 'cText_' . $code;
            $descrCode    = 'cBeschreibung_' . $code;
            $texts[$code] = isset($post[$textCode])
                ? \str_replace('"', '&quot;', $post[$textCode])
                : '';
            $descr[$code] = isset($post[$descrCode])
                ? \str_replace('"', '&quot;', $post[$descrCode])
                : '';
            $checkboxDTO->addLanguage(
                $code,
                language: [
                    'text'  => $texts[$code],
                    'descr' => $descr[$code]
                ]
            );
        }

        return $checkboxDTO;
    }
}
