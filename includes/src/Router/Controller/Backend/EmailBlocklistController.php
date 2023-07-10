<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class EmailBlocklistController
 * @package JTL\Router\Controller\Backend
 */
class EmailBlocklistController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::SETTINGS_EMAIL_BLACKLIST_VIEW);
        $this->getText->loadAdminLocale('pages/emailblacklist');

        $step = 'emailblacklist';
        if ($this->request->postInt('einstellungen') > 0) {
            $this->saveAdminSectionSettings(\CONF_EMAILBLACKLIST, $this->request->getBody());
        }
        if ($this->tokenIsValid && $this->request->postInt('emailblacklist') === 1) {
            $addresses = \explode(';', Text::filterXSS($this->request->post('cEmail')));
            if (\count($addresses) > 0) {
                $this->db->query('TRUNCATE temailblacklist');
                foreach ($addresses as $mail) {
                    $mail = \strip_tags(\trim($mail));
                    if (\mb_strlen($mail) > 0) {
                        $this->db->insert('temailblacklist', (object)['cEmail' => $mail]);
                    }
                }
            }
        }
        $blocklist = $this->db->selectAll('temailblacklist', [], []);
        $blocked   = $this->db->getObjects(
            "SELECT *, DATE_FORMAT(dLetzterBlock, '%d.%m.%Y %H:%i') AS Datum
                FROM temailblacklistblock
                ORDER BY dLetzterBlock DESC
                LIMIT 100"
        );
        $this->getAdminSectionSettings(\CONF_EMAILBLACKLIST);

        return $this->smarty->assign('blacklist', $blocklist)
            ->assign('blocked', $blocked)
            ->assign('step', $step)
            ->getResponse('emailblacklist.tpl');
    }
}
