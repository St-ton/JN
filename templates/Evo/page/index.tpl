{if !empty($cFehler)}
    <div class="alert alert-danger">{$cFehler}</div>
{/if}

{include file="productwizard/index.tpl"}

{if isset($StartseiteBoxen) && $StartseiteBoxen|@count > 0}
    <hr>
    {assign var='moreLink' value=null}
    {assign var='moreTitle' value=null}
    {foreach name=startboxen from=$StartseiteBoxen item=Box}
        {if isset($Box->Artikel->elemente) && count($Box->Artikel->elemente)>0 && isset($Box->cURL)}
            {if $Box->name === 'TopAngebot'}
                {lang key="topOffer" section="global" assign='title'}
                {lang key='showAllTopOffers' section='global' assign='moreTitle'}
            {elseif $Box->name === 'Sonderangebote'}
                {lang key="specialOffer" section="global" assign='title'}
                {lang key='showAllSpecialOffers' section='global' assign='moreTitle'}
            {elseif $Box->name === 'NeuImSortiment'}
                {lang key="newProducts" section="global" assign='title'}
                {lang key='showAllNewProducts' section='global' assign='moreTitle'}
            {elseif $Box->name === 'Bestseller'}
                {lang key="bestsellers" section="global" assign='title'}
                {lang key='showAllBestsellers' section='global' assign='moreTitle'}
            {/if}
            {assign var='moreLink' value=$Box->cURL}
            {include file='snippets/product_slider.tpl' productlist=$Box->Artikel->elemente title=$title hideOverlays=true moreLink=$moreLink moreTitle=$moreTitle}
        {/if}
    {/foreach}
{/if}

{block name="index-additional"}
{if isset($oNews_arr) && $oNews_arr|@count > 0}
    <hr>
    <h2>{lang key="news" section="news"}</h2>
    {foreach name=news from=$oNews_arr item=oNews}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a href="{$oNews->cURL}">{$oNews->cBetreff}</a>&nbsp;-&nbsp;
                    {$oNews->dErstellt_de}{if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                    |
                    <a href="{$oNews->cURL}#comments" title="{lang key="readComments" section="news"}">{$oNews->nNewsKommentarAnzahl} {if $oNews->nNewsKommentarAnzahl == 1}{lang key="newsComment" section="news"}{else}{lang key="newsComments" section="news"}{/if}{/if}</a>
                </h3>
            </div>
            <div class="panel-body">
                {if !empty($oNews->cPreviewImage)}
                    <div class="col-lg-4 col-xs-6">
                        <a href="{$oNews->cURL}">
                            <img src="{$ShopURL}/{$oNews->cPreviewImage}" alt="" class="img-responsive" />
                        </a>
                    </div>
                {/if}
                <div class="news-preview panel-strap">
                    {if $oNews->cVorschauText|strlen > 0}
                        {$oNews->cVorschauText}<span class="read-more">{$oNews->cMehrURL}</span>
                    {elseif $oNews->cText|strip_tags|strlen > 200}
                        {$oNews->cText|strip_tags|truncate:200:""}<span class="read-more">{$oNews->cMehrURL}</span>
                    {else}
                        {$oNews->cText}
                    {/if}
                </div>
            </div>
        </div>
    {/foreach}
{/if}
{/block}