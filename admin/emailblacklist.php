<?php declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('SETTINGS_EMAIL_BLACKLIST_VIEW', true, true);
$step = 'emailblacklist';
$db   = Shop::Container()->getDB();
if (Request::postInt('einstellungen') > 0) {
    saveAdminSectionSettings(CONF_EMAILBLACKLIST, $_POST);
}
if (Request::postInt('emailblacklist') === 1 && Form::validateToken()) {
    $addresses = explode(';', Text::filterXSS($_POST['cEmail']));
    if (count($addresses) > 0) {
        $db->query('TRUNCATE temailblacklist');
        foreach ($addresses as $mail) {
            $mail = strip_tags(trim($mail));
            if (mb_strlen($mail) > 0) {
                $db->insert('temailblacklist', (object)['cEmail' => $mail]);
            }
        }
    }
}
$blacklist = $db->selectAll('temailblacklist', [], []);
$blocked   = $db->getObjects(
    "SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
        FROM temailblacklistblock
        ORDER BY dLetzterBlock DESC
        LIMIT 100"
);
getAdminSectionSettings(CONF_EMAILBLACKLIST);
$smarty->assign('blacklist', $blacklist)
    ->assign('blocked', $blocked)
    ->assign('step', $step)
    ->display('emailblacklist.tpl');
