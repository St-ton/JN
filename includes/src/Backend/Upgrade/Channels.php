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
            (object)['name' => self::CHANNEL_STABLE, 'disabled' => false],
            (object)['name' => self::CHANNEL_BETA, 'disabled' => !\SHOW_UPGRADE_CHANNEL_BETA],
            (object)['name' => self::CHANNEL_ALPHA, 'disabled' => !\SHOW_UPGRADE_CHANNEL_ALPHA],
            (object)['name' => self::CHANNEL_BLEEDING_EDGE, 'disabled' => !\SHOW_UPGRADE_CHANNEL_BLEEDING_EDGE],
        ];
        $selected = self::getActiveChannel();
        foreach ($channels as $i => $channel) {
            $channel->selected = false;
            $channel->id       = $i + 1;
            if ($channel->name === $selected) {
                $channel->selected = true;
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
