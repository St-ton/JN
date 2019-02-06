{if !isset($Exportformat->kExportformat)}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('newExportformat')}
{else}
    {include file='tpl_inc/seite_header.tpl' cTitel=__('modifyExportformat')}
{/if}
<div id="content">
    <form name="wxportformat_erstellen" method="post" action="exportformate.php">
        {$jtl_token}
        <input type="hidden" name="neu_export" value="1" />
        <input type="hidden" name="kExportformat" value="{if isset($Exportformat->kExportformat)}{$Exportformat->kExportformat}{/if}" />
        {if isset($Exportformat->bPluginContentFile) && $Exportformat->bPluginContentFile}
            <input type="hidden" name="bPluginContentFile" value="1" />
        {/if}
        {if !empty($Exportformat->kPlugin)}
            <input type="hidden" name="kPlugin" value="{$Exportformat->kPlugin}" />
        {/if}
        <div class="panel panel-default settings">
            <div class="panel-body">
                <ul class="jtl-list-group">
                    <li class="input-group{if isset($cPlausiValue_arr.cName)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cName">{__('name')}{if isset($cPlausiValue_arr.cName)} <span class="fillout">{__('FillOut')}</span>{/if}</label>
                        </span>
                        <input class="form-control" type="text" name="cName" id="cName" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($Exportformat->cName)}{$Exportformat->cName}{/if}" tabindex="1" />
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kSprache">{__('language')}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kSprache" id="kSprache">
                                {foreach $sprachen as $sprache}
                                    <option value="{$sprache->kSprache}" {if isset($Exportformat->kSprache) && $Exportformat->kSprache == $sprache->kSprache || (isset($cPlausiValue_arr.kSprache) && $cPlausiValue_arr.kSprache == $sprache->kSprache)}selected{/if}>{$sprache->cNameDeutsch}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kWaehrung">{__('currency')}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kWaehrung" id="kWaehrung">
                                {foreach $waehrungen as $waehrung}
                                    <option value="{$waehrung->kWaehrung}" {if isset($Exportformat->kSprache) && $Exportformat->kWaehrung == $waehrung->kWaehrung || (isset($cPlausiValue_arr.kWaehrung) && $cPlausiValue_arr.cName == $waehrung->kWaehrung)}selected{/if}>{$waehrung->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kKampagne">{__('campaigns')}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kKampagne" id="kKampagne">
                                <option value="0"></option>
                                {foreach $oKampagne_arr as $oKampagne}
                                    <option value="{$oKampagne->kKampagne}" {if isset($Exportformat->kSprache) && $Exportformat->kKampagne == $oKampagne->kKampagne || (isset($cPlausiValue_arr.kKampagne) && $cPlausiValue_arr.kKampagne == $oKampagne->kKampagne)}selected{/if}>{$oKampagne->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="kKundengruppe">{__('customerGroup')}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="kKundengruppe" id="kKundengruppe">
                                {foreach $kundengruppen as $kdgrp}
                                    <option value="{$kdgrp->kKundengruppe}" {if isset($Exportformat->kSprache) && $Exportformat->kKundengruppe == $kdgrp->kKundengruppe || (isset($cPlausiValue_arr.kKundengruppe) && $cPlausiValue_arr.kKundengruppe == $kdgrp->kKundengruppe)}selected{/if}>{$kdgrp->cName}</option>
                                {/foreach}
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="cKodierung">{__('encoding')}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="cKodierung" id="cKodierung">
                                <option value="ASCII" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'ASCII') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'ASCII')}selected{/if}>
                                    ASCII
                                </option>
                                <option value="UTF-8" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'UTF-8') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'UTF-8')}selected{/if}>
                                    UTF-8 + BOM
                                </option>
                                <option value="UTF-8noBOM" {if (isset($Exportformat->cKodierung) && $Exportformat->cKodierung === 'UTF-8noBOM') || (isset($cPlausiValue_arr.cKodierung) && $cPlausiValue_arr.cKodierung === 'UTF-8noBOM')}selected{/if}>
                                    UTF-8
                                </option>
                            </select>
                        </span>
                    </li>
                    <li class="input-group item">
                        <span class="input-group-addon"><label for="nUseCache">{__('useCache')}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="nUseCache" id="nUseCache">
                                <option value="1" {if (isset($Exportformat->nUseCache) && $Exportformat->nUseCache === '1')}selected{/if}>{__('yes')}</option>
                                <option value="0" {if (!isset($Exportformat->nUseCache) || $Exportformat->nUseCache === '0')}selected{/if}>{__('no')}</option>
                            </select>
                        </span>
                    </li>

                    <li class="input-group item">
                        <span class="input-group-addon"><label for="nVarKombiOption">{__('varikombiOption')}</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="nVarKombiOption" id="nVarKombiOption">
                                <option value="1" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 1) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 1)}selected{/if}>{__('varikombiOption1')}</option>
                                <option value="2" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 2) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 2)}selected{/if}>{__('varikombiOption2')}</option>
                                <option value="3" {if (isset($Exportformat->nVarKombiOption) && $Exportformat->nVarKombiOption == 3) || (isset($cPlausiValue_arr.nVarKombiOption) && $cPlausiValue_arr.nVarKombiOption == 3)}selected{/if}>{__('varikombiOption3')}</option>
                            </select>
                        </span>
                    </li>

                    <li class="input-group item">
                        <span class="input-group-addon"><label for="nSplitgroesse">{__('splitSize')}</label></span>
                        <input class="form-control" type="text" name="nSplitgroesse" id="nSplitgroesse" value="{if isset($cPostVar_arr.nSplitgroesse)}{$cPostVar_arr.nSplitgroesse}{elseif isset($Exportformat->nSplitgroesse)}{$Exportformat->nSplitgroesse}{/if}" tabindex="2" />
                    </li>

                    <li class="input-group item{if isset($cPlausiValue_arr.cDateiname)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cDateiname">{__('filename')}{if isset($cPlausiValue_arr.cDateiname)} <span class="fillout">{__('FillOut')}</span>{/if}</label>
                        </span>
                        <input class="form-control{if isset($cPlausiValue_arr.cDateiname)} fieldfillout{/if}" type="text" name="cDateiname" id="cDateiname" value="{if isset($cPostVar_arr.cDateiname)}{$cPostVar_arr.cDateiname}{elseif isset($Exportformat->cDateiname)}{$Exportformat->cDateiname}{/if}" tabindex="2" />
                    </li>
                </ul>
                {if !isset($Exportformat->bPluginContentFile)|| !$Exportformat->bPluginContentFile}
                    <p><label for="cKopfzeile">{__('header')}</label>
                        {getHelpDesc placement='right' cDesc=__('onlyIfNeeded')}
                        <textarea name="cKopfzeile" id="cKopfzeile" class="codemirror smarty field">{if isset($cPostVar_arr.cKopfzeile)}{$cPostVar_arr.cKopfzeile|replace:"\t":"<tab>"}{elseif isset($Exportformat->cKopfzeile)}{$Exportformat->cKopfzeile}{/if}</textarea>
                    </p>
                    <p><label for="cContent">{__('template')}</label>
                        {getHelpDesc placement='right' cDesc=__('smartyRules')}
                        <textarea name="cContent" id="cContent" class="codemirror smarty field{if isset($oSmartyError)}fillout{/if}">{if isset($cPostVar_arr.cContent)}{$cPostVar_arr.cContent|replace:"\t":"<tab>"}{elseif isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}</textarea>
                    </p>
                    <p><label for="cFusszeile">{__('footer')}</label>
                        {getHelpDesc placement='right' cDesc=__('onlyIfNeededFooter')}
                        <textarea name="cFusszeile" id="cFusszeile" class="codemirror smarty field">{if isset($cPostVar_arr.cFusszeile)}{$cPostVar_arr.cFusszeile|replace:"\t":"<tab>"}{elseif isset($Exportformat->cFusszeile)}{$Exportformat->cFusszeile}{/if}</textarea>
                    </p>
                {else}
                    <input name="cContent" type="hidden" value="{if isset($Exportformat->cContent)}{$Exportformat->cContent}{/if}" />
                {/if}
            </div>
        </div>
        <div class="panel panel-default settings">
            <div class="panel-heading">
                <h3 class="panel-title">{__('settings')}</h3>
            </div>
            <div class="panel-body">
                <ul class="jtl-list-group">
                    {foreach $Conf as $cnf}
                        {if $cnf->cConf === 'Y'}
                            <li class="input-group">
                                <span class="input-group-addon"><label for="{$cnf->cWertName}">{$cnf->cName}</label></span>
                                {if $cnf->cInputTyp === 'selectbox'}
                                    <span class="input-group-wrap">
                                        <select class="form-control" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                            {foreach $cnf->ConfWerte as $wert}
                                                <option value="{$wert->cWert}" {if isset($cnf->gesetzterWert) && $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                {else}
                                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="3" />
                                {/if}
                                {if $cnf->cBeschreibung}
                                    <span class="input-group-addon">
                                        {getHelpDesc cDesc=$cnf->cBeschreibung}
                                    </span>
                                {/if}
                            </li>
                        {else}
                            <h3 style="text-align:center;">{$cnf->cName}</h3>
                        {/if}
                    {/foreach}
                </ul>
            </div>
        </div>
        <div class="save_wrapper">
            <button type="submit" class="btn btn-primary" value="{if !isset($Exportformat->kExportformat) || !$Exportformat->kExportformat}{__('newExportformatSave')}{else}{__('modifyExportformatSave')}{/if}">
                <i class="fa fa-save"></i> {if !isset($Exportformat->kExportformat) || !$Exportformat->kExportformat}{__('newExportformatSave')}{else}{__('modifyExportformatSave')}{/if}
            </button>
        </div>
    </form>

    {if isset($Exportformat->kExportformat)}
        {getRevisions type='export' key=$Exportformat->kExportformat show=['cContent','cKopfzeile','cFusszeile'] data=$Exportformat}
    {/if}
</div>