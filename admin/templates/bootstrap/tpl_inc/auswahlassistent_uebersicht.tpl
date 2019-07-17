{include file='tpl_inc/seite_header.tpl' cTitel=__('auswahlassistent') cBeschreibung=__('auswahlassistentDesc')
         cDokuURL=__('auswahlassistentURL')}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <div class="block">
            {include file='tpl_inc/language_switcher.tpl' action='auswahlassistent.php'}
        </div>
        <ul class="nav nav-tabs" role="tablist">
            <li class="tab{if !isset($cTab) || $cTab === 'uebersicht'} active{/if}">
                <a data-toggle="tab" role="tab" href="#overview">{__('aaOverview')}</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#config">{__('settings')}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="overview" class="tab-pane fade{if !isset($cTab) || $cTab === 'uebersicht'} active show{/if}">
                <form name="uebersichtForm" method="post" action="auswahlassistent.php">
                    {$jtl_token}
                    <input type="hidden" name="tab" value="uebersicht" />
                    <div class="card">
                        {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}
                            <div class="table-responsive card-body">
                                <table class="list table">
                                    <thead>
                                        <tr>
                                            <th class="check" style="width:35px">&nbsp;</th>
                                            <th class="tcenter">{__('active')}</th>
                                            <th class="tleft">{__('name')}</th>
                                            <th class="tcenter">{__('aaLocation')}</th>
                                            <th class="tright">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $oAuswahlAssistentGruppe_arr as $oAuswahlAssistentGruppe}
                                            <tr{if !$oAuswahlAssistentGruppe->nAktiv} class="text-danger"{/if}>
                                                <td class="check">
                                                    <input name="kAuswahlAssistentGruppe_arr[]" type="checkbox"
                                                           value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                           id="group-{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"/>
                                                </td>
                                                <td>{if !$oAuswahlAssistentGruppe->nAktiv}<i class="fal fa-times text-danger"></i>{else}<i class="fal fa-check text-success"></i>{/if}</td>
                                                <td class="tleft">
                                                    <label for="group-{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}">
                                                        {$oAuswahlAssistentGruppe->cName}
                                                    </label>
                                                </td>
                                                <td class="tcenter">
                                                    {foreach $oAuswahlAssistentGruppe->oAuswahlAssistentOrt_arr as $oAuswahlAssistentOrt}
                                                        {$oAuswahlAssistentOrt->cOrt}{if !$oAuswahlAssistentOrt@last}, {/if}
                                                    {/foreach}
                                                </td>
                                                <td class="tright" width="265">
                                                    {if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}
                                                        <button class="btn btn-default btn-circle button down"
                                                           id="btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                           title="{__('showQuestions')}">
                                                            <i class="fa fa-question-circle-o"></i>
                                                        </button>
                                                    {/if}
                                                        <a href="auswahlassistent.php?a=editGrp&g={$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}&token={$smarty.session.jtl_token}"
                                                           class="btn btn-primary btn-circle edit" title="{__('modify')}">
                                                            <i class="fal fa-edit"></i>
                                                        </a>
                                                </td>
                                            </tr>
                                            {if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}
                                                <tr>
                                                    <td class="tleft" colspan="5"
                                                        id="row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                        style="display: none;">
                                                        <div id="rowdiv_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                             style="display: none;">
                                                            <table class="list table">
                                                                <tr>
                                                                    <th class="tcenter"></th>
                                                                    <th class="tleft">{__('question')}</th>
                                                                    <th class="tcenter">{__('attribute')}</th>
                                                                    <th class="tcenter">{__('sorting')}</th>
                                                                    <th class="tright">&nbsp;</th>
                                                                </tr>
                                                                {foreach $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr as $oAuswahlAssistentFrage}
                                                                    <tr{if !$oAuswahlAssistentFrage->nAktiv} class="text-danger"{/if}>
                                                                        <td>{if !$oAuswahlAssistentFrage->nAktiv}<i class="fal fa-times"></i>{/if}</td>
                                                                        <td class="tleft">{$oAuswahlAssistentFrage->cFrage}</td>
                                                                        <td class="tcenter">{$oAuswahlAssistentFrage->cName}</td>
                                                                        <td class="tcenter">{$oAuswahlAssistentFrage->nSort}</td>
                                                                        <td class="tright" style="width:250px">
                                                                            <div class="btn-group">
                                                                                <a href="auswahlassistent.php?a=editQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-default edit">
                                                                                    <i class="fal fa-edit"></i>
                                                                                </a>
                                                                                <a href="auswahlassistent.php?a=delQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-danger remove">
                                                                                    <i class="fas fa-trash-alt"></i>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                {/foreach}
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <script>
                                                    $("#btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").on('click', function () {
                                                        $("#row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").slideToggle(100, 'linear');
                                                        $("#rowdiv_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").slideToggle(100, 'linear');
                                                    });
                                                </script>
                                            {/if}
                                        {/foreach}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="check">
                                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            <td colspan="4">
                                                <label for="ALLMSGS">{__('globalSelectAll')}</label>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        {else}
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                        {/if}
                        <div class="card-footer save-wrapper">
                            {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}
                                <button type="submit" name="a" value="delGrp" class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i> {__('delete')}
                                </button>
                            {/if}
                            <button type="submit" name="a" value="newQuest" class="btn btn-default">
                                <i class="fa fa-share"></i> {__('aaQuestion')}
                            </button>
                            <button type="submit" name="a" value="newGrp" class="btn btn-primary">
                                <i class="fa fa-share"></i> {__('aaGroup')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- #overview -->
            <div id="config" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings'
                         action='auswahlassistent.php' buttonCaption=__('save') tab='einstellungen' title=__('settings')}
            </div>
            <!-- #config -->
        </div>
        <!-- .tab-content -->
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div><!-- #content -->

{include file='tpl_inc/footer.tpl'}
