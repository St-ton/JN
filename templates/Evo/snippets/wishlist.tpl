{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <h1>{$CWunschliste->cName}{if $isCurrenctCustomer === false && isset($CWunschliste->oKunde->cVorname)} {lang key='from' section='product rating' alt_section='login,productDetails,productOverview,global,'} {$CWunschliste->oKunde->cVorname}{/if}</h1>

    {include file='snippets/extension.tpl'}

    {if $step === 'wunschliste versenden' && $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
        {*{include file='account/wishlist_email_form.tpl'}*}
        <h1>{lang key='wishlistViaEmail' section='login'}</h1>
        <div class="row">
            <div class="col-xs-12">
                {block name='wishlist-email-form'}
                    <div class="panel-wrap">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{block name='wishlist-email-form-title'}{$CWunschliste->cName}{/block}</h3>
                            </div>
                            <div class="panel-body">
                                {block name='wishlist-email-form-body'}
                                    <form method="post" action="{get_static_route id='wunschliste.php'}" name="Wunschliste">
                                        {$jtl_token}
                                        <input type="hidden" name="wlvm" value="1"/>
                                        <input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"/>
                                        <input type="hidden" name="send" value="1"/>
                                        <label for="wishlist-email">{lang key='wishlistEmails' section='login'}{if $Einstellungen.global.global_wunschliste_max_email > 0} | {lang key='wishlistEmailCount' section='login'}: {$Einstellungen.global.global_wunschliste_max_email}{/if}</label>
                                        <textarea id="wishlist-email" name="email" rows="5" style="width:100%" class="form-control"></textarea>
                                        <hr>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <button name="action" type="submit" value="sendViaMail" class="btn btn-primary">{lang key='wishlistSend' section='login'}</button>
                                            </div>
                                        </div>
                                    </form>
                                {/block}
                            </div>
                        </div>
                    </div>
                {/block}
            </div>
        </div>
    {else}
        {if $hasItems === true || !empty($wlsearch)}
            <div id="wishlist-search">
                <form method="post" action="{get_static_route id='wunschliste.php'}" name="WunschlisteSuche" class="form form-inline">
                    {$jtl_token}
                    {if $CWunschliste->nOeffentlich == 1 && !empty($cURLID)}
                        <input type="hidden" name="wlid" value="{$cURLID}" />
                    {else}
                        <input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}" />
                    {/if}
                    <div class="input-group">
                        <input name="cSuche" size="35" type="text" value="{$wlsearch}" placeholder="{lang key='wishlistSearch' section='login'}" class="form-control" />
                        <span class="input-group-btn">
                            <button class="btn btn-default" name="action" value="search" type="submit"><i class="fa fa-search"></i> {lang key='wishlistSearchBTN' section='login'}</button>
                            {if !empty($wlsearch)}
                                <a href="{get_static_route id='wunschliste.php'}?wl={$CWunschliste->kWunschliste}" class="btn btn-default">{lang key='wishlistRemoveSearch' section='login'}</a>
                            {/if}
                        </span>
                    </div>
                </form>
            </div>
        {/if}
        <form method="post" action="{get_static_route id='wunschliste.php'}{if $CWunschliste->nStandard != 1}?wl={$CWunschliste->kWunschliste}{/if}" name="Wunschliste" class="basket_wrapper{if $hasItems === true} top15{/if}">
            {$jtl_token}
            {block name='wishlist'}
                <input type="hidden" name="wla" value="1"/>
                <input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"/>
                {if !empty($wlsearch)}
                    <input type="hidden" name="wlsearch" value="1"/>
                    <input type="hidden" name="cSuche" value="{$wlsearch}"/>
                {/if}
                {if !empty($CWunschliste->CWunschlistePos_arr)}
                    {if $isCurrenctCustomer === true}
                        <div id="edit-wishlist-name">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="wishlist-name">{lang key='name' section='global'}</label>
                                </span>
                                <input id="wishlist-name" type="text" class="form-control" placeholder="name" name="wishlistName" value="{$CWunschliste->cName}" />
                            </div>
                        </div>
                    {/if}
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>{lang key='wishlistProduct' section='login'}</th>
                            <th class="hidden-xs hidden-sm">&nbsp;</th>
                            <th>{lang key='wishlistComment' section='login'}</th>
                            <th class="text-center">{lang key='wishlistPosCount' section='login'}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $CWunschliste->CWunschlistePos_arr as $wlPosition}
                            <tr>
                                <td class="img-col hidden-xs hidden-sm">
                                    <a href="{$wlPosition->Artikel->cURLFull}">
                                        <img alt="{$wlPosition->Artikel->cName}" src="{$wlPosition->Artikel->cVorschaubildURL}" class="img-responsive">
                                    </a>
                                </td>
                                <td>
                                    <a href="{$wlPosition->Artikel->cURL}">{$wlPosition->cArtikelName}</a>
                                    {if $wlPosition->Artikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                        <p>{lang key='productOutOfStock' section='productDetails'}</p>
                                    {elseif $wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                        <p>{lang key='priceOnApplication' section='global'}</p>
                                    {else}
                                        {include file='productdetails/price.tpl' Artikel=$wlPosition->Artikel tplscope='wishlist'}
                                    {/if}
                                    {foreach $wlPosition->CWunschlistePosEigenschaft_arr as $CWunschlistePosEigenschaft}
                                        {if $CWunschlistePosEigenschaft->cFreifeldWert}
                                            <p>
                                            <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                            {$CWunschlistePosEigenschaft->cFreifeldWert}{if $wlPosition->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}</p>{/if}
                                        {else}
                                            <p>
                                            <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                            {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $wlPosition->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}</p>{/if}
                                        {/if}
                                    {/foreach}
                                </td>
                                <td>
                                    <textarea{if $isCurrenctCustomer !== true} readonly="readonly"{/if} class="form-control" rows="4" name="Kommentar_{$wlPosition->kWunschlistePos}">{$wlPosition->cKommentar}</textarea>
                                </td>
                                {if $wlPosition->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                    <td></td>
                                    <td class="text-right">
                                        <div class="btn-group-vertical">
                                            <button href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlplo={$wlPosition->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}"
                                               class="btn btn-default"
                                               title="{lang key='wishlistremoveItem' section='login'}">
                                                <span class="fa fa-trash-o"></span>
                                            </button>
                                        </div>
                                    </td>
                                {else}
                                    <td>
                                        <input{if $isCurrenctCustomer !== true} readonly="readonly"{/if}
                                                name="Anzahl_{$wlPosition->kWunschlistePos}"
                                                class="wunschliste_anzahl form-control" type="text" size="1"
                                                value="{$wlPosition->fAnzahl|replace_delim}"><br/>{$wlPosition->Artikel->cEinheit}
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group-vertical">
                                            {if $wlPosition->Artikel->bHasKonfig}
                                                <a href="{$wlPosition->Artikel->cURLFull}" class="btn btn-primary"
                                                   title="{lang key='product' section='global'} {lang key='configure' section='global'}">
                                                    <span class="fa fa-gears"></span>
                                                </a>
                                            {else}
                                                <button name="addToCart" value="{$wlPosition->kWunschlistePos}"
                                                        class="btn btn-primary"
                                                        title="{lang key='wishlistaddToCart' section='login'}">
                                                    <span class="fa fa-shopping-cart"></span>
                                                </button>
                                            {/if}
                                            {if $isCurrenctCustomer === true}
                                                <button name="remove" value="{$wlPosition->kWunschlistePos}"
                                                   class="btn btn-default"
                                                   title="{lang key='wishlistremoveItem' section='login'}">
                                                    <span class="fa fa-trash-o"></span>
                                                </button>
                                            {/if}
                                        </div>
                                    </td>
                                {/if}
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="pull-right btn-group">
                                <button class="btn btn-primary submit" name="action" value="addAllToCart">
                                    <i class="fa fa-shopping-cart"></i> <span class="hidden-xs">{lang key='wishlistAddAllToCart' section='login'}</span>
                                </button>
                                {if $isCurrenctCustomer === true}
                                    <button type="submit" title="{lang key='wishlistUpdate' section='login'}" class="btn btn-default" name="action" value="update">
                                        <i class="fa fa-refresh"></i> <span class="hidden-xs">{lang key='wishlistUpdate' section='login'}</span>
                                    </button>
                                    <button class="btn btn-default submit" name="action" value="removeAll">
                                        <i class="fa fa-trash-o"></i> <span class="hidden-xs">{lang key='wishlistDelAll' section='login'}</span>
                                    </button>
                                {/if}
                            </div>
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info">{lang key='noEntriesAvailable' section='global'}</div>
                {/if}
            {/block}
        </form>
        {if $isCurrenctCustomer === true}
            <div class="wishlist-actions top15">
                <div class="panel-heading">
                    <h5 class="panel-title">{block name='wishlist-title'}{if $CWunschliste->nOeffentlich == 1}{lang key='wishlistURL' section='login'}{else}{/if}{/block}</h5>
                </div>
                <div class="panel-body">
                    {block name='wishlist-body'}
                        {if $CWunschliste->nOeffentlich == 1}
                            <form method="post" action="{get_static_route id='wunschliste.php'}">
                                {$jtl_token}
                                <input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"/>
                                <div class="input-group">
                                    <input type="text" name="wishlist-url" readonly="readonly"
                                           value="{get_static_route id='wunschliste.php'}?wlid={$CWunschliste->cURLID}"
                                           class="form-control">
                                    <span class="input-group-btn">
                                        {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                            <button type="submit" name="action" value="sendViaMail"
                                                    {if !$hasItems} disabled="disabled"{/if}
                                                    class="btn btn-default"
                                                    title="{lang key='wishlistViaEmail' section='login'}">
                                               <i class="fa fa-envelope"></i>
                                           </button>
                                        {/if}
                                    </span>
                                </div>
                            </form>
                        {else}
                            {lang key='wishlistNoticePrivate' section='login'}&nbsp;
                        {/if}
                    {/block}
                </div>
            </div>
    
            {*@todo*}
            {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
                {block name='account-wishlist'}
                    <div id="wishlist" class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{block name='account-wishlist-title'}{lang key='yourWishlist' section='login'}{/block}</h3>
                        </div>
                        <div class="panel-body">
                            {block name='account-wishlist-body'}
                                {if !empty($oWunschliste_arr[0]->kWunschliste)}
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>{lang key='wishlistName' section='login'}</th>
                                            <th>{lang key='wishlistStandard' section='login'}</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oWunschliste_arr as $Wunschliste}
                                            <tr>
                                                <td>
                                                    <a href="{get_static_route id='wunschliste.php'}{if $Wunschliste->nStandard != 1}?wl={$Wunschliste->kWunschliste}{/if}">{$Wunschliste->cName}</a>
                                                </td>
                                                <td>{if $Wunschliste->nStandard == 1}{lang key='active' section='global'}{/if} {if $Wunschliste->nStandard == 0}{lang key='inactive' section='global'}{/if}</td>
                                                <td class="text-right">
                                                    <form method="post" action="{get_static_route id='wunschliste.php'}">
                                                        {$jtl_token}
                                                        <input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"/>
                                                        <input type="hidden" name="kWunschlisteTarget" value="{$Wunschliste->kWunschliste}"/>
                                                        <span class="btn-group btn-group-sm">
                                                            {if $Wunschliste->nStandard != 1}
                                                                <button class="btn btn-default" name="action" value="setAsDefault">
                                                                    <i class="fa fa-check"></i> <span class="hidden-xs">{lang key='wishlistStandard' section='login'}</span>
                                                                </button>
                                                            {else}
                                                                <button class="btn btn-success disabled" name="action" value="setAsDefault">
                                                                <i class="fa fa-check"></i> <span class="hidden-xs">{lang key='wishlistStandard' section='login'}</span>
                                                                </button>
                                                            {/if}
                                                            {if $Wunschliste->nOeffentlich == 1}
                                                                <button type="submit" name="action" value="setPrivate"
                                                                        class="btn btn-default"
                                                                        title="{lang key='wishlistSetPrivate' section='login'}">
                                                                    <i class="fa fa-eye-slash"></i> <span class="hidden-xs">{lang key='wishlistSetPrivate' section='login'}</span>
                                                                </button>
                                                            {/if}
                                                            {if $Wunschliste->nOeffentlich == 0}
                                                                <button type="submit" name="action" value="setPublic" class="btn btn-default">
                                                                    <i class="fa fa-eye"></i> <span class="hidden-xs">{lang key='wishlistSetPublic' section='login'}</span>
                                                                </button>
                                                            {/if}
                                                            <button type="submit" class="btn btn-danger" name="action" value="delete">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </span>
                                                    </form>
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                {/if}
                                <form method="post" action="{get_static_route id='wunschliste.php'}" class="form form-inline">
                                    <input type="hidden" name="kWunschliste" value="{$CWunschliste->kWunschliste}"/>
                                    {$jtl_token}
                                    <div class="input-group">
                                        <input name="cWunschlisteName" type="text" class="form-control input-sm" placeholder="{lang key='wishlistAddNew' section='login'}" size="25" />
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-default btn-sm" name="action" value="createNew"><i class="fa fa-save"></i> {lang key='wishlistSaveNew' section='login'}</button>
                                        </span>
                                    </div>
                                </form>
                            {/block}
                        </div>
                    </div>
                {/block}
            {/if}
        {/if}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
