<?php
/**
 * HOOK_BACKEND_ACCOUNT_LOGIN
 *
 * Dieses Plugin erweitert den Backendnutzer um sein Profilbild
 *
 * @package   jtl_backenduser_extension
 * @copyright JTL-Software-GmbH
 *
 * @global array $args_arr
 * @global Plugin $oPlugin
 */

$oAdminExt = $args_arr['oAdmin'];
$oAdminExt->extensionAvatar = Shop::DB()->select(
    'tadminloginattribut',
    'kAdminlogin',
    $oAdminExt->kAdminlogin,
    'cName',
    'useAvatarUpload',
    null,
    null,
    false,
    'cAttribValue'
);
$_SESSION['AdminAccount']->extensionAvatar    = $oAdminExt->extensionAvatar->cAttribValue;

$args_arr['oAdmin'] = $oAdminExt;
