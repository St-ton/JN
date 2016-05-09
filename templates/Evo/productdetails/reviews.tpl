<div class="reviews row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">
        {block name="productdetails-review-overview"}
        <div id="reviews-overview">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {include file='productdetails/rating.tpl' total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                        {lang key="averageProductRating" section="product rating"}
                    </h3>
                </div>
                <div class="panel-body">
                    <form method="post" action="bewertung.php" id="article_rating" class="row">
                        {$jtl_token}
                        {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0}
                            <div id="article_votes" class="col-xs-12 col-md-6">
                                {foreach name=sterne from=$Artikel->Bewertungen->nSterne_arr item=nSterne key=i}
                                    {assign var=int1 value=5}
                                    {math equation='x - y' x=$int1 y=$i assign='schluessel'}
                                    {assign var=int2 value=100}
                                    {math equation='a/b*c' a=$nSterne b=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl c=$int2 assign='percent'}
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-6 col-lg-4">
                                            {if $nSterne > 0}
                                                <a href="index.php?a={$Artikel->kArtikel}&amp;btgsterne={$schluessel}">{$schluessel} {if $i == 4}{lang key="starSingular" section="product rating"}{else}{lang key="starPlural" section="product rating"}{/if}</a>
                                            {else}
                                                {$schluessel} {if $i == 4}{lang key="starSingular" section="product rating"}{else}{lang key="starPlural" section="product rating"}{/if}
                                            {/if}
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-lg-8">
                                            <div class="progress">
                                                {if $nSterne > 0}
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="{$percent|round}" aria-valuemin="0" aria-valuemax="100" style="width: {$percent|round}%;">
                                                        {$nSterne}
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                        <div class="col-xs-12 {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl === 0}col-md-10 col-md-push-1 {else}col-md-6 {/if}">
                            {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl == 0}
                                <p>{lang key="firstReview" section="global"}: </p>
                            {else}
                                <p>{lang key="shareYourExperience" section="product rating"}: </p>
                            {/if}
                            <input name="bfa" type="hidden" value="1" />
                            <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                            <input name="bewerten" type="submit" value="{lang key="productAssess" section="product rating"}" class="submit btn btn-primary" />
                        </div>
                    </form>
                </div>
            </div>{* /panel *}
        </div>{* /reviews-overview *}
        {/block}

        {if isset($Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich) && $Artikel->HilfreichsteBewertung->oBewertung_arr|@count > 0 && $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0}
            <div class="review-wrapper reviews-mosthelpful panel">
                <form method="post" action="bewertung.php">
                    {$jtl_token}
                    {block name="productdetails-review-most-helpful"}
                    <input name="bhjn" type="hidden" value="1" />
                    <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                    <input name="btgsterne" type="hidden" value="{$BlaetterNavi->nSterne}" />
                    <input name="btgseite" type="hidden" value="{$BlaetterNavi->nAktuelleSeite}" />
                    <div class="panel-wrap">
                        <div class="review panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{lang key="theMostUsefulRating" section="product rating"}</h3>
                            </div>
                            <div class="panel-body">
                                {foreach name=artikelhilfreichstebewertungen from=$Artikel->HilfreichsteBewertung->oBewertung_arr item=oBewertung}
                                    {include file="productdetails/review_item.tpl" oBewertung=$oBewertung bMostUseful=true}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                    {/block}
                </form>
            </div>
        {/if}

        {if $Artikel->Bewertungen->oBewertung_arr|@count > 0}
            {if $Artikel->Bewertungen->oBewertung_arr|@count == 1 && $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0 && $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung == $oBewertung->kBewertung}
                {* only one review so far. don't display this stuff *}
            {else}
                <div class="review-wrapper reviews-sortcontrol">
                    <form id="sortierenID" method="get" action="index.php" class="form-inline">
                        {$jtl_token}
                        <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                        <input name="btgsterne" type="hidden" value="{$BlaetterNavi->nSterne}" />
                        <input name="btgseite" type="hidden" value="{$BlaetterNavi->nAktuelleSeite}" />
                        <div class="pull-right">
                            <label class="sr-only" for="reviews-sortby">{lang key="reviewsSortedBy" section="product rating"}</label>
                            <select id="reviews-sortby" name="sortierreihenfolge" onchange="$('#sortierenID').submit();" class="form-control">
                                <option value="2"{if $Artikel->Bewertungen->Sortierung == 2} selected{/if}>{lang key="recentReviewFirst" section="product rating"}</option>
                                <option value="3"{if $Artikel->Bewertungen->Sortierung == 3} selected{/if}>{lang key="latestReviewFirst" section="product rating"}</option>
                                <option value="4"{if $Artikel->Bewertungen->Sortierung == 4} selected{/if}>{lang key="highestReviewFirst" section="product rating"}</option>
                                <option value="5"{if $Artikel->Bewertungen->Sortierung == 5} selected{/if}>{lang key="lowestReviewFirst" section="product rating"}</option>
                                <option value="6"{if $Artikel->Bewertungen->Sortierung == 6} selected{/if}>{lang key="usefulClassifiedReviewFirst" section="product rating"}</option>
                                <option value="7"{if $Artikel->Bewertungen->Sortierung == 7} selected{/if}>{lang key="unusefulClassifiedReviewFirst" section="product rating"}</option>
                            </select>
                            {*<input name="submit__" type="submit" value="{lang key="goButton" section="product rating"}" class="btn btn-default btn-sm"/>*}
                        </div>
                        <div class="form-control-static hidden-xs hidden-sm">
                            <strong>
                                {lang key="page" section="productOverview"}
                                {if $BlaetterNavi->nAktiv == 1}
                                    {$BlaetterNavi->nAktuelleSeite}
                                {else}
                                    1
                                {/if}
                            </strong>
                            {lang key="of" section="productOverview"}
                            {if $BlaetterNavi->nAktiv == 1}
                                {$BlaetterNavi->nSeiten}
                            {else}
                                1
                            {/if}
                        </div>
                        <div class="clearfix"></div>
                    </form>
                </div>
                <form method="post" action="bewertung.php" class="reviews-list">
                    {$jtl_token}
                    <input name="bhjn" type="hidden" value="1" />
                    <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                    <input name="btgsterne" type="hidden" value="{$BlaetterNavi->nSterne}" />
                    <input name="btgseite" type="hidden" value="{$BlaetterNavi->nAktuelleSeite}" />

                    {foreach name=artikelbewertungen from=$Artikel->Bewertungen->oBewertung_arr item=oBewertung}
                        {if $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0 && $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->kBewertung == $oBewertung->kBewertung}
                            {* helpful review already displayed on top *}
                        {else}
                            <div class="review panel panel-default {if $smarty.foreach.artikelbewertungen.last}last{/if}">
                                <div class="panel-body">
                                    {include file="productdetails/review_item.tpl" oBewertung=$oBewertung}
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </form>
            {/if}

            {if $Artikel->Bewertungen->nAnzahlSprache > $Einstellungen.bewertung.bewertung_anzahlseite && $BlaetterNavi->nAktiv == 1}
                <div class="reviews-pagination row">
                    <div class="col-xs-4">
                        <ul class="pagination">
                            <li>
                                {if $BlaetterNavi->nAktuelleSeite > 1}
                                    <a href="index.php?a={$Artikel->kArtikel}&btgsterne={$BlaetterNavi->nSterne}&btgseite={$BlaetterNavi->nVoherige}">&laquo; {lang key="previous" section="productOverview"}</a>
                                {/if}
                                {if $BlaetterNavi->nAnfang != 0}
                                    <a href="index.php?a={$Artikel->kArtikel}&btgsterne={$BlaetterNavi->nSterne}&btgseite={$BlaetterNavi->nAnfang}">{$BlaetterNavi->nAnfang}</a> ...
                                {/if}
                            </li>
                            {foreach name=blaetter from=$BlaetterNavi->nBlaetterAnzahl_arr item=Blatt key=i}
                                <li class="{if $BlaetterNavi->nAktuelleSeite == $Blatt}active{/if}">
                                    <a href="index.php?a={$Artikel->kArtikel}&btgsterne={$BlaetterNavi->nSterne}&btgseite={$Blatt}">{$Blatt}</a>
                                </li>
                            {/foreach}
                            <li>
                                {if $BlaetterNavi->nAktuelleSeite < $BlaetterNavi->nSeiten}
                                    <a href="index.php?a={$Artikel->kArtikel}&btgsterne={$BlaetterNavi->nSterne}&btgseite={$BlaetterNavi->nNaechste}">{lang key="next" section="productOverview"} &raquo;</a>
                                {/if}
                            </li>
                        </ul>
                    </div>
                </div>
            {/if}
        {/if}
    </div>{* /col *}
</div>{* /row *}