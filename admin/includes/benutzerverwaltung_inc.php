<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Backend\TwoFA;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\IO\IOResponse;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use function Functional\group;
use function Functional\map;

/**
 * @param int $adminID
 * @return null|stdClass
 */
function getAdmin(int $adminID): ?stdClass
{
    return Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $adminID);
}

/**
 * @return array
 */
function getAdminList(): array
{
    return Shop::Container()->getDB()->query(
        'SELECT * FROM tadminlogin
            LEFT JOIN tadminlogingruppe
                ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe
         ORDER BY kAdminlogin',
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @return array
 */
function getAdminGroups(): array
{
    return Shop::Container()->getDB()->query(
        'SELECT tadminlogingruppe.*, COUNT(tadminlogin.kAdminlogingruppe) AS nCount
            FROM tadminlogingruppe
            LEFT JOIN tadminlogin
                ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe
            GROUP BY tadminlogingruppe.kAdminlogingruppe',
        ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @return array
 */
function getAdminDefPermissions(): array
{
    $groups = Shop::Container()->getDB()->selectAll('tadminrechtemodul', [], [], '*', 'nSort ASC');
    $perms  = group(Shop::Container()->getDB()->selectAll('tadminrecht', [], []), function ($e) {
        return $e->kAdminrechtemodul;
    });
    foreach ($groups as $group) {
        $group->kAdminrechtemodul = (int)$group->kAdminrechtemodul;
        $group->nSort             = (int)$group->nSort;
        $group->cName             = __($group->cName);
        $group->oPermission_arr   = map($perms[$group->kAdminrechtemodul] ?? [], function ($permission) {
            $permission->cBeschreibung = __('permission_' . $permission->cRecht);

            return $permission;
        });
    }

    return $groups;
}

/**
 * @param int $groupID
 * @return null|stdClass
 */
function getAdminGroup(int $groupID): ?stdClass
{
    return Shop::Container()->getDB()->select('tadminlogingruppe', 'kAdminlogingruppe', $groupID);
}

/**
 * @param int $groupID
 * @return array
 */
function getAdminGroupPermissions(int $groupID): array
{
    $permissions = [];
    $data        = Shop::Container()->getDB()->selectAll('tadminrechtegruppe', 'kAdminlogingruppe', $groupID);
    foreach ($data as $oPermission) {
        $permissions[] = $oPermission->cRecht;
    }

    return $permissions;
}

/**
 * @param string     $row
 * @param string|int $value
 * @return bool
 */
function getInfoInUse($row, $value): bool
{
    return is_object(Shop::Container()->getDB()->select('tadminlogin', $row, $value));
}

/**
 * @param string $languageTag
 */
function changeAdminUserLanguage(string $languageTag): void
{
    $_SESSION['AdminAccount']->language = $languageTag;
    $_SESSION['Sprachen']               = LanguageHelper::getInstance()->gibInstallierteSprachen();

    Shop::Container()->getDB()->update(
        'tadminlogin',
        'kAdminlogin',
        $_SESSION['AdminAccount']->kAdminlogin,
        (object)['language' => $languageTag]
    );
}

/**
 * @param int $adminID
 * @return array
 */
function benutzerverwaltungGetAttributes(int $adminID): array
{
    $extAttribs = Shop::Container()->getDB()->selectAll(
        'tadminloginattribut',
        'kAdminlogin',
        $adminID,
        'kAttribut, cName, cAttribValue, cAttribText',
        'cName ASC'
    );

    return array_column($extAttribs, null, 'cName');
}

/**
 * @param stdClass $account
 * @param array    $extAttribs
 * @param array    $messages
 * @param array    $errorMap
 * @return bool
 */
function benutzerverwaltungSaveAttributes(stdClass $account, array $extAttribs, array &$messages, array &$errorMap)
{
    if (is_array($extAttribs)) {
        $result = true;
        validateAccount($extAttribs, $messages);

        executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
            'oAccount' => $account,
            'type'     => 'VALIDATE',
            'attribs'  => &$extAttribs,
            'messages' => &$messages,
            'result'   => &$result,
        ]);

        if ($result !== true) {
            $errorMap = array_merge($errorMap, $result);

            return false;
        }

        $handledKeys = [];
        $db          = Shop::Container()->getDB();
        foreach ($extAttribs as $key => $value) {
            $key      = Text::filterXSS($key);
            $longText = null;
            if (is_array($value) && count($value) > 0) {
                $shortText = Text::filterXSS($value[0]);
                if (count($value) > 1) {
                    $longText = $value[1];
                }
            } else {
                $shortText = Text::filterXSS($value);
            }
            if ($db->queryPrepared(
                'INSERT INTO tadminloginattribut (kAdminlogin, cName, cAttribValue, cAttribText)
                    VALUES (:loginID, :loginName, :attribVal, :attribText)
                    ON DUPLICATE KEY UPDATE
                    cAttribValue = :attribVal,
                    cAttribText = :attribText',
                [
                        'loginID'    => $account->kAdminlogin,
                        'loginName'  => $key,
                        'attribVal'  => $shortText,
                        'attribText' => $longText ?? 'NULL'
                    ],
                ReturnType::DEFAULT
            ) === 0) {
                $messages['error'] .= sprintf(__('errorKeyChange'), $key);
            }
            $handledKeys[] = $key;
        }
        // nicht (mehr) vorhandene Attribute löschen
        $db->query(
            'DELETE FROM tadminloginattribut
                WHERE kAdminlogin = ' . (int)$account->kAdminlogin . "
                    AND cName NOT IN ('" . implode("', '", $handledKeys) . "')",
            ReturnType::DEFAULT
        );
    }

    return true;
}

/**
 * @param array $attribs
 * @param array $messages
 * @return array|bool
 */
function validateAccount(array &$attribs, array &$messages)
{
    $result = true;

    if (!$attribs['useAvatar']) {
        $attribs['useAvatar'] = 'N';
    }

    if ($attribs['useAvatar'] === 'U') {
        if (isset($_FILES['extAttribs']) && !empty($_FILES['extAttribs']['name']['useAvatarUpload'])) {
            $attribs['useAvatarUpload'] = uploadAvatarImage($_FILES['extAttribs'], 'useAvatarUpload');

            if ($attribs['useAvatarUpload'] === false) {
                $messages['error'] .= __('errorImageUpload');

                $result = ['useAvatarUpload' => 1];
            }
        } elseif (empty($attribs['useAvatarUpload'])) {
            $messages['error'] .= __('errorImageMissing');

            $result = ['useAvatarUpload' => 1];
        }
    } elseif (!empty($attribs['useAvatarUpload'])) {
        if (\is_file(\PFAD_ROOT . $attribs['useAvatarUpload'])) {
            \unlink(\PFAD_ROOT . $attribs['useAvatarUpload']);
        }
        $attribs['useAvatarUpload'] = '';
    }

    foreach (LanguageHelper::getAllLanguages() as $language) {
        $useVita_ISO = 'useVita_' . $language->cISO;
        if (!empty($attribs[$useVita_ISO])) {
            $shortText = StringHandler::filterXSS($attribs[$useVita_ISO]);
            $longtText = $attribs[$useVita_ISO];

            if (\mb_strlen($shortText) > 255) {
                $shortText = \mb_substr($shortText, 0, 250) . '...';
            }

            $attribs[$useVita_ISO] = [$shortText, $longtText];
        }
    }

    return $result;
}

/**
 * @param array $tmpFile
 * @param string $attribName
 * @return bool|string
 */
function uploadAvatarImage(array $tmpFile, string $attribName)
{
    $imgType = \array_search($tmpFile['type'][$attribName], [
        \IMAGETYPE_JPEG => \image_type_to_mime_type(\IMAGETYPE_JPEG),
        \IMAGETYPE_PNG  => \image_type_to_mime_type(\IMAGETYPE_PNG),
        \IMAGETYPE_BMP  => \image_type_to_mime_type(\IMAGETYPE_BMP),
        \IMAGETYPE_GIF  => \image_type_to_mime_type(\IMAGETYPE_GIF),
    ], true);

    if ($imgType !== false) {
        $imagePath = \PFAD_MEDIA_IMAGE . 'avatare/';
        $imageName = \pathinfo($tmpFile['name'][$attribName], \PATHINFO_FILENAME)
            . \image_type_to_extension($imgType);
        if (\is_dir(\PFAD_ROOT . $imagePath) || \mkdir(\PFAD_ROOT . $imagePath, 0755)) {
            if (\move_uploaded_file($tmpFile['tmp_name'][$attribName], \PFAD_ROOT . $imagePath . $imageName)) {
                return '/' . $imagePath . $imageName;
            }
        }
    }

    return false;
}

/**
 * @param stdClass $oAccount
 * @return bool
 */
function benutzerverwaltungDeleteAttributes(stdClass $oAccount): bool
{
    return Shop::Container()->getDB()->delete('tadminloginattribut', 'kAdminlogin', (int)$oAccount->kAdminlogin) >= 0;
}

/**
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountLock(array &$messages): string
{
    $adminID = Request::postInt('id');
    $account = Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $adminID);
    if (!empty($account->kAdminlogin) && (int)$account->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
        $messages['error'] .= __('errorSelfLock');
    } elseif (is_object($account)) {
        if ((int)$account->kAdminlogingruppe === ADMINGROUP) {
            $messages['error'] .= __('errorLockAdmin');
        } else {
            $result = true;
            Shop::Container()->getDB()->update('tadminlogin', 'kAdminlogin', $adminID, (object)['bAktiv' => 0]);
            executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $account,
                'type'     => 'LOCK',
                'attribs'  => null,
                'messages' => &$messages,
                'result'   => &$result,
            ]);
            if ($result === true) {
                $messages['notice'] .= __('successLock');
            }
        }
    } else {
        $messages['error'] .= __('errorUserNotFound');
    }

    return 'index_redirect';
}

/**
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountUnLock(array &$messages): string
{
    $adminID = Request::postInt('id');
    $account = Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $adminID);
    if (is_object($account)) {
        $result = true;
        Shop::Container()->getDB()->update('tadminlogin', 'kAdminlogin', $adminID, (object)['bAktiv' => 1]);
        executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
            'oAccount' => $account,
            'type'     => 'UNLOCK',
            'attribs'  => null,
            'messages' => &$messages,
            'result'   => &$result,
        ]);
        if ($result === true) {
            $messages['notice'] .= __('successUnlocked');
        }
    } else {
        $messages['error'] .= __('errorUserNotFound');
    }

    return 'index_redirect';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function benutzerverwaltungActionAccountEdit(JTLSmarty $smarty, array &$messages): string
{
    $_SESSION['AdminAccount']->TwoFA_valid = true;

    $db          = Shop::Container()->getDB();
    $adminID     = Request::postInt('id', null);
    $qrCode      = '';
    $knownSecret = '';
    if ($adminID !== null) {
        $twoFA = new TwoFA($db);
        $twoFA->setUserByID($_POST['id']);

        if ($twoFA->is2FAauthSecretExist() === true) {
            $qrCode      = $twoFA->getQRcode();
            $knownSecret = $twoFA->getSecret();
        }
    }
    $smarty->assign('QRcodeString', $qrCode)
        ->assign('cKnownSecret', $knownSecret);

    if (isset($_POST['save'])) {
        $errors              = [];
        $tmpAcc              = new stdClass();
        $tmpAcc->kAdminlogin = Request::postInt('kAdminlogin');
        $tmpAcc->cName       = htmlspecialchars(trim($_POST['cName']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $tmpAcc->cMail       = htmlspecialchars(trim($_POST['cMail']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $tmpAcc->language    = $_POST['language'];
        $tmpAcc->cLogin      = trim($_POST['cLogin']);
        $tmpAcc->cPass       = trim($_POST['cPass']);
        $tmpAcc->b2FAauth    = Request::postInt('b2FAauth');
        $tmpAttribs          = $_POST['extAttribs'] ?? [];

        if (0 < mb_strlen($_POST['c2FAsecret'])) {
            $tmpAcc->c2FAauthSecret = trim($_POST['c2FAsecret']);
        }

        $validUntil = Request::postInt('dGueltigBisAktiv') === 1;
        if ($validUntil) {
            try {
                $tmpAcc->dGueltigBis = new DateTime($_POST['dGueltigBis']);
            } catch (Exception $e) {
                $tmpAcc->dGueltigBis = '';
            }
            if ($tmpAcc->dGueltigBis !== false && $tmpAcc->dGueltigBis !== '') {
                $tmpAcc->dGueltigBis = $tmpAcc->dGueltigBis->format('Y-m-d H:i:s');
            }
        }
        $tmpAcc->kAdminlogingruppe = Request::postInt('kAdminlogingruppe');
        if ((bool)$tmpAcc->b2FAauth && !isset($tmpAcc->c2FAauthSecret)) {
            $errors['c2FAsecret'] = 1;
        }
        if (mb_strlen($tmpAcc->cName) === 0) {
            $errors['cName'] = 1;
        }
        if (mb_strlen($tmpAcc->cMail) === 0) {
            $errors['cMail'] = 1;
        }
        if (mb_strlen($tmpAcc->cPass) === 0 && $tmpAcc->kAdminlogin === 0) {
            $errors['cPass'] = 1;
        }
        if (mb_strlen($tmpAcc->cLogin) === 0) {
            $errors['cLogin'] = 1;
        } elseif ($tmpAcc->kAdminlogin === 0 && getInfoInUse('cLogin', $tmpAcc->cLogin)) {
            $errors['cLogin'] = 2;
        }
        if ($validUntil && $tmpAcc->kAdminlogingruppe !== ADMINGROUP && mb_strlen($tmpAcc->dGueltigBis) === 0) {
            $errors['dGueltigBis'] = 1;
        }
        if ($tmpAcc->kAdminlogin > 0) {
            $oldAcc     = getAdmin($tmpAcc->kAdminlogin);
            $groupCount = (int)$db->query(
                'SELECT COUNT(*) AS nCount
                    FROM tadminlogin
                    WHERE kAdminlogingruppe = 1',
                ReturnType::SINGLE_OBJECT
            )->nCount;
            if ($oldAcc !== null
                && (int)$oldAcc->kAdminlogingruppe === ADMINGROUP
                && (int)$tmpAcc->kAdminlogingruppe !== ADMINGROUP
                && $groupCount <= 1
            ) {
                $errors['bMinAdmin'] = 1;
            }
        }
        if (count($errors) > 0) {
            $smarty->assign('cError_arr', $errors);
            $messages['error'] .= __('errorFillRequired');
            if (isset($errors['bMinAdmin']) && (int)$errors['bMinAdmin'] === 1) {
                $messages['error'] .= __('errorAtLeastOneAdmin');
            }
        } elseif ($tmpAcc->kAdminlogin > 0) {
            if (!$validUntil) {
                $tmpAcc->dGueltigBis = '_DBNULL_';
            }
            // if we change the current admin-user, we have to update his session-credentials too!
            if ((int)$tmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin
                && $tmpAcc->cLogin !== $_SESSION['AdminAccount']->cLogin) {
                $_SESSION['AdminAccount']->cLogin = $tmpAcc->cLogin;
            }
            if (mb_strlen($tmpAcc->cPass) > 0) {
                $tmpAcc->cPass = Shop::Container()->getPasswordService()->hash($tmpAcc->cPass);
                // if we change the current admin-user, we have to update his session-credentials too!
                if ((int)$tmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
                    $_SESSION['AdminAccount']->cPass = $tmpAcc->cPass;
                }
            } else {
                unset($tmpAcc->cPass);
            }

            $_SESSION['AdminAccount']->language = $tmpAcc->language;

            if ($db->update('tadminlogin', 'kAdminlogin', $tmpAcc->kAdminlogin, $tmpAcc) >= 0
                && benutzerverwaltungSaveAttributes($tmpAcc, $tmpAttribs, $messages, $errors)
            ) {
                $result = true;
                executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $tmpAcc,
                    'type'     => 'SAVE',
                    'attribs'  => &$tmpAttribs,
                    'messages' => &$messages,
                    'result'   => &$result,
                ]);
                if ($result === true) {
                    $messages['notice'] .= __('successUserSave');

                    return 'index_redirect';
                }
                $smarty->assign('cError_arr', array_merge($errors, (array)$result));
            } else {
                $messages['error'] .= __('errorUserSave');
                $smarty->assign('cError_arr', $errors);
            }
        } else {
            unset($tmpAcc->kAdminlogin);
            $tmpAcc->bAktiv        = 1;
            $tmpAcc->nLoginVersuch = 0;
            $tmpAcc->dLetzterLogin = '_DBNULL_';
            if (!isset($tmpAcc->dGueltigBis) || mb_strlen($tmpAcc->dGueltigBis) === 0) {
                $tmpAcc->dGueltigBis = '_DBNULL_';
            }
            $tmpAcc->cPass = Shop::Container()->getPasswordService()->hash($tmpAcc->cPass);

            if (($tmpAcc->kAdminlogin = $db->insert('tadminlogin', $tmpAcc))
                && benutzerverwaltungSaveAttributes($tmpAcc, $tmpAttribs, $messages, $errors)
            ) {
                $result = true;
                executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $tmpAcc,
                    'type'     => 'SAVE',
                    'attribs'  => &$tmpAttribs,
                    'messages' => &$messages,
                    'result'   => &$result,
                ]);
                if ($result === true) {
                    $messages['notice'] .= __('successUserAdd');

                    return 'index_redirect';
                }
                $smarty->assign('cError_arr', array_merge($errors, (array)$result));
            } else {
                $messages['error'] .= __('errorUserAdd');
                $smarty->assign('cError_arr', $errors);
            }
        }

        $account    = &$tmpAcc;
        $extAttribs = [];
        foreach ($tmpAttribs as $key => $attrib) {
            $extAttribs[$key] = (object)[
                'kAttribut'    => null,
                'cName'        => $key,
                'cAttribValue' => $attrib,
            ];
        }
        if ((int)$account->kAdminlogingruppe === 1) {
            unset($account->kAdminlogingruppe);
        }
    } elseif ($adminID > 0) {
        $account    = getAdmin($adminID);
        $extAttribs = benutzerverwaltungGetAttributes($adminID);
    } else {
        $account    = new stdClass();
        $extAttribs = [];
    }

    $smarty->assign('attribValues', $extAttribs);

    $extContent = '';
    executeHook(HOOK_BACKEND_ACCOUNT_PREPARE_EDIT, [
        'oAccount' => $account,
        'smarty'   => $smarty,
        'attribs'  => $extAttribs,
        'content'  => &$extContent,
    ]);

    $groupCount = (int)$db->query(
        'SELECT COUNT(*) AS nCount
            FROM tadminlogin
            WHERE kAdminlogingruppe = 1',
        ReturnType::SINGLE_OBJECT
    )->nCount;
    $smarty->assign('oAccount', $account)
        ->assign('nAdminCount', $groupCount)
        ->assign('extContent', $extContent);

    return 'account_edit';
}

/**
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountDelete(array &$messages): string
{
    $adminID    = Request::postInt('id');
    $groupCount = (int)Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nCount
            FROM tadminlogin
            WHERE kAdminlogingruppe = 1',
        ReturnType::SINGLE_OBJECT
    )->nCount;
    $account    = Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $adminID);

    if (isset($account->kAdminlogin) && (int)$account->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
        $messages['error'] .= __('errorSelfDelete');
    } elseif (is_object($account)) {
        if ((int)$account->kAdminlogingruppe === ADMINGROUP && $groupCount <= 1) {
            $messages['error'] .= __('errorAtLeastOneAdmin');
        } elseif (benutzerverwaltungDeleteAttributes($account) &&
            Shop::Container()->getDB()->delete('tadminlogin', 'kAdminlogin', $adminID)) {
            $result = true;
            executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $account,
                'type'     => 'DELETE',
                'attribs'  => null,
                'messages' => &$messages,
                'result'   => &$result,
            ]);
            if ($result === true) {
                $messages['notice'] .= __('successUserDelete');
            }
        } else {
            $messages['error'] .= __('errorUserDelete');
        }
    } else {
        $messages['error'] .= __('errorUserNotFound');
    }

    return 'index_redirect';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 */
function benutzerverwaltungActionGroupEdit(JTLSmarty $smarty, array &$messages): string
{
    $db      = Shop::Container()->getDB();
    $debug   = isset($_POST['debug']);
    $groupID = Request::postInt('id', null);
    if (isset($_POST['save'])) {
        $errors                        = [];
        $adminGroup                    = new stdClass();
        $adminGroup->kAdminlogingruppe = Request::postInt('kAdminlogingruppe');
        $adminGroup->cGruppe           = htmlspecialchars(
            trim($_POST['cGruppe']),
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );
        $adminGroup->cBeschreibung     = htmlspecialchars(
            trim($_POST['cBeschreibung']),
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );
        $groupPermissions              = $_POST['perm'];

        if (mb_strlen($adminGroup->cGruppe) === 0) {
            $errors['cGruppe'] = 1;
        }
        if (mb_strlen($adminGroup->cBeschreibung) === 0) {
            $errors['cBeschreibung'] = 1;
        }
        if (count($groupPermissions) === 0) {
            $errors['cPerm'] = 1;
        }
        if (count($errors) > 0) {
            $smarty->assign('cError_arr', $errors)
                ->assign('oAdminGroup', $adminGroup)
                ->assign('cAdminGroupPermission_arr', $groupPermissions);

            if (isset($errors['cPerm'])) {
                $messages['error'] .= __('errorAtLeastOneRight');
            } else {
                $messages['error'] .= __('errorFillRequired');
            }
        } else {
            if ($adminGroup->kAdminlogingruppe > 0) {
                $db->update(
                    'tadminlogingruppe',
                    'kAdminlogingruppe',
                    (int)$adminGroup->kAdminlogingruppe,
                    $adminGroup
                );
                $db->delete(
                    'tadminrechtegruppe',
                    'kAdminlogingruppe',
                    (int)$adminGroup->kAdminlogingruppe
                );
                $permission                    = new stdClass();
                $permission->kAdminlogingruppe = (int)$adminGroup->kAdminlogingruppe;
                foreach ($groupPermissions as $oAdminGroupPermission) {
                    $permission->cRecht = $oAdminGroupPermission;
                    $db->insert('tadminrechtegruppe', $permission);
                }
                $messages['notice'] .= __('successGroupEdit');

                return 'group_redirect';
            }
            unset($adminGroup->kAdminlogingruppe);
            $groupID = $db->insert('tadminlogingruppe', $adminGroup);
            $db->delete('tadminrechtegruppe', 'kAdminlogingruppe', $groupID);
            $permission                    = new stdClass();
            $permission->kAdminlogingruppe = $groupID;
            foreach ($groupPermissions as $oAdminGroupPermission) {
                $permission->cRecht = $oAdminGroupPermission;
                $db->insert('tadminrechtegruppe', $permission);
            }
            $messages['notice'] .= __('successGroupCreate');

            return 'group_redirect';
        }
    } elseif ($groupID > 0) {
        if ((int)$groupID === 1) {
            header('location: benutzerverwaltung.php?action=group_view&token=' . $_SESSION['jtl_token']);
        }
        $smarty->assign('bDebug', $debug)
            ->assign('oAdminGroup', getAdminGroup($groupID))
            ->assign('cAdminGroupPermission_arr', getAdminGroupPermissions($groupID));
    }

    return 'group_edit';
}

/**
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionGroupDelete(array &$messages): string
{
    $groupID = Request::postInt('id');
    $data    = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS member_count
            FROM tadminlogin
            WHERE kAdminlogingruppe = ' . $groupID,
        ReturnType::SINGLE_OBJECT
    );
    if ((int)$data->member_count !== 0) {
        $messages['error'] .= __('errorGroupDeleteCustomer');

        return 'group_redirect';
    }

    if ($groupID !== ADMINGROUP) {
        Shop::Container()->getDB()->delete('tadminlogingruppe', 'kAdminlogingruppe', $groupID);
        Shop::Container()->getDB()->delete('tadminrechtegruppe', 'kAdminlogingruppe', $groupID);
        $messages['notice'] .= __('successGroupDelete');
    } else {
        $messages['error'] .= __('errorGroupDelete');
    }

    return 'group_redirect';
}

/**
 *
 */
function benutzerverwaltungActionQuickChangeLanguage()
{
    $language = Request::verifyGPDataString('language');
    $referer  = Request::verifyGPDataString('referer');
    changeAdminUserLanguage($language);
    header('Location: ' . $referer);
}

/**
 * @param string     $tab
 * @param array|null $messages
 */
function benutzerverwaltungRedirect($tab = '', array &$messages = null)
{
    if (isset($messages['notice']) && !empty($messages['notice'])) {
        $_SESSION['benutzerverwaltung.notice'] = $messages['notice'];
    } else {
        unset($_SESSION['benutzerverwaltung.notice']);
    }
    if (isset($messages['error']) && !empty($messages['error'])) {
        $_SESSION['benutzerverwaltung.error'] = $messages['error'];
    } else {
        unset($_SESSION['benutzerverwaltung.error']);
    }

    $urlParams = null;
    if (!empty($tab)) {
        $urlParams = ['tab' => Text::filterXSS($tab)];
    }

    header('Location: benutzerverwaltung.php' . (is_array($urlParams)
            ? '?' . http_build_query($urlParams, '', '&')
            : ''));
    exit;
}

/**
 * @param string    $step
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @throws SmartyException
 */
function benutzerverwaltungFinalize($step, JTLSmarty $smarty, array &$messages)
{
    if (isset($_SESSION['benutzerverwaltung.notice'])) {
        $messages['notice'] = $_SESSION['benutzerverwaltung.notice'];
        unset($_SESSION['benutzerverwaltung.notice']);
    }
    if (isset($_SESSION['benutzerverwaltung.error'])) {
        $messages['error'] = $_SESSION['benutzerverwaltung.error'];
        unset($_SESSION['benutzerverwaltung.error']);
    }

    switch ($step) {
        case 'account_edit':
            $smarty->assign('oAdminGroup_arr', getAdminGroups())
                ->assign(
                    'languages',
                    Shop::Container()->getGetText()->getAdminLanguages()
                );
            break;
        case 'account_view':
            $smarty->assign('oAdminList_arr', getAdminList())
                ->assign('oAdminGroup_arr', getAdminGroups());
            break;
        case 'group_edit':
            $smarty->assign('oAdminDefPermission_arr', getAdminDefPermissions());
            break;
        case 'index_redirect':
            benutzerverwaltungRedirect('account_view', $messages);
            break;
        case 'group_redirect':
            benutzerverwaltungRedirect('group_view', $messages);
            break;
    }

    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_NOTE, $messages['notice'], 'userManagementNote');
    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, $messages['error'], 'userManagementError');

    $smarty->assign('action', $step)
        ->assign('cTab', Text::filterXSS(Request::verifyGPDataString('tab')))
        ->display('benutzer.tpl');
}

/**
 * @return IOResponse
 * @throws Exception
 */
function getRandomPasswordIO(): IOResponse
{
    $response = new IOResponse();
    $password = Shop::Container()->getPasswordService()->generate(PASSWORD_DEFAULT_LENGTH);
    $response->assign('cPass', 'value', $password);

    return $response;
}
