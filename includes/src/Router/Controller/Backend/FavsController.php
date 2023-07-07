<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\AdminFavorite;
use JTL\Helpers\Text;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->getText->loadAdminLocale('pages/favs');

        $adminID = $this->account->getID();
        if ($this->tokenIsValid
            && $this->request->post('title') !== null
            && $this->request->post('url') !== null
            && $this->request->request('action') === 'save'
        ) {
            $titles = Text::filterXSS($this->request->post('title'));
            $urls   = Text::filterXSS($this->request->post('url'));
            if (\is_array($titles) && \is_array($urls) && \count($titles) === \count($urls)) {
                $adminFav = new AdminFavorite($this->db);
                $adminFav->remove($adminID);
                foreach ($titles as $i => $title) {
                    $adminFav->add($adminID, $title, $urls[$i], $i);
                }
            }
        }

        return $this->smarty->assign('favorites', $this->account->favorites())
            ->getResponse('favs.tpl');
    }
}
