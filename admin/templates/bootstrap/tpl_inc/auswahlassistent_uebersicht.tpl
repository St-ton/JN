{include file='tpl_inc/seite_header.tpl' cTitel=__('auswahlassistent') cBeschreibung=__('auswahlassistentDesc')
         cDokuURL=__('auswahlassistentURL')}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <div class="card">
            <div class="card-body">
                {include file='tpl_inc/language_switcher.tpl' action='auswahlassistent.php'}
            </div>
        </div>
        <div class="tabs">
            <nav class="tavs-nav">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {if !isset($cTab) || $cTab === 'uebersicht'} active{/if}" data-toggle="tab" role="tab" href="#overview">
                            {__('aaOverview')}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if isset($cTab) && $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#config">
                            {__('settings')}
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="tab-content">
                <div id="overview" class="tab-pane fade{if !isset($cTab) || $cTab === 'uebersicht'} active show{/if}">
                    <form name="uebersichtForm" method="post" action="auswahlassistent.php">
                        {$jtl_token}
                        <input type="hidden" name="tab" value="uebersicht" />
                        <div>
                            {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}
                                <div class="table-responsive">
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
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input" name="kAuswahlAssistentGruppe_arr[]" type="checkbox"
                                                               value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                               id="group-{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"/>
                                                            <label class="custom-control-label" for="group-{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"></label>
                                                        </div>
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
                                                            <a href="#" class="btn btn-default btn-circle button down"
                                                               id="btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                                               title="{__('showQuestions')}">
                                                                <i class="fa fa-question-circle-o"></i>
                                                            </a>
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
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="tcenter"></th>
                                                                            <th class="tleft">{__('question')}</th>
                                                                            <th class="tcenter">{__('attribute')}</th>
                                                                            <th class="tcenter">{__('sorting')}</th>
                                                                            <th class="tright">&nbsp;</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    {foreach $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr as $oAuswahlAssistentFrage}
                                                                        <tr{if !$oAuswahlAssistentFrage->nAktiv} class="text-danger"{/if}>
                                                                            <td>{if !$oAuswahlAssistentFrage->nAktiv}<i class="fal fa-times"></i>{/if}</td>
                                                                            <td class="tleft">{$oAuswahlAssistentFrage->cFrage}</td>
                                                                            <td class="tcenter">{$oAuswahlAssistentFrage->cName}</td>
                                                                            <td class="tcenter">{$oAuswahlAssistentFrage->nSort}</td>
                                                                            <td class="tright" style="width:250px">
                                                                                <a href="auswahlassistent.php?a=delQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-danger btn-circle remove">
                                                                                    <i class="fas fa-trash-alt"></i>
                                                                                </a>
                                                                                <a href="auswahlassistent.php?a=editQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}&token={$smarty.session.jtl_token}" class="btn btn-default btn-circle edit">
                                                                                    <i class="fal fa-edit"></i>
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                    {/foreach}
                                                                    </tbody>
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
                                    </table>
                                </div>
                            {else}
                                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                            {/if}
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox mb-3">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}
                                        <div class="ml-auto col-sm-6 col-xl-auto submit">
                                            <button type="submit" name="a" value="delGrp" class="btn btn-danger btn-block mb-3">
                                                <i class="fas fa-trash-alt"></i> {__('delete')}
                                            </button>
                                        </div>
                                    {/if}
                                    <div class="{if !(isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0)}ml-auto{/if} col-sm-6 col-xl-auto submit">
                                        <button type="submit" name="a" value="newQuest" class="btn btn-outline-primary btn-block mb-3">
                                            <i class="fa fa-share"></i> {__('aaQuestion')}
                                        </button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto submit">
                                        <button type="submit" name="a" value="newGrp" class="btn btn-primary btn-block mb-3">
                                            <i class="fa fa-share"></i> {__('aaGroup')}
                                        </button>
                                    </div>
                                </div>
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
        </div>
        <!-- .tab-content -->
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div><!-- #content -->

{include file='tpl_inc/footer.tpl'}
