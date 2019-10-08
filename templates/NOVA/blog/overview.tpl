{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='blog-overview'}
    {container}
        {block name='blog-overview-heading'}
            {opcMountPoint id='opc_before_heading'}
            <h1>{lang key='news' section='news'}</h1>
        {/block}

        {block name='blog-overview-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}
    {/container}
    {opcMountPoint id='opc_before_filter'}
    {block name='filter'}
        {container}
        {row class='align-items-end mt-6 mb-2'}
            {col cols=12 class='col-xl'}
                {get_static_route id='news.php' assign=routeURL}
                {block name='blog-overview-form'}
                    {form id="frm_filter" name="frm_filter" action=$cCanonicalURL|default:$routeURL}
                        {formgroup}
                            {formrow}
                                {col cols=12 sm=4 lg='auto'}
                                    {select name="nSort" class="onchangeSubmit custom-select mb-3 mb-xl-0" aria=["label"=>"{lang key='newsSort' section='news'}"]}
                                        <option value="-1"{if $nSort === -1} selected{/if}>{lang key='newsSort' section='news'}</option>
                                        <option value="1"{if $nSort === 1} selected{/if}>{lang key='newsSortDateDESC' section='news'}</option>
                                        <option value="2"{if $nSort === 2} selected{/if}>{lang key='newsSortDateASC' section='news'}</option>
                                        <option value="3"{if $nSort === 3} selected{/if}>{lang key='newsSortHeadlineASC' section='news'}</option>
                                        <option value="4"{if $nSort === 4} selected{/if}>{lang key='newsSortHeadlineDESC' section='news'}</option>
                                        <option value="5"{if $nSort === 5} selected{/if}>{lang key='newsSortCommentsDESC' section='news'}</option>
                                        <option value="6"{if $nSort === 6} selected{/if}>{lang key='newsSortCommentsASC' section='news'}</option>
                                    {/select}
                                {/col}
                                {col cols=12 sm=4 lg='auto'}
                                    {select name="cDatum" class="onchangeSubmit custom-select mb-3 mb-xl-0" aria=["label"=>"{lang key='newsDateFilter' section='news'}"]}
                                        <option value="-1"{if $cDatum == -1} selected{/if}>{lang key='newsDateFilter' section='news'}</option>
                                        {if !empty($oDatum_arr)}
                                            {foreach $oDatum_arr as $oDatum}
                                                <option value="{$oDatum->cWert}"{if $cDatum == $oDatum->cWert} selected{/if}>{$oDatum->cName}</option>
                                            {/foreach}
                                        {/if}
                                    {/select}
                                {/col}
                                {lang key='newsCategorie' section='news' assign='cCurrentKategorie'}
                                {if $oNewsCat->getID() > 0}
                                    {assign var=kNewsKategorie value=$oNewsCat->getID()}
                                {else}
                                    {assign var=kNewsKategorie value=$kNewsKategorie|default:0}
                                {/if}
                                {col cols=12 sm=4 lg='auto'}
                                    {select name="nNewsKat" class="onchangeSubmit custom-select mb-3 mb-xl-0" aria=["label"=>"{lang key='newsCategorie' section='news'}"]}
                                        <option value="-1"{if $kNewsKategorie === -1} selected{/if}>{lang key='newsCategorie' section='news'}</option>
                                        {if !empty($oNewsKategorie_arr)}
                                            {assign var=selectedCat value=$kNewsKategorie}
                                            {block name='blog-overview-include-newscategories-recursive'}
                                                {include file='snippets/newscategories_recursive.tpl' i=0 selectedCat=$selectedCat}
                                            {/block}
                                        {/if}
                                    {/select}
                                {/col}
                                {col cols=12 sm=4 lg='auto'}
                                    {select
                                        name="{$oPagination->getId()}_nItemsPerPage"
                                        id="{$oPagination->getId()}_nItemsPerPage"
                                        class="onchangeSubmit custom-select mb-3 mb-xl-0"
                                        aria=["label"=>"{lang key='newsPerSite' section='news'}"]
                                    }
                                        <option value="-1" {if $oPagination->getItemsPerPage() == 0} selected{/if}>
                                            {lang key='showAll'}
                                        </option>
                                        {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
                                            <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected{/if}>
                                                {$nItemsPerPageOption}
                                            </option>
                                        {/foreach}
                                    {/select}
                                {/col}
                            {/formrow}
                        {/formgroup}
                    {/form}
                {/block}
            {/col}
            {col cols=12 class='col-sm-auto ml-auto'}
                {block name='blog-overview-include-pagination-top'}
                    {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['pagi'] noWrapper=true}
                {/block}
            {/col}
        {/row}
        {block name='blog-overview-hr-top'}
            <hr class="mt-n1 mb-5">
        {/block}
        {/container}
    {/block}
    {block name='blog-overview-category'}
        {if $noarchiv === 1}
            {block name='blog-overview-alert-no-archive'}
                {container}
                {alert variant="info"}{lang key='noNewsArchiv' section='news'}.{/alert}
                {/container}
            {/block}
        {elseif !empty($newsItems)}
            {container}
                <div id="newsContent" itemprop="mainEntity" itemscope itemtype="https://schema.org/Blog">
                    {if $oNewsCat->getID() > 0}
                        {block name='blog-overview-subheading'}
                            {opcMountPoint id='opc_before_news_category_heading'}
                            <h2>{$oNewsCat->getName()}</h2>
                        {/block}
                        {block name='blog-overview-preview-image'}
                            {row}
                                {if !empty($oNewsCat->getPreviewImage())}
                                    {col cols=12 sm=8}
                                        {$oNewsCat->getDescription()}
                                    {/col}
                                    {col cols=12 sm=4}
                                        {image webp=true center=true fluid=true
                                            src=$oNewsCat->getImage(\JTL\Media\Image::SIZE_MD)
                                                srcset="{$oNewsCat->getImage(\JTL\Media\Image::SIZE_XS)} 300w,
                                                {$oNewsCat->getImage(\JTL\Media\Image::SIZE_SM)} 600w,
                                                {$oNewsCat->getImage(\JTL\Media\Image::SIZE_MD)} 1200w,
                                                {$oNewsCat->getImage(\JTL\Media\Image::SIZE_LG)} 1800w"
                                            sizes="auto"
                                            alt=$oNewsCat->getName()|escape:'quotes'
                                        }
                                    {/col}
                                {else}
                                    {col sm=12}{$oNewsCat->getDescription()}{/col}
                                {/if}
                            {/row}
                        <hr>
                        {/block}
                        {block name='blog-overview-include-pagination-bottom'}
                            {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['label']}
                        {/block}
                    {/if}
                    {opcMountPoint id='opc_before_news_list'}
                    {row class="mt-4"}
                        {block name='blog-overview-previews'}
                            {foreach $newsItems as $newsItem}
                                {col cols=12 md=6 lg=4}
                                    {block name='blog-overview-include-preview'}
                                        {include file='blog/preview.tpl'}
                                    {/block}
                                {/col}
                            {/foreach}
                        {/block}
                    {/row}
                </div>
                {opcMountPoint id='opc_after_news_list'}
                {block name='blog-overview-include-pagination-bottom'}
                    {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['pagi']}
                {/block}
            {/container}
        {/if}
    {/block}
{/block}
