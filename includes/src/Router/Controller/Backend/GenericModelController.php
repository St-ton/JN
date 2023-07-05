<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Model\DataModelInterface;
use JTL\Pagination\Pagination;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use function Functional\every;
use function Functional\map;

/**
 * Class GenericModelController
 * @package JTL\Router\Controller\Backend
 */
abstract class GenericModelController extends AbstractBackendController
{
    /**
     * @var string
     */
    protected string $adminBaseFile = '';

    /**
     * @var string
     */
    protected string $modelClass;

    /**
     * @var string
     */
    protected string $step = 'overview';

    /**
     * @var DataModelInterface|null
     */
    protected ?DataModelInterface $item = null;

    /**
     * @var string
     */
    protected string $tab = 'overview';

    /**
     * @var DataModelInterface|null
     */
    protected ?DataModelInterface $child = null;

    /**
     * @param string $template
     * @return ResponseInterface
     */
    public function handle(string $template): ResponseInterface
    {
        $this->item = new $this->modelClass($this->db);
        $this->step = $_SESSION['step'] ?? 'overview';
        $valid      = Form::validateToken();
        $action     = Request::postVar('action') ?? Request::getVar('action');
        $itemID     = $_SESSION['modelid'] ?? Request::postInt('id', null) ?? Request::getInt('id', null);
        $continue   = (bool)($_SESSION['continue'] ?? Request::postInt('save-model-continue') === 1);
        $save       = $valid && ($continue || Request::postInt('save-model') === 1);
        $modelIDs   = Request::postVar('mid', []);
        $cancel     = Request::postInt('go-back') === 1;
        if (\count($modelIDs) === 0 && Request::postInt('id', null) > 0) {
            $modelIDs = [Request::postInt('id')];
        }
        $delete       = $valid && Request::postInt('model-delete') === 1 && \count($modelIDs) > 0;
        $disable      = $valid && Request::postInt('model-disable') === 1 && \count($modelIDs) > 0;
        $enable       = $valid && Request::postInt('model-enable') === 1 && \count($modelIDs) > 0;
        $create       = Request::postInt('model-create') === 1;
        $saveSettings = Request::postVar('a') === 'saveSettings';
        if ($cancel) {
            return $this->modelPRG();
        }
        if ($continue === false) {
            unset($_SESSION['modelid']);
        }
        if ($action === 'detail') {
            $this->step = 'detail';
        }
        if ($itemID > 0) {
            $this->item = $this->modelClass::load(['id' => $itemID], $this->db);
        }
        unset($_SESSION['step'], $_SESSION['continue']);

        if ($save === true) {
            return $this->save($itemID, $continue);
        }
        if ($delete === true) {
            return $this->update($continue, $modelIDs);
        }
        if ($saveSettings === true) {
            $this->saveSettings();
        } elseif ($disable === true) {
            $this->disable($modelIDs);
        } elseif ($enable === true) {
            $this->enable($modelIDs);
        } elseif ($create === true) {
            $this->item = new $this->modelClass($this->db);
            $this->step = 'detail';
        }
        if ($this->item !== null) {
            foreach ($this->item->getAttributes() as $attribute) {
                if (\str_contains($attribute->getDataType(), '\\')) {
                    $className   = $attribute->getDataType();
                    $this->child = new $className($this->getDB());
                }
            }
        }
        $this->setMessages();

        $models     = $this->modelClass::loadAll($this->db, [], []);
        $pagination = (new Pagination($template))
            ->setItemCount($models->count())
            ->assemble();

        return $this->smarty->assign('step', $this->step)
            ->assign('item', $this->item)
            ->assign('models', $models->forPage($pagination->getPage() + 1, $pagination->getItemsPerPage()))
            ->assign('action', $this->getAction())
            ->assign('pagination', $pagination)
            ->assign('childModel', $this->child)
            ->assign('tab', $this->tab)
            ->getResponse($template);
    }

    /**
     * @return string
     */
    protected function getAction(): string
    {
        return $this->baseURL . '/' . $this->adminBaseFile;
    }

    /**
     * @param int  $itemID
     * @param bool $continue
     * @return ResponseInterface
     */
    protected function save(int $itemID, bool $continue): ResponseInterface
    {
        if ($this->updateFromPost($this->item, Text::filterXSS($_POST)) === true) {
            $_SESSION['modelid']         = $itemID;
            $_SESSION['modelSuccessMsg'] = \__('successSave');
            $_SESSION['step']            = $continue ? 'detail' : 'overview';
        } else {
            $_SESSION['modelErrorMsg'] = \__('errorSave');
        }
        $_SESSION['continue'] = $continue;

        return $this->modelPRG();
    }

    /**
     * @param bool  $continue
     * @param int[] $modelIDs
     * @return ResponseInterface
     */
    protected function update(bool $continue, array $modelIDs): ResponseInterface
    {
        if ($this->deleteFromPost($modelIDs) === true) {
            $_SESSION['modelSuccessMsg'] = \__('successDelete');
            $_SESSION['step']            = $continue ? 'detail' : 'overview';
        } else {
            $_SESSION['modelErrorMsg'] = \__('errorDelete');
        }

        return $this->modelPRG();
    }

    protected function setMessages(): void
    {
        if (isset($_SESSION['modelSuccessMsg'])) {
            $this->alertService->addSuccess($_SESSION['modelSuccessMsg'], 'successModel');
            unset($_SESSION['modelSuccessMsg']);
        }
        if (isset($_SESSION['modelErrorMsg'])) {
            $this->alertService->addError($_SESSION['modelErrorMsg'], 'errorModel');
            unset($_SESSION['modelErrorMsg']);
        }
    }

    /**
     * @param array $ids
     */
    protected function enable(array $ids): void
    {
        if ($this->setState($ids, 1)) {
            $_SESSION['modelSuccessMsg'] = \__('successSave');
        }
    }

    /**
     * @param array $ids
     */
    protected function disable(array $ids): void
    {
        if ($this->setState($ids, 0)) {
            $_SESSION['modelSuccessMsg'] = \__('successSave');
        }
    }

    /**
     * @param array $ids
     * @param int   $state
     * @return bool
     */
    protected function setState(array $ids, int $state): bool
    {
        return every(map($ids, function ($id) use ($state) {
            try {
                /** @var DataModelInterface $model */
                $model = $this->modelClass::load(['id' => (int)$id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
                $model->setAttribValue('active', $state);

                return $model->save(['active']);
            } catch (Exception) {
                return false;
            }
        }), function (bool $e): bool {
            return $e === true;
        });
    }

    /**
     * @param int $status
     * @return ResponseInterface
     */
    public function modelPRG(int $status = 303): ResponseInterface
    {
        return new RedirectResponse($this->baseURL . $this->route, $status);
    }

    /**
     * @param DataModelInterface $model
     * @param array              $post
     * @return bool
     */
    public function updateFromPost(DataModelInterface $model, array $post): bool
    {
        foreach ($model->getAttributes() as $attr) {
            $name         = $attr->getName();
            $type         = $attr->getDataType();
            $isChildModel = \str_contains($type, '\\') && \class_exists($type);
            if ($isChildModel) {
                if (isset($post[$name]) && \is_array($post[$name])) {
                    $test = $post[$name];
                    $res  = [];
                    foreach ($test as $key => $values) {
                        foreach ($values as $idx => $value) {
                            $item       = $res[$idx] ?? [];
                            $item[$key] = $value;
                            $res[$idx]  = $item;
                        }
                    }
                    $model->$name = $res;
                }
            } elseif (isset($post[$name])) {
                $model->$name = $post[$name];
            }
        }

        return $model->save();
    }

    /**
     * @param int[] $ids
     * @return bool
     */
    public function deleteFromPost(array $ids): bool
    {
        return every(map($ids, function ($id) {
            try {
                /** @var DataModelInterface $model */
                $model = $this->modelClass::load(['id' => (int)$id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
            } catch (Exception) {
                return false;
            }

            return $model->delete();
        }), static function (bool $e): bool {
            return $e === true;
        });
    }

    /**
     * @return void
     */
    public function saveSettings(): void
    {
        $this->tab = 'settings';
        $this->saveAdminSectionSettings(\CONF_CONSENTMANAGER, $_POST);
    }
}
