<div itemscope itemtype="https://schema.org/Article" class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title">
            <a href="{$oNewsUebersicht->cURL}">
                <strong><span itemprop="headline">{$oNewsUebersicht->cBetreff}</span></strong>
            </a>
            <div class="text-muted pull-right v-box">
                {if empty($oNewsUebersicht->dGueltigVon)}{assign var="dDate" value=$oNewsUebersicht->dErstellt}{else}{assign var="dDate" value=$oNewsUebersicht->dGueltigVon}{/if}
                {if !empty($Einstellungen.global.global_shopname)}
                    <span itemprop="publisher" class="hidden">{$Einstellungen.global.global_shopname}</span>
                {/if}
                {if (isset($oNewsUebersicht->oAuthor))}
                    {include file="snippets/author.tpl" oAuthor=$oNewsUebersicht->oAuthor}&nbsp;-&nbsp;
                {/if}
                {if isset($oNewsUebersicht->dErstellt)}<time itemprop="dateModified" class="hidden">{$oNewsUebersicht->dErstellt}</time>{/if}
                <time itemprop="datePublished" datetime="{$dDate}" class="hidden">{$dDate}</time><span class="v-box">{$oNewsUebersicht->dErstellt_de}</span>
                {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                    |
                    <a class="v-box" href="{$oNewsUebersicht->cURL}#comments" title="{lang key="readComments" section="news"}"><span itemprop="commentCount">{$oNewsUebersicht->nNewsKommentarAnzahl}</span>
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
        <div class=" row">
            {if !empty($oNewsUebersicht->cPreviewImage)}
                <div class="col-sm-4 col-xs-12">
                    <a href="{$oNewsUebersicht->cURL}">
                        <img itemprop="image" src="{$ShopURL}/{$oNewsUebersicht->cPreviewImage}" alt="" class="img-responsive center-block"/>
                    </a>
                </div>
            {/if}
            {if $oNewsUebersicht->cVorschauText|strip_tags|count_characters > 0}
                <div itemprop="description" class="{if !empty($oNewsUebersicht->cPreviewImage)}col-sm-8 {/if}col-xs-12">
                    {if $oNewsUebersicht->cText|strip_tags|count_characters < 200}
                        {$oNewsUebersicht->cVorschauText}
                    {else}
                        {$oNewsUebersicht->cText|strip_tags|truncate:200:""}
                    {/if}
                    <div class="row">
                        <div class="col-xs-12">
                            <span class="pull-right">{$oNewsUebersicht->cMehrURL}</span>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>