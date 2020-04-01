<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Consent\ConsentModel as Model;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Model\DataModelInterface;
use JTL\Pagination\Pagination;
use function Functional\every;
use function Functional\map;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$db           = Shop::Container()->getDB();
$alertService = Shop::Container()->getAlertService();

$item     = new Model($db);
$step     = $_SESSION['step'] ?? 'overview';
$valid    = Form::validateToken();
$action   = Request::postVar('action') ?? Request::getVar('action');
$itemID   = $_SESSION['modelid'] ?? Request::postInt('id', null) ?? Request::getInt('id', null);
$continue = $_SESSION['continue'] ?? Request::postInt('save-model-continue') === 1;
$save     = $valid && ($continue || Request::postInt('save-model') === 1);
$cancel   = Request::postInt('go-back') === 1;
$delete   = $valid && Request::postInt('model-delete') === 1 && count(Request::postVar('mid')) > 0;
if ($cancel) {
    modelPRG();
}
if ($continue === false) {
    unset($_SESSION['modelid']);
}
if ($action === 'detail') {
    $step = 'detail';
}
if ($itemID > 0) {
    $item = Model::load(['id' => $itemID], $db);
}
unset($_SESSION['step'], $_SESSION['continue']);

function modelPRG(): void
{
    header('Location: ' . Shop::getAdminURL() . '/consent.php', true, 303);
    exit;
}

/**
 * @param DataModelInterface $model
 * @param array              $post
 * @return bool
 */
function updateFromPost(DataModelInterface $model, array $post): bool
{
    foreach ($model->getAttributes() as $attr) {
        $name         = $attr->getName();
        $type         = $attr->getDataType();
        $isChildModel = strpos($type, '\\') !== false && class_exists($type);
        if ($isChildModel) {
            if (isset($post[$name]) && is_array($post[$name])) {
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
function deleteFromPost(array $ids): bool
{
    $db = Shop::Container()->getDB();

    return every(map($ids, static function ($id) use ($db) {
        try {
            $model = Model::load(['id' => (int)$id], $db, DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            return false;
        }

        return $model->delete();
    }), static function (bool $e) {
        return $e === true;
    });
}

if ($save === true && $cancel === false) {
    if (updateFromPost($item, $_POST) === true) {
        $_SESSION['modelid']    = $itemID;
        $_SESSION['successMsg'] = sprintf(__('successSave'));
        $_SESSION['step']       = $continue ? 'detail' : 'overview';
    } else {
        $_SESSION['errorMsg'] = sprintf(__('errorSave'));
    }
    $_SESSION['continue'] = $continue;
    modelPRG();
} elseif ($delete === true) {
    if (deleteFromPost(Request::postVar('mid')) === true) {
        $_SESSION['successMsg'] = sprintf(__('successDelete'));
        $_SESSION['step']       = $continue ? 'detail' : 'overview';
    } else {
        $_SESSION['errorMsg'] = sprintf(__('errorDelete'));
    }
    modelPRG();
}

if (isset($_SESSION['successMsg'])) {
    $alertService->addAlert(
        Alert::TYPE_SUCCESS,
        $_SESSION['successMsg'],
        'successModel'
    );
    unset($_SESSION['successMsg']);
}
if (isset($_SESSION['errorMsg'])) {
    $alertService->addAlert(
        Alert::TYPE_ERROR,
        $_SESSION['errorMsg'],
        'errorModel'
    );
    unset($_SESSION['errorMsg']);
}

$consents   = Model::loadAll($db, [], []);
$pagination = (new Pagination('consents'))
    ->setItemCount($consents->count())
    ->assemble();

$smarty->assign('step', $step)
    ->assign('item', $item)
    ->assign('consents', $consents)
    ->assign('action', Shop::getAdminURL() . '/consent.php')
    ->assign('pagination', $pagination)
    ->display('consent.tpl');
