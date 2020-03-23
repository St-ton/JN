<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Consent;

use Illuminate\Support\Collection;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Manager
 * @package JTL\Consent
 */
class Manager implements ManagerInterface
{
    /**
     * @var Collection
     */
    private $activeItems;

    /**
     * @inheritDoc
     */
    public function getConsents(): array
    {
        return Frontend::get('consents') ?? [];
    }

    /**
     * @inheritDoc
     */
    public function itemRevokeConsent(ItemInterface $item): void
    {
        $consents = $this->getConsents();
        $consents[$item->getItemID()] = false;
        Frontend::set('consents', $consents);
    }

    /**
     * @inheritDoc
     */
    public function itemGiveConsent(ItemInterface $item): void
    {
        $consents = $this->getConsents();
        $consents[$item->getItemID()] = true;
        Frontend::set('consents', $consents);
    }

    /**
     * @inheritDoc
     */
    public function itemHasConsent(ItemInterface $item): bool
    {
        return $this->hasConsent($item->getItemID());
    }

    /**
     * @inheritDoc
     */
    public function hasConsent(string $itemID): bool
    {
        return (($this->getConsents())[$itemID]) ?? false;
    }

    /**
     * @inheritDoc
     */
    public function save($data): ?array
    {
        if (!\is_array($data)) {
            return [];
        }
        $consents = [];
        foreach ($data as $item => $value) {
            if (!\is_string($item) || !\in_array($value, ['true', 'false'], true)) {
                continue;
            }
            $consents[$item] = $value === 'true';
        }
        Frontend::set('consents', $consents);

        return Frontend::get('consents');
    }

    /**
     * @inheritDoc
     */
    public function initActiveItems(int $languageID): Collection
    {
        $models = ConsentModel::loadAll(Shop::Container()->getDB(), 'active', 1)->map(
            static function (ConsentModel $model) use ($languageID) {
                return (new Item($languageID))->loadFromModel($model);
            }
        );
        \executeHook(CONSENT_MANAGER_GET_ACTIVE_ITEMS, ['items' => $models]);
        $this->activeItems = $models;

        return $this->activeItems;
    }

    /**
     * @inheritDoc
     */
    public function getActiveItems(int $languageID): Collection
    {
        return $this->activeItems ?? $this->initActiveItems($languageID);
    }
}
