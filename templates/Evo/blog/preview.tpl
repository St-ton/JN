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
        <div class="news-preview panel-strap">
            {if $oNewsUebersicht->cVorschauText|count_characters > 0}
                {$oNewsUebersicht->cVorschauText} <span class="read-more">{$oNewsUebersicht->cMehrURL}</span>
            {elseif $oNewsUebersicht->cText|strip_tags|count_characters > 200}
                {$oNewsUebersicht->cText|strip_tags|truncate:200:""} <span class="read-more">{$oNewsUebersicht->cMehrURL}</span>
            {else}
                {$oNewsUebersicht->cText}
            {/if}
        </div>
    </div>
</div>