{include file='layout/header.tpl'}
{if $step === 'formular'}
    {if isset($checkout) && $checkout == 1}
        {include file='checkout/inc_steps.tpl'}
        {if !empty($smarty.session.Kunde->kKunde)}
            {lang key="changeBillingAddress" section="account data" assign="panel_heading"}
        {else}
            {lang key="createNewAccount" section="account data" assign="panel_heading"}
        {/if}
    {elseif empty($smarty.session.Kunde->kKunde)}
        <h1 class="text-center">{lang key="createNewAccount" section="account data"}</h1>
    {/if}

    {include file="snippets/extension.tpl"}
    {include file='register/form.tpl'}

{elseif $step === 'formular eingegangen'}
    <h1>{lang key="accountCreated" section="global"}</h1>
    <p>{lang key="activateAccountDesc" section="global"}</p>
    <br />
{/if}
{include file='layout/footer.tpl'}