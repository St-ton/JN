{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-login'}
    {card
        class="box box-login mb-7"
        id="sidebox{$oBox->getID()}"
        title="{if empty($smarty.session.Kunde)}{lang key='login'}{else}{lang key='hello'}, {$smarty.session.Kunde->cVorname} {$smarty.session.Kunde->cNachname}{/if}"
    }
        <hr class="mt-0 mb-4">
        {block name='boxes-box-login-content'}
            {if empty($smarty.session.Kunde->kKunde)}
                {block name='boxes-box-login-form'}
                    {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="form box_login evo-validate"}
                        {block name='boxes-box-login-form-data'}
                            {input type="hidden" name="login" value="1"}
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'email', 'email-box-login', 'email', null,{lang key='emailadress'}, true
                                ]
                            }
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'password', 'password-box-login', 'passwort', null,{lang key='password' section='account data'}, true
                                ]
                            }
                        {/block}
                        {if isset($showLoginCaptcha) && $showLoginCaptcha}
                            {block name='boxes-box-login-form-captcha'}
                                {formgroup class="text-center"}
                                    {captchaMarkup getBody=true}
                                {/formgroup}
                            {/block}
                        {/if}
                        {block name='boxes-box-login-form-submit'}
                            {formgroup}
                                {if !empty($oRedirect->cURL)}
                                    {foreach $oRedirect->oParameter_arr as $oParameter}
                                        {input type="hidden" name=$oParameter->Name value=$oParameter->Wert}
                                    {/foreach}
                                    {input type="hidden" name="r" value=$oRedirect->nRedirect}
                                    {input type="hidden" name="cURL" value=$oRedirect->cURL}
                                {/if}
                                {button type="submit" name="speichern" value="1" variant="primary" block=true class="submit"}
                                    {lang key='login' section='checkout'}
                                {/button}
                            {/formgroup}
                        {/block}
                        {block name='boxes-box-login-form-links'}
                            {nav vertical=true class="register-or-resetpw"}
                                {navitem class="resetpw" href="{get_static_route id='pass.php' secure=true}"}
                                    <span class="fa fa-question-circle"></span> {lang key='forgotPassword'}
                                {/navitem}
                                {navitem class="register" href="{get_static_route id='registrieren.php'}"}
                                    <span class="fa fa-pencil-alt"></span> {lang key='newHere'} {lang key='registerNow'}
                                {/navitem}
                            {/nav}
                        {/block}
                    {/form}
                {/block}
            {else}
                {block name='boxes-box-login-actions'}
                    {link href="{get_static_route id='jtl.php'}" class="btn btn-secondary btn-block btn-sm btn-account"}{lang key='myAccount'}{/link}
                    {link href="{get_static_route id='jtl.php'}?logout=1&token={$smarty.session.jtl_token}" class="btn btn-block btn-sm btn-warning btn-logout"}{lang key='logOut'}{/link}
                {/block}
            {/if}
        {/block}
    {/card}
{/block}
