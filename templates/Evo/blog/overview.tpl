<h1>{lang key="news" section="news"}</h1>

{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}

{include file="snippets/extension.tpl"}

<div class="well well-sm">
    <form id="frm_filter" name="frm_filter" action="news.php" method="post" class="form-inline text-center">
        {$jtl_token}

        <select name="nSort" onchange="this.form.submit();" class="form-control">
            <option value="-1"{if $nSort == -1} selected{/if}>{lang key="newsSort" section="news"}</option>
            <option value="1"{if $nSort == 1} selected{/if}>{lang key="newsSortDateDESC" section="news"}</option>
            <option value="2"{if $nSort == 2} selected{/if}>{lang key="newsSortDateASC" section="news"}</option>
            <option value="3"{if $nSort == 3} selected{/if}>{lang key="newsSortHeadlineASC" section="news"}</option>
            <option value="4"{if $nSort == 4} selected{/if}>{lang key="newsSortHeadlineDESC" section="news"}</option>
            <option value="5"{if $nSort == 5} selected{/if}>{lang key="newsSortCommentsDESC" section="news"}</option>
            <option value="6"{if $nSort == 6} selected{/if}>{lang key="newsSortCommentsASC" section="news"}</option>
        </select>

        <select name="cDatum" onchange="this.form.submit();" class="form-control">
            <option value="-1"{if $cDatum == -1} selected{/if}>{lang key="newsDateFilter" section="news"}</option>
            {if !empty($oDatum_arr)}
                {foreach name="datum" from=$oDatum_arr item=oDatum}
                    <option value="{$oDatum->cWert}"{if $cDatum == $oDatum->cWert} selected{/if}>{$oDatum->cName}</option>
                {/foreach}
            {/if}
        </select>

        {lang key="newsCategorie" section="news" assign="cCurrentKategorie"}
        <select name="nNewsKat" onchange="this.form.submit();" class="form-control">
            <option value="-1"{if $nNewsKat == -1} selected{/if}>{lang key="newsCategorie" section="news"}</option>
            {if !empty($oNewsKategorie_arr)}
                {foreach name="newskats" from=$oNewsKategorie_arr item=oNewsKategorie}
                    {if $nNewsKat == $oNewsKategorie->kNewsKategorie}{assign var="cCurrentKategorie" value=$oNewsKategorie->cName}{/if}
                    <option value="{$oNewsKategorie->kNewsKategorie}"{if $nNewsKat == $oNewsKategorie->kNewsKategorie} selected{/if}>{$oNewsKategorie->cName}</option>
                {/foreach}
            {/if}
        </select>

        <select name="nAnzahl" onchange="this.form.submit();" class="form-control">
            <option value="-1"{if $smarty.session.NewsNaviFilter->nAnzahl == -1} selected{/if}>{lang key="newsPerSite" section="news"}</option>
            <option value="2"{if $smarty.session.NewsNaviFilter->nAnzahl == 2} selected{/if}>2</option>
            <option value="5"{if $smarty.session.NewsNaviFilter->nAnzahl == 5} selected{/if}>5</option>
            <option value="10"{if $smarty.session.NewsNaviFilter->nAnzahl == 10} selected{/if}>10</option>
            <option value="20"{if $smarty.session.NewsNaviFilter->nAnzahl == 20} selected{/if}>20</option>
        </select>

        <input name="submitGo" type="submit" value="{lang key="filterGo" section="global"}" class="btn btn-default" />
    </form>
</div>

{if isset($noarchiv) && $noarchiv}
    <div class="alert alert-info">{lang key="noNewsArchiv" section="news"}.</div>
{else}
    {if !empty($oNewsUebersicht_arr)}
        <div id="newsContent">
            {if !empty($cCurrentKategorie)}
                <h2>{$cCurrentKategorie}</h2>
                <hr>
            {/if}
            {foreach name=uebersicht from=$oNewsUebersicht_arr item=oNewsUebersicht}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <a href="{$oNewsUebersicht->cURL}">
                                <strong>{$oNewsUebersicht->cBetreff}</strong>
                            </a>
                            <div class="text-muted pull-right">{$oNewsUebersicht->dErstellt_de}
                                {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                                    |
                                    <a href="{$oNewsUebersicht->cURL}#comments" title="{lang key="readComments" section="news"}">{$oNewsUebersicht->nNewsKommentarAnzahl}
                                        {if $oNewsUebersicht->nNewsKommentarAnzahl == 1}
                                            {lang key="newsComment" section="news"}
                                        {else}
                                            {lang key="newsComments" section="news"}
                                        {/if}
                                    </a>
                                {/if}
                            </div>
                        </div>

                    </div>
                    <div class="panel-body">
                        {if !empty($oNewsUebersicht->cPreviewImage)}
                            <div class="col-lg-4 col-xs-6">
                                <a href="{$oNewsUebersicht->cURL}">
                                    <img src="{$ShopURL}/{$oNewsUebersicht->cPreviewImage}" alt="" class="img-responsive" />
                                </a>
                            </div>
                        {/if}
                        <div class="news-preview panel-strap"">
                            {if $oNewsUebersicht->cVorschauText|count_characters > 0}
                                {$oNewsUebersicht->cVorschauText}<span class="read-more">{$oNewsUebersicht->cMehrURL}</span>
                            {elseif $oNewsUebersicht->cText|strip_tags|count_characters > 200}
                                {$oNewsUebersicht->cText|strip_tags|truncate:200:""}<span class="read-more">{$oNewsUebersicht->cMehrURL}</span>
                            {else}
                                {$oNewsUebersicht->cText}
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}

    {if isset($oBlaetterNavi->nAktiv) && $oBlaetterNavi->nAktiv == 1}
        <div class="row">
            <div class="col-xs-7 col-md-8 col-lg-9">
                <ul class="pagination">
                    {if $oBlaetterNavi->nAktuelleSeite > 1}
                        <li>
                            <a href="news.php?s={$oBlaetterNavi->nVoherige}">&laquo; {lang key="previous" section="productOverview"}</a>
                        </li>
                    {/if}
                    {if $oBlaetterNavi->nAnfang != 0}
                        <li><a href="news.php?s={$oBlaetterNavi->nAnfang}">{$oBlaetterNavi->nAnfang}</a> ...</li>
                    {/if}
                    {foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
                        {if $oBlaetterNavi->nAktuelleSeite == $Blatt}
                            <li class="active"><span>{$Blatt}</span></li>
                        {else}
                            <li><a href="news.php?s={$Blatt}">{$Blatt}</a></li>
                        {/if}
                    {/foreach}
                    {if $oBlaetterNavi->nEnde != 0}
                        <li> ... <a href="news.php?s={$oBlaetterNavi->nEnde}">{$oBlaetterNavi->nEnde}</a></li>
                    {/if}
                    {if $oBlaetterNavi->nAktuelleSeite < $oBlaetterNavi->nSeiten}
                        <li>
                            <a href="news.php?s={$oBlaetterNavi->nNaechste}">{lang key="next" section="productOverview"} &raquo;</a>
                        </li>
                    {/if}
                </ul>
            </div>
            <div class="col-xs-6 col-md-4 col-lg-3 text-right">
                <div class="pagination pagination-text">
                    {$oBlaetterNavi->nVon}
                    - {$oBlaetterNavi->nBis} {lang key="from" section="product rating"} {$oBlaetterNavi->nAnzahl}
                </div>
            </div>
        </div>
    {/if}
{/if}