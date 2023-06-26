<div class="form-group form-row align-items-center {if $cnf->isHighlight() || (isset($cSuche) && $cnf->getID() == $cSuche)} highlight{/if}">
    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="{$cnf->getValueName()}">
        {$cnf->getName()}{if strpos($cnf->getValueName(), '_guthaben') && $cnf->getInputType() !== 'selectbox'} <span id="EinstellungAjax_{$cnf->getValueName()}"></span>{/if}:
    </label>
    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 {if $cnf->getInputType() === 'number'}config-type-number{/if}">
        {if $cnf->getInputType() === 'selectbox'}
            {if $cnf->getValueName() === 'kundenregistrierung_standardland' || $cnf->getValueName() === 'lieferadresse_abfragen_standardland' }
                <select class="custom-select" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}">
                    {foreach $countries as $country}
                        <option value="{$country->getISO()}" {if $cnf->getSetValue() == $country->getISO()}selected{/if}>{$country->getName()}</option>
                    {/foreach}
                </select>
            {elseif $smarty.const.ENABLE_EXPERIMENTAL_ROUTING_SCHEMES === false && (
                $cnf->getValueName() === 'routing_scheme' || $cnf->getValueName() === 'routing_default_language')
            }
                <select class="custom-select" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" disabled>
                    {foreach $cnf->getValues() as $value}
                        {if $value->cWert === 'F'}
                            <option value="F" selected>{$value->cName}</option>
                        {/if}
                    {/foreach}
                </select>
            {else}
                <select class="custom-select" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}">
                    {foreach $cnf->getValues() as $value}
                        <option value="{$value->cWert}" {if $cnf->getSetValue() == $value->cWert}selected{/if}>
                            {$value->cName}
                        </option>
                    {/foreach}
                </select>
            {/if}
        {elseif $cnf->getInputType() === 'listbox'}
            <select name="{$cnf->getValueName()}[]"
                    id="{$cnf->getValueName()}"
                    multiple="multiple"
                    class="selectpicker custom-select combo"
                    data-selected-text-format="count > 2"
                    data-size="7">
                {foreach $cnf->getValues() as $value}
                    <option value="{$value->cWert}" {foreach $cnf->getSetValue() as $setValue}{if $setValue->cWert == $value->cWert}selected{/if}{/foreach}>{$value->cName}</option>
                {/foreach}
            </select>
        {elseif $cnf->getInputType() === 'selectkdngrp'}
            <select name="{$cnf->getValueName()}[]" id="{$cnf->getValueName()}" class="custom-select combo">
                {foreach $cnf->getValues() as $value}
                    <option value="{$value->kKundengruppe}" {foreach $cnf->getSetValue() as $setValue}{if $setValue->cWert == $value->kKundengruppe}selected{/if}{/foreach}>
                        {$value->cName}
                    </option>
                {/foreach}
            </select>
        {elseif $cnf->getInputType() === 'color'}
            {include file='snippets/colorpicker.tpl'
            cpID="config-{$cnf->getValueName()}"
            useAlpha=$cnf->getValueName() === 'bilder_hintergrundfarbe'
            cpName=$cnf->getValueName()
            cpValue=$cnf->getSetValue()}
        {elseif $cnf->getInputType() === 'pass'}
            <input class="form-control" autocomplete="off" type="password" name="{$cnf->getValueName()}"
                   id="{$cnf->getValueName()}" placeholder="****" tabindex="1" />
        {elseif $cnf->getInputType() === 'number'}
            <div class="input-group form-counter">
                <div class="input-group-prepend">
                    <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                        <span class="fas fa-minus"></span>
                    </button>
                </div>
                <input class="form-control" type="number"
                       name="{$cnf->getValueName()}"
                       id="{$cnf->getValueName()}"
                       value="{if $cnf->getSetValue() !== null}{$cnf->getSetValue()}{/if}"
                       tabindex="1"
                        {if strpos($cnf->getValueName(), '_guthaben') || strpos($cnf->getValueName(), '_bestandskundenguthaben') || strpos($cnf->getValueName(), '_neukundenguthaben')}
                            onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$cnf->getValueName()}', this);"
                        {/if}
                />
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                        <span class="fas fa-plus"></span>
                    </button>
                </div>
            </div>
        {else}
            <input class="form-control" type="text"
                   name="{$cnf->getValueName()}"
                   id="{$cnf->getValueName()}"
                   value="{if $cnf->getSetValue() !== null}{$cnf->getSetValue()}{/if}"
                   {if strpos($cnf->getValueName(), '_guthaben') || strpos($cnf->getValueName(), '_bestandskundenguthaben') || strpos($cnf->getValueName(), '_neukundenguthaben')}
                       onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$cnf->getValueName()}', this);"
                   {/if}
                   tabindex="1" />
        {/if}
    </div>
    {include file='snippets/einstellungen_icons.tpl' cnf=$cnf}
</div>
