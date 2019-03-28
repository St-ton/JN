{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1 class="mb-5">{lang key='welcome' section='login'} {$smarty.session.Kunde->cVorname} {$smarty.session.Kunde->cNachname}</h1>
{row}
    {col md=6}
         {$alertList->displayAlertByKey('myAccountDesc')}
    {/col}
    {col md=6}
    {block name='account-credit'}
        {card class='text-center'}
            {lang key='yourMoneyOnAccount' section='login'}: <strong>{$Kunde->cGuthabenLocalized}</strong>
        {/card}
    {/block}
    {/col}
{/row}
<hr class="mt-3 mb-5">
{row class='mb-5'}
    {col cols=12 md=6}
        {card no-body=true}
            {cardheader class="bg-info"}
                {link class='text-decoration-none' href="$cCanonicalURL?bestellungen=1"}
                    {lang key='myOrders'}
                {/link}
                {link class='float-right' href="$cCanonicalURL?bestellungen=1"}
                    {lang key='showAll'}
                {/link}
            {/cardheader}
            {cardbody class='card-table'}
                {if count($Bestellungen) > 0}
                    <div class="text-center">
                    {foreach $Bestellungen as $order}
                        {if $order@index === 5}{break}{/if}
                        {row no-gutters=true class="mb-2"}
                            {col cols=3}
                                {$order->dBestelldatum}
                            {/col}
                            {col cols=3}
                                {$order->cBestellwertLocalized}
                            {/col}
                            {col cols=5}
                                {$order->Status}
                            {/col}
                            {col cols=1}
                                {link href="$cCanonicalURL?bestellung={$order->kBestellung}"
                                    title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}"
                                    data=["toggle" => "tooltip", "placement" => "bottom"]}
                                    <i class="fa fa-eye"></i>
                                {/link}
                            {/col}
                        {/row}
                    {/foreach}
                    </div>
                {else}
                    {lang key='noOrdersYet' section='account data'}
                {/if}
            {/cardbody}
        {/card}
    {/col}
    {col cols=12 md=6}
        {card no-body=true}
            {cardheader class="bg-info"}
                {link class='text-decoration-none' href="$cCanonicalURL?editRechnungsadresse=1"}
                    {lang key='myPersonalData'}
                {/link}
                {link class='float-right' href="$cCanonicalURL?editRechnungsadresse=1"}
                    {lang key='showAll'}
                {/link}
            {/cardheader}
            {cardbody}
                <p>{lang key='billingAdress' section='account data'}<br />
                    <small>{$Kunde->cStrasse}, {$Kunde->cPLZ} {$Kunde->cOrt}, {$Kunde->cLand}</small>
                </p>
                <p>{lang key='contactInformation' section='account data'}, {lang key='email' section='account data'} {lang key='and'} {lang key='password' section='account data'}<br />
                    <small>{$Kunde->cMail}</small>
                </p>
                {link href="{get_static_route id='jtl.php' params=['pass' => 1]}" class='btn btn-light btn-sm'}
                    <span class="fa fa-lock"></span> {lang key='changePassword' section='login'}
                {/link}
            {/cardbody}
        {/card}
    {/col}
{/row}

{row class='mb-5'}
    {col cols=12 md=6}
        {card no-body=true id='my-wishlists'}
            {cardheader class="bg-info"}
            {link class='text-decoration-none' href="{get_static_route id='wunschliste.php'}"}
                {lang key='myWishlists'}
            {/link}
            {/cardheader}
            {cardbody}
                {if count($oWunschliste_arr) >0}
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
                                        title="{lang key='wishlistNotPrivat' section='login'}"}
                                        <i class="fa fa-eye"></i></span>
                                    {/button}
                                {/if}
                                {/form}
                            {/col}
                        {/row}
                    {/foreach}
                {else}
                    {lang key='noWishlist' section='account data'}
                {/if}
            {/cardbody}
        {/card}
    {/col}
    {col cols=12 md=6}
        {card no-body=true}
            {cardheader class="bg-info"}
            {link class='text-decoration-none' href="{get_static_route id='vergleichsliste.php'}"}
                {lang key='myCompareList'}
            {/link}
            {/cardheader}
            {cardbody}
                {if count($compareList->oArtikel_arr) > 0}
                    {lang key='compareListItemCount' section='account data' printf=count($compareList->oArtikel_arr)}
                {else}
                    {lang key='compareListNoItems'}
                {/if}
                <p class="text-center mt-3">{link href="{get_static_route id='vergleichsliste.php'}"}{lang key='goToCompareList' section='comparelist'}{/link}</p>
            {/cardbody}
        {/card}
    {/col}
{/row}

{include file='account/downloads.tpl'}

{row class='mb-5'}
    {col cols=12}
        <div class="float-right">
            <p class="mb-3">
                {link class='btn btn-secondary' href="{get_static_route id='jtl.php' params=['del' => 1]}"}
                    <span class="fa fa-chain-broken"></span> {lang key='deleteAccount' section='login'}
                {/link}
            </p>
            <p class="mb-3">
                {link href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}" class="btn btn-primary"}
                    <span class="fa fa-sign-out-alt"></span>  {lang key='logOut'}
                {/link}
            </p>
        </div>
    {/col}
{/row}
