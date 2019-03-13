{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {if isset($smarty.get.reg)}
        {alert variant="success"}{lang key='accountCreated' section='global'}{/alert}
    {/if}

    {include file='snippets/extension.tpl'}

    {if isset($nWarenkorb2PersMerge) && $nWarenkorb2PersMerge === 1}
        <script type="text/javascript">
            $(window).on('load', function() {
                $(function() {
                    var options = {
                        message: '{lang key='basket2PersMerge' section='login'}',
                        label1: '{lang key='no' section='global'}',
                        label2: '{lang key='yes' section='global'}',
                        title: '{lang key='basket' section='global'}'
                    };
                    eModal.confirm(options).then(
                        function() {
                            window.location = "{get_static_route id='bestellvorgang.php'}?basket2Pers=1"
                        }
                    );
                });
            });
        </script>
    {/if}

    {row id="account"}
        {col cols=12}
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
            {elseif $step === 'kunden_werben_kunden'}
                {include file='account/customers_recruiting.tpl'}
            {elseif $step === 'bewertungen'}
                {include file='account/feedback.tpl'}
            {else}
                {include file='account/my_account.tpl'}
            {/if}
        {/col}
    {/row}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
