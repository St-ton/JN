{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file='layout/header.tpl'}

{if isset($smarty.get.reg)}
    <div class="alert alert-success">{lang key="accountCreated" section="global"}</div>
{elseif !isset($hinweis)}
    <div class="alert alert-info">{lang key="myAccountDesc" section="login"}</div>
{/if}

{if (isset($nWarenkorb2PersMerge) && $nWarenkorb2PersMerge === 1)}
    <script type="text/javascript">
        $(function() {
            eModal.confirm('{lang key="basket2PersMerge" section="login"}', '{lang key="basket" section="global"}', function(res) {
                if (res) {
                    window.location = "{get_static_route id='jtl.php'}?basket2Pers=1"
                }
            });
        });
    </script>
{/if}

{$showLoginPanel = true}
{if $step == 'login' || (isset($editRechnungsadresse) && $editRechnungsadresse)}
  {$showLoginPanel = false}
{/if}

<div id="account" class="row">
    {if $showLoginPanel}
        <div class="col-xs-12 col-md-3">

            <div class="list-group">
                <a href="{get_static_route id='jtl.php'}" class="list-group-item{if $step === 'mein Konto'} active{/if}">&Uuml;bersicht</a>
                <a href="{get_static_route id='jtl.php' params=['bestellungen' => 1]}" class="list-group-item{if $step === 'bestellung' || $step === 'bestellungen'} active{/if}">Bestellungen</a>
                <a href="{get_static_route id='jtl.php' params=['editRechnungsadresse' => 1]}" class="list-group-item{if $step === 'rechnungsdaten'} active{/if}">Adressen</a>
                <a href="{get_static_route id='jtl.php' params=['wllist' => 1]}" class="list-group-item{if $step|substr:0:11 === 'wunschliste'} active{/if}">Wunschliste</a>
            </div>
        </div>
    {/if}

    <div class="col-xs-12 {if !$showLoginPanel}col-md-12{else}col-md-9{/if}">

        {if $step === 'login'}
            {include file='account/login.tpl'}
        {elseif $step === 'mein Konto'}
            {include file='account/my_account.tpl'}
        {elseif $step === 'rechnungsdaten'}
            {include file='account/address_form.tpl'}
        {elseif $step === 'passwort aendern'}
            {include file='account/change_password.tpl'}
        {elseif $step === 'bestellung'}
            {include file='account/order_details.tpl'}
        {elseif $step === 'bestellungen'}
            {include file='account/orders.tpl'}
        {elseif $step === 'account loeschen'}
            {include file='account/delete_account.tpl'}
        {elseif $step === 'wunschliste'}
            {include file='account/wishlists.tpl'}
        {elseif $step === 'wunschliste anzeigen'}
            {include file='account/wishlist.tpl'}
        {elseif $step === 'wunschliste versenden'}
            {include file='account/wishlist_email_form.tpl'}
        {elseif $step === 'kunden_werben_kunden'}
            {include file='account/customers_recruiting.tpl'}
        {/if}

    </div>
</div>

{include file='layout/footer.tpl'}