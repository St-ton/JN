 {include file='tpl_inc/seite_header.tpl' cTitel=__('benutzer') cBeschreibung=__('benutzerDesc') cDokuURL=__('benutzerURL')}
<div id="content" class="container-fluid">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if empty($cTab) || $cTab === 'account_view'} active{/if}" data-toggle="tab" role="tab" href="#account_view">
                        {__('benutzerTab')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'group_view'} active{/if}" data-toggle="tab" role="tab" href="#group_view">
                        {__('gruppenTab')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="account_view" class="tab-pane fade{if empty($cTab) || $cTab === 'account_view'} active show{/if}">
                <div class="subheading1">{__('benutzerKategorie')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">#</th>
                            <th class="tcenter">{__('username')}</th>
                            <th class="tcenter">{__('benutzer2FA')}</th>
                            <th class="tcenter">{__('email')}</th>
                            <th class="tcenter">{__('group')}</th>
                            <th class="tcenter">{__('benutzerLoginVersuche')}</th>
                            <th class="tcenter">{__('benutzerLetzterLogin')}</th>
                            <th class="tcenter">{__('benutzerGueltigBis')}</th>
                            <th class="tcenter" width="135">{__('action')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oAdminList_arr as $oAdmin}
                            <tr>
                                <td class="tleft">{$oAdmin->kAdminlogin}</td>
                                <td class="tcenter">{$oAdmin->cLogin}</td>
                                <td class="tcenter">{if $oAdmin->b2FAauth}{__('stateON')}{else}{__('stateOFF')}{/if}</td>
                                <td class="tcenter">{$oAdmin->cMail}</td>
                                <td class="tcenter">
                                    {if $oAdmin->kAdminlogingruppe > 1}
                                        <form method="post" action="benutzerverwaltung.php">
                                            {$jtl_token}
                                            <input type="hidden" name="id" value="{$oAdmin->kAdminlogingruppe}" />
                                            <button type="submit" class="btn btn-default" name="action" value="group_edit">{$oAdmin->cGruppe}</button>
                                        </form>
                                    {else}
                                        {$oAdmin->cGruppe}
                                    {/if}
                                </td>
                                <td class="tcenter">{$oAdmin->nLoginVersuch}</td>
                                <td class="tcenter">{if $oAdmin->dLetzterLogin && $oAdmin->dLetzterLogin !== null}{$oAdmin->dLetzterLogin|date_format:'%d.%m.%Y %H:%M:%S'}{else}---{/if}</td>
                                <td class="tcenter">{if !$oAdmin->bAktiv}gesperrt{else}{if $oAdmin->dGueltigBis && $oAdmin->dGueltigBis !== null}{$oAdmin->dGueltigBis|date_format:'%d.%m.%Y %H:%M:%S'}{else}---{/if}{/if}</td>
                                <td class="tcenter">
                                    <form method="post" action="benutzerverwaltung.php">
                                        {$jtl_token}
                                        <input type="hidden" name="id" value="{$oAdmin->kAdminlogin}" />
                                        {if $oAdmin->bAktiv}
                                            <button class="notext btn btn-default btn-circle" name="action" value="account_lock" title="{__('sperrenLabel')}"><i class="fa fa-lock"></i></button>
                                        {else}
                                            <button class="notext btn btn-default btn-circle" name="action" value="account_unlock" title="{__('entsperrenLabel')}"><i class="fa fa-unlock"></i></button>
                                        {/if}
                                        <button class="notext btn btn-danger btn-circle" name="action" value="account_delete" onclick="return confirm('{__('sureDeleteGroup')}');" title="{__('delete')}"><i class="fas fa-trash-alt"></i></button>
                                        <button class="notext btn btn-primary btn-circle" name="action" value="account_edit" title="{__('modify')}"><i class="fal fa-edit"></i></button>
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="save-wrapper">
                    <form action="benutzerverwaltung.php" method="post">
                        {$jtl_token}
                        <input type="hidden" name="action" value="account_edit" />
                        <button type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {__('benutzerNeu')}</button>
                    </form>
                </div>
            </div>
            <div id="group_view" class="tab-pane fade{if isset($cTab) && $cTab === 'group_view'} active show{/if}">
                <div class="subheading1">{__('gruppenKategorie')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">#</th>
                            <th class="tleft">{__('group')}</th>
                            <th class="tleft">{__('description')}</th>
                            <th class="tcenter">{__('user')}</th>
                            <th class="tcenter">{__('action')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oAdminGroup_arr as $oGroup}
                            <tr>
                                <td class="tleft">{$oGroup->kAdminlogingruppe}</td>
                                <td class="tleft">{$oGroup->cGruppe}</td>
                                <td class="tleft">{__($oGroup->cBeschreibung)}</td>
                                <td class="tcenter">{$oGroup->nCount}</td>
                                <td class="tcenter">
                                    {if $oGroup->kAdminlogingruppe !== '1'}
                                        <form method="post" action="benutzerverwaltung.php">
                                            {$jtl_token}
                                            <input type="hidden" value="{$oGroup->kAdminlogingruppe}" name="id" />
                                            <button type="submit" class="delete btn btn-danger btn-circle" name="action" value="group_delete" onclick="return confirm('{__('sureDeleteGroup')}');" {if 0 < (int)$oGroup->nCount}title="{__('loeschenLabelDeaktiviert')}" disabled="disabled"{else}title="{__('loeschenLabel')}"{/if}>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <button type="submit" class="edit btn btn-primary btn-circle" name="action" value="group_edit" title="{__('modify')}">
                                                <i class="fal fa-edit"></i>
                                            </button>
                                        </form>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="save-wrapper">
                    <form action="benutzerverwaltung.php" method="post">
                        <input type="hidden" name="action" value="group_edit" />
                        {$jtl_token}
                        <button type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {__('gruppeNeu')}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>