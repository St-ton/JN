{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-sitemap'}
    {if $Einstellungen.sitemap.sitemap_seiten_anzeigen === 'Y'}
        {block name='page-sitemap-pages'}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_linkgroups_prepend'}
            {card header={lang key='sitemapSites'} class="mb-5"}
                {block name='page-sitemap-pages-content'}
                    {row}
                        {foreach $linkgroups as $linkgroup}
                            {if !empty($linkgroup->getName()) && $linkgroup->getName() !== 'hidden' && !empty($linkgroup->getLinks())}
                                {col cols=6 md=4 lg=3}
                                    {nav vertical=true}
                                        {block name='page-sitemap-include-linkgroup-list'}
                                            {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier=$linkgroup->getTemplate() tplscope='sitemap'}
                                        {/block}
                                    {/nav}
                                {/col}
                            {/if}
                        {/foreach}
                    {/row}
                {/block}
            {/card}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_linkgroups_append'}
        {/block}
    {/if}
    {if $Einstellungen.sitemap.sitemap_kategorien_anzeigen === 'Y' && isset($oKategorieliste->elemente) && $oKategorieliste->elemente|@count > 0}
        {block name='page-sitemap-categories'}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_categories_prepend'}
            {card header={lang key='sitemapKats'} class="mb-5"}
                {block name='page-sitemap-categories-content'}
                    {row}
                        {foreach $oKategorieliste->elemente as $oKategorie}
                            {if $oKategorie->Unterkategorien|@count > 0}
                                {col cols=6 md=4 lg=3}
                                    <ul class="list-unstyled">
                                        <li class="my-2">
                                            {link href=$oKategorie->cURLFull title=$oKategorie->cName}
                                                <strong>{$oKategorie->cKurzbezeichnung}</strong>
                                            {/link}
                                        </li>
                                        {foreach $oKategorie->Unterkategorien as $oSubKategorie}
                                            <li class="my-2">
                                                {link href=$oSubKategorie->cURLFull title=$oKategorie->cName}
                                                    {$oSubKategorie->cKurzbezeichnung}
                                                {/link}
                                            </li>
                                            {if $oSubKategorie->Unterkategorien|@count > 0}
                                                <li class="my-2">
                                                    <ul class="sub-categories list-unstyled pl-4">
                                                        {foreach $oSubKategorie->Unterkategorien as $oSubSubKategorie}
                                                            <li class="my-2">
                                                                {link href=$oSubSubKategorie->cURLFull
                                                                   title=$oKategorie->cName}
                                                                    {$oSubSubKategorie->cKurzbezeichnung}
                                                                {/link}
                                                            </li>
                                                        {/foreach}
                                                    </ul>
                                                </li>
                                            {/if}
                                        {/foreach}
                                    </ul>
                                {/col}
                            {/if}
                        {/foreach}

                        {col cols=6 md=4 lg=3}
                            <ul class="list-unstyled">
                                {foreach $oKategorieliste->elemente as $oKategorie}
                                    {if $oKategorie->Unterkategorien|@count == 0}
                                        <li class="my-2">
                                            &nbsp;&nbsp;{link href=$oKategorie->cURLFull title=$oKategorie->cName}
                                                {$oKategorie->cKurzbezeichnung}
                                            {/link}
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                        {/col}
                    {/row}
                {/block}
            {/card}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_categories_append'}
        {/block}
    {/if}
    {if $Einstellungen.sitemap.sitemap_globalemerkmale_anzeigen === 'Y' && $oGlobaleMerkmale_arr|@count > 0}
        {block name='page-sitemap-global-attributes'}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_attributes_prepend'}
            {card header={lang key='sitemapGlobalAttributes'} class="mb-5"}
                {block name='page-sitemap-global-attributes-content'}
                    {row}
                        {foreach $oGlobaleMerkmale_arr as $oGlobaleMerkmale}
                            {col cols=6 md=4 lg=3}
                                <strong>{$oGlobaleMerkmale->cName}</strong>
                                <ul class="list-unstyled">
                                    {foreach $oGlobaleMerkmale->oMerkmalWert_arr as $oGlobaleMerkmaleWerte}
                                        <li class="my-2">
                                            {link href=$oGlobaleMerkmaleWerte->cURL}{$oGlobaleMerkmaleWerte->cWert}{/link}
                                        </li>
                                    {/foreach}
                                </ul>
                            {/col}
                        {/foreach}
                    {/row}
                {/block}
            {/card}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_attributes_append'}
        {/block}
    {/if}
    {if $Einstellungen.sitemap.sitemap_hersteller_anzeigen === 'Y' && $oHersteller_arr|@count > 0}
        {block name='page-sitemap-manufacturer'}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_manufactutrers_prepend'}
            {card header={lang key='sitemapNanufacturer'} class="mb-5"}
                {block name='page-sitemap-manufacturer-content'}
                    {row}
                        {foreach $oHersteller_arr as $oHersteller}
                            {col cols=6 md=4 lg=3 class="my-1"}
                                {link href=$oHersteller->cURL}{$oHersteller->cName}{/link}
                            {/col}
                        {/foreach}
                    {/row}
                {/block}
            {/card}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_manufactutrers_append'}
        {/block}
    {/if}
    {if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_news_anzeigen === 'Y' && !empty($oNewsMonatsUebersicht_arr) && $oNewsMonatsUebersicht_arr|@count > 0}
        {block name='page-sitemap-news'}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_prepend'}
            {card header={lang key='sitemapNews'} class="mb-5"}
                {block name='page-sitemap-news-content'}
                    {row}
                        {foreach $oNewsMonatsUebersicht_arr as $oNewsMonatsUebersicht}
                            {if $oNewsMonatsUebersicht->oNews_arr|@count > 0}
                                {math equation='x-y' x=$oNewsMonatsUebersicht@iteration y=1 assign='i'}
                                {col cols=6 md=4 lg=3}
                                    <strong>{link href=$oNewsMonatsUebersicht->cURLFull}{$oNewsMonatsUebersicht->cName}{/link}</strong>
                                    <ul class="list-unstyled">
                                        {foreach $oNewsMonatsUebersicht->oNews_arr as $oNews}
                                            <li class="my-2">&nbsp;&nbsp;{link href=$oNews->cURLFull}{$oNews->cBetreff}{/link}</li>
                                        {/foreach}
                                    </ul>
                                {/col}
                            {/if}
                        {/foreach}
                    {/row}
                {/block}
            {/card}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_append'}
        {/block}
    {/if}
    {if $Einstellungen.news.news_benutzen === 'Y'
        && $Einstellungen.sitemap.sitemap_newskategorien_anzeigen === 'Y'
        && !empty($oNewsKategorie_arr)
        && $oNewsKategorie_arr|@count > 0
    }
        {block name='page-sitemap-news-categories'}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_cat_prepend'}
            {card header={lang key='sitemapNewsCats'} class="mb-5"}
                {block name='page-sitemap-news-categories-content'}
                    {row}
                        {foreach $oNewsKategorie_arr as $oNewsKategorie}
                            {if $oNewsKategorie->oNews_arr|@count > 0}
                                {col cols=6 md=4 lg=3}
                                    <strong>{link href=$oNewsKategorie->cURLFull}{$oNewsKategorie->cName}{/link}</strong>
                                    <ul class="list-unstyled">
                                        {foreach $oNewsKategorie->oNews_arr as $oNews}
                                            <li class="my-2">
                                                &nbsp;&nbsp;{link href=$oNews->cURLFull}{$oNews->cBetreff}{/link}
                                            </li>
                                        {/foreach}
                                    </ul>
                                {/col}
                            {/if}
                        {/foreach}
                    {/row}
                {/block}
            {/card}
            {include file='snippets/opc_mount_point.tpl' id='opc_sitemap_news_cat_append'}
        {/block}
    {/if}
{/block}
