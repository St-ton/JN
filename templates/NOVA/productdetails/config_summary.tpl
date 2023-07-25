{block name='productdetails-config-summary'}
{strip}
    {block name='productdetails-config-summary-name-net'}
        <tr>
            <td colspan="2">{$Artikel->cName}</td>
            <td class="cfg-price">{$Artikel->Preise->cVKLocalized[$NettoPreise]}</td>
        </tr>
    {/block}
    {if $oKonfig->oKonfig_arr|count > 0}
        {$isIgnoreMultiplier = false}
        {block name='productdetails-config-summary-conf-groups'}
            {foreach $oKonfig->oKonfig_arr as $configGroup}
                {$configLocalization = $configGroup->getSprache()}
                {$isValid = in_array($configGroup->getID(), $oKonfig->invalidGroups|default:[])}
                {if !empty($configGroup->getMin()) || !empty($configGroup->getMax())}
                    {if $configGroup->getMin() === 1 && $configGroup->getMax() === 1}
                        {$incorrectText="{lang key='configChooseOneComponent' section='productDetails'}"}
                    {elseif $configGroup->getMin() === $configGroup->getMax()}
                        {$incorrectText="{lang key='configChooseNumberComponents' section='productDetails' printf=$configGroup->getMin()}"}
                    {elseif !empty($configGroup->getMin()) && $configGroup->getMax()<$configGroup->getItemCount()}
                        {$incorrectText="{lang key='configChooseMinMaxComponents' section='productDetails' printf=$configGroup->getMin()|cat:':::'|cat:$configGroup->getMax()}"}
                    {elseif !empty($configGroup->getMin())}
                        {$incorrectText="{lang key='configChooseMinComponents' section='productDetails' printf=$configGroup->getMin()}"}
                    {elseif $configGroup->getMax()<$configGroup->getItemCount()}
                        {$incorrectText="{lang key='configChooseMaxComponents' section='productDetails' printf=$configGroup->getMax()}"}
                    {else}
                        {$incorrectText="{lang key='optional'}"}
                    {/if}
                {elseif $configGroup->getMin() == 0}
                    {$incorrectText="{lang key='optional'}"}
                {/if}
                <tr class="{if $configGroup@iteration is odd}accent-bg{/if} {if $configGroup->getMin() == 0}cfg-group-optional{/if}">
                    <td class="cfg-summary-item" colspan="3">
                        <a id="cfg-nav-{$configGroup->getID()}"
                           class="cfg-group js-cfg-group {if $configGroup@first}visited{/if}"
                           href="#cfg-grp-{$configGroup->getID()}" data-id="{$configGroup->getID()}">
                            {$configLocalization->getName()} <span class="{if $isValid}d-none {/if}cfg-group-icon cfg-group-check js-group-checked"
                                                                   {if $configGroup->getMin() == 0}title="{lang key='configIsOptional' section='productDetails'}"
                                                                   data-toggle="tooltip"{/if}><i class="fas fa-check"></i></span>
                            <span class="{if !$isValid}d-none {/if}cfg-group-icon cfg-group-missing"
                                  title="{lang key='configIsNotCorrect' section='productDetails'} <br> {$incorrectText}"
                                  data-toggle="tooltip"
                                data-html="true"><i class="fas fa-times"></i></span>
                        </a>

                    {foreach $configGroup->oItem_arr as $oKonfigitem}
                        {if $oKonfigitem->bAktiv && !$oKonfigitem->ignoreMultiplier()}
                            {row}
                                {col cols=2 class="text-nowrap-util"}{$oKonfigitem->fAnzahl} &times;{/col}
                                {col cols=7 class="word-break"}{$oKonfigitem->getName()}{/col}
                                {col cols=3 class="cfg-price"}{$oKonfigitem->getFullPriceLocalized(true, false, 1)}{/col}
                            {/row}
                        {elseif $oKonfigitem->bAktiv && $oKonfigitem->ignoreMultiplier()}
                            {row}
                                {col cols=12}{lang key='one-off' section='checkout'}{/col}
                                {col cols=2 class="text-nowrap-util"}{$oKonfigitem->fAnzahl} &times;{/col}
                                {col cols=7 class="word-break"}{$oKonfigitem->getName()}{/col}
                                {col cols=3 class="cfg-price"}{$oKonfigitem->getFullPriceLocalized()}{/col}
                            {/row}
                        {/if}
                    {/foreach}
                    </td>
                </tr>
            {/foreach}
        {/block}
    {/if}
{/strip}
{/block}
