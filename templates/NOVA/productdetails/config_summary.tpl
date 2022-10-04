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
            {foreach $oKonfig->oKonfig_arr as $oKonfiggruppe}
                {$configLocalization = $oKonfiggruppe->getSprache()}
                <tr class="{if $oKonfiggruppe@iteration is odd}accent-bg{/if}">
                    <td class="cfg-summary-item" colspan="3">
                        <a id="cfg-nav-{$oKonfiggruppe->getID()}"
                           class="cfg-group js-cfg-group {if $oKonfiggruppe@first}visited{/if}"
                           href="#cfg-grp-{$oKonfiggruppe->getID()}" data-id="{$oKonfiggruppe->getID()}">
                            {$configLocalization->getName()} <span class="{if $oKonfiggruppe->getID()|in_array:$oKonfig->invalidGroups|default:[]}d-none {/if}js-group-checked"><i class="fas fa-check"></i></span>
                        </a>

                    {foreach $oKonfiggruppe->oItem_arr as $oKonfigitem}
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
        {*{if $isIgnoreMultiplier}
            {block name='productdetails-config-summary-conf-groups-ignore-multiplier'}
                <tr>
                    <td colspan="3" class="highlighted">{lang key='one-off' section='checkout'}</td>
                </tr>
                {foreach $oKonfig->oKonfig_arr as $oKonfiggruppe}
                    {if $oKonfiggruppe->bAktiv}
                        {foreach $oKonfiggruppe->oItem_arr as $oKonfigitem}
                            {if $oKonfigitem->bAktiv && $oKonfigitem->ignoreMultiplier()}
                                <tr>
                                    <td class="text-nowrap-util">{$oKonfigitem->fAnzahl} &times;</td>
                                    <td class="word-break">{$oKonfigitem->getName()}</td>
                                    <td class="cfg-price">{$oKonfigitem->getFullPriceLocalized()}</td>
                                </tr>
                            {/if}
                        {/foreach}
                    {/if}
                {/foreach}
            {/block}
        {/if}*}
    {/if}
{/strip}
{/block}
