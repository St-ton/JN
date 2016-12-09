<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $filterOptions as $filterOption}
        <li>
            <a rel="nofollow" href="{$filterOption->cURL}">
                <span class="value">
                    <i class="fa {if isset($NaviFilter->xxx->yyy) && $NaviFilter->xxx->yyy == $filterOption->cName}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                    {$filterOption->cName|escape:'html'}
                    <span class="badge pull-right">{$filterOption->nAnzahl}</span>
                </span>
            </a>
        </li>
    {/foreach}
</ul>