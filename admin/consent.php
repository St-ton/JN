<?php declare(strict_types=1);

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Consent\ConsentModel;
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

$item = new ConsentModel($db);

$step     = 'overview';
$valid    = Form::validateToken();
$action   = Request::postVar('action') ?? Request::getVar('action');
$itemID   = Request::postInt('id', null) ?? Request::getInt('id', null);
$continue = Request::postInt('save-model-continue') === 1;
$save     = $valid && ($continue || Request::postInt('save-model') === 1);
$cancel   = Request::postInt('go-back') === 1;
$delete   = $valid && Request::postInt('model-delete') === 1 && count(Request::postVar('mid')) > 0;

if ($cancel === false && $itemID !== null) {
    $step = 'detail';
    $item = ConsentModel::load(['id' => $itemID], $db);
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
            $model = ConsentModel::load(['id' => (int)$id], $db, DataModelInterface::ON_NOTEXISTS_FAIL);
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
        $alertService->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successSave')),
            'successSaveModel'
        );
        $step = $continue ? 'detail' : 'overview';
    } else {
        $alertService->addAlert(
            Alert::TYPE_ERROR,
            sprintf(__('errorSave')),
            'errorSaveModel'
        );
    }
} elseif ($delete === true) {
    if (deleteFromPost(Request::postVar('mid')) === true) {
        $alertService->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successDelete')),
            'successDeleteModel'
        );
        $step = $continue ? 'detail' : 'overview';
    } else {
        $alertService->addAlert(
            Alert::TYPE_ERROR,
            sprintf(__('errorDelete')),
            'errorDeleteModel'
        );
    }
}

$consents   = ConsentModel::loadAll($db, [], []);
$pagination = (new Pagination('consents'))
    ->setItemCount($consents->count())
    ->assemble();

$smarty->assign('step', $step)
    ->assign('item', $item)
    ->assign('consents', $consents)
    ->assign('action', Shop::getAdminURL() . '/consent.php')
    ->assign('pagination', $pagination)
    ->display('consent.tpl');
