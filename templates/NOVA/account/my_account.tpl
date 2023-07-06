{block name='account-my-account'}
    {block name='heading'}
        <div class="h2">{lang key='welcome' section='login'} {$Kunde->cVorname} {$Kunde->cNachname}</div>
    {/block}
    {opcMountPoint id='opc_before_account_page'}
    {block name='account-my-account-head-data'}
        {row class="account-head-data"}
            {col cols=12 lg=6}
                {block name='account-my-account-alert'}
                    {lang key='myAccountDesc' section='login'}
                {/block}
            {/col}
            {col cols=12 lg=6}
                {block name='account-my-account-account-credit'}
                    {card class='account-head-data-credit'}
                        {lang key='yourMoneyOnAccount' section='login'}: {$Kunde->cGuthabenLocalized}
                    {/card}
                {/block}
            {/col}
        {/row}
    {/block}
    {block name='account-my-account-account-data'}
        {row}
            {col cols=12 lg=6 class="account-data-item account-data-item-address"}
                {block name='account-my-account-billing-address'}
                    {card no-body=true}
                        {cardheader}
                            {block name='account-my-account-billing-address-header'}
                                {row class="align-items-center-util"}
                                    {col}
                                        <span class="h3">
                                            {link class='text-decoration-none-util'
                                                href="$cCanonicalURL?editRechnungsadresse=1"}
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
                            {/block}
                        {/cardheader}
                        {block name='account-my-account-billing-address-body'}
                            <div class="table-responsive">
                                <table class="table table-vertical-middle table-hover">
                                    <tbody>
                                    {block name='account-my-account-billing-address-billing-address'}
                                        <tr>
                                            <td class="min-w-sm">
                                                {lang key='billingAdress' section='account data'}
                                                <small class="text-muted-util d-block">{$Kunde->cStrasse}
                                                    {$Kunde->cHausnummer}, {$Kunde->cPLZ} {$Kunde->cOrt}, {$Kunde->cLand}
                                                </small>
                                            </td>
                                            <td class="text-right-util">
                                                {link href="$cCanonicalURL?editRechnungsadresse=1" aria=["label"=>{lang
                                                key='editBillingAdress' section='account data'}]}
                                                    <span class="fas fa-pencil-alt"></span>
                                                {/link}
                                            </td>
                                        </tr>
                                    {/block}
                                    {block name='account-my-account-billing-address-contact'}
                                        <tr>
                                            <td class="min-w-sm">
                                                {lang key='contactInformation' section='account data'} {lang key='and'}
                                                {lang key='email' section='account data'}
                                                <small class="text-muted-util d-block">{$Kunde->cMail}</small>
                                            </td>
                                            <td class="text-right-util">
                                                {link class='float-right' href="$cCanonicalURL?editRechnungsadresse=1"
                                                aria=["label"=>{lang key='editCustomerData' section='account data'}]
                                                }
                                                    <span class="fas fa-pencil-alt"></span>
                                                {/link}
                                            </td>
                                        </tr>
                                    {/block}
                                    {block name='account-my-account-billing-address-password'}
                                        <tr>
                                            <td class="min-w-sm">
                                                {lang key='password' section='account data'}
                                            </td>
                                            <td class="text-right-util">
                                                {link href="{get_static_route id='jtl.php' params=['pass' => 1]}"
                                                aria=["label"=>{lang key='changePassword' section='login'}]}
                                                    <span class="fas fa-pencil-alt"></span>
                                                {/link}
                                            </td>
                                        </tr>
                                    {/block}
                                </table>
                            </div>
                        {/block}
                    {/card}
                {/block}
            {/col}

            {col cols=12 lg=6 class="account-data-item account-data-shipping-address"}
                {block name='account-my-account-shipping-address-shipping-address'}
                    {card no-body=true class="account-shipping-address"}
                        {cardheader}
                            {block name='account-my-account-shipping-address-header'}
                                {row class="align-items-center-util"}
                                    {col}
                                        <span class="h3">
                                            {link class='text-decoration-none-util' href="$cCanonicalURL?editLieferadresse=1"}
                                                {lang key='myShippingAddresses'}
                                            {/link}
                                        </span>
                                    {/col}
                                    {col class="col-auto font-size-sm"}
                                        {link href="$cCanonicalURL?editLieferadresse=1" aria=["label"=>{lang
                                        key='editShippingAddress' section='account data'}]}
                                            {lang key='shippingAddress' section='account data'}
                                        {/link}
                                    {/col}
                                {/row}
                            {/block}
                        {/cardheader}
                        {block name='account-my-account-shipping-address-body'}
                            {if count($Lieferadressen) > 0}
                                {block name='account-my-account-shipping-addresses'}
                                    <div class="table-responsive">
                                        <table class="table table-vertical-middle table-hover">
                                            <tbody>
                                            {foreach $Lieferadressen as $lieferadresse}
                                                <tr>
                                                    {block name='account-my-account-shipping-address-name'}
                                                        <td>
                                                            {$lieferadresse->cStrasse} {$lieferadresse->cHausnummer},
                                                            {$lieferadresse->cPLZ}
                                                            {$lieferadresse->cOrt}, {$lieferadresse->cLand}
                                                            <small class="text-muted-util d-block">
                                                                Bestellungen
                                                                <span class="badge badge-light">
                                                                    {$lieferadresse->totalBestellungen}
                                                                </span>
                                                            </small>
                                                        </td>
                                                    {/block}
                                                    {block name='account-my-account-shipping-address-default'}
                                                        <td class="text-right-util">
                                                            <div class="d-inline-flex flex-nowrap">
                                                                <span data-switch-label-state="default-{$lieferadresse->kLieferadresse}" class="">
                                                                    {lang key='standard'}
                                                                </span>
                                                                <div class="custom-control custom-switch">
                                                                    <input type='checkbox'
                                                                           class='custom-control-input la-default-switch'
                                                                           id="la-default-{$lieferadresse->kLieferadresse}"
                                                                           data-la-id="{$lieferadresse->kLieferadresse}"
                                                                           {if $lieferadresse->nIstStandardLieferadresse > 0}checked{/if}
                                                                           aria-label="{if $lieferadresse->nIstStandardLieferadresse > 0}{lang key='defaultShippingAddresses' section="account data"}{/if}">
                                                                    <label class="custom-control-label" for="la-default-{$lieferadresse->kLieferadresse}"></label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    {/block}
                                                </tr>
                                            {/foreach}
                                            </tbody>
                                        </table>
                                    </div>
                                {/block}
                            {else}
                                {block name='account-my-account-shipping-address-no-data'}
                                    {cardbody}
                                        <p>
                                            Sie haben noch keine Lieferadressen.
                                        </p>
                                        {link class="btn btn-outline-secondary btn-sm" href="$cCanonicalURL?editLieferadresse=1"}
                                            <i class="fa fa-truck" aria-label="{lang key='shippingAdress' section='account data'}"
                                               title="{lang key='shippingAdress' section='account data'}"></i>
                                            Lieferadressen anlegen
                                        {/link}
                                    {/cardbody}
                                {/block}
                            {/if}
                        {/block}
                    {/card}
                {/block}
            {/col}
        {/row}
        {row}
            {col cols=12 lg=6 class="account-data-item account-data-item-orders"}
                {block name='account-my-account-orders-content'}
                    {card no-body=true}
                        {cardheader}
                            {block name='account-my-account-orders-content-header'}
                                {row class="align-items-center-util"}
                                    {col}
                                        <span class="h3">
                                            {link class='text-decoration-none-util' href="$cCanonicalURL?bestellungen=1"}
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
                            {/block}
                        {/cardheader}
                        {if count($Bestellungen) > 0}
                            {block name='account-my-account-orders-body'}
                                <div class="table-responsive">
                                    <table class="table table-vertical-middle table-hover">
                                        <tbody>
                                        {foreach $Bestellungen as $order}
                                            {if $order@index === 5}{break}{/if}
                                            <tr title="{lang key='showOrder' section='login'}: {lang key='orderNo'
                                            section='login'} {$order->cBestellNr}"
                                                class="clickable-row cursor-pointer"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                data-boundary="window"
                                                data-href="{$cCanonicalURL}?bestellung={$order->kBestellung}">
                                                <td>{$order->dBestelldatum}</td>
                                                <td class="text-right-util">{$order->cBestellwertLocalized}</td>
                                                <td class="text-right-util">
                                                   {$order->Status}
                                                </td>
                                                <td class="text-right-util d-none d-md-table-cell">
                                                    <a href="{$cCanonicalURL}?return={$order->kBestellung}"
                                                       class="me-2 mr-2"><i class="fa fa-retweet" aria-label="{lang key='rma' section='rma'}: {lang key='rma_artikelwahl' section='rma'}" title="{lang key='rma' section='rma'}: {lang key='rma_artikelwahl' section='rma'}"></i></a>
                                                    <i class="fa fa-eye"></i>
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            {/block}
                        {else}
                            {block name='account-my-account-orders-content-nodata'}
                                {cardbody}
                                    {lang key='noOrdersYet' section='account data'}
                                {/cardbody}
                            {/block}
                        {/if}
                    {/card}
                {/block}
            {/col}
            {col cols=12 lg=6 class="account-data-item account-data-item-rma"}
                {block name='account-my-account-rma'}
                    {card no-body=true class="account-rma"}
                        {assign var=rmas value=$RMAService->getRMAs()}
                        {cardheader}
                            {block name='account-my-account-comparelist-header'}
                                {row class="align-items-center-util"}
                                    {col}
                                        <span class="h3">
                                            {if count($rmas) > 0}
                                                {link class='text-decoration-none-util' href="$cCanonicalURL?rmas=1"}
                                                    {lang key='myReturns' section='rma'}
                                                {/link}
                                            {else}
                                                {lang key='myReturns' section='rma'}
                                            {/if}
                                        </span>
                                    {/col}
                                    {if count($rmas) > 0}
                                        {col class="col-auto font-size-sm"}
                                            {link href="$cCanonicalURL?rmas=1"}
                                                {lang key='manageReturns' section='rma'}
                                            {/link}
                                        {/col}
                                    {/if}
                                {/row}
                            {/block}
                        {/cardheader}
                        {block name='account-my-account-comparelist-body'}
                            {if count($rmas) > 0}
                                <div class="table-responsive">
                                    <table class="table table-vertical-middle table-hover">
                                        <tbody>
                                            {foreach $rmas as $rma}
                                                <tr>
                                                    <td>
                                                        <div class="d-block font-weight-bold">
                                                            <span class="far fa-calendar mr-2"></span>{$rma->getCreateDate()}
                                                        </div>
                                                        <small class="text-muted-util d-block font-weight-bold">
                                                            Artikel
                                                            <span class="badge badge-light">
                                                                {$rma->getPositions()|count|default:0}
                                                            </span>
                                                        </small>
                                                        {assign var=pkAddress value=$rma->getPickupAddress()}
                                                        {if $pkAddress !== null}
                                                            <small class="text-muted-util d-block">
                                                                {if $pkAddress->getCompanyName()}{$pkAddress->getCompanyName()}<br />{/if}
                                                                {$pkAddress->getStreet()} {$pkAddress->getHouseNumber()}<br />
                                                                {$pkAddress->getPostalCode()} {$pkAddress->getCity()}
                                                            </small>
                                                        {/if}
                                                    </td>
                                                    <td class="text-right-util">
                                                        {link class='float-right' href="$cCanonicalURL?editRMA={$rma->getID()}"
                                                        aria=["label"=>"Retoure bearbeiten"]}
                                                            <span class="fas fa-pencil-alt"></span>
                                                        {/link}
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            {else}
                                {block name='account-my-account-rma-no-data'}
                                    {cardbody class="d-flex justify-content-center align-items-center flex-column"}
                                        <p>Sie haben noch keine Warenrücksendungen.</p>
                                        {link class="btn btn-outline-secondary btn-sm" href="$cCanonicalURL?newRMA=1"}
                                            {lang key='createRetoure' section='rma'}
                                        {/link}
                                    {/cardbody}
                                {/block}
                            {/if}
                        {/block}
                    {/card}
                {/block}
            {/col}
        {/row}
        {row}
            {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
            {col cols=12 lg=6 class="account-data-item account-data-item-wishlist"}
                {block name='account-my-account-wishlist-content'}
                    {card no-body=true id='my-wishlists'}
                        {cardheader}
                            {block name='account-my-account-wishlist-header'}
                                <span class="h3">
                                    {link class='text-decoration-none-util' href="{get_static_route id='wunschliste.php'}"}
                                        {lang key='myWishlists'}
                                    {/link}
                                </span>
                            {/block}
                        {/cardheader}
                        {if count($oWunschliste_arr) >0}
                            {block name='account-my-account-wishlists'}
                            <div class="table-responsive">
                                <table class="table table-vertical-middle table-hover">
                                    <tbody>
                                    {get_static_route id='wunschliste.php' assign='wlSlug'}
                                    {foreach $oWunschliste_arr as $wishlist}
                                        <tr>
                                            {block name='account-my-account-wishlist-name'}
                                                <td>
                                                    {link href="{$wlSlug}?wl={$wishlist->getID()}"}{$wishlist->getName()}{/link}<br />
                                                    <small>{$wishlist->getProductCount()} {lang key='products'}</small>
                                                </td>
                                            {/block}
                                            {block name='account-my-account-wishlist-visibility'}
                                                <td class="text-right-util">
                                                    <div class="d-inline-flex flex-nowrap">
                                                        <span data-switch-label-state="public-{$wishlist->getID()}" class="{if $wishlist->isPublic() !== true}d-none{/if}">
                                                            {lang key='public'}
                                                        </span>
                                                        <span data-switch-label-state="private-{$wishlist->getID()}" class="{if $wishlist->isPublic()}d-none{/if}">
                                                            {lang key='private'}
                                                        </span>
                                                        <div class="custom-control custom-switch">
                                                            <input type='checkbox'
                                                                   class='custom-control-input wl-visibility-switch'
                                                                   id="wl-visibility-{$wishlist->getID()}"
                                                                   data-wl-id="{$wishlist->getID()}"
                                                                   {if $wishlist->isPublic()}checked{/if}
                                                                   aria-label="{if $wishlist->isPublic()}{lang key='wishlistNoticePublic' section='login'}{else}{lang key='wishlistNoticePrivate' section='login'}{/if}"
                                                            >
                                                            <label class="custom-control-label" for="wl-visibility-{$wishlist->getID()}"></label>
                                                        </div>
                                                    </div>
                                                </td>
                                            {/block}
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            {/block}
                        {else}
                            {block name='account-my-account-wishlist-no-data'}
                                {cardbody}
                                    {lang key='noWishlist' section='account data'}
                                {/cardbody}
                            {/block}
                        {/if}
                    {/card}
                {/block}
            {/col}
            {/if}
            {if $Einstellungen.vergleichsliste.vergleichsliste_anzeigen === 'Y'}
            {col cols=12 lg=6 class="account-data-item account-data-item-comparelist"}
                {block name='account-my-account-comparelist'}
                    {card no-body=true class="account-comparelist"}
                        {cardheader}
                            {block name='account-my-account-comparelist-header'}
                                <span class="h3">
                                    {link class='text-decoration-none-util' href="{get_static_route id='vergleichsliste.php'}"}
                                        {lang key='myCompareList'}
                                    {/link}
                                </span>
                            {/block}
                        {/cardheader}
                        {cardbody}
                            {block name='account-my-account-comparelist-body'}
                                <p>
                                    {if count($compareList->oArtikel_arr) > 0}
                                        {lang key='compareListItemCount' section='account data' printf=count($compareList->oArtikel_arr)}
                                    {else}
                                        {lang key='compareListNoItems'}
                                    {/if}
                                </p>
                                {link class="btn btn-outline-secondary btn-sm" href="{get_static_route id='vergleichsliste.php'}"}
                                    {lang key='goToCompareList' section='comparelist'}
                                {/link}
                            {/block}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
            {/if}
        {/row}
    {/block}
    {opcMountPoint id='opc_after_account_page'}

    {block name='account-my-account-include-downloads'}
        {include file='account/downloads.tpl'}
    {/block}

    {block name='account-my-account-actions'}
        {row class="btn-row"}
            {col md="auto" cols=12}
                {link class='btn btn-outline-danger btn-back' href="{get_static_route id='jtl.php' params=['del' => 1]}"}
                    <span class="fa fa-chain-broken"></span> {lang key='deleteAccount' section='login'}
                {/link}
            {/col}
            {col md="auto" cols=12 class="ml-auto-util"}
                {link href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}" class="btn btn-primary btn-block min-w-sm"}
                    <span class="fa fa-sign-out-alt"></span> {lang key='logOut'}
                {/link}
            {/col}
        {/row}
    {/block}
{/block}
