{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='blog-overview'}
    {container}
        {block name='blog-overview-heading'}
            {include file='snippets/opc_mount_point.tpl' id='opc_before_heading'}
            <h1>{lang key='news' section='news'}</h1>
        {/block}

        {block name='blog-overview-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}
    {/container}
    {include file='snippets/opc_mount_point.tpl' id='opc_before_filter'}
    {block name='filter'}
        {container}
        {row}
            {col cols="auto"}
            {get_static_route id='news.php' assign=routeURL}
                {block name='blog-overview-form'}
                    {form id="frm_filter" name="frm_filter" action=$cCanonicalURL|default:$routeURL class="form-inline mb-4"}
                        {formgroup}
                            {select name="nSort" class="onchangeSubmit mb-2" aria=["label"=>"{lang key='newsSort' section='news'}"]}
                                <option value="-1"{if $nSort === -1} selected{/if}>{lang key='newsSort' section='news'}</option>
                                <option value="1"{if $nSort === 1} selected{/if}>{lang key='newsSortDateDESC' section='news'}</option>
                                <option value="2"{if $nSort === 2} selected{/if}>{lang key='newsSortDateASC' section='news'}</option>
                                <option value="3"{if $nSort === 3} selected{/if}>{lang key='newsSortHeadlineASC' section='news'}</option>
                                <option value="4"{if $nSort === 4} selected{/if}>{lang key='newsSortHeadlineDESC' section='news'}</option>
                                <option value="5"{if $nSort === 5} selected{/if}>{lang key='newsSortCommentsDESC' section='news'}</option>
                                <option value="6"{if $nSort === 6} selected{/if}>{lang key='newsSortCommentsASC' section='news'}</option>
                            {/select}

                            {select name="cDatum" class="onchangeSubmit mb-2" aria=["label"=>"{lang key='newsDateFilter' section='news'}"]}
                                <option value="-1"{if $cDatum == -1} selected{/if}>{lang key='newsDateFilter' section='news'}</option>
                                {if !empty($oDatum_arr)}
                                    {foreach $oDatum_arr as $oDatum}
                                        <option value="{$oDatum->cWert}"{if $cDatum == $oDatum->cWert} selected{/if}>{$oDatum->cName}</option>
                                    {/foreach}
                                {/if}
                            {/select}
                            {lang key='newsCategorie' section='news' assign='cCurrentKategorie'}
                            {if $oNewsCat->getID() > 0}
                                {assign var=kNewsKategorie value=$oNewsCat->getID()}
                            {else}
                                {assign var=kNewsKategorie value=$kNewsKategorie|default:0}
                            {/if}
                            {select name="nNewsKat" class="onchangeSubmit mb-2" aria=["label"=>"{lang key='newsCategorie' section='news'}"]}
                                <option value="-1"{if $kNewsKategorie === -1} selected{/if}>{lang key='newsCategorie' section='news'}</option>
                                {if !empty($oNewsKategorie_arr)}
                                    {assign var=selectedCat value=$kNewsKategorie}
                                    {block name='blog-overview-include-newscategories-recursive'}
                                        {include file='snippets/newscategories_recursive.tpl' i=0 selectedCat=$selectedCat}
                                    {/block}
                                {/if}
                            {/select}
                            {select
                                name="{$oPagination->getId()}_nItemsPerPage"
                                id="{$oPagination->getId()}_nItemsPerPage"
                                class="onchangeSubmit mb-2"
                                aria=["label"=>"{lang key='newsPerSite' section='news'}"]
                            }
                                <option value="-1" {if $oPagination->getItemsPerPage() == 0} selected{/if}>
                                    {lang key='newsPerSite' section='news'}
                                </option>
                                {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
                                    <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected{/if}>
                                        {$nItemsPerPageOption}
                                    </option>
                                {/foreach}
                            {/select}
                            {block name='blog-overview-form-submit'}
                                {button name="submitGo" type="submit" value="1" class="mb-2"}{lang key='filterGo'}{/button}
                            {/block}
                        {/formgroup}
                    {/form}
                {/block}
            {/col}
        {/row}
        {/container}
    {/block}
    {block name='blog-overview-category'}
        {if $noarchiv === 1}
            {block name='blog-overview-alert-no-archive'}
                {container}
                {alert variant="info"}{lang key='noNewsArchiv' section='news'}.{/alert}
                {/container}
            {/block}
        {elseif !empty($oNewsUebersicht_arr)}
            {container}
                <div id="newsContent" itemprop="mainEntity" itemscope itemtype="https://schema.org/Blog">
                    {if $oNewsCat->getID() > 0}
                        {block name='blog-overview-subheading'}
                            {include file='snippets/opc_mount_point.tpl' id='opc_before_news_category_heading'}
                            <h2>{$oNewsCat->getName()}</h2>
                        {/block}
                        {block name='blog-overview-preview-image'}
                            {row}
                                {if !empty($oNewsCat->getPreviewImage())}
                                    {col cols=12 sm=8}{$oNewsCat->getDescription()}{/col}
                                    {col cols=12 sm=4}{image src=$oNewsCat->getPreviewImage() center=true fluid=true}{/col}
                                {else}
                                    {col sm=12}{$oNewsCat->getDescription()}{/col}
                                {/if}
                            {/row}
                        <hr>
                        {/block}
                        {block name='blog-overview-include-pagination'}
                            {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['label']}
                        {/block}
                    {/if}
                    {include file='snippets/opc_mount_point.tpl' id='opc_before_news_list'}
                    {row class="mt-4"}
                        {block name='blog-overview-previews'}
                            {foreach $oNewsUebersicht_arr as $oNewsUebersicht}
                                {col cols=12 md=6}
                                    {block name='blog-overview-include-preview'}
                                        {include file='blog/preview.tpl'}
                                    {/block}
                                {/col}
                            {/foreach}
                        {/block}
                    {/row}
                </div>
                {include file='snippets/opc_mount_point.tpl' id='opc_after_news_list'}
                {block name='blog-overview-include-pagination'}
                    {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['pagi']}
                {/block}
            {/container}
        {/if}
    {/block}
{/block}
