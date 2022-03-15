<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\Shop;
use stdClass;

/**
 * Class RootHandler
 * @package JTL\Router
 */
class RootHandler extends AbstractHandler
{
    /**
     * @param array $args
     * @return stdClass|null
     */
    public function handle(array $args): ?stdClass
    {
        $home = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE);
        if ($home !== null) {
            return (object)[
                'cSeo'     => $home->getSEO(),
                'kLink'    => $home->getID(),
                'kKey'     => $home->getID(),
                'cKey'     => 'kLink',
                'kSprache' => $home->getLanguageID()
            ];
        }

        return null;
    }
}
