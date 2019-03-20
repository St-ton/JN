<div class="widget-custom-data">
    <ul class="infolist clearall">
        {foreach $oModul_arr as $oModul}
            {if $oModul->cDefine !== 'SHOP_ERWEITERUNG_RMA'}
                <li class="{if $oModul@first}first{elseif $oModul@last}last{/if}">
                    <p class="key">{$oModul->cName}
                        <span class="value {if $oModul->bActive}success{/if}">
                            {if $oModul->bActive}
                                <span class="label label-success pull-right">{__('active')}</span>
                            {else}
                                <a href="{$oModul->cURL}" target="_blank" rel="noopener">{__('buyNow')}</a>
                            {/if}
                        </span>
                    </p>
                </li>
            {/if}
        {/foreach}
    </ul>
</div>
