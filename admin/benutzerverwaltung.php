<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ACCOUNT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
$cAction = 'account_view';
if (isset($_REQUEST['action'])) {
    $cAction = $_REQUEST['action'];
    if (!validateToken()) {
        $cAction = 'account_view';
    }
}

switch ($cAction) {
    case 'account_lock':
        $kAdminlogin = (int)$_POST['id'];
        $oAccount    = Shop::DB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);

        if (!empty($oAccount->kAdminlogin) && $oAccount->kAdminlogin == $_SESSION['AdminAccount']->kAdminlogin) {
            $cFehler = 'Sie k&ouml;nnen sich nicht selbst sperren.';
        } elseif (is_object($oAccount)) {
            if ($oAccount->kAdminlogingruppe == ADMINGROUP) {
                $cFehler = 'Administratoren k&ouml;nnen nicht gesperrt werden.';
            } else {
                Shop::DB()->query("UPDATE tadminlogin SET bAktiv = 0 WHERE kAdminlogin = " . $kAdminlogin, 4);
                $cHinweis = 'Benutzer wurde erfolgreich gesperrt.';
            }
        } else {
            $cFehler = 'Benutzer wurde nicht gefunden.';
        }
        $cAction = 'account_view';
        break;

    case 'account_unlock':
        $kAdminlogin = (int)$_POST['id'];
        Shop::DB()->query("UPDATE tadminlogin SET bAktiv = '1' WHERE kAdminlogin = " . $kAdminlogin, 4);
        $cHinweis = 'Benutzer wurde erfolgreich entsperrt.';
        $cAction  = 'account_view';
        break;

    case 'account_delete':
        $kAdminlogin = (int)$_POST['id'];
        $oCount      = Shop::DB()->query("SELECT COUNT(*) AS nCount FROM tadminlogin WHERE kAdminlogingruppe = 1", 1);
        $oAccount    = Shop::DB()->select('tadminlogin', 'kAdminlogin', $kAdminlogin);
        if (isset($oAccount->kAdminlogin) && $oAccount->kAdminlogin == $_SESSION['AdminAccount']->kAdminlogin) {
            $cFehler = 'Sie k&ouml;nnen sich nicht selbst l&ouml;schen';
        } elseif (is_object($oAccount)) {
            if ($oAccount->kAdminlogingruppe == ADMINGROUP && $oCount->nCount <= 1) {
                $cFehler = 'Es muss mindestens ein Administrator im System vorhanden sein.';
            } else {
                Shop::DB()->delete('tadminlogin', 'kAdminlogin', $kAdminlogin);
                $cHinweis = 'Benutzer wurden erfolgreich gel&ouml;scht.';
            }
        } else {
            $cFehler = 'Benutzer wurde nicht gefunden.';
        }
        $cAction = 'account_view';
        break;

    case 'account_edit':
        $kAdminlogin = (isset($_POST['id']) ? (int)$_POST['id'] : null);
        if (isset($_POST['save'])) {
            $cError_arr           = array();
            $oTmpAcc              = new stdClass();
            $oTmpAcc->kAdminlogin = (isset($_POST['kAdminlogin'])) ? (int)$_POST['kAdminlogin'] : 0;
            $oTmpAcc->cName       = trim($_POST['cName']);
            $oTmpAcc->cMail       = trim($_POST['cMail']);
            $oTmpAcc->cLogin      = trim($_POST['cLogin']);
            $oTmpAcc->cPass       = trim($_POST['cPass']);

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

            if (strlen($oTmpAcc->cName) === 0) {
                $cError_arr['cName'] = 1;
            }
            if (strlen($oTmpAcc->cMail) === 0) {
                $cError_arr['cMail'] = 1;
            }
            if (strlen($oTmpAcc->cPass) === 0 && $oTmpAcc->kAdminlogin == 0) {
                $cError_arr['cPass'] = 1;
            }
            if (strlen($oTmpAcc->cLogin) === 0) {
                $cError_arr['cLogin'] = 1;
            } elseif ($oTmpAcc->kAdminlogin == 0 && getInfoInUse('cLogin', $oTmpAcc->cLogin)) {
                $cError_arr['cLogin'] = 2;
            }
            if ($dGueltigBisAktiv && $oTmpAcc->kAdminlogingruppe != ADMINGROUP) {
                if (strlen($oTmpAcc->dGueltigBis) === 0) {
                    $cError_arr['dGueltigBis'] = 1;
                }
            }
            if ($oTmpAcc->kAdminlogin > 0) {
                $oOldAcc = getAdmin($oTmpAcc->kAdminlogin);
                $oCount  = Shop::DB()->query("SELECT COUNT(*) AS nCount FROM tadminlogin WHERE kAdminlogingruppe = 1", 1);
                if ($oOldAcc->kAdminlogingruppe == ADMINGROUP && $oTmpAcc->kAdminlogingruppe != ADMINGROUP && $oCount->nCount <= 1) {
                    $cError_arr['bMinAdmin'] = 1;
                }
            }
            if (count($cError_arr) > 0) {
                $smarty->assign('oAccount', $oTmpAcc)
                       ->assign('cError_arr', $cError_arr);
                $cFehler = 'Bitte alle Pflichtfelder ausf&uuml;llen.';
                if (isset($cError_arr['bMinAdmin']) && intval($cError_arr['bMinAdmin']) === 1) {
                    $cFehler = 'Es muss mindestens ein Administrator im System vorhanden sein.';
                }
            } else {
                if ($oTmpAcc->kAdminlogin > 0) {
                    if (!$dGueltigBisAktiv) {
                        $oTmpAcc->dGueltigBis = '_DBNULL_';
                    }
                    if (strlen($oTmpAcc->cPass) > 0) {
                        $oTmpAcc->cPass = AdminAccount::generatePasswordHash($oTmpAcc->cPass);
                    } else {
                        unset($oTmpAcc->cPass);
                    }
                    if (Shop::DB()->update('tadminlogin', 'kAdminlogin', $oTmpAcc->kAdminlogin, $oTmpAcc) >= 0) {
                        $cHinweis = 'Benutzer wurde erfolgreich bearbeitet.';
                    } else {
                        $cFehler = 'Benutzer konnte nicht bearbeitet werden.';
                    }
                    $cAction = 'account_view';
                } else {
                    unset($oTmpAcc->kAdminlogin);
                    $oTmpAcc->bAktiv        = 1;
                    $oTmpAcc->nLoginVersuch = 0;
                    $oTmpAcc->dLetzterLogin = '_DBNULL_';
                    if (!isset($oTmpAcc->dGueltigBis) || strlen($oTmpAcc->dGueltigBis) === 0) {
                        $oTmpAcc->dGueltigBis = '_DBNULL_';
                    }
                    $oTmpAcc->cPass = AdminAccount::generatePasswordHash($oTmpAcc->cPass);
                    if (Shop::DB()->insert('tadminlogin', $oTmpAcc)) {
                        $cHinweis = 'Benutzer wurde erfolgreich hinzugef&uuml;gt';
                    } else {
                        $cFehler = 'Benutzer konnte nicht angelegt werden.';
                    }
                    $cAction = 'account_view';
                }
            }
        } else {
            if ($kAdminlogin > 0) {
                $oCount = Shop::DB()->query("SELECT COUNT(*) AS nCount FROM tadminlogin WHERE kAdminlogingruppe = 1", 1);
                $smarty->assign('oAccount', getAdmin($kAdminlogin))
                       ->assign('nAdminCount', $oCount->nCount);
            }
        }
        break;

    case 'group_delete':
        $kAdminlogingruppe = (int)$_POST['id'];
        if ($kAdminlogingruppe !== ADMINGROUP) {
            Shop::DB()->delete('tadminlogingruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
            Shop::DB()->delete('tadminrechtegruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
            $cHinweis = 'Gruppe wurde erfolgreich gel&ouml;scht.';
        } else {
            $cFehler = 'Gruppe kann nicht entfernt werden.';
        }

        $cAction = 'group_view';
        break;

    case 'group_edit':
        $bDebug            = isset($_POST['debug']);
        $kAdminlogingruppe = (isset($_POST['id']) ? (int)$_POST['id'] : null);
        if (isset($_POST['save'])) {
            $cError_arr                     = array();
            $oAdminGroup                    = new stdClass();
            $oAdminGroup->kAdminlogingruppe = (isset($_POST['kAdminlogingruppe'])) ? (int)$_POST['kAdminlogingruppe'] : 0;
            $oAdminGroup->cGruppe           = trim($_POST['cGruppe']);
            $oAdminGroup->cBeschreibung     = trim($_POST['cBeschreibung']);
            $oAdminGroupPermission_arr      = $_POST['perm'];

            if (strlen($oAdminGroup->cGruppe) === 0) {
                $cError_arr['cGruppe'] = 1;
            }
            if (strlen($oAdminGroup->cBeschreibung) === 0) {
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
                    $cFehler = 'Mindestens eine Berechtigung ausw&auml;hlen.';
                } else {
                    $cFehler = 'Bitte alle Pflichtfelder ausf&uuml;llen.';
                }
            } else {
                if ($oAdminGroup->kAdminlogingruppe > 0) {
                    // update sql
                    Shop::DB()->update('tadminlogingruppe', 'kAdminlogingruppe', (int)$oAdminGroup->kAdminlogingruppe, $oAdminGroup);
                    // remove old perms
                    Shop::DB()->delete('tadminrechtegruppe', 'kAdminlogingruppe', (int)$oAdminGroup->kAdminlogingruppe);
                    // insert new perms
                    $oPerm                    = new stdClass();
                    $oPerm->kAdminlogingruppe = (int)$oAdminGroup->kAdminlogingruppe;
                    foreach ($oAdminGroupPermission_arr as $oAdminGroupPermission) {
                        $oPerm->cRecht = $oAdminGroupPermission;
                        Shop::DB()->insert('tadminrechtegruppe', $oPerm);
                    }
                    $cHinweis = 'Gruppe wurde erfolgreich bearbeitet.';
                } else {
                    // insert sql
                    unset($oAdminGroup->kAdminlogingruppe);
                    $kAdminlogingruppe = Shop::DB()->insert('tadminlogingruppe', $oAdminGroup);
                    // remove old perms
                    Shop::DB()->delete('tadminrechtegruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
                    // insert new perms
                    $oPerm                    = new stdClass();
                    $oPerm->kAdminlogingruppe = $kAdminlogingruppe;
                    foreach ($oAdminGroupPermission_arr as $oAdminGroupPermission) {
                        $oPerm->cRecht = $oAdminGroupPermission;
                        Shop::DB()->insert('tadminrechtegruppe', $oPerm);
                    }
                    $cHinweis = 'Gruppe wurde erfolgreich angelegt.';
                }
                $cAction = 'group_view';
            }
        } elseif ($kAdminlogingruppe > 0) {
            if ($kAdminlogingruppe == 1) {
                header('location: benutzerverwaltung.php?action=group_view&token=' . $_SESSION['jtl_token']);
            }
            $smarty->assign('bDebug', $bDebug)
                   ->assign('oAdminGroup', getAdminGroup($kAdminlogingruppe))
                   ->assign('cAdminGroupPermission_arr', getAdminGroupPermissions($kAdminlogingruppe));
        }
        $smarty->assign('oAdminDefPermission_arr', getAdminDefPermissions());
        break;
}

$smarty->assign('oAdminList_arr', getAdminList())
       ->assign('oAdminGroup_arr', getAdminGroups())
       ->assign('hinweis', (isset($cHinweis) ? $cHinweis : null))
       ->assign('fehler', (isset($cFehler) ? $cFehler : null))
       ->assign('action', $cAction)
       ->display('benutzer.tpl');
