{include file='layout/header.tpl'}
{if $step === 'formular'}
    {if isset($checkout) && $checkout == 1}
        {include file='checkout/inc_steps.tpl'}
        {if !empty($smarty.session.Kunde->kKunde)}
            {lang key="changeBillingAddress" section="account data" assign="panel_heading"}
        {else}
            {lang key="createNewAccount" section="account data" assign="panel_heading"}
        {/if}
    {/if}

    {include file="snippets/extension.tpl"}
    <div id="new_customer" class="row">
    <div class="col-xs-12">
        {if !isset($checkout) && empty($smarty.session.Kunde->kKunde)}
            <h1>{lang key="createNewAccount" section="account data"}</h1>
        {/if}
        <div class="panel-wrap">
            <div class="panel panel-default" id="panel-register-form">
                {if isset($panel_heading)}
                    <div class="panel-heading">
                        <h3 class="panel-title">{$panel_heading}</h3>
                    </div>
                {/if}
                <div class="panel-body">
                    {include file='register/form.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>
                    

{elseif $step === 'formular eingegangen'}
    <h1>{lang key="accountCreated" section="global"}</h1>
    <p>{lang key="activateAccountDesc" section="global"}</p>
    <br />
{/if}
{include file='layout/footer.tpl'}