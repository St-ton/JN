{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-my-account'}
    {block name='heading'}
        <div class="h2 mb-4">{lang key='welcome' section='login'} {$smarty.session.Kunde->cVorname} {$smarty.session.Kunde->cNachname}</div>
    {/block}
    {include file='snippets/opc_mount_point.tpl' id='opc_before_account_page'}
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
        {row class='mb-5'}
            {col cols=12 lg=6}
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
                            <div class="text-center">
                            {block name='account-my-account-orders'}
                                <table class="table table-vertical-middle table-hover">
                                    <tbody>
                                    {foreach $Bestellungen as $order}
                                        {if $order@index === 5}{break}{/if}
                                        <tr>
                                            <td>{$order->dBestelldatum}</td>
                                            <td class="text-right">{$order->cBestellwertLocalized}</td>
                                            <td>{$order->Status}</td>
                                            <td class="text-right">
                                                {link href="$cCanonicalURL?bestellung={$order->kBestellung}"
                                                    title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}"
                                                    data=["toggle" => "tooltip", "placement" => "bottom"]
                                                    class="no-deco"}
                                                    <i class="fa fa-eye"></i>
                                                {/link}
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
            {col cols=12 lg=6}
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
                                    {link class='float-right' href="$cCanonicalURL?editRechnungsadresse=1"}
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
                                        {link class='float-right' href="$cCanonicalURL?editRechnungsadresse=1"}
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
                                        {link class='float-right' href="$cCanonicalURL?editRechnungsadresse=1"}
                                            <span class="fas fa-pencil-alt"></span>
                                        {/link}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="min-w-sm">
                                        {lang key='password' section='account data'}
                                    </td>
                                    <td class="text-right">
                                        {link href="{get_static_route id='jtl.php' params=['pass' => 1]}"}
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

        {row class='mb-5'}
            {col cols=12 lg=6}
                {block name='account-my-account-wishlist-content'}
                    {card no-body=true id='my-wishlists'}
                        {cardheader class="bg-info"}
                            <span class="h3 mb-0">
                                {link class='text-decoration-none' href="{get_static_route id='wunschliste.php'}"}
                                    {lang key='myWishlists'}
                                {/link}
                            </span>
                        {/cardheader}
                        {cardbody}
                            {if count($oWunschliste_arr) >0}
                                {block name='account-my-account-wishlists'}
                                    {foreach $oWunschliste_arr as $wishlist}
                                            {row}
                                                {col md=6}
                                                    <p>{link href="{get_static_route id='wunschliste.php'}?wl={$wishlist->kWunschliste}"}{$wishlist->cName}{/link}<br />
                                                    <small>{$wishlist->productCount} {lang key='products'}</small>
                                                    </p>
                                                {/col}
                                                {col md=6}
                                                    {lang key='currently'}: {if (int)$wishlist->nOeffentlich === 1}{lang key='public'}{else}{lang key='private'}{/if}
                                                    {form method='post' class='float-right' action=$cCanonicalURL}
                                                    {input type='hidden' name='wl' value=$wishlist->kWunschliste}
                                                    {input type='hidden' name='accountPage' value=1}
                                                    {if $wishlist->nOeffentlich == 1}
                                                        {button size="sm"
                                                            type="submit"
                                                            name="wlAction"
                                                            value="setPrivate"
                                                            data=["toggle" => "tooltip", "placement" => "bottom"]
                                                            aria=["label"=>"{lang key='wishlistPrivat' section='login'}"]
                                                            title="{lang key='wishlistPrivat' section='login'}"}
                                                            <i class="fa fa-eye-slash"></i></span>
                                                        {/button}
                                                    {/if}
                                                    {if $wishlist->nOeffentlich == 0}
                                                        {button size="sm"
                                                            type="submit"
                                                            name="wlAction"
                                                            value="setPublic"
                                                            data=["toggle" => "tooltip", "placement" => "bottom"]
                                                            aria=["label"=>"{lang key='wishlistNotPrivat' section='login'}"]
                                                            title="{lang key='wishlistNotPrivat' section='login'}"}
                                                            <i class="fa fa-eye"></i></span>
                                                        {/button}
                                                    {/if}
                                                    {/form}
                                                {/col}
                                            {/row}
                                    {/foreach}
                                {/block}
                            {else}
                                {lang key='noWishlist' section='account data'}
                            {/if}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
            {col cols=12 lg=6}
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
                            {link class="btn btn-outline-secondary btn-sm" href="{get_static_route id='vergleichsliste.php'}"}
                                {lang key='goToCompareList' section='comparelist'}
                            {/link}
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
        {/row}
    {/block}
    {include file='snippets/opc_mount_point.tpl' id='opc_after_account_page'}

    {block name='account-my-account-include-downloads'}
        {include file='account/downloads.tpl'}
    {/block}

    {block name='account-my-account-actions'}
        {row class='mb-5'}
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
