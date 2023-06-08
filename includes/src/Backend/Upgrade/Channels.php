<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class Channels
 * @package JTL\Backend\Upgrade
 * @since 5.3.0
 */
class Channels
{
    public const CHANNEL_STABLE        = 'STABLE';
    public const CHANNEL_BETA          = 'BETA';
    public const CHANNEL_ALPHA         = 'ALPHA';
    public const CHANNEL_BLEEDING_EDGE = 'BLEEDINGEDGE';

    public static function getActiveChannel(?DbInterface $db = null): string
    {
        return $_SESSION['selected_upgrade_channel']
            ?? ($db ?? Shop::Container()->getDB())->select('tversion', [], [])->releaseType
            ?? 'STABLE';
    }

    public static function getChannels(): array
    {
        $channels = [
            (object)['id' => 1, 'name' => self::CHANNEL_STABLE, 'selected' => false, 'disabled' => false],
            (object)['id' => 2, 'name' => self::CHANNEL_BETA, 'selected' => false, 'disabled' => false],
            (object)['id' => 3, 'name' => self::CHANNEL_ALPHA, 'selected' => false, 'disabled' => false],
            (object)['id' => 4, 'name' => self::CHANNEL_BLEEDING_EDGE, 'selected' => false, 'disabled' => true],
        ];
        $selected = self::getActiveChannel();
        foreach ($channels as $channel) {
            if ($channel->name === $selected) {
                $channel->selected = true;
                break;
            }
        }

        return $channels;
    }

    public static function updateChannel(string $channel): array
    {
        $_SESSION['selected_upgrade_channel'] = $channel;

        return self::getChannels();
    }
}
