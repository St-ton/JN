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
                {if $configItem->isConfigurable()}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$configItem->getValueName()}">
                            {$configItem->getName()}:
                        </label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 {if $configItem->getInputType() === 'number'}config-type-number{/if}">
                            {if $configItem->getInputType() === 'selectbox'}
                                <select name="{$configItem->getValueName()}" id="{$configItem->getValueName()}" class="custom-select combo">
                                    {foreach $configItem->getValues() as $value}
                                        <option value="{$value->cWert}" {if $configItem->getSetValue() == $value->cWert}selected{/if}>{$value->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $configItem->getInputType() === 'listbox'}
                                <select name="{$configItem->getValueName()}[]"
                                        id="{$configItem->getValueName()}"
                                        multiple="multiple"
                                        class="selectpicker custom-select combo"
                                        data-selected-text-format="count > 2"
                                        data-size="7">
                                {foreach $configItem->getValues() as $value}
                                    <option value="{$value->kKundengruppe}" {foreach $configItem->getSetValue() as $setValue}{if $setValue->cWert == $value->kKundengruppe}selected{/if}{/foreach}>{$value->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->getInputType() === 'number'}
                                <div class="input-group form-counter">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input class="form-control"
                                           type="number"
                                           name="{$configItem->getValueName()}"
                                           id="{$configItem->getValueName()}"
                                           value="{if $configItem->getSetValue() !== null}{$configItem->getSetValue()}{/if}"
                                           tabindex="1"
                                            {if $configItem->getValueName()|strpos:'_bestandskundenguthaben' || $configItem->getValueName()|strpos:'_neukundenguthaben'}
                                                onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->getValueName()}', this);"
                                            {/if} />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            {elseif $configItem->getInputType() === 'selectkdngrp'}
                                <select name="{$configItem->getValueName()}[]" id="{$configItem->getValueName()}" class="custom-select combo">
                                {foreach $configItem->getValues() as $value}
                                    <option value="{$value->kKundengruppe}" {foreach $configItem->getSetValue() as $setValue}{if $setValue->cWert == $value->kKundengruppe}selected{/if}{/foreach}>{$value->cName}</option>
                                {/foreach}
                                </select>
                            {elseif $configItem->getInputType() === 'pass'}
                                <input class="form-control" type="password" name="{$configItem->getValueName()}" id="{$configItem->getValueName()}"  value="{if $configItem->getSetValue() !== null}{$configItem->getSetValue()}{/if}" />
                            {else}
                                <input class="form-control"
                                       type="text"
                                       name="{$configItem->getValueName()}"
                                       id="{$configItem->getValueName()}"
                                       value="{if $configItem->getSetValue() !== null}{$configItem->getSetValue()}{/if}"
                                       tabindex="1"
                                        {if $configItem->getValueName()|strpos:'_bestandskundenguthaben' || $configItem->getValueName()|strpos:'_neukundenguthaben'}
                                            onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$configItem->getValueName()}', this);"
                                        {/if} />
                            {/if}
                            {if $configItem->getValueName()|strpos:'_bestandskundenguthaben' || $configItem->getValueName()|strpos:'_neukundenguthaben'}
                                <span id="EinstellungAjax_{$configItem->getValueName()}"></span>
                            {/if}
                        </div>
                        {include file='snippets/einstellungen_icons.tpl' cnf=$configItem}
                    </div>
                {elseif $showNonConf|default:false}
                    <div class="subheading1 mt-6">
                        {$configItem->getName()}
                    </div>
                    <hr class="mb-3">
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
