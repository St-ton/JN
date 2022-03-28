<?php declare(strict_types=1);

use JTL\Plugin\HookManager;

/**
 * @param int   $hookID
 * @param array $args_arr
 */
function executeHook(int $hookID, array $args_arr = [])
{
    HookManager::getInstance()->executeHook($hookID, $args_arr);
}
