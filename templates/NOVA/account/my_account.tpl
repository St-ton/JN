{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-my-account'}
    {block name='heading'}
        <div class="h2 mb-4">{lang key='welcome' section='login'} {$smarty.session.Kunde->cVorname} {$smarty.session.Kunde->cNachname}</div>
    {/block}
    {opcMountPoint id='opc_before_account_page'}
    {block name='account-my-account-head-data'}
        {row}
            {col cols=12 lg=6 class="mb-5"}
                {block name='account-my-account-alert'}
                    {lang key='myAccountDesc' section='login'}
                {/block}
            {/col}
            {col cols=12 lg=6 class="mb-5"}
                {block name='account-my-account-account-credit'}
                    {card class='text-center border border-primary font-weight-bold'}
                        {lang key='yourMoneyOnAccount' section='login'}: {$Kunde->cGuthabenLocalized}
                    {/card}
                {/block}
            {/col}
        {/row}
    {/block}
    {block name='account-my-account-account-data'}
        {row}
            {col cols=12 lg=6 class='mb-5'}
                {block name='account-my-account-orders-content'}
                    {card no-body=true}
                        {cardheader class="bg-info"}
                            {row class="align-items-center"}
                                {col}
                                    <span class="h3 mb-0">
                                        {link class='text-decoration-none' href="$cCanonicalURL?bestellungen=1"}
                                            {lang key='myOrders'}
                                        {/link}
                                    </span>
                                {/col}
                                {col class="col-auto font-size-sm"}
                                    {link href="$cCanonicalURL?bestellungen=1"}
                                        {lang key='showAll'}
                                    {/link}
                                {/col}
                            {/row}
                        {/cardheader}
                        {if count($Bestellungen) > 0}
                            <div class="table-responsive">
                            {block name='account-my-account-orders'}
                                <table class="table table-vertical-middle table-hover">
                                    <tbody>
                                    {foreach $Bestellungen as $order}
                                        {if $order@index === 5}{break}{/if}
                                        <tr title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}"
                                            class="clickable-row"
                                            data-toggle="tooltip"
                                            data-href="{$cCanonicalURL}?bestellung={$order->kBestellung}">
                                            <td>{$order->dBestelldatum}</td>
                                            <td class="text-right">{$order->cBestellwertLocalized}</td>
                                            <td class="text-right">
                                               {$order->Status}
                                            </td>
                                            <td class="text-right d-none d-md-block">
                                                <i class="fa fa-eye"></i>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            {/block}
                            </div>
                        {else}
                            {lang key='noOrdersYet' section='account data'}
                        {/if}
                    {/card}
                {/block}
            {/col}
            {col cols=12 lg=6 class='mb-5'}
                {block name='account-my-account-billing-address'}
                    {card no-body=true}
                        {cardheader class="bg-info"}
                            {row class="align-items-center"}
                                {col}
                                    <span class="h3 mb-0">
                                        {link class='text-decoration-none' href="$cCanonicalURL?editRechnungsadresse=1"}
                                            {lang key='myPersonalData'}
                                        {/link}
                                    </span>
                                {/col}
                                {col class="col-auto font-size-sm"}
                                    {link href="$cCanonicalURL?editRechnungsadresse=1"}
                                        {lang key='showAll'}
                                    {/link}
                                {/col}
                            {/row}
                        {/cardheader}
                        <div class="table-responsive">
                            <table class="table table-vertical-middle table-hover">
                                <tbody>
                                <tr>
                                    <td class="min-w-sm">
                                        {lang key='billingAdress' section='account data'}
                                        <small class="text-muted d-block">{$Kunde->cStrasse}, {$Kunde->cPLZ} {$Kunde->cOrt}, {$Kunde->cLand}</small>
                                    </td>
                                    <td class="text-right">
                                        {link href="$cCanonicalURL?editRechnungsadresse=1"
                                            aria=["label"=>{lang key='editBillingAdress' section='account data'}]
                                        }
                                            <span class="fas fa-pencil-alt"></span>
                                        {/link}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="min-w-sm">
                                        {lang key='contactInformation' section='account data'} {lang key='and'} {lang key='email' section='account data'}
                                        <small class="text-muted d-block">{$Kunde->cMail}</small>
                                    </td>
                                    <td class="text-right">
                                        {link class='float-right' href="$cCanonicalURL?editRechnungsadresse=1"
                                            aria=["label"=>{lang key='editCustomerData' section='account data'}]
                                        }
                                            <span class="fas fa-pencil-alt"></span>
                                        {/link}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="min-w-sm">
                                        {lang key='password' section='account data'}
                                    </td>
                                    <td class="text-right">
                                        {link href="{get_static_route id='jtl.php' params=['pass' => 1]}"
                                            aria=["label"=>{lang key='changePassword' section='login'}]
                                        }
                                            <span class="fas fa-pencil-alt"></span>
                                        {/link}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    {/card}
                {/block}
            {/col}
        {/row}

        {row}
            {col cols=12 lg=6 class='mb-5'}
                {block name='account-my-account-wishlist-content'}
                    {card no-body=true id='my-wishlists'}
                        {cardheader class="bg-info"}
                            <span class="h3 mb-0">
                                {link class='text-decoration-none' href="{get_static_route id='wunschliste.php'}"}
                                    {lang key='myWishlists'}
                                {/link}
                            </span>
                        {/cardheader}
                        {if count($oWunschliste_arr) >0}
                            {block name='account-my-account-wishlists'}
                            <div class="table-responsive">
                                <table class="table table-vertical-middle table-hover">
                                    <tbody>
                                    {foreach $oWunschliste_arr as $wishlist}
                                        <tr>
                                            <td>
                                                {link href="{get_static_route id='wunschliste.php'}?wl={$wishlist->kWunschliste}"}{$wishlist->cName}{/link}<br />
                                                <small>{$wishlist->productCount} {lang key='products'}</small>
                                            </td>
                                            <td class="text-right">
                                                <div class="d-inline-flex flex-nowrap mr-1">
                                                    <span data-switch-label-state="public-{$wishlist->kWunschliste}" class="{if $wishlist->nOeffentlich != 1}d-none{/if}">
                                                        {lang key='public'}
                                                    </span>
                                                    <span data-switch-label-state="private-{$wishlist->kWunschliste}" class="{if $wishlist->nOeffentlich == 1}d-none{/if}">
                                                        {lang key='private'}
                                                    </span>
                                                    <div class="custom-control custom-switch ml-2">
                                                        <input type='checkbox'
                                                               class='custom-control-input wl-visibility-switch'
                                                               id="wl-visibility-{$wishlist->kWunschliste}"
                                                               data-wl-id="{$wishlist->kWunschliste}"
                                                               {if $wishlist->nOeffentlich == 1}checked{/if}
                                                               aria-label="{if $wishlist->nOeffentlich == 1}{lang key='wishlistNoticePublic' section='login'}{else}{lang key='wishlistNoticePrivate' section='login'}{/if}"
                                                        >
                                                        <label class="custom-control-label" for="wl-visibility-{$wishlist->kWunschliste}"></label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            {/block}
                        {else}
                            {lang key='noWishlist' section='account data'}
                        {/if}
                    {/card}
                {/block}
            {/col}
            {col cols=12 lg=6 class='mb-5'}
                {block name='account-my-account-comparelist'}
                    {card no-body=true}
                        {cardheader class='bg-info'}
                            <span class="h3 mb-0">
                                {link class='text-decoration-none' href="{get_static_route id='vergleichsliste.php'}"}
                                    {lang key='myCompareList'}
                                {/link}
                            </span>
                        {/cardheader}
                        {cardbody class="d-flex justify-content-center align-items-center flex-column"}
                            <p>
                                {if count($compareList->oArtikel_arr) > 0}
                                    {lang key='compareListItemCount' section='account data' printf=count($compareList->oArtikel_arr)}
                                {else}
                                    {lang key='compareListNoItems'}
                                {/if}
                            </p>
                            {link class="btn btn-outline-secondary" href="{get_static_route id='vergleichsliste.php'}"}
                                {lang key='goToCompareList' section='comparelist'}
                            {/link}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
        {/row}
    {/block}
    {opcMountPoint id='opc_after_account_page'}

    {block name='account-my-account-include-downloads'}
        {include file='account/downloads.tpl'}
    {/block}

    {block name='account-my-account-actions'}
        {row}
            {col class="col-md-auto"}
                {link class='btn btn-outline-danger btn-block mb-3 mb-md-0' href="{get_static_route id='jtl.php' params=['del' => 1]}"}
                    <span class="fa fa-chain-broken"></span> {lang key='deleteAccount' section='login'}
                {/link}
            {/col}
            {col class="col-md-auto ml-auto"}
                {link href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}" class="btn btn-primary btn-block min-w-sm"}
                    <span class="fa fa-sign-out-alt"></span> {lang key='logOut'}
                {/link}
            {/col}
        {/row}
    {/block}
{/block}
