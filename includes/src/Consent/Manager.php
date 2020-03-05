<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Consent;

use Illuminate\Support\Collection;
use JTL\Shop;

class Manager implements ManagerInterface
{
    public function revokeConsent(ItemInterface $item): void
    {
        // TODO: Implement revokeConset() method.
    }

    public function giveConsent(ItemInterface $item): void
    {
        // TODO: Implement giveConsent() method.
    }

    public function hasConsent(ItemInterface $item): bool
    {
        // TODO: Implement hasConsent() method.
        return false;
    }

    public function getActiveItems(int $languageID): Collection
    {
        $all = ConsentModel::loadAll(Shop::Container()->getDB(), 'active', 1);

        return $all->filter(static function (ConsentModel $model) use ($languageID) {
            return $model->initFrontend($languageID) === true;
        });
    }
}
