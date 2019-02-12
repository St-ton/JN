<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Backend\TwoFA;
use Helpers\Request;

/**
 * @param int $kAdminlogin
 * @return null|stdClass
 */
function getAdmin(int $kAdminlogin)
{
    return Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);
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
        \DB\ReturnType::ARRAY_OF_OBJECTS
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
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @return array
 */
function getAdminDefPermissions(): array
{
    $groups = Shop::Container()->getDB()->selectAll('tadminrechtemodul', [], [], '*', 'nSort ASC');
    $perms  = \Functional\group(Shop::Container()->getDB()->selectAll('tadminrecht', [], []), function ($e) {
        return $e->kAdminrechtemodul;
    });
    foreach ($groups as $group) {
        $group->kAdminrechtemodul = (int)$group->kAdminrechtemodul;
        $group->nSort             = (int)$group->nSort;
        $group->oPermission_arr   = $perms[$group->kAdminrechtemodul] ?? [];
    }

    return $groups;
}

/**
 * @param int $kAdminlogingruppe
 * @return null|stdClass
 */
function getAdminGroup(int $kAdminlogingruppe)
{
    return Shop::Container()->getDB()->select('tadminlogingruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
}

/**
 * @param int $groupID
 * @return array
 */
function getAdminGroupPermissions(int $groupID)
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
function getInfoInUse($row, $value)
{
    return is_object(Shop::Container()->getDB()->select('tadminlogin', $row, $value));
}

/**
 * @param int $langID
 */
function changeAdminUserLanguage(int $langID)
{
    $_SESSION['AdminAccount']->kSprache = $langID;
    $_SESSION['AdminAccount']->cISO     = Shop::Lang()->getIsoFromLangID($langID)->cISO;

    Shop::Container()->getDB()->update(
        'tadminlogin',
        'kAdminlogin',
        $langID,
        (object)['kSprache' => $langID]
    );
}

/**
 * @param int $kAdminlogin
 * @return array
 */
function benutzerverwaltungGetAttributes(int $kAdminlogin)
{
    $extAttribs = Shop::Container()->getDB()->selectAll(
        'tadminloginattribut',
        'kAdminlogin',
        $kAdminlogin,
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
            $key      = StringHandler::filterXSS($key);
            $longText = null;
            if (is_array($value) && count($value) > 0) {
                $shortText = StringHandler::filterXSS($value[0]);
                if (count($value) > 1) {
                    $longText = $value[1];
                }
            } else {
                $shortText = StringHandler::filterXSS($value);
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
                \DB\ReturnType::DEFAULT
            ) === 0) {
                $messages['error'] .= $key . __('errorKeyChange');
            }
            $handledKeys[] = $key;
        }
        // nicht (mehr) vorhandene Attribute löschen
        $db->query(
            'DELETE FROM tadminloginattribut
                WHERE kAdminlogin = ' . (int)$account->kAdminlogin . "
                    AND cName NOT IN ('" . implode("', '", $handledKeys) . "')",
            \DB\ReturnType::DEFAULT
        );
    }

    return true;
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
function benutzerverwaltungActionAccountLock(array &$messages)
{
    $kAdminlogin = (int)$_POST['id'];
    $account     = Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);
    if (!empty($account->kAdminlogin) && (int)$account->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
        $messages['error'] .= __('errorSelfLock');
    } elseif (is_object($account)) {
        if ((int)$account->kAdminlogingruppe === ADMINGROUP) {
            $messages['error'] .= __('errorLockAdmin');
        } else {
            $result = true;
            Shop::Container()->getDB()->update('tadminlogin', 'kAdminlogin', $kAdminlogin, (object)['bAktiv' => 0]);
            executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $account,
                'type'     => 'LOCK',
                'attribs'  => null,
                'messages' => &$messages,
                'result'   => &$result,
            ]);
            if (true === $result) {
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
function benutzerverwaltungActionAccountUnLock(array &$messages)
{
    $kAdminlogin = (int)$_POST['id'];
    $account     = Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);
    if (is_object($account)) {
        $result = true;
        Shop::Container()->getDB()->update('tadminlogin', 'kAdminlogin', $kAdminlogin, (object)['bAktiv' => 1]);
        executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
            'oAccount' => $account,
            'type'     => 'UNLOCK',
            'attribs'  => null,
            'messages' => &$messages,
            'result'   => &$result,
        ]);
        if (true === $result) {
            $messages['notice'] .= __('successUnlocked');
        }
    } else {
        $messages['error'] .= __('errorUserNotFound');
    }

    return 'index_redirect';
}

/**
 * @param \Smarty\JTLSmarty $smarty
 * @param array             $messages
 * @return string
 */
function benutzerverwaltungActionAccountEdit(\Smarty\JTLSmarty $smarty, array &$messages)
{
    $_SESSION['AdminAccount']->TwoFA_valid = true;

    $db             = Shop::Container()->getDB();
    $kAdminlogin    = (isset($_POST['id']) ? (int)$_POST['id'] : null);
    $szQRcodeString = '';
    $szKnownSecret  = '';
    if (null !== $kAdminlogin) {
        $oTwoFA = new TwoFA($db);
        $oTwoFA->setUserByID($_POST['id']);

        if (true === $oTwoFA->is2FAauthSecretExist()) {
            $szQRcodeString = $oTwoFA->getQRcode();
            $szKnownSecret  = $oTwoFA->getSecret();
        }
    }
    $smarty->assign('QRcodeString', $szQRcodeString)
           ->assign('cKnownSecret', $szKnownSecret);

    if (isset($_POST['save'])) {
        $errors               = [];
        $oTmpAcc              = new stdClass();
        $oTmpAcc->kAdminlogin = isset($_POST['kAdminlogin']) ? (int)$_POST['kAdminlogin'] : 0;
        $oTmpAcc->cName       = htmlspecialchars(trim($_POST['cName']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $oTmpAcc->cMail       = htmlspecialchars(trim($_POST['cMail']), ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $oTmpAcc->kSprache    = (int)$_POST['kSprache'];
        $oTmpAcc->cLogin      = trim($_POST['cLogin']);
        $oTmpAcc->cPass       = trim($_POST['cPass']);
        $oTmpAcc->b2FAauth    = (int)$_POST['b2FAauth'];
        $tmpAttribs           = $_POST['extAttribs'] ?? [];

        if (0 < mb_strlen($_POST['c2FAsecret'])) {
            $oTmpAcc->c2FAauthSecret = trim($_POST['c2FAsecret']);
        }

        $dGueltigBisAktiv = (isset($_POST['dGueltigBisAktiv']) && ($_POST['dGueltigBisAktiv'] === '1'));
        if ($dGueltigBisAktiv) {
            try {
                $oTmpAcc->dGueltigBis = new DateTime($_POST['dGueltigBis']);
            } catch (Exception $e) {
                $oTmpAcc->dGueltigBis = '';
            }
            if ($oTmpAcc->dGueltigBis !== false && $oTmpAcc->dGueltigBis !== '') {
                $oTmpAcc->dGueltigBis = $oTmpAcc->dGueltigBis->format('Y-m-d H:i:s');
            }
        }
        $oTmpAcc->kAdminlogingruppe = (int)$_POST['kAdminlogingruppe'];

        if ((bool)$oTmpAcc->b2FAauth && !isset($oTmpAcc->c2FAauthSecret)) {
            $errors['c2FAsecret'] = 1;
        }
        if (mb_strlen($oTmpAcc->cName) === 0) {
            $errors['cName'] = 1;
        }
        if (mb_strlen($oTmpAcc->cMail) === 0) {
            $errors['cMail'] = 1;
        }
        if (mb_strlen($oTmpAcc->cPass) === 0 && $oTmpAcc->kAdminlogin === 0) {
            $errors['cPass'] = 1;
        }
        if (mb_strlen($oTmpAcc->cLogin) === 0) {
            $errors['cLogin'] = 1;
        } elseif ($oTmpAcc->kAdminlogin === 0 && getInfoInUse('cLogin', $oTmpAcc->cLogin)) {
            $errors['cLogin'] = 2;
        }
        if ($dGueltigBisAktiv && $oTmpAcc->kAdminlogingruppe !== ADMINGROUP && mb_strlen($oTmpAcc->dGueltigBis) === 0) {
            $errors['dGueltigBis'] = 1;
        }
        if ($oTmpAcc->kAdminlogin > 0) {
            $oOldAcc = getAdmin($oTmpAcc->kAdminlogin);
            $oCount  = $db->query(
                'SELECT COUNT(*) AS nCount
                    FROM tadminlogin
                    WHERE kAdminlogingruppe = 1',
                \DB\ReturnType::SINGLE_OBJECT
            );
            if ((int)$oOldAcc->kAdminlogingruppe === ADMINGROUP
                && (int)$oTmpAcc->kAdminlogingruppe !== ADMINGROUP
                && $oCount->nCount <= 1
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
        } elseif ($oTmpAcc->kAdminlogin > 0) {
            if (!$dGueltigBisAktiv) {
                $oTmpAcc->dGueltigBis = '_DBNULL_';
            }
            // if we change the current admin-user, we have to update his session-credentials too!
            if ((int)$oTmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin
                && $oTmpAcc->cLogin !== $_SESSION['AdminAccount']->cLogin) {
                $_SESSION['AdminAccount']->cLogin = $oTmpAcc->cLogin;
            }
            if (mb_strlen($oTmpAcc->cPass) > 0) {
                $oTmpAcc->cPass = Shop::Container()->getPasswordService()->hash($oTmpAcc->cPass);
                // if we change the current admin-user, we have to update his session-credentials too!
                if ((int)$oTmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
                    $_SESSION['AdminAccount']->cPass = $oTmpAcc->cPass;
                }
            } else {
                unset($oTmpAcc->cPass);
            }

            $_SESSION['AdminAccount']->kSprache = $oTmpAcc->kSprache;

            if ($db->update('tadminlogin', 'kAdminlogin', $oTmpAcc->kAdminlogin, $oTmpAcc) >= 0
                && benutzerverwaltungSaveAttributes($oTmpAcc, $tmpAttribs, $messages, $errors)
            ) {
                $result = true;
                executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $oTmpAcc,
                    'type'     => 'SAVE',
                    'attribs'  => &$tmpAttribs,
                    'messages' => &$messages,
                    'result'   => &$result,
                ]);
                if (true === $result) {
                    $messages['notice'] .= __('successUserSave');

                    return 'index_redirect';
                }
                $smarty->assign('cError_arr', array_merge($errors, (array)$result));
            } else {
                $messages['error'] .= __('errorUserSave');
                $smarty->assign('cError_arr', $errors);
            }
        } else {
            unset($oTmpAcc->kAdminlogin);
            $oTmpAcc->bAktiv        = 1;
            $oTmpAcc->nLoginVersuch = 0;
            $oTmpAcc->dLetzterLogin = '_DBNULL_';
            if (!isset($oTmpAcc->dGueltigBis) || mb_strlen($oTmpAcc->dGueltigBis) === 0) {
                $oTmpAcc->dGueltigBis = '_DBNULL_';
            }
            $oTmpAcc->cPass = Shop::Container()->getPasswordService()->hash($oTmpAcc->cPass);

            if (($oTmpAcc->kAdminlogin = $db->insert('tadminlogin', $oTmpAcc))
                && benutzerverwaltungSaveAttributes($oTmpAcc, $tmpAttribs, $messages, $errors)
            ) {
                $result = true;
                executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $oTmpAcc,
                    'type'     => 'SAVE',
                    'attribs'  => &$tmpAttribs,
                    'messages' => &$messages,
                    'result'   => &$result,
                ]);
                if (true === $result) {
                    $messages['notice'] .= __('successUserAdd');

                    return 'index_redirect';
                }
                $smarty->assign('cError_arr', array_merge($errors, (array)$result));
            } else {
                $messages['error'] .= __('errorUserAdd');
                $smarty->assign('cError_arr', $errors);
            }
        }

        $oAccount   = &$oTmpAcc;
        $extAttribs = [];
        foreach ($tmpAttribs as $key => $attrib) {
            $extAttribs[$key] = (object)[
                'kAttribut'    => null,
                'cName'        => $key,
                'cAttribValue' => $attrib,
            ];
        }
        if ((int)$oAccount->kAdminlogingruppe === 1) {
            unset($oAccount->kAdminlogingruppe);
        }
    } elseif ($kAdminlogin > 0) {
        $oAccount   = getAdmin($kAdminlogin);
        $extAttribs = benutzerverwaltungGetAttributes($kAdminlogin);
    } else {
        $oAccount   = new stdClass();
        $extAttribs = [];
    }

    $extContent = '';
    executeHook(HOOK_BACKEND_ACCOUNT_PREPARE_EDIT, [
        'oAccount' => $oAccount,
        'smarty'   => $smarty,
        'attribs'  => $extAttribs,
        'content'  => &$extContent,
    ]);

    $oCount = $db->query(
        'SELECT COUNT(*) AS nCount
            FROM tadminlogin
            WHERE kAdminlogingruppe = 1',
        \DB\ReturnType::SINGLE_OBJECT
    );
    $smarty->assign('oAccount', $oAccount)
           ->assign('nAdminCount', $oCount->nCount)
           ->assign('extContent', $extContent);

    return 'account_edit';
}

/**
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionAccountDelete(array &$messages)
{
    $kAdminlogin = (int)$_POST['id'];
    $oCount      = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS nCount
            FROM tadminlogin
            WHERE kAdminlogingruppe = 1',
        \DB\ReturnType::SINGLE_OBJECT
    );
    $oAccount    = Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);

    if (isset($oAccount->kAdminlogin) && (int)$oAccount->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
        $messages['error'] .= __('errorSelfDelete');
    } elseif (is_object($oAccount)) {
        if ((int)$oAccount->kAdminlogingruppe === ADMINGROUP && $oCount->nCount <= 1) {
            $messages['error'] .= __('errorAtLeastOneAdmin');
        } elseif (benutzerverwaltungDeleteAttributes($oAccount) &&
            Shop::Container()->getDB()->delete('tadminlogin', 'kAdminlogin', $kAdminlogin)) {
            $result = true;
            executeHook(HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $oAccount,
                'type'     => 'DELETE',
                'attribs'  => null,
                'messages' => &$messages,
                'result'   => &$result,
            ]);
            if (true === $result) {
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
 * @param \Smarty\JTLSmarty $smarty
 * @param array            $messages
 * @return string
 */
function benutzerverwaltungActionGroupEdit(\Smarty\JTLSmarty $smarty, array &$messages)
{
    $db                = Shop::Container()->getDB();
    $bDebug            = isset($_POST['debug']);
    $kAdminlogingruppe = isset($_POST['id'])
        ? (int)$_POST['id']
        : null;
    if (isset($_POST['save'])) {
        $cError_arr                     = [];
        $oAdminGroup                    = new stdClass();
        $oAdminGroup->kAdminlogingruppe = isset($_POST['kAdminlogingruppe'])
            ? (int)$_POST['kAdminlogingruppe']
            : 0;
        $oAdminGroup->cGruppe           = htmlspecialchars(
            trim($_POST['cGruppe']),
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );
        $oAdminGroup->cBeschreibung     = htmlspecialchars(
            trim($_POST['cBeschreibung']),
            ENT_COMPAT | ENT_HTML401,
            JTL_CHARSET
        );
        $oAdminGroupPermission_arr      = $_POST['perm'];

        if (mb_strlen($oAdminGroup->cGruppe) === 0) {
            $cError_arr['cGruppe'] = 1;
        }
        if (mb_strlen($oAdminGroup->cBeschreibung) === 0) {
            $cError_arr['cBeschreibung'] = 1;
        }
        if (count($oAdminGroupPermission_arr) === 0) {
            $cError_arr['cPerm'] = 1;
        }
        if (count($cError_arr) > 0) {
            $smarty->assign('cError_arr', $cError_arr)
                   ->assign('oAdminGroup', $oAdminGroup)
                   ->assign('cAdminGroupPermission_arr', $oAdminGroupPermission_arr);

            if (isset($cError_arr['cPerm'])) {
                $messages['error'] .= __('errorAtLeastOneRight');
            } else {
                $messages['error'] .= __('errorFillRequired');
            }
        } else {
            if ($oAdminGroup->kAdminlogingruppe > 0) {
                $db->update(
                    'tadminlogingruppe',
                    'kAdminlogingruppe',
                    (int)$oAdminGroup->kAdminlogingruppe,
                    $oAdminGroup
                );
                $db->delete(
                    'tadminrechtegruppe',
                    'kAdminlogingruppe',
                    (int)$oAdminGroup->kAdminlogingruppe
                );
                $oPerm                    = new stdClass();
                $oPerm->kAdminlogingruppe = (int)$oAdminGroup->kAdminlogingruppe;
                foreach ($oAdminGroupPermission_arr as $oAdminGroupPermission) {
                    $oPerm->cRecht = $oAdminGroupPermission;
                    $db->insert('tadminrechtegruppe', $oPerm);
                }
                $messages['notice'] .= __('successGroupEdit');

                return 'group_redirect';
            }
            unset($oAdminGroup->kAdminlogingruppe);
            $kAdminlogingruppe = $db->insert('tadminlogingruppe', $oAdminGroup);
            $db->delete('tadminrechtegruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
            $oPerm                    = new stdClass();
            $oPerm->kAdminlogingruppe = $kAdminlogingruppe;
            foreach ($oAdminGroupPermission_arr as $oAdminGroupPermission) {
                $oPerm->cRecht = $oAdminGroupPermission;
                $db->insert('tadminrechtegruppe', $oPerm);
            }
            $messages['notice'] .= __('successGroupCreate');

            return 'group_redirect';
        }
    } elseif ($kAdminlogingruppe > 0) {
        if ((int)$kAdminlogingruppe === 1) {
            header('location: benutzerverwaltung.php?action=group_view&token=' . $_SESSION['jtl_token']);
        }
        $smarty->assign('bDebug', $bDebug)
               ->assign('oAdminGroup', getAdminGroup($kAdminlogingruppe))
               ->assign('cAdminGroupPermission_arr', getAdminGroupPermissions($kAdminlogingruppe));
    }

    return 'group_edit';
}

/**
 * @param array $messages
 * @return string
 */
function benutzerverwaltungActionGroupDelete(array &$messages)
{
    $kAdminlogingruppe = (int)$_POST['id'];
    $oResult           = Shop::Container()->getDB()->query(
        'SELECT COUNT(*) AS member_count
            FROM tadminlogin
            WHERE kAdminlogingruppe = ' . $kAdminlogingruppe,
        \DB\ReturnType::SINGLE_OBJECT
    );
    if ((int)$oResult->member_count !== 0) {
        $messages['error'] .= __('errorGroupDeleteCustomer');

        return 'group_redirect';
    }

    if ($kAdminlogingruppe !== ADMINGROUP) {
        Shop::Container()->getDB()->delete('tadminlogingruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
        Shop::Container()->getDB()->delete('tadminrechtegruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
        $messages['notice'] .= __('successGroupDelete');
    } else {
        $messages['error'] .= __('errorGroupDelete');
    }

    return 'group_redirect';
}

/**
 * @param \Smarty\JTLSmarty $smarty
 * @param array             $messages
 */
function benutzerverwaltungActionQuickChangeLanguage(\Smarty\JTLSmarty $smarty, array &$messages)
{
    $kSprache = Request::verifyGPCDataInt('kSprache');
    changeAdminUserLanguage($kSprache);

    header('Location: ' . $_SERVER['HTTP_REFERER']);
}

/**
 * @param string     $cTab
 * @param array|null $messages
 */
function benutzerverwaltungRedirect($cTab = '', array &$messages = null)
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
    if (!empty($cTab)) {
        $urlParams = ['tab' => StringHandler::filterXSS($cTab)];
    }

    header('Location: benutzerverwaltung.php' . (is_array($urlParams)
            ? '?' . http_build_query($urlParams, '', '&')
            : ''));
    exit;
}

/**
 * @param string            $step
 * @param \Smarty\JTLSmarty $smarty
 * @param array             $messages
 * @throws SmartyException
 */
function benutzerverwaltungFinalize($step, \Smarty\JTLSmarty $smarty, array &$messages)
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
                   ->assign('languages', Shop::Lang()->getInstalled());
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
           ->assign('cTab', StringHandler::filterXSS(Request::verifyGPDataString('tab')))
           ->display('benutzer.tpl');
}

/**
 * @return IOResponse
 * @throws Exception
 */
function getRandomPasswordIO()
{
    $response = new IOResponse();
    $password = Shop::Container()->getPasswordService()->generate(PASSWORD_DEFAULT_LENGTH);
    $response->assign('cPass', 'value', $password);

    return $response;
}
