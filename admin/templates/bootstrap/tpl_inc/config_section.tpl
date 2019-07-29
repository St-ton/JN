<form{if !empty($name)} name="{$name}"{/if} method="{if !empty($method)}{$method}{else}post{/if}"{if !empty($action)} action="{$action}"{/if}>
    {$jtl_token}
    <input type="hidden" name="einstellungen" value="1" />
    {if !empty($a)}
        <input type="hidden" name="a" value="{$a}" />
    {/if}
    {if !empty($tab)}
        <input type="hidden" name="tab" value="{$tab}" />
    {/if}
    <div class="settings">
        {if !empty($title)}
            <span class="subheading1">{$title}</span>
            <hr class="mb-3">
        {/if}
        <div>
            {foreach $config as $configItem}
                {if $configItem->cConf === 'Y'}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$configItem->cWertName}">{$configItem->cName}{if $configItem->cWertName|strpos:'_bestandskundenguthaben' || $configItem->cWertName|strpos:'_neukundenguthaben'}<span id="EinstellungAjax_{$configItem->cWertName}"></span>{/if}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            {if $configItem->cInputTyp === 'selectbox'}
                                <select name="{$configItem->cWertName}" id="{$configItem->cWertName}" class="custom-select combo">
                                    {foreach $configItem->ConfWerte as $wert}
                                        <option value="{$wert->cWert}" {if $configItem->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'listbox'}
                                <select name="{$configItem->cWertName}[]" id="{$configItem->cWertName}" multiple="multiple" class="custom-select combo">
                                {foreach $configItem->ConfWerte as $wert}
                                    <option value="{$wert->kKundengruppe}" {foreach $configItem->gesetzterWert as $gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'number'}
                                <input class="form-control" type="number" step="any" name="{$configItem->cWertName}" id="{$configItem->cWertName}" value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" tabindex="1"{if $configItem->cWertName|strpos:'_bestandskundenguthaben' || $configItem->cWertName|strpos:'_neukundenguthaben'} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->cWertName}', this);"{/if} />
                            {elseif $configItem->cInputTyp === 'selectkdngrp'}
                                <select name="{$configItem->cWertName}[]" id="{$configItem->cWertName}" class="custom-select combo">
                                {foreach $configItem->ConfWerte as $wert}
                                    <option value="{$wert->kKundengruppe}" {foreach $configItem->gesetzterWert as $gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->cInputTyp === 'pass'}
                                <input class="form-control" type="password" name="{$configItem->cWertName}" id="{$configItem->cWertName}"  value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" />
                            {else}
                                <input class="form-control" type="text" name="{$configItem->cWertName}" id="{$configItem->cWertName}"  value="{if isset($configItem->gesetzterWert)}{$configItem->gesetzterWert}{/if}" tabindex="1"{if $configItem->cWertName|strpos:'_bestandskundenguthaben' || $configItem->cWertName|strpos:'_neukundenguthaben'} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->cWertName}', this);"{/if} />
                            {/if}
                        </div>
                        {if $configItem->cBeschreibung}
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$configItem->cBeschreibung cID=$configItem->kEinstellungenConf}</div>
                        {/if}
                    </div>
                {/if}
            {/foreach}
        </div>
        <div class="save-wrapper card-footer">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" type="submit" class="btn btn-primary btn-block">{if !empty($buttonCaption)}{$buttonCaption}{else}{__('saveWithIcon')}{/if}</button>
                </div>
            </div>
        </div>
    </div>
</form>