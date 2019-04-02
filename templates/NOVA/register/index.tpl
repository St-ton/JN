{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    {if $step === 'formular'}
        {if isset($checkout) && $checkout == 1}
            {include file='checkout/inc_steps.tpl'}
            {if !empty($smarty.session.Kunde->kKunde)}
                {lang key='changeBillingAddress' section='account data' assign='panel_heading'}
            {else}
                {lang key='createNewAccount' section='account data' assign='panel_heading'}
            {/if}
        {/if}
    
        {include file='snippets/extension.tpl'}
        {if !empty($fehlendeAngaben)}
            {alert variant="danger"}{lang key='mandatoryFieldNotification' section='errorMessages'}{/alert}
        {/if}
        {if isset($fehlendeAngaben.email_vorhanden) && $fehlendeAngaben.email_vorhanden == 1}
            {alert variant="danger"}{lang key='emailAlreadyExists' section='account data'}{/alert}
        {/if}
        {if isset($fehlendeAngaben.formular_zeit) && $fehlendeAngaben.formular_zeit == 1}
            {alert variant="danger"}{lang key='formToFast' section='account data'}{/alert}
        {/if}
        {row id="new_customer"}
            {col cols=12}
                {if !isset($checkout) && empty($smarty.session.Kunde->kKunde)}
                    <h1>{lang key='createNewAccount' section='account data'}</h1>
                {/if}
                {card id="panel-register-form"}
                    {include file='register/form.tpl'}
                {/card}
            {/col}
        {/row}
    {elseif $step === 'formular eingegangen'}
        <h1>{lang key='accountCreated'}</h1>
        <p>{lang key='activateAccountDesc'}</p>
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
