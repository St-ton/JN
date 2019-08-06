<div class="widget-custom-data">
    <ul class="infolist list-group list-group-flush">
        {foreach $oModul_arr as $module}
            <li class="list-group-item {if $module@first}first{elseif $module@last}last{/if}">
                <p class="key">{$module->cName}
                    <span class="value {if $module->bActive}success{/if}">
                        {if $module->bActive}
                            <span class="label label-success pull-right">{__('active')}</span>
                        {else}
                            <a href="{$module->cURL}" target="_blank" rel="noopener">{__('buyNow')}</a>
                        {/if}
                    </span>
                </p>
            </li>
        {/foreach}
    </ul>
</div>
