{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1 class="mb-5">{lang key='welcome' section='login'} {if $Kunde->cAnrede === 'w'}{lang key='salutationW'}{elseif $Kunde->cAnrede === 'm'}{lang key='salutationM'}{/if} {$smarty.session.Kunde->cNachname}</h1>
{row}
    {col md=6}
        {block name='account-billing-address'}
            <div id="panel-billing-address" class="mb-5">
                <div class="h3">{block name='account-billing-address-title'}<span class="fa fa-home"></span> {lang key='billingAdress' section='account data'}{/block}</div>
                {block name='account-billing-address-body'}
                    <p>{include file='checkout/inc_billing_address.tpl' additional=false}</p>
                    {link href="{get_static_route id='jtl.php' params=['editRechnungsadresse' => 1]}" class="small edit top15"}
                        <span class="fa fa-pencil"></span> {lang key='modifyBillingAdress' section='global'}
                    {/link}
                {/block}
            </div>
        {/block}
    {/col}
    {col md=6}
        {block name='account-credit'}
            <div class="mb-3">
                {lang key='yourMoneyOnAccount' section='login'}: <strong>{$Kunde->cGuthabenLocalized}</strong>
            </div>
        {/block}
        {block name='account-general'}
            <div id="account-general">
                {if $Einstellungen.kundenwerbenkunden.kwk_nutzen === 'Y'}
                <p class="mb-3">
                    {link href="{get_static_route id='jtl.php' params=['KwK' => 1]}"}
                        <span class="fa fa-comment"></span> {lang key='kwkName' section='login'}
                    {/link}
                </p>
                {/if}
                <p class="mb-3">
                    {link href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}" class="btn btn-secondary"}
                        <span class="fa fa-sign-out-alt"></span>  {lang key='logOut'}
                    {/link}
                </p>
                <p class="mb-5">
                    {link href="{get_static_route id='jtl.php' params=['pass' => 1]}" class="btn btn-light"}
                        <span class="fa fa-lock"></span> {lang key='changePassword' section='login'}
                    {/link}
                </p>
                <p class="mb-3">
                    {link class="btn btn-danger" href="{get_static_route id='jtl.php' params=['del' => 1]}"}
                        <span class="fa fa-chain-broken"></span> {lang key='deleteAccount' section='login'}
                    {/link}
                </p>
            </div>
        {/block}
    {/col}
{/row}
{include file='account/downloads.tpl'}
