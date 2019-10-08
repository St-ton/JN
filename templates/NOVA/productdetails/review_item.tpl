{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-review-item'}
    {row id="comment{$oBewertung->kBewertung}" class="review-comment {if $Einstellungen.bewertung.bewertung_hilfreich_anzeigen === 'Y' && isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde > 0 && $smarty.session.Kunde->kKunde != $oBewertung->kKunde}use_helpful{/if} {if isset($bMostUseful) && $bMostUseful}most_useful{/if}"}
        {if $oBewertung->nHilfreich > 0}
            {block name='productdetails-review-itme-helpful'}
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
        {block name='productdetails-review-item-content'}
            {col cols=12}
                {row class="mt-1" itemprop="review" itemscope=true itemtype="http://schema.org/Review"}
                    <span itemprop="name" class="d-none">{$oBewertung->cTitel}</span>
                    {block name='productdetails-review-item-review'}
                        {col class="col-auto text-center" itemprop="reviewRating" itemscope=true itemtype="http://schema.org/Rating"}
                            {block name='productdetails-review-item-rating'}
                                {block name='productdetails-review-item-include-rating'}
                                    {include file='productdetails/rating.tpl' stars=$oBewertung->nSterne}
                                {/block}
                                <small class="d-none">
                                    <span itemprop="ratingValue">{$oBewertung->nSterne}</span> {lang key='from'}
                                    <span itemprop="bestRating">5</span>
                                    <meta itemprop="worstRating" content="1">
                                </small>
                            {/block}
                            {if $Einstellungen.bewertung.bewertung_hilfreich_anzeigen === 'Y'}
                                {if isset($smarty.session.Kunde) && $smarty.session.Kunde->kKunde > 0 && $smarty.session.Kunde->kKunde != $oBewertung->kKunde}
                                    {block name='productdetails-review-item-buttons'}
                                        {formrow class="review-helpful mt-3 mb-5 mg-lg-0" id="help{$oBewertung->kBewertung}"}
                                            {col class='col-auto ml-auto'}
                                                {button size="sm" class="helpful btn-icon btn-icon-primary" title="{lang key='yes'}" name="hilfreich_{$oBewertung->kBewertung}" type="submit"}
                                                    <i class="far fa-thumbs-up"></i>
                                                {/button}
                                                <span class="d-block"><b>{$oBewertung->nHilfreich}</b></span>
                                            {/col}
                                            {col class='col-auto mr-auto'}
                                                {button size="sm" class="not_helpful btn-icon btn-icon-primary" title="{lang key='no'}" name="nichthilfreich_{$oBewertung->kBewertung}" type="submit"}
                                                    <i class="far fa-thumbs-down"></i>
                                                {/button}
                                                <span class="d-block"><b>{$oBewertung->nNichtHilfreich}</b></span>
                                            {/col}
                                        {/formrow}
                                    {/block}
                                {/if}
                            {/if}
                        {/col}
                    {/block}
                    {block name='productdetails-review-item-details'}
                        {col class='col-lg'}
                            <blockquote>
                                <span class="subheadline">{$oBewertung->cTitel}</span>
                                <p itemprop="reviewBody">{$oBewertung->cText|nl2br}</p>
                                <div class="blockquote-footer">
                                    <span itemprop="author" itemscope=true itemtype="http://schema.org/Person">
                                        <span itemprop="name">{$oBewertung->cName}</span>
                                    </span>,
                                    <meta itemprop="datePublished" content="{$oBewertung->dDatum}" />{$oBewertung->Datum}
                                </div>
                            </blockquote>
                            <meta itemprop="thumbnailURL" content="{$Artikel->cVorschaubildURL}">
                            {if !empty($oBewertung->cAntwort)}
                                <div class="review-reply ml-3">
                                    <span class="subheadline">{lang key='reply' section='product rating'} {$cShopName}:</span>
                                    <blockquote>
                                        <p>{$oBewertung->cAntwort}</p>
                                        <div class="blockquote-footer">{$oBewertung->AntwortDatum}</div>
                                    </blockquote>
                                </div>
                            {/if}
                        {/col}
                    {/block}
                {/row}
            {/col}
        {/block}
    {/row}
{/block}
