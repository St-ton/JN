{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='header'}
    {include file='layout/header.tpl'}
{/block}

{block name='content'}
    <h1>{lang key='forgotPassword' section='global'}</h1>
    
    {include file='snippets/extension.tpl'}
    {if $step === 'formular'}
        {$alertList->displayAlertByKey('forgotPasswordDesc')}
        {row}
            {col cols=12 md=8 md-offset=2}
                {block name='password-reset-form'}
                {card}
                    {block name='password-reset-form-body'}
                    {form id="passwort_vergessen" action="{get_static_route id='pass.php'}{if $bExclusive === true}?exclusive_content=1{/if}" method="post" class="evo-validate"}
                        {$jtl_token}
                        <fieldset>
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'email', 'email', 'email', null,
                                    {lang key='emailadress'}, true
                                ]
                            }
                            {formgroup}
                                {if $bExclusive === true}
                                  {input type="hidden" name="exclusive_content" value="1"}
                                {/if}
                                {input type="hidden" name="passwort_vergessen" value="1"}
                                {input type="submit" class="btn btn-primary btn-block submit submit_once" value="{lang key='createNewPassword' section='forgot password'}"}
                            {/formgroup}
                        </fieldset>
                    {/form}
                    {/block}
                {/card}
                {/block}
            {/col}
        {/row}
    {elseif $step === 'confirm'}
        {row}
            {col cols=12 md=8 md-offset=2}
                {block name='password-reset-confirm'}
                    {card}
                        <div class="h3">{block name='password-reset-confirm-title'}{lang key='customerInformation' section='global'}{/block}</div>
                        {block name='password-reset-confirm-body'}
                        {form id="passwort_vergessen" action="{get_static_route id='pass.php'}{if $bExclusive === true}?exclusive_content=1{/if}" method="post" class="evo-validate"}
                            {$jtl_token}
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
                                {formgroup}
                                    {if $bExclusive === true}
                                        {input type="hidden" name="exclusive_content" value="1"}
                                    {/if}
                                    {input type="hidden" name="fpwh" value="{$fpwh}"}
                                    {input type="submit" class="btn btn-primary btn-block submit submit_once" value="{lang key='createNewPassword' section='forgot password'}"}
                                {/formgroup}
                            </fieldset>
                        {/form}
                        {/block}
                    {/card}
                {/block}
            {/col}
        {/row}
    {else}
        {alert variant="success"}{lang key='newPasswortWasGenerated' section='forgot password'}{/alert}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
