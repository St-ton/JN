<h1>{lang key='news' section='news'}</h1>

{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}

{include file='snippets/extension.tpl'}

{include file='snippets/opc_mount_point.tpl' id='opc_news_overview_filter_prepend'}
<div class="well well-sm">
    <form id="frm_filter" name="frm_filter" action="{get_static_route id='news.php'}" method="post" class="form-inline text-center">
        {$jtl_token}

        <select name="nSort" onchange="this.form.submit();" class="form-control form-group" aria-label="{lang key='newsSort' section='news'}">
            <option value="-1"{if $nSort == -1} selected{/if}>{lang key='newsSort' section='news'}</option>
            <option value="1"{if $nSort == 1} selected{/if}>{lang key='newsSortDateDESC' section='news'}</option>
            <option value="2"{if $nSort == 2} selected{/if}>{lang key='newsSortDateASC' section='news'}</option>
            <option value="3"{if $nSort == 3} selected{/if}>{lang key='newsSortHeadlineASC' section='news'}</option>
            <option value="4"{if $nSort == 4} selected{/if}>{lang key='newsSortHeadlineDESC' section='news'}</option>
            <option value="5"{if $nSort == 5} selected{/if}>{lang key='newsSortCommentsDESC' section='news'}</option>
            <option value="6"{if $nSort == 6} selected{/if}>{lang key='newsSortCommentsASC' section='news'}</option>
        </select>

        <select name="cDatum" onchange="this.form.submit();" class="form-control form-group" aria-label="{lang key='newsDateFilter' section='news'}">
            <option value="-1"{if $cDatum == -1} selected{/if}>{lang key='newsDateFilter' section='news'}</option>
            {if !empty($oDatum_arr)}
                {foreach $oDatum_arr as $oDatum}
                    <option value="{$oDatum->cWert}"{if $cDatum == $oDatum->cWert} selected{/if}>{$oDatum->cName}</option>
                {/foreach}
            {/if}
        </select>
        {lang key='newsCategorie' section='news' assign='cCurrentKategorie'}
        {if isset($oNewsCat->kNewsKategorie)}
            {assign var='kNewsKategorie' value=(int)$oNewsCat->kNewsKategorie}
        {else}
            {assign var='kNewsKategorie' value=0}
        {/if}
        <select name="nNewsKat" onchange="this.form.submit();" class="form-control form-group" aria-label="{lang key='newsCategorie' section='news'}">
            <option value="-1"{if $kNewsKategorie === -1} selected{/if}>{lang key='newsCategorie' section='news'}</option>
            {if !empty($oNewsKategorie_arr)}
{               {assign var='selectedCat' value=$kNewsKategorie}
                {include file='snippets/newscategories_recursive.tpl' i=0 selectedCat=$selectedCat}
            {/if}
        </select>
        <select class="form-control form-group" name="{$oPagination->getId()}_nItemsPerPage" id="{$oPagination->getId()}_nItemsPerPage"
                onchange="this.form.submit();" aria-label="{lang key='newsPerSite' section='news'}">
            <option value="0" {if $oPagination->getItemsPerPage() == 0} selected{/if}>
                {lang key='newsPerSite' section='news'}
            </option>
            {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
                <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected{/if}>
                    {$nItemsPerPageOption}
                </option>
            {/foreach}
        </select>

        <button name="submitGo" type="submit" value="{lang key='filterGo'}" class="btn btn-default">{lang key='filterGo'}</button>
    </form>
</div>
{include file='snippets/opc_mount_point.tpl' id='opc_news_overview_filter_append'}
{if isset($noarchiv) && $noarchiv}
    <div class="alert alert-info">{lang key='noNewsArchiv' section='news'}.</div>
{elseif !empty($oNewsUebersicht_arr)}
    <div id="newsContent" itemprop="mainEntity" itemscope itemtype="https://schema.org/Blog">
        {if !empty($oNewsCat)}
            <h2>{$oNewsCat->cName}</h2>
            <div class="row">
                {if !empty($oNewsCat->cPreviewImage)}
                    <div class="col-sm-8 col-xs-12">{$oNewsCat->cBeschreibung}</div>
                    <div class="col-sm-4 col-xs-12"><img src="{$oNewsCat->cPreviewImage}" class="img-responsive center-block"></div>
                {else}
                    <div class="col-sm-12">{$oNewsCat->cBeschreibung}</div>
                {/if}
            </div>
            <hr>
            {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['label']}
        {/if}
        {foreach $oNewsUebersicht_arr as $oNewsUebersicht}
            {include file='blog/preview.tpl'}
            {include file='snippets/opc_mount_point.tpl' id='opc_news_overview_preview_append'|cat:$oNewsUebersicht@iteration}
        {/foreach}
    </div>
    {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['pagi']}
{/if}
{include file='snippets/opc_mount_point.tpl' id='opc_news_overview_preview_append'}