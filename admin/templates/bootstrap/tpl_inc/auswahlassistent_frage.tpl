{if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:__('aaQuestionEdit')}
{else}
    {assign var=cTitel value=__('auswahlassistent')|cat:' - '|cat:__('aaQuestion')}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('auswahlassistentDesc')
cDokuURL=__('auswahlassistentURL')}

<div id="content">
    {if !isset($noModule) || !$noModule}
        <form class="navbar-form settings" method="post" action="auswahlassistent.php">
            {$jtl_token}
            <input name="speichern" type="hidden" value="1">
            <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
            <input name="tab" type="hidden" value="frage">
            <input name="a" type="hidden" value="addQuest">
            {if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
                <input class="form-control" name="kAuswahlAssistentFrage" type="hidden"
                       value="{if isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0}{$kAuswahlAssistentFrage}{else}{$oFrage->kAuswahlAssistentFrage}{/if}">
            {/if}
            <div class="card">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cFrage">
                                {__('question')}
                                {if isset($cPlausi_arr.cName)}
                                    <span class="fillout">{__('FillOut')}</span>
                                {/if}
                            </label>
                        </span>
                        <input id="cFrage" class="form-control{if isset($cPlausi_arr.cFrage)} fieldfillout{/if}"
                               name="cFrage" type="text"
                               value="{if isset($cPost_arr.cFrage)}{$cPost_arr.cFrage}{elseif isset($oFrage->cFrage)}{$oFrage->cFrage}{/if}">
                        <span class="input-group-addon">{getHelpDesc cDesc="{__('hintQuestionName')}"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kAuswahlAssistentGruppe">
                                {__('group')}
                                {if isset($cPlausi_arr.kAuswahlAssistentGruppe)}
                                    <span class="fillout">{__('FillOut')}</span>
                                {/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="kAuswahlAssistentGruppe" name="kAuswahlAssistentGruppe" class="form-control{if isset($cPlausi_arr.kAuswahlAssistentGruppe)} fieldfillout{/if}">
                                <option value="-1">{__('aaChoose')}</option>
                                {foreach $oAuswahlAssistentGruppe_arr as $oAuswahlAssistentGruppe}
                                    <option value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"
                                            {if isset($oAuswahlAssistentGruppe->kAuswahlAssistentGruppe) && ((isset($cPost_arr.kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $cPost_arr.kAuswahlAssistentGruppe) || (isset($oFrage->kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $oFrage->kAuswahlAssistentGruppe))} selected{/if}>{$oAuswahlAssistentGruppe->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                        <span class="input-group-addon">{getHelpDesc cDesc="{__('hintQuestionGroup')}"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kMM">{__('attribute')} {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 1} <span class="fillout">{__('FillOut')}</span>{/if}
                                {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 2 }<span class="fillout">{__('aaMerkmalTaken')}</span>{/if}
                            </label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="kMM" name="kMerkmal" class="form-control{if isset($cPlausi_arr.kMerkmal)} fieldfillout{/if}">
                                <option value="-1">{__('aaChoose')}</option>
                                {foreach $oMerkmal_arr as $oMerkmal}
                                    <option value="{$oMerkmal->kMerkmal}"{if (isset($cPost_arr.kMerkmal) && $oMerkmal->kMerkmal == $cPost_arr.kMerkmal) || (isset($oFrage->kMerkmal) && $oMerkmal->kMerkmal == $oFrage->kMerkmal)} selected{/if}>{$oMerkmal->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                        <span class="input-group-addon">{getHelpDesc cDesc="{__('hintQuestionAttribute')}"}</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nSort">
                                {__('sorting')}
                                {if isset($cPlausi_arr.nSort)}
                                    <span class="fillout">{__('FillOut')}</span>
                                {/if}
                            </label>
                        </span>
                        <input id="nSort" class="form-control{if isset($cPlausi_arr.nSort)} fieldfillout{/if}"
                               name="nSort" type="text"
                               value="{if isset($cPost_arr.nSort)}{$cPost_arr.nSort}{elseif isset($oFrage->nSort)}{$oFrage->nSort}{else}1{/if}">
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="{__('hintQuestionPosition')}"}
                        </span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nAktiv">{__('active')}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nAktiv" class="form-control" name="nAktiv">
                                <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 1)} selected{/if}>
                                    {__('yes')}
                                </option>
                                <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 0)} selected{/if}>
                                    {__('no')}
                                </option>
                            </select>
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc="{__('hintQuestionActive')}"}
                        </span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group">
                        <button name="speichernSubmit" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                        <a href="auswahlassistent.php" class="btn btn-danger">{__('goBack')}</a>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div>

{include file='tpl_inc/footer.tpl'}