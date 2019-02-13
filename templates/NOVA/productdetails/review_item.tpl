{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{row id="comment{$oBewertung->kBewertung}" class="review-comment {if $Einstellungen.bewertung.bewertung_hilfreich_anzeigen === 'Y' && isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde > 0 && $smarty.session.Kunde->kKunde != $oBewertung->kKunde}use_helpful{/if} {if isset($bMostUseful) && $bMostUseful}most_useful{/if}"}
    {if $oBewertung->nHilfreich > 0}
        {block name='productdetails-review-helpful'}
        {col cols=12 class="review-helpful-total"}
            <small class="text-muted">
                {if $oBewertung->nHilfreich > 0}
                    {$oBewertung->nHilfreich}
                {else}
                    {lang key='nobody' section='product rating'}
                {/if}
                {lang key='from' section='product rating'} {$oBewertung->nAnzahlHilfreich}
                {if $oBewertung->nAnzahlHilfreich > 1}
                    {lang key='ratingHelpfulCount' section='product rating'}
                {else}
                    {lang key='ratingHelpfulCountExt' section='product rating'}
                {/if}
            </small>
        {/col}
        {/block}
    {/if}
    {block name='productdetails-review-content'}
    {col cols=12}
        {row class="mt-1" itemprop="review" itemscope=true itemtype="http://schema.org/Review"}
            <span itemprop="name" class="d-none">{$oBewertung->cTitel}</span>

            {col cols=12 md=3 class="text-md-center" itemprop="reviewRating" itemscope=true itemtype="http://schema.org/Rating"}
                {row}
                    {col cols=6 md=12 class="mb-3"}
                        {include file='productdetails/rating.tpl' stars=$oBewertung->nSterne}
                        <small class="hide">
                            <span itemprop="ratingValue">{$oBewertung->nSterne}</span> {lang key='from'}
                            <span itemprop="bestRating">5</span>
                            <meta itemprop="worstRating" content="1">
                        </small>
                    {/col}

                    {if $Einstellungen.bewertung.bewertung_hilfreich_anzeigen === 'Y'}
                        {if isset($smarty.session.Kunde) && $smarty.session.Kunde->kKunde > 0 && $smarty.session.Kunde->kKunde != $oBewertung->kKunde}
                            {col cols=6 md=12 class="review-helpful text-right text-md-center" id="help{$oBewertung->kBewertung}"}
                                <button class="helpful btn btn-xs" title="{lang key='yes'}" name="hilfreich_{$oBewertung->kBewertung}" type="submit">
                                    <i class="far fa-thumbs-up"></i>
                                </button>
                                <sup class="badge badge-light"><b>{$oBewertung->nHilfreich}</b></sup>
                                <button class="not_helpful btn btn-xs ml-2" title="{lang key='no'}" name="nichthilfreich_{$oBewertung->kBewertung}" type="submit">
                                    <i class="far fa-thumbs-down"></i>
                                </button>
                                <sup class="badge badge-light"><b>{$oBewertung->nNichtHilfreich}</b></sup>
                            {/col}
                        {/if}
                    {/if}
                {/row}
            {/col}
            {col cols=12 md=9}
                <strong class="mb-3">{$oBewertung->cTitel}</strong>
                <blockquote>
                    <p itemprop="reviewBody">{$oBewertung->cText|nl2br}</p><br>
                    - <span itemprop="author" itemscope=true itemtype="http://schema.org/Person"><span itemprop="name">{$oBewertung->cName}</span></span>,
                        <small>
                            <meta itemprop="datePublished" content="{$oBewertung->dDatum}" />{$oBewertung->Datum}
                        </small>
                </blockquote>
                <img itemprop="image" src="{$Artikel->cVorschaubild}" alt="{$oBewertung->cTitel}" class="d-none" />
                {if !empty($oBewertung->cAntwort)}
                    <div class="review-reply">
                        <strong>Antwort von {$cShopName}:</strong>
                        <blockquote>
                            <p>{$oBewertung->cAntwort}</p><br>
                            <small>{$oBewertung->AntwortDatum}</small>
                        </blockquote>
                    </div>
                {/if}
            {/col}
        {/row}
    {/col}
    {/block}
{/row}
