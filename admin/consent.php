<?php declare(strict_types=1);

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Consent\ConsentModel;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Model\DataModelInterface;
use JTL\Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);

$step = 'overview';
$db   = Shop::Container()->getDB();

$consents = ConsentModel::loadAll($db, [], []);
$item     = new ConsentModel($db);

$action = Request::postVar('action') ?? Request::getVar('action');
$itemID = Request::postInt('id', null) ?? Request::getInt('id', null);

if ($action === 'detail' && $itemID !== null) {
    $step = 'detail';
    $item = ConsentModel::load(['id' => $itemID], $db);
}

function updateFromPost(DataModelInterface $model, array $post)
{
    foreach ($model->getAttributes() as $attr) {
        $name         = $attr->getName();
        $type         = $attr->getDataType();
        $isChildModel = strpos($type, '\\') !== false && class_exists($type);
//        Shop::dbg($isChildModel, false, 'is child ' . $name . '?');
        if ($isChildModel) {
            if (isset($post[$name]) && is_array($post[$name])) {
                $test = $post[$name];
                Shop::dbg($test);
                $res = [];
                foreach ($test as $key => $values) {
                    foreach ($values as $idx => $value) {
                        $item       = $res[$idx] ?? [];
                        $item[$key] = $value;
                        $res[$idx]  = $item;
                    }
                }
                Shop::dbg($res, true, 'RES:');
                die();
            }
        } elseif (isset($post[$name])) {
//            Shop::dbg($post[$name], false, 'setting attr ' . $name . ' to ');
            $model->$name = $post[$name];
        } else {
            Shop::dbg($attr, false, 'attr not set at post:');
        }
    }

    return $model;
}

//$t = ConsentModel::load(['id' => 3], $db);
//Shop::dbg($t);
//$t->itemID = 'recaptcha' . random_int(0,99);
//$t->save();
//foreach (\array_keys($item->getAttributes()) as $attr) {
//    Shop::dbg($attr, false, 'ATTR:');
////if ($request->has($attr)) {
////    $value        = $request->get($attr);
////    $model->$attr = $value;
////}
//}
//if (!empty($_POST)) {
//    foreach ($_POST as $item => $value) {
//        Shop::dbg($value, false, $item);
//    }
//}

if (!empty($_POST)) {
    $res = updateFromPost($item, $_POST);
    Shop::dbg($res, false, 'RESulting model:');
    Shop::dbg($_POST['localization'], true);
}

$pagination = (new Pagination('consents'))
    ->setItemCount($consents->count())
    ->assemble();

$smarty->assign('step', $step)
    ->assign('item', $item)
    ->assign('consents', $consents)
    ->assign('action', Shop::getAdminURL() . '/consent.php')
    ->assign('pagination', $pagination)
    ->display('consent.tpl');
