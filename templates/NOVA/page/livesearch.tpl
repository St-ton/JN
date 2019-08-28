{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-livesearch'}
    {if count($LivesucheTop) > 0 || count($LivesucheLast) > 0}
        {container}
            {opcMountPoint id='opc_before_livesearch'}
            {row id="livesearch" class="mt-4"}
                {block name='page-livesearch-top-searches'}
                    {col}
                        <div class="h2 font-weight-bold">{lang key='topsearch'}{$Einstellungen.sonstiges.sonstiges_livesuche_all_top_count}</div>
                        <ul class="list-unstyled">
                            {if count($LivesucheTop) > 0}
                                {foreach $LivesucheTop as $suche}
                                    <li class="my-2">
                                        {link href=$suche->cURL}{$suche->cSuche}{/link}, {lang key='matches'}:
                                        <span class="badge-pill badge-primary">{$suche->nAnzahlTreffer}</span>
                                    </li>
                                {/foreach}
                            {else}
                                <li class="my-2">{lang key='noDataAvailable'}</li>
                            {/if}
                        </ul>
                    {/col}
                {/block}
                {block name='page-livesearch-latest-searches'}
                    {col}
                        <div class="h2 font-weight-bold">{lang key='lastsearch'}</div>
                        <ul class="list-unstyled">
                            {if count($LivesucheLast) > 0}
                                {foreach $LivesucheLast as $suche}
                                    <li class="my-2">
                                        {link href=$suche->cURL}{$suche->cSuche}{/link}, {lang key='matches'}:
                                        <span class="badge-pill badge-primary">{$suche->nAnzahlTreffer}</span>
                                    </li>
                                {/foreach}
                            {else}
                                <li class="my-2">{lang key='noDataAvailable'}</li>
                            {/if}
                        </ul>
                    {/col}
                {/block}
            {/row}
        {/container}
    {/if}
{/block}
