{if is_array($oNews_arr)}
    <ul class="linklist">
        {foreach name="news" from=$oNews_arr item=oNews}
            <li>
                <img src="https://images.jtl-software.de/shop/icon_info.gif" alt="" title="{$oNews->cBetreff}" />
                <a href="{$oNews->cUrlExt|urldecode}" title="{$oNews->cBetreff}" target="_blank">{$oNews->cBetreff|truncate:'50':'...'}</a>
                <span class="date">{$oNews->dGueltigVon|date_format:"%d.%m.%Y"}</span>
            </li>
        {/foreach}
    </ul>
{else}
    <div class="widget-container"><div class="alert alert-error">Keine Daten verf&uuml;gbar</div></div>
{/if}