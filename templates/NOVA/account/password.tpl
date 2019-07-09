{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-password'}
    {block name='account-password-include-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='account-password-content'}
        {block name='account-password-include-extension'}
            {container}
                {include file='snippets/extension.tpl'}
            {/container}
        {/block}
        {block name='account-password-heading'}
            {container}
                <h1>{lang key='forgotPassword' section='global'}</h1>
            {/container}
        {/block}
        {if $step === 'formular'}
            {container}
                {block name='account-password-alert'}
                    {$alertList->displayAlertByKey('forgotPasswordDesc')}
                {/block}
                {row}
                    {col cols=12 md=8 md-offset=2}
                        {block name='account-password-form-password-reset'}
                            {card}
                                {form id="passwort_vergessen"
                                    action="{get_static_route id='pass.php'}{if $bExclusive === true}?exclusive_content=1{/if}"
                                    method="post"
                                    class="evo-validate label-slide"
                                }
                                    {block name='account-password-form-password-reset-content'}
                                        <fieldset>
                                            {include file='snippets/form_group_simple.tpl'
                                                options=[
                                                    'email', 'email', 'email', null,
                                                    {lang key='emailadress'}, true
                                                ]
                                            }
                                            {block name='account-password-form-reset-submit'}
                                                {formgroup}
                                                    {if $bExclusive === true}
                                                      {input type="hidden" name="exclusive_content" value="1"}
                                                    {/if}
                                                    {input type="hidden" name="passwort_vergessen" value="1"}
                                                    {button type="submit" value="1" block=true class="submit_once" variant="primary"}
                                                        {lang key='createNewPassword' section='forgot password'}
                                                    {/button}
                                                {/formgroup}
                                            {/block}
                                        </fieldset>
                                    {/block}
                                {/form}
                            {/card}
                        {/block}
                    {/col}
                {/row}
            {/container}
        {elseif $step === 'confirm'}
            {container}
                {row}
                    {col cols=12 md=8 md-offset=2}
                        {block name='account-password-form-password-reset-confirm'}
                            {card}
                                <div class="h3">{block name='account-password-password-reset-confirm-title'}{lang key='customerInformation' section='global'}{/block}</div>
                                {form id="passwort_vergessen" action="{get_static_route id='pass.php'}{if $bExclusive === true}?exclusive_content=1{/if}" method="post" class="evo-validate"}
                                    {block name='account-password-form-password-reset-confirm-content'}
                                        <fieldset>
                                            {include file='snippets/form_group_simple.tpl'
                                                options=[
                                                    "password", "pw_new", "pw_new", null,
                                                    {lang key='password' section='account data'}, true
                                                ]
                                            }
                                            {include file='snippets/form_group_simple.tpl'
                                                options=[
                                                    "password", "pw_new_confirm", "pw_new_confirm", null,
                                                    {lang key='passwordRepeat' section='account data'}, true
                                                ]
                                            }
                                            {block name='account-password-form-confirm-submit'}
                                                {formgroup}
                                                    {if $bExclusive === true}
                                                        {input type="hidden" name="exclusive_content" value="1"}
                                                    {/if}
                                                    {input type="hidden" name="fpwh" value=$fpwh}
                                                    {button type="submit" value="1" block=true class="submit_once" variant="primary"}
                                                        {lang key='createNewPassword' section='forgot password'}
                                                    {/button}
                                                {/formgroup}
                                            {/block}
                                        </fieldset>
                                    {/block}
                                {/form}
                            {/card}
                        {/block}
                    {/col}
                {/row}
            {/container}
        {else}
            {block name='account-password-alert-success'}
                {container}
                    {alert variant="success"}{lang key='newPasswortWasGenerated' section='forgot password'}{/alert}
                {/container}
            {/block}
        {/if}
    {/block}

    {block name='account-password-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
