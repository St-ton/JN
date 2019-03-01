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
                $(function () {
                    eModal.confirm({ldelim}
                            message: '{lang key='basket2PersMerge' section='login'}',
                            label1: '{lang key='no' section='global'}',
                            label2: '{lang key='yes' section='global'}'
                                {rdelim},
                        '{lang key='basket' section='global'}',
                        function (res) {
                            if (res) {
                                window.location = "{get_static_route id='jtl.php'}?basket2Pers=1"
                            }
                        }
                    );
                });
            } );
        </script>
    {/if}

    {if !isset($showLoginPanel)}
        {$showLoginPanel = true}
    {/if}
    {if $step === 'login' || (!empty($editRechnungsadresse))}
        {$showLoginPanel = false}
    {/if}

    {row id="account"}
        {if $showLoginPanel}
            {col cols=12 md=3}
                {listgroup class="mb-5"}
                    {listgroupitem href="{get_static_route id='jtl.php'}" active=($step === 'mein Konto')}
                        {lang key='accountOverview' section='account data'}
                    {/listgroupitem}
                    {listgroupitem href="{get_static_route id='jtl.php' params=['bestellungen' => 1]}" active=($step === 'bestellung' || $step === 'bestellungen')}
                        {lang key='orders' section='account data'}
                    {/listgroupitem}
                    {listgroupitem href="{get_static_route id='jtl.php' params=['editRechnungsadresse' => 1]}" active=($step === 'rechnungsdaten')}
                        {lang key='address' section='account data'}
                    {/listgroupitem}
                    {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
                        {listgroupitem href="{get_static_route id='jtl.php' params=['wllist' => 1]}" active=($step|substr:0:11 === 'wunschliste')}
                            {lang key='wishlists' section='account data'}
                        {/listgroupitem}
                    {/if}
                    {listgroupitem href="{get_static_route id='jtl.php' params=['bewertungen' => 1]}" active=($step === 'bewertungen')}
                        {lang key='allRatings'}
                    {/listgroupitem}
                {/listgroup}
            {/col}
        {/if}
        {col cols=12 md="{if !$showLoginPanel}12{else}9{/if}"}
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
            {elseif $step === 'bewertungen'}
                {include file='account/feedback.tpl'}
            {/if}
        {/col}
    {/row}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
