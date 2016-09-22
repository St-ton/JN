{if is_array($oNews_arr)}
    <ul class="linklist">
        {foreach name="news" from=$oNews_arr item=oNews}
            <li>
                <p>
                    <i class="fa fa-info"></i> <span class="date">{$oNews->dErstellt|date_format:"%d.%m.%Y"}</span>
                    <a href="{$oNews->cUrlExt|urldecode}" title="{$oNews->cBetreff}" target="_blank">{$oNews->cBetreff|truncate:'50':'...'}</a>
                </p>
            </li>
        {/foreach}
    </ul>
{else}
    <div class="widget-container"><div class="alert alert-error">Keine Daten verf&uuml;gbar</div></div>
{/if}