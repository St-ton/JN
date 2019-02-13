{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if count($LivesucheTop) > 0 || count($LivesucheLast) > 0}
    {include file='snippets/opc_mount_point.tpl' id='opc_livesearch_prepend'}
    {row id="livesearch"}
        {col cols=12 sm=6}
            {card title="{lang key='topsearch'}{$Einstellungen.sonstiges.sonstiges_livesuche_all_top_count}"}
                {listgroup}
                    {if count($LivesucheTop) > 0}
                        {foreach $LivesucheTop as $suche}
                            {listgroupitem class="tag"}
                                {link href="{$suche->cURL}"}{$suche->cSuche}{/link}
                                <span class="badge-pill badge-primary float-right">{$suche->nAnzahlTreffer}</span>
                            {/listgroupitem}
                        {/foreach}
                    {else}
                        {listgroupitem}{lang key='noDataAvailable'}{/listgroupitem}
                    {/if}
                {/listgroup}
            {/card}
        {/col}

        {col cols=12 sm=6}
            {card title="{lang key='lastsearch'}"}
                {listgroup}
                    {if count($LivesucheLast) > 0}
                        {foreach $LivesucheLast as $suche}
                            {listgroupitem class="tag"}
                                {link href="{$suche->cURL}"}{$suche->cSuche}{/link}
                                <span class="badge-pill badge-primary float-right">{$suche->nAnzahlTreffer}</span>
                            {/listgroupitem}
                        {/foreach}
                    {else}
                        {listgroupitem}{lang key='noDataAvailable'}{/listgroupitem}
                    {/if}
                {/listgroup}
            {/card}
        {/col}
    {/row}
    {include file='snippets/opc_mount_point.tpl' id='opc_livesearch_append'}
{/if}
