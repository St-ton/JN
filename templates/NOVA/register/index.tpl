{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='register-index'}
    {block name='register-index-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='register-index-content'}
        {container}
            {if $step === 'formular'}
                {if isset($checkout) && $checkout == 1}
                    {block name='register-index-include-inc-steps'}
                        {include file='checkout/inc_steps.tpl'}
                    {/block}
                    {block name='register-index-heading'}
                        {if !empty($smarty.session.Kunde->kKunde)}
                            {lang key='changeBillingAddress' section='account data' assign='panel_heading'}
                        {else}
                            {lang key='createNewAccount' section='account data' assign='panel_heading'}
                        {/if}
                    {/block}
                {/if}
                {block name='register-index-include-extension'}
                    {include file='snippets/extension.tpl'}
                {/block}
                {block name='register-index-alert'}
                {if !empty($fehlendeAngaben)}
                    {alert variant="danger"}{lang key='mandatoryFieldNotification' section='errorMessages'}{/alert}
                {/if}
                {if isset($fehlendeAngaben.email_vorhanden) && $fehlendeAngaben.email_vorhanden == 1}
                    {alert variant="danger"}{lang key='emailAlreadyExists' section='account data'}{/alert}
                {/if}
                {if isset($fehlendeAngaben.formular_zeit) && $fehlendeAngaben.formular_zeit == 1}
                    {alert variant="danger"}{lang key='formToFast' section='account data'}{/alert}
                {/if}
                {/block}
                {block name='register-index-new-customer'}
                    {row id="new_customer"}
                        {col cols=12}
                            {if !isset($checkout) && empty($smarty.session.Kunde->kKunde)}
                                {include file='snippets/opc_mount_point.tpl' id='opc_before_heading'}
                                <h1>{lang key='createNewAccount' section='account data'}</h1>
                            {/if}
                            {include file='snippets/opc_mount_point.tpl' id='opc_before_form_card'}
                            {card id="panel-register-form"}
                                {block name='register-index-include-form'}
                                    {include file='snippets/opc_mount_point.tpl' id='opc_before_form'}
                                    {include file='register/form.tpl'}
                                    {include file='snippets/opc_mount_point.tpl' id='opc_after_form'}
                                {/block}
                            {/card}
                        {/col}
                    {/row}
                {/block}
            {elseif $step === 'formular eingegangen'}
                {block name='register-index-account-created'}
                    {include file='snippets/opc_mount_point.tpl' id='opc_before_heading'}
                    <h1>{lang key='accountCreated'}</h1>
                    {include file='snippets/opc_mount_point.tpl' id='opc_after_heading'}
                    <p>{lang key='activateAccountDesc'}</p>
                {/block}
            {/if}
        {/container}
    {/block}

    {block name='register-index-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
