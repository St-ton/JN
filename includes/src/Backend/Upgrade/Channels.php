<?php declare(strict_types=1);

namespace JTL\Backend\Upgrade;

use JTL\Shop;
use JTL\Smarty\JTLSmarty;

class Channels
{
    public static function getActiveChannel(): string
    {
        return $_SESSION['selected_upgrade_channel'] ?? 'stable';
    }

    public static function getChannels(): array
    {
        $channels = [
            (object)['id' => 1, 'name' => 'stable', 'selected' => false, 'disabled' => false],
            (object)['id' => 2, 'name' => 'beta', 'selected' => false, 'disabled' => false],
            (object)['id' => 2, 'name' => 'bleeding edge', 'selected' => false, 'disabled' => true],
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
