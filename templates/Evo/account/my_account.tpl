<h1>{lang key="welcome" section="login"} {$Kunde->cAnredeLocalized} {$smarty.session.Kunde->cNachname}</h1>

{if isset($smarty.get.reg)}
    <div class="alert alert-success">{lang key="accountCreated" section="global"}</div>
{elseif !isset($hinweis)}
    <div class="alert alert-info">{lang key="myAccountDesc" section="login"}</div>
{/if}

{if $hinweis}
    <div class="alert alert-info">{$hinweis}</div>
{/if}

{if $cFehler}
    <div class="alert alert-danger">{$cFehler}</div>
{/if}

{include file="snippets/extension.tpl"}

{if $Bestellungen|@count > 0}
    {block name="account-orders"}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{block name="account-orders-title"}{lang key="yourOrders" section="login"}{/block}</h3>
        </div>
        <div class="panel-body">
            {block name="account-orders-body"}
            {assign var=bDownloads value=false}
            {foreach name=bestellungen from=$Bestellungen item=Bestellung}
                {if isset($Bestellung->bDownload) && $Bestellung->bDownload > 0}
                    {assign var=bDownloads value=true}
                {/if}
            {/foreach}

            <table class="table table-striped">
                <thead class="hidden-xs">
                <tr>
                    <th>{lang key="orderNo" section="login"}</th>
                    <th>{lang key="value" section="login"}</th>
                    <th>{lang key="orderDate" section="login"}</th>
                    <th class="hidden-xs">{lang key="orderStatus" section="login"}</th>
                    {if $bDownloads}
                        <th class="hidden-xs">{lang key="downloads" section="global"}</th>
                    {/if}
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody class="small">
                {foreach name=bestellungen from=$Bestellungen item=Bestellung}
                    <tr>
                        <td>{$Bestellung->cBestellNr}</td>
                        <td>{$Bestellung->cBestellwertLocalized}</td>
                        <td>{$Bestellung->dBestelldatum}</td>
                        <td class="hidden-xs">{$Bestellung->Status}</td>
                        {if $bDownloads}
                            <td class="hidden-xs">
                                {if isset($Bestellung->bDownload) && $Bestellung->bDownload > 0}
                                    <div class="dl_active"></div>
                                {/if}
                            </td>
                        {/if}
                        <td class="text-right">
                            <a class="btn btn-default btn-xs" href="jtl.php?bestellung={$Bestellung->kBestellung}" title="{lang key="showOrder" section="login"}: {lang key="orderNo" section="login"} {$Bestellung->cBestellNr}">
                                <span class="fa fa-list-alt"></span> <span class="hidden-xs">{lang key="showOrder" section="login"}</span>
                            </a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            {/block}
        </div>
    </div>
    {/block}
{/if}

<div class="row">
    {* Rechnungsadresse *}
    <div class="col-xs-12 col-lg-4">
        {block name="account-billing-address"}
        <div class="panel panel-default" id="panel-billing-address">
            <div class="panel-heading">
                <h3 class="panel-title">{block name="account-billing-address-title"}{lang key="billingAdress" section="account data"}{/block}</h3>
            </div>
            <div class="panel-body">
                {block name="account-billing-address-body"}
                <p>
                    {include file='checkout/inc_billing_address.tpl'}
                </p>
                <form method="post" action="jtl.php">
                    {$jtl_token}

                    <button class="btn btn-default btn-sm btn-block" name="editRechnungsadresse" value="1">
                        <span class="fa fa-home"></span> {lang key="modifyBillingAdress" section="global"}
                    </button>
                </form>
                {/block}
            </div>
        </div>
        {/block}
    </div>

    <div class="col-xs-12 col-lg-8">
        {* Wishlist *}
        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
            {block name="account-wishlist"}
            <div id="wishlist" class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{block name="account-wishlist-title"}{lang key="yourWishlist" section="login"}{/block}</h3>
                </div>
                <div class="panel-body">
                    {block name="account-wishlist-body"}
                    {if !empty($oWunschliste_arr[0]->kWunschliste)}
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{lang key="wishlistName" section="login"}</th>
                                <th>{lang key="wishlistStandard" section="login"}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach name=wunschlisten from=$oWunschliste_arr item=Wunschliste}
                                <tr>
                                    <td><a href="jtl.php?wl={$Wunschliste->kWunschliste}">{$Wunschliste->cName}</a></td>
                                    <td>{if $Wunschliste->nStandard == 1}{lang key="active" section="global"}{/if} {if $Wunschliste->nStandard == 0}{lang key="inactive" section="global"}{/if}</td>
                                    <td class="text-right">
                                        <form method="post" action="jtl.php">
                                            {$jtl_token}
                                            <span class="btn-group">
                                                {if $Wunschliste->nStandard != 1}
                                                    <button class="btn btn-default btn-xs" name="wls" value="{$Wunschliste->kWunschliste}">
                                                        <span class="fa fa-ok"></span> {lang key="wishlistStandard" section="login"}
                                                    </button>
                                                {/if}
                                                {if $Wunschliste->nOeffentlich == 1}
                                                    <button type="submit" class="btn btn-default btn-xs" name="wl" value="{$Wunschliste->kWunschliste}" title="{lang key="wishlistPrivat" section="login"}">
                                                        <span class="fa fa-eye-close"></span><span class="hidden-xs"> {lang key="wishlistSetPrivate" section="login"}</span>
                                                    </button>
                                                {/if}
                                                {if $Wunschliste->nOeffentlich == 0}
                                                    <button type="submit" class="btn btn-default btn-xs" name="wl" value="{$Wunschliste->kWunschliste}" title="{lang key="wishlistNotPrivat" section="login"}">
                                                        <span class="fa fa-eye-slash"></span><span class="hidden-xs"> {lang key="wishlistNotPrivat" section="login"}</span>
                                                    </button>
                                                {/if}
                                                <button type="submit" class="btn btn-danger btn-xs" name="wllo" value="{$Wunschliste->kWunschliste}">
                                                    <span class="fa fa-trash-o"></span>
                                                </button>
                                            </span>
                                        </form>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    {/if}
                    <form method="post" action="jtl.php" class="form form-inline">
                        {$jtl_token}
                        <input name="wlh" type="hidden" value="1" />
                        <div class="input-group">
                            <input name="cWunschlisteName" type="text" class="form-control input-sm" placeholder="{lang key="wishlistAddNew" section="login"}" size="25">
                            <span class="input-group-btn">
                                <input type="submit" class="btn btn-default btn-sm" name="submit" value="{lang key="wishlistSaveNew" section="login"}" />
                            </span>
                        </div>
                    </form>
                    {/block}
                </div>
            </div>
            {/block}
        {/if}

        {block name="account-credit"}
        <div class="panel panel-default pull-right">
            <div class="panel-body">
                {lang key="yourMoneyOnAccount" section="login"}: <strong>{$Kunde->cGuthabenLocalized}</strong>
            </div>
        </div>
        {/block}
    </div>

</div>{* /row *}

<div class="btn-group pull-right">
    {if $Einstellungen.kundenwerbenkunden.kwk_nutzen === 'Y'}
        <a class="btn btn-default" href="jtl.php?KwK=1">
            <span class="fa fa-comment"></span> {lang key="kwkName" section="login"}
        </a>
    {/if}
    <a class="btn btn-default" href="jtl.php?pass=1">
        <span class="fa fa-lock"></span> {lang key="changePassword" section="login"}
    </a>
    <a class="btn btn-danger" href="jtl.php?del=1">
        <span class="fa fa-chain-broken"></span> {lang key="deleteAccount" section="login"}
    </a>
</div>

{include file="account/downloads.tpl"}
{include file="account/uploads.tpl"}

{if isset($nWarenkorb2PersMerge) && $nWarenkorb2PersMerge == 1}
   <script type="text/javascript">
       var cAnwort = confirm('{lang key="basket2PersMerge" section="login"}');
       if(cAnwort) window.location = "jtl.php?basket2Pers=1";
   </script>
{/if}