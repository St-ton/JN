{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<div class="reviews mt-3">
    {block name='productdetails-review-overview'}
    {row id="reviews-overview"}
        {col cols=12 md=6 order=1 order-md=0}
            {card class="mb-3"}
                <div class="card-title">
                    <div class="h6">
                        {lang key='averageProductRating' section='product rating'}
                    </div>
                </div>
                {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0}
                    {link href="#article_votes" data=["toggle"=>"collapse"] role="button" aria=["expanded"=>"true","controls"=>"article_votes"]}
                        {include file='productdetails/rating.tpl' total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}<i class="ml-3 fas fa-chevron-down"></i>
                    {/link}
                {/if}
                {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0}
                    {collapse id="article_votes" visible=true}
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
                                        <a href="{$Artikel->cURLFull}?btgsterne={$schluessel}#tab-votes">{$schluessel} {if $i == 4}{lang key='starSingular' section='product rating'}{else}{lang key='starPlural' section='product rating'}{/if}</a>
                                    {else}
                                        {$schluessel} {if $i == 4}{lang key='starSingular' section='product rating'}{else}{lang key='starPlural' section='product rating'}{/if}
                                    {/if}
                                    {if isset($bewertungSterneSelected) && $bewertungSterneSelected === $schluessel}
                                        </strong>
                                    {/if}
                                {/col}
                                {col cols=6}
                                    {progress now="{$percent|round}" min=0 max=100}

                                {/col}
                                {col cols=2}
                                    {if !empty($nSterne)}{$nSterne}{else}0{/if}
                                {/col}
                            {/row}
                        {/foreach}
                        {if isset($bewertungSterneSelected) && $bewertungSterneSelected > 0}
                            <p>
                                <a href="{$Artikel->cURLFull}#tab-votes" class="btn btn-default">
                                    {lang key='allRatings'}
                                </a>
                            </p>
                        {/if}
                    {/collapse}
                {/if}
            {/card}
        {/col}
        {col cols=12 md=6  order=0 order-md=1}
            {form method="post" action="{get_static_route id='bewertung.php'}#tab-votes" id="article_rating"}
                {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl == 0}
                    <p>{lang key='firstReview'}: </p>
                {else}
                    <p>{lang key='shareYourExperience' section='product rating'}: </p>
                {/if}
                {input type="hidden" name="bfa" value="1"}
                {input type="hidden" name="a" value="{$Artikel->kArtikel}"}
                {input type="submit" name="bewerten" value="{if $bereitsBewertet === false}{lang key='productAssess'
                    section='product rating'}{else}{lang key='edit' section='product rating'}{/if}"
                    class="btn btn-secondary w-auto mb-3"
                }
            {/form}
        {/col}
    {/row}
    {/block}

    {if $ratingPagination->getPageItemCount() > 0 || isset($Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich) &&
    $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0}
        <p>{lang key='reviewsInCurrLang' section='product rating'}</p>
    {else}
        <p>{lang key='noReviewsInCurrLang' section='product rating'}</p>
    {/if}
    {if isset($Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich) &&
        $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0
    }
        {card class="reviews-mosthelpful mb-3" no-body=true}
            {cardheader}
                {lang key='theMostUsefulRating' section='product rating'}
            {/cardheader}
            {form method="post" action="{get_static_route id='bewertung.php'}#tab-votes"}
                {block name='productdetails-review-most-helpful'}
                    {input type="hidden" name="bhjn" value="1"}
                    {input type="hidden" name="a" value="{$Artikel->kArtikel}"}
                    {input type="hidden" name="btgsterne" value="{$BlaetterNavi->nSterne}"}
                    {input type="hidden" name="btgseite" value="{$BlaetterNavi->nAktuelleSeite}"}

                    {cardbody class="review"}
                        {foreach $Artikel->HilfreichsteBewertung->oBewertung_arr as $oBewertung}
                            {include file='productdetails/review_item.tpl' oBewertung=$oBewertung bMostUseful=true}
                        {/foreach}
                    {/cardbody}
                {/block}
            {/form}
        {/card}
    {/if}

    {if $ratingPagination->getPageItemCount() > 0}
        {include file='snippets/pagination.tpl' oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes'}
        {form method="post" action="{get_static_route id='bewertung.php'}#tab-votes" class="reviews-list"}
            {input type="hidden" name="bhjn" value="1"}
            {input type="hidden" name="a" value="{$Artikel->kArtikel}"}
            {input type="hidden" name="btgsterne" value="{$BlaetterNavi->nSterne}"}
            {input type="hidden" name="btgseite" value="{$BlaetterNavi->nAktuelleSeite}"}
            {foreach $ratingPagination->getPageItems() as $oBewertung}
                {card class="review mb-3 {if $oBewertung@last}last{/if}"}
                    {include file='productdetails/review_item.tpl' oBewertung=$oBewertung}
                {/card}

            {/foreach}
        {/form}
        {include file='snippets/pagination.tpl' oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes' showFilter=false}
    {/if}
</div>
