<h1>{$CWunschliste->cName}</h1>

{if $hinweis}
    <div class="alert alert-info">{$hinweis}</div>
{/if}

{*
   <form method="post" action="jtl.php" name="WunschlisteSuche" class="form">
      <input type="hidden" name="wlsearch" value="1" />
      <input type="hidden" name="wl" value="{$CWunschliste->kWunschliste}" />
      <input type="hidden" name="{$session_name}" value="{$session_id}" />

      <fieldset>
         <legend>{lang key="wishlistSearch" section="login"}</legend>
         <input name="cSuche" type="text" value="{$wlsearch}" />
         <input name="submitSuche" type="submit" value="{lang key="wishlistSearchBTN" section="login"}" />
         {if $wlsearch}
            <a href="jtl.php?wl={$CWunschliste->kWunschliste}" class="wishlistlink">{lang key="wishlistRemoveSearch" section="login"}</a>
         {/if}
      </fieldset>
   </form>
*}
<form method="post" action="jtl.php" name="Wunschliste" class="basket_wrapper">
    {$jtl_token}
    {block name="wishlist"}
    <input type="hidden" name="wla" value="1" />
    <input type="hidden" name="wl" value="{$CWunschliste->kWunschliste}" />
    <input type="hidden" name="WunschlisteName" value="{$CWunschliste->cName}" />

    {if isset($wlsearch)}
        <input type="hidden" name="wlsearch" value="1" />
        <input type="hidden" name="cSuche" value="{$wlsearch}" />
    {/if}
    {if !empty($CWunschliste->CWunschlistePos_arr)}
        <table class="table table-striped">
            <thead>
            <tr>
                <th>{lang key="wishlistProduct" section="login"}</th>
                <th class="hidden-xs hidden-sm">&nbsp;</th>
                <th>{lang key="wishlistComment" section="login"}</th>
                <th class="text-center">{lang key="wishlistPosCount" section="login"}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach name=wunschlistepos from=$CWunschliste->CWunschlistePos_arr item=CWunschlistePos}
                <tr>
                    <td class="img-col hidden-xs hidden-sm">
                        <a href="{$CWunschlistePos->Artikel->cURL}">
                            <img alt="{$CWunschlistePos->Artikel->cName}" src="{$CWunschlistePos->Artikel->cVorschaubild}" class="img-responsive">
                        </a>
                    </td>
                    <td>
                        <a href="{$CWunschlistePos->Artikel->cURL}">{$CWunschlistePos->cArtikelName}</a>
                        {if $CWunschlistePos->Artikel->Preise->fVKNetto==0 && $Einstellungen.global.global_preis0 === 'N'}
                            <p>{lang key="priceOnApplication" section="global"}</p>
                        {else}
                            <p><b>{lang key="price"}:</b> {$CWunschlistePos->cPreis}</p>
                            {if $CWunschlistePos->Artikel->cLocalizedVPE}
                                <p>
                                    <small><b>{lang key="basePrice" section="global"}:</b> {$CWunschlistePos->Artikel->cLocalizedVPE[$NettoPreise]}</small>
                                </p>
                            {/if}
                        {/if}
                        {*<p><span class="vat_info">{include file='snippets/shipping_tax_info.tpl' taxdata=$WunschlistePos->Artikel->taxData}</span></p>*}
                        {foreach name=eigenschaft from=$CWunschlistePos->CWunschlistePosEigenschaft_arr item=CWunschlistePosEigenschaft}
                            {if $CWunschlistePosEigenschaft->cFreifeldWert}
                                <p>
                                <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                {$CWunschlistePosEigenschaft->cFreifeldWert}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}</p>{/if}
                            {else}
                                <p>
                                <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}</p>{/if}
                            {/if}
                        {/foreach}
                    </td>
                    <td>
                        <textarea class="form-control" rows="4" name="Kommentar_{$CWunschlistePos->kWunschlistePos}">{$CWunschlistePos->cKommentar}</textarea>
                    </td>
                    <td>
                        <input name="Anzahl_{$CWunschlistePos->kWunschlistePos}" class="wunschliste_anzahl form-control" type="text" size="1" value="{$CWunschlistePos->fAnzahl|replace_delim}"><br />{$CWunschlistePos->Artikel->cEinheit}
                    </td>
                    <td class="text-right btn-group-vertical">
                        {* @todo: button href? *}
                        {if $CWunschlistePos->Artikel->bHasKonfig}
                            <a href="{$CWunschlistePos->Artikel->cURL}" class="btn btn-default" title="{lang key="product" section="global"} {lang key="configure" section="global"}">
                                <span class="fa fa-gears"></span>
                            </a>
                        {else}
                            <a href="jtl.php?wl={$CWunschliste->kWunschliste}&wlph={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}" class="btn btn-default" title="{lang key="wishlistaddToCart" section="login"}">
                                <span class="fa fa-shopping-cart"></span>
                            </a>
                        {/if}
                        <a href="jtl.php?wl={$CWunschliste->kWunschliste}&wlplo={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}" class="btn btn-default" title="{lang key="wishlistremoveItem" section="login"}">
                            <span class="fa fa-trash-o"></span>
                        </a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-right btn-group">
                    <button type="submit" title="{lang key="wishlistUpdate" section="login"}" class="btn btn-default">
                        <i class="fa fa-refresh"></i>
                    </button>
                    <a href="jtl.php?wl={$CWunschliste->kWunschliste}&wlpah=1{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}" class="btn btn-primary submit">{lang key="wishlistAddAllToCart" section="login"}</a>
                    <a href="jtl.php?wl={$CWunschliste->kWunschliste}&wldl=1" class="btn btn-default submit">{lang key="wishlistDelAll" section="login"}</a>
                </div>
            </div>
        </div>
    {/if}
    <div class="panel panel-blank top15">
        <div class="panel-heading">
            <h5 class="panel-title">{block name="wishlist-title"}{if $CWunschliste->nOeffentlich == 1}{lang key="wishlistURL" section="login"}{else}{lang key="yourWishlist" section="login"}{/if}{/block}</h5>
        </div>
        <div class="panel-body">
            {block name="wishlist-body"}
            {if $CWunschliste->nOeffentlich == 1}
                <div class="row">
                    <div class="col-xs-12">
                        <div class="input-group">
                            <input type="text" name="wishlist-url" disabled="disabled" value="{$ShopURL}/index.php?wlid={$CWunschliste->cURLID}" class="form-control">
                            <span class="input-group-btn">
                                {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                   <button type="submit" name="wlvm" value="1" class="btn btn-default" title="{lang key="wishlistViaEmail" section="login"}">
                                       <i class="fa fa-envelope"></i>
                                   </button>
                                {/if}
                                <button type="submit" name="nstd" value="0" class="btn btn-default" title="{lang key="wishlistSetPrivate" section="login"}">
                                    <i class="fa fa-eye-slash"></i> {lang key="wishlistSetPrivate" section="login"}
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            {else}
                {lang key="wishlistNoticePrivate" section="login"}&nbsp;
                <button type="submit" name="nstd" value="1" class="btn btn-default">
                    <span class="fa fa-eye"></span> {lang key="wishlistSetPublic" section="login"}
                </button>
            {/if}
            {/block}
        </div>
    </div>
    {/block}
</form>