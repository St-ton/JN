{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.sitemap.sitemap_seiten_anzeigen === 'Y'}
    {block name='sitemap-pages'}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_linkgroups_prepend'}
        {card class="sitemap" no-body=true}
            {cardheader}
                <div class="h3">{block name='sitemap-pages-title'}{lang key='sitemapSites'}{/block}</div>
            {/cardheader}
            {cardbody}
            {block name='sitemap-pages-body'}
                {row}
                    {foreach $linkgroups as $linkgroup}
                        {if !empty($linkgroup->getName()) && $linkgroup->getName() !== 'hidden' && !empty($linkgroup->getLinks())}
                            {col sm=6 md=4}
                                {nav vertical=true}
                                    {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier=$linkgroup->getTemplate() tplscope='sitemap'}
                                {/nav}
                            {/col}
                        {/if}
                    {/foreach}
                {/row}
            {/block}
            {/cardbody}
        {/card}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_linkgroups_append'}
    {/block}
{/if}
{if $Einstellungen.sitemap.sitemap_kategorien_anzeigen === 'Y' && isset($oKategorieliste->elemente) && $oKategorieliste->elemente|@count > 0}
    {block name='sitemap-categories'}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_categories_prepend'}
        {card class="sitemap" no-body=true}
            {cardheader}
                <div class="h3">{block name='sitemap-categories-title'}{lang key='sitemapKats'}{/block}</div>
            {/cardheader}
            {cardbody}
            {block name='sitemap-categories-body'}
                {row}
                    {foreach $oKategorieliste->elemente as $oKategorie}
                        {if $oKategorie->Unterkategorien|@count > 0}
                            {col sm=6 md=4}
                                {listgroup}
                                    {listgroupitem}
                                        {link href=$oKategorie->cURLFull title=$oKategorie->cName}
                                            <strong>{$oKategorie->cKurzbezeichnung}</strong>
                                        {/link}
                                    {/listgroupitem}
                                    {foreach $oKategorie->Unterkategorien as $oSubKategorie}
                                        {listgroupitem}
                                            {link href=$oSubKategorie->cURLFull title=$oKategorie->cName}
                                                {$oSubKategorie->cKurzbezeichnung}
                                            {/link}
                                        {/listgroupitem}
                                        {if $oSubKategorie->Unterkategorien|@count > 0}
                                            {listgroupitem}
                                                {listgroup class="sub-categories"}
                                                    {foreach $oSubKategorie->Unterkategorien as $oSubSubKategorie}
                                                        {listgroupitem}
                                                            {link href=$oSubSubKategorie->cURLFull
                                                               title=$oKategorie->cName}
                                                                {$oSubSubKategorie->cKurzbezeichnung}
                                                            {/link}
                                                        {/listgroupitem}
                                                    {/foreach}
                                                {/listgroup}
                                            {/listgroupitem}
                                        {/if}
                                    {/foreach}
                                {/listgroup}
                            {/col}
                        {/if}
                    {/foreach}

                    {col sm=6 md=4}
                        {listgroup}
                            {foreach $oKategorieliste->elemente as $oKategorie}
                                {if $oKategorie->Unterkategorien|@count == 0}
                                    {listgroupitem}
                                        &nbsp;&nbsp;{link href=$oKategorie->cURLFull title=$oKategorie->cName}
                                            {$oKategorie->cKurzbezeichnung}
                                        {/link}
                                    {/listgroupitem}
                                {/if}
                            {/foreach}
                        {/listgroup}
                    {/col}
                {/row}
            {/block}
            {/cardbody}
        {/card}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_categories_append'}
    {/block}
{/if}
{if $Einstellungen.sitemap.sitemap_globalemerkmale_anzeigen === 'Y' && $oGlobaleMerkmale_arr|@count > 0}
    {block name='sitemap-global-attributes'}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_attributes_prepend'}
        {card class="sitemap" no-body=true}
            {cardheader}
                <div class="h3">{block name='sitemap-global-attributes-title'}{lang key='sitemapGlobalAttributes'}{/block}</div>
            {/cardheader}
            {cardbody}
            {block name='sitemap-global-attributes-body'}
                {foreach $oGlobaleMerkmale_arr as $oGlobaleMerkmale}
                    <strong>{$oGlobaleMerkmale->cName}</strong>
                    {listgroup}
                        {foreach $oGlobaleMerkmale->oMerkmalWert_arr as $oGlobaleMerkmaleWerte}
                            {listgroupitem class="p33"}
                                {link href=$oGlobaleMerkmaleWerte->cURL}{$oGlobaleMerkmaleWerte->cWert}{/link}
                            {/listgroupitem}
                        {/foreach}
                    {/listgroup}
                {/foreach}
            {/block}
            {/cardbody}
        {/card}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_attributes_append'}
    {/block}
{/if}
{if $Einstellungen.sitemap.sitemap_hersteller_anzeigen === 'Y' && $oHersteller_arr|@count > 0}
    {block name='sitemap-manufacturer'}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_manufactutrers_prepend'}
        {card no-body=true}
            {cardheader}
                <div class="h3">{block name='sitemap-manufacturer-title'}{lang key='sitemapNanufacturer'}{/block}</div>
            {/cardheader}
            {cardbody}
            {block name='sitemap-manufacturer-body'}
                {listgroup}
                    {foreach $oHersteller_arr as $oHersteller}
                        {listgroupitem}{link href=$oHersteller->cURL}{$oHersteller->cName}{/link}{/listgroupitem}
                    {/foreach}
                {/listgroup}
            {/block}
            {/cardbody}
        {/card}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_manufactutrers_append'}
    {/block}
{/if}
{if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_news_anzeigen === 'Y' && !empty($oNewsMonatsUebersicht_arr) && $oNewsMonatsUebersicht_arr|@count > 0}
    {block name='sitemap-news'}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_prepend'}
        {card no-body=true}
            {cardheader}
                <div class="h3">{block name='sitemap-news-title'}{lang key='sitemapNews'}{/block}</div>
            {/cardheader}
            {cardbody}
            {block name='sitemap-news-body'}
                {row}
                    {foreach $oNewsMonatsUebersicht_arr as $oNewsMonatsUebersicht}
                        {if $oNewsMonatsUebersicht->oNews_arr|@count > 0}
                            {math equation='x-y' x=$oNewsMonatsUebersicht@iteration y=1 assign='i'}
                            {col sm=6 md=4}
                                <strong>{link href=$oNewsMonatsUebersicht->cURLFull}{$oNewsMonatsUebersicht->cName}{/link}</strong>
                                {listgroup}
                                    {foreach $oNewsMonatsUebersicht->oNews_arr as $oNews}
                                        {listgroupitem}&nbsp;&nbsp;{link href=$oNews->cURLFull}{$oNews->cBetreff}{/link}{/listgroupitem}
                                    {/foreach}
                                {/listgroup}
                            {/col}
                        {/if}
                    {/foreach}
                {/row}
            {/block}
            {/cardbody}
        {/card}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_append'}
    {/block}
{/if}
{if $Einstellungen.news.news_benutzen === 'Y'
    && $Einstellungen.sitemap.sitemap_newskategorien_anzeigen === 'Y'
    && !empty($oNewsKategorie_arr)
    && $oNewsKategorie_arr|@count > 0
}
    {block name='sitemap-news-categories'}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_cat_prepend'}
        {card no-body=true}
            {cardheader}
                <div class="h3">{block name='sitemap-news-categories-title'}{lang key='sitemapNewsCats'}{/block}</div>
            {/cardheader}
            {cardbody}
            {block name='sitemap-news-categories-body'}
                {row}
                    {foreach $oNewsKategorie_arr as $oNewsKategorie}
                        {if $oNewsKategorie->oNews_arr|@count > 0}
                            {col sm=6 md=4}
                                <strong>{link href=$oNewsKategorie->cURLFull}{$oNewsKategorie->cName}{/link}</strong>
                                {listgroup}
                                    {foreach $oNewsKategorie->oNews_arr as $oNews}
                                        {listgroupitem}
                                            &nbsp;&nbsp;{link href=$oNews->cURLFull}{$oNews->cBetreff}{/link}
                                        {/listgroupitem}
                                    {/foreach}
                                {/listgroup}
                            {/col}
                        {/if}
                    {/foreach}
                {/row}
            {/block}
            {/cardbody}
        {/card}
        {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_cat_append'}
    {/block}
{/if}
