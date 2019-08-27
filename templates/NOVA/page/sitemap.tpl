{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-sitemap'}
    {container}
        {if $Einstellungen.sitemap.sitemap_seiten_anzeigen === 'Y'}
            {block name='page-sitemap-pages'}
                {opcMountPoint id='opc_before_pages'}
                {card header={lang key='sitemapSites'} class="mb-5"}
                    {block name='page-sitemap-pages-content'}
                        {row}
                            {foreach $linkgroups as $linkgroup}
                                {if !empty($linkgroup->getName()) && $linkgroup->getName() !== 'hidden' && !empty($linkgroup->getLinks())}
                                    {col cols=12 md=4 lg=3}
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
            {/block}
        {/if}
        {if $Einstellungen.sitemap.sitemap_kategorien_anzeigen === 'Y' && isset($oKategorieliste->elemente) && $oKategorieliste->elemente|@count > 0}
            {block name='page-sitemap-categories'}
                {opcMountPoint id='opc_before_categories'}
                {card header={lang key='sitemapKats'} class="mb-5"}
                    {block name='page-sitemap-categories-content'}
                        {row}
                            {foreach $oKategorieliste->elemente as $oKategorie}
                                {if $oKategorie->Unterkategorien|@count > 0}
                                    {col cols=12 md=4 lg=3}
                                        <ul class="list-unstyled">
                                            <li class="py-2">
                                                {link href=$oKategorie->cURLFull title=$oKategorie->cName class="nice-deco"}
                                                    <strong>{$oKategorie->cKurzbezeichnung}</strong>
                                                {/link}
                                            </li>
                                            {foreach $oKategorie->Unterkategorien as $oSubKategorie}
                                                <li class="py-2">
                                                    {link href=$oSubKategorie->cURLFull title=$oKategorie->cName class="nice-deco"}
                                                        {$oSubKategorie->cKurzbezeichnung}
                                                    {/link}
                                                </li>
                                                {if $oSubKategorie->Unterkategorien|@count > 0}
                                                    <li class="py-2">
                                                        <ul class="sub-categories list-unstyled pl-4">
                                                            {foreach $oSubKategorie->Unterkategorien as $oSubSubKategorie}
                                                                <li class="py-2">
                                                                    {link href=$oSubSubKategorie->cURLFull
                                                                       title=$oKategorie->cName class="nice-deco"}
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

                            {col cols=12 md=4 lg=3}
                                <ul class="list-unstyled">
                                    {foreach $oKategorieliste->elemente as $oKategorie}
                                        {if $oKategorie->Unterkategorien|@count == 0}
                                            <li class="py-2">
                                                &nbsp;&nbsp;{link href=$oKategorie->cURLFull title=$oKategorie->cName class="nice-deco"}
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
            {/block}
        {/if}
        {if $Einstellungen.sitemap.sitemap_hersteller_anzeigen === 'Y' && $oHersteller_arr|@count > 0}
            {block name='page-sitemap-manufacturer'}
                {opcMountPoint id='opc_before_manufacturers'}
                {card header={lang key='sitemapNanufacturer'} class="mb-5"}
                    {block name='page-sitemap-manufacturer-content'}
                        {row}
                            {foreach $oHersteller_arr as $oHersteller}
                                {col cols=12 md=4 lg=3 class="py-2"}
                                    {link href=$oHersteller->cURL  class="nice-deco"}{$oHersteller->cName}{/link}
                                {/col}
                            {/foreach}
                        {/row}
                    {/block}
                {/card}
            {/block}
        {/if}
        {if $Einstellungen.news.news_benutzen === 'Y' && $Einstellungen.sitemap.sitemap_news_anzeigen === 'Y' && !empty($oNewsMonatsUebersicht_arr) && $oNewsMonatsUebersicht_arr|@count > 0}
            {block name='page-sitemap-news'}
                {opcMountPoint id='opc_before_news'}
                {card header={lang key='sitemapNews'} class="mb-5"}
                    {block name='page-sitemap-news-content'}
                        {row}
                            {foreach $oNewsMonatsUebersicht_arr as $oNewsMonatsUebersicht}
                                {if $oNewsMonatsUebersicht->oNews_arr|@count > 0}
                                    {math equation='x-y' x=$oNewsMonatsUebersicht@iteration y=1 assign='i'}
                                    {col cols=12 md=4 lg=3}
                                        <strong>{link href=$oNewsMonatsUebersicht->cURLFull class="nice-deco"}{$oNewsMonatsUebersicht->cName}{/link}</strong>
                                        <ul class="list-unstyled">
                                            {foreach $oNewsMonatsUebersicht->oNews_arr as $oNews}
                                                <li class="py-2">&nbsp;&nbsp;{link href=$oNews->cURLFull class="nice-deco"}{$oNews->cBetreff}{/link}</li>
                                            {/foreach}
                                        </ul>
                                    {/col}
                                {/if}
                            {/foreach}
                        {/row}
                    {/block}
                {/card}
            {/block}
        {/if}
        {if $Einstellungen.news.news_benutzen === 'Y'
            && $Einstellungen.sitemap.sitemap_newskategorien_anzeigen === 'Y'
            && !empty($oNewsKategorie_arr)
            && $oNewsKategorie_arr|@count > 0
        }
            {block name='page-sitemap-news-categories'}
                {opcMountPoint id='opc_before_news_categories'}
                {card header={lang key='sitemapNewsCats'} class="mb-5"}
                    {block name='page-sitemap-news-categories-content'}
                        {row}
                            {foreach $oNewsKategorie_arr as $oNewsKategorie}
                                {if $oNewsKategorie->oNews_arr|@count > 0}
                                    {col cols=12 md=4 lg=3}
                                        <strong>{link href=$oNewsKategorie->cURLFull}{$oNewsKategorie->cName}{/link}</strong>
                                        <ul class="list-unstyled">
                                            {foreach $oNewsKategorie->oNews_arr as $oNews}
                                                <li class="py-2">
                                                    &nbsp;&nbsp;{link href=$oNews->cURLFull class="nice-deco"}{$oNews->cBetreff}{/link}
                                                </li>
                                            {/foreach}
                                        </ul>
                                    {/col}
                                {/if}
                            {/foreach}
                        {/row}
                    {/block}
                {/card}
            {/block}
        {/if}
    {/container}
{/block}
