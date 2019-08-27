{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-index'}
    {block name='include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='account-index-content'}
        {container}
            {if isset($smarty.get.reg)}
                {block name='account-index-alert'}
                    {alert variant="success"}{lang key='accountCreated' section='global'}{/alert}
                {/block}
            {/if}
            {block name='account-index-include-extension'}
                {include file='snippets/extension.tpl'}
            {/block}

            {if isset($nWarenkorb2PersMerge) && $nWarenkorb2PersMerge === 1}
                {block name='account-index-script-basket-merge'}
                    <script type="text/javascript">
                        var deferredTasks = window.deferredTasks || [];
                        deferredTasks.push(["ready", function () {
                            $(function() {
                                eModal.addLabel('{lang key='yes' section='global'}', '{lang key='no' section='global'}');
                                var options = {
                                    message: '{lang key='basket2PersMerge' section='login'}',
                                    label: '{lang key='yes' section='global'}',
                                    title: '{lang key='basket' section='global'}'
                                };
                                eModal.confirm(options).then(
                                    function() {
                                        window.location = "{get_static_route id='bestellvorgang.php'}?basket2Pers=1"
                                    }
                                );
                            });
                        }]);
                    </script>
                {/block}
            {/if}

            {opcMountPoint id='opc_before_account'}

            {row id="account"}
                {col cols=12}
                    {if $step === 'login'}
                        {block name='account-index-include-login'}
                            {include file='account/login.tpl'}
                        {/block}
                    {elseif $step === 'mein Konto'}
                        {block name='account-index-include-my-account'}
                            {include file='account/my_account.tpl'}
                        {/block}
                    {elseif $step === 'rechnungsdaten'}
                        {block name='account-index-include-address-form'}
                            {include file='account/address_form.tpl'}
                        {/block}
                    {elseif $step === 'passwort aendern'}
                        {block name='account-index-include-change-password'}
                            {include file='account/change_password.tpl'}
                        {/block}
                    {elseif $step === 'bestellung'}
                        {block name='account-index-include-order-details'}
                            {include file='account/order_details.tpl'}
                        {/block}
                    {elseif $step === 'bestellungen'}
                        {block name='account-index-include-orders'}
                            {include file='account/orders.tpl'}
                        {/block}
                    {elseif $step === 'account loeschen'}
                        {block name='account-index-include-delete-account'}
                            {include file='account/delete_account.tpl'}
                        {/block}
                    {elseif $step === 'kunden_werben_kunden'}
                        {block name='account-index-include-customers-recruiting'}
                            {include file='account/customers_recruiting.tpl'}
                        {/block}
                    {elseif $step === 'bewertungen'}
                        {block name='account-index-include-feedback'}
                            {include file='account/feedback.tpl'}
                        {/block}
                    {else}
                        {block name='account-index-include-my-account-default'}
                            {include file='account/my_account.tpl'}
                        {/block}
                    {/if}
                {/col}
            {/row}
        {/container}
    {/block}

    {block name='account-index-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
