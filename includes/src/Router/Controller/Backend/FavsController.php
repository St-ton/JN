<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AdminFavorite;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class FavsController
 * @package JTL\Router\Controller\Backend
 */
class FavsController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/favs');

        $adminID = $this->account->getID();
        if (isset($_POST['title'], $_POST['url'])
            && Form::validateToken()
            && Request::verifyGPDataString('action') === 'save'
        ) {
            $titles = Text::filterXSS($_POST['title']);
            $urls   = Text::filterXSS($_POST['url']);
            if (\is_array($titles) && \is_array($urls) && \count($titles) === \count($urls)) {
                $adminFav = new AdminFavorite($this->db);
                $adminFav->remove($adminID);
                foreach ($titles as $i => $title) {
                    $adminFav->add($adminID, $title, $urls[$i], $i);
                }
            }
        }

        return $smarty->assign('favorites', $this->account->favorites())
            ->assign('route', $this->route)
            ->getResponse('favs.tpl');
    }
}
