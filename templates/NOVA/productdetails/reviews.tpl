{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-reviews'}
    <div class="reviews mt-3">
        {block name='productdetails-reviews-content'}
        {row id='reviews-overview' class='align-items-center'}
            {block name='productdetails-reviews-overview'}
                {col cols=12 md=4 order=1 order-md=0}
                    {card class="mb-3"}
                        {block name='productdetails-reviews-heading'}
                            <div class="card-title">
                                <div class="subheadline">
                                    {lang key='averageProductRating' section='product rating'}
                                </div>
                            </div>
                        {/block}
                        {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0}
                            {block name='productdetails-reviews-rating-dropdown'}
                            <div class="dropdown">
                                <button class="btn btn-link px-0 dropdown-toggle" type="button" id="ratingDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {block name='productdetails-reviews-include-rating'}
                                        {include file='productdetails/rating.tpl' total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                                        <span class="mx-2">({$Artikel->Bewertungen->oBewertungGesamt->nAnzahl} {lang key='Votes'})</span>
                                    {/block}
                                </button>
                                <div class="dropdown-menu min-w-lg p-0" aria-labelledby="ratingDropdown" data-dropdown-stay>
                                    <div class="dropdown-body p-3">
                                        {block name='productdetails-reviews-votes'}
                                            {foreach name=sterne from=$Artikel->Bewertungen->nSterne_arr item=nSterne key=i}
                                                {assign var=int1 value=5}
                                                {math equation='x - y' x=$int1 y=$i assign='schluessel'}
                                                {assign var=int2 value=100}
                                                {math equation='a/b*c' a=$nSterne b=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl c=$int2 assign='percent'}
                                                {row class="mb-2"}
                                                    {col cols=4}
                                                    {if isset($bewertungSterneSelected) && $bewertungSterneSelected === $schluessel}
                                                        <strong>
                                                    {/if}
                                                    {if $nSterne > 0 && (!isset($bewertungSterneSelected) || $bewertungSterneSelected !== $schluessel)}
                                                        {link href="{$Artikel->cURLFull}?btgsterne={$schluessel}#tab-votes"}{$schluessel} {if $i == 4}{lang key='starSingular' section='product rating'}{else}{lang key='starPlural' section='product rating'}{/if}{/link}
                                                    {else}
                                                        {$schluessel} {if $i == 4}{lang key='starSingular' section='product rating'}{else}{lang key='starPlural' section='product rating'}{/if}
                                                    {/if}
                                                    {if isset($bewertungSterneSelected) && $bewertungSterneSelected === $schluessel}
                                                        </strong>
                                                    {/if}
                                                    {/col}
                                                    {col cols=6}
                                                        {progress now=$percent|round min=0 max=100}
                                                    {/col}
                                                    {col cols=2}
                                                        {if !empty($nSterne)}{$nSterne}{else}0{/if}
                                                    {/col}
                                                {/row}
                                            {/foreach}
                                            {if isset($bewertungSterneSelected) && $bewertungSterneSelected > 0}
                                                {block name='productdetails-reviews-note-all-ratings'}
                                                    <p>
                                                        {link href="{$Artikel->cURLFull}#tab-votes" class="btn btn-outline-primary "}
                                                        {lang key='allRatings'}
                                                        {/link}
                                                    </p>
                                                {/block}
                                            {/if}
                                        {/block}
                                    </div>
                                </div>
                            </div>
                            {/block}
                        {/if}
                    {/card}
                {/col}
            {/block}
            {block name='productdetails-reviews-votes'}
                {col cols=12 md=8  order=0 order-md=1}
                    {form method="post" action="{get_static_route id='bewertung.php'}#tab-votes" id="article_rating"}
                        <div class="subheadline">
                            {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl == 0}
                                <p>{lang key='firstReview'}: </p>
                            {else}
                                <p>{lang key='shareYourExperience' section='product rating'}: </p>
                            {/if}
                        </div>
                        {input type="hidden" name="bfa" value="1"}
                        {input type="hidden" name="a" value=$Artikel->kArtikel}
                        {button type="submit" name="bewerten" value="1" variant="outline-primary" class="w-auto mb-3"}
                            {if $bereitsBewertet === false}
                                {lang key='productAssess' section='product rating'}
                            {else}
                                {lang key='edit' section='product rating'}
                            {/if}
                        {/button}
                    {/form}
                {/col}
            {/block}
        {/row}
        {/block}

        {block name='productdetails-reviews-reviews-in-lang'}
            {if $ratingPagination->getPageItemCount() > 0 || isset($Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich) &&
            $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0}
                <p>{lang key='reviewsInCurrLang' section='product rating'}</p>
            {else}
                <p>{lang key='noReviewsInCurrLang' section='product rating'}</p>
            {/if}
        {/block}
        {if isset($Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich) &&
            $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0
        }
            {block name='productdetails-reviews-form-most-useful'}
                {card class="reviews-mosthelpful mb-3" no-body=true}
                    {cardheader}
                        <span class="h3 mb-0">
                            {lang key='theMostUsefulRating' section='product rating'}
                        </span>
                    {/cardheader}
                    {form method="post" action="{get_static_route id='bewertung.php'}#tab-votes"}
                        {block name='productdetails-reviews-most-helpful'}
                            {input type="hidden" name="bhjn" value="1"}
                            {input type="hidden" name="a" value=$Artikel->kArtikel}
                            {input type="hidden" name="btgsterne" value=$BlaetterNavi->nSterne}
                            {input type="hidden" name="btgseite" value=$BlaetterNavi->nAktuelleSeite}

                            {cardbody class="review"}
                                {foreach $Artikel->HilfreichsteBewertung->oBewertung_arr as $oBewertung}
                                    {block name='productdetails-reviews-form-most-useful-include-review-item'}
                                        {include file='productdetails/review_item.tpl' oBewertung=$oBewertung bMostUseful=true}
                                    {/block}
                                {/foreach}
                            {/cardbody}
                        {/block}
                    {/form}
                {/card}
            {/block}
        {/if}

        {if $ratingPagination->getPageItemCount() > 0}
            {block name='productdetails-reviews-include-pagination-bottom'}
                {include file='snippets/pagination.tpl' oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes'}
            {/block}
            {block name='productdetails-reviews-form'}
                {form id="reviews-list" method="post" action="{get_static_route id='bewertung.php'}#tab-votes" class="reviews-list"}
                    {input type="hidden" name="bhjn" value="1"}
                    {input type="hidden" name="a" value=$Artikel->kArtikel}
                    {input type="hidden" name="btgsterne" value=$BlaetterNavi->nSterne}
                    {input type="hidden" name="btgseite" value=$BlaetterNavi->nAktuelleSeite}
                    {foreach $ratingPagination->getPageItems() as $oBewertung}
                        {card class="review mb-3 {if $oBewertung@last}last{/if}"}
                            {block name='productdetails-reviews-form-include-review-item'}
                                {include file='productdetails/review_item.tpl' oBewertung=$oBewertung}
                            {/block}
                        {/card}
                    {/foreach}
                {/form}
            {/block}
            {block name='productdetails-reviews-include-pagination-bottom'}
                {include file='snippets/pagination.tpl' oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes' showFilter=false}
            {/block}
        {/if}
    </div>
{/block}
{block name='productdetails-reviews-scripts'}
    {inline_script}<script>
        $('.js-helpful').on('click', function (e) {
            e.preventDefault();
            $.evo.extended().updateReviewHelpful($(this));
        });
    </script>{/inline_script}
{/block}