<div class="widget-custom-data widget-bots">
    {if is_array($oBots_arr) && count($oBots_arr) > 0}
        <ul class="infolist clearall">
            {foreach name="bots" from=$oBots_arr item=oBots}
                <li {if $smarty.foreach.bots.first}class="first" {elseif $smarty.foreach.bots.last}class="last"{/if}>
                    {if isset($oBots->cName) && $oBots->cName|count_characters > 0}
                        <strong>{$oBots->cName}:</strong>
                    {elseif isset($oBots->cUserAgent) && $oBots->cUserAgent|count_characters > 0}
                        <strong>{$oBots->cUserAgent}:</strong>
                    {else}
                        <strong>Unbekannt:</strong>
                    {/if}
                    <span class="value">{$oBots->nCount}</span>
                </li>
            {/foreach}
        </ul>
    {else}
        <div class="alert alert-info">Keine Statistiken gefunden</div>
    {/if}
</div>