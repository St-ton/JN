{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-login'}
    {block name='account-login-heading'}
        <h1>{if !empty($oRedirect->cName)}{$oRedirect->cName}{else}{lang key='loginTitle' section='login'}{/if}</h1>
    {/block}
    {if !$bCookieErlaubt}
        {block name='account-login-alert-no-cookie'}
            {alert variant="danger" class="d-none" id="no-cookies-warning"}
                <strong>{lang key='noCookieHeader' section='errorMessages'}</strong>
                <p>{lang key='noCookieDesc' section='errorMessages'}</p>
            {/alert}
        {/block}
        {block name='account-login-script-no-cookie'}
            <script type="text/javascript">
                var deferredTasks = window.deferredTasks || [];
                deferredTasks.push(["ready",function () {
                    $(function () {
                        if (navigator.cookieEnabled === false) {
                            $('#no-cookies-warning').show();
                        }
                    });
                }]);
            </script>
        {/block}
    {elseif !$alertNote}
        {block name='account-login-alert'}
            {alert variant="info"}{lang key='loginDesc' section='login'} {if isset($oRedirect->cName) && $oRedirect->cName}{lang key='redirectDesc1'} {$oRedirect->cName} {lang key='redirectDesc2'}.{/if}{/alert}
        {/block}
    {/if}

    {block name='account-login-form'}
        {include file='snippets/opc_mount_point.tpl' id='opc_before_login'}
        {row}
            {col sm=8 lg=6}
                {form id="login_form" action="{get_static_route id='jtl.php'}" method="post" role="form" class="evo-validate label-slide"}
                    <fieldset>
                        <legend>{lang key='loginForRegisteredCustomers' section='checkout'}</legend>
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'email', 'email', 'email', null,
                                {lang key='emailadress'}, true
                            ]
                        }

                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'password', 'password', 'passwort', null,
                                {lang key='password' section='account data'}, true
                            ]
                        }

                        {if isset($showLoginCaptcha) && $showLoginCaptcha}
                           {formgroup class="text-center"}
                                {captchaMarkup getBody=true}
                           {/formgroup}
                        {/if}

                        {block name='account-login-form-submit'}
                            {formgroup}
                                {input type="hidden" name="login" value="1"}
                                {if !empty($oRedirect->cURL)}
                                    {foreach $oRedirect->oParameter_arr as $oParameter}
                                        {input type="hidden" name=$oParameter->Name value=$oParameter->Wert}
                                    {/foreach}
                                    {input type="hidden" name="r" value=$oRedirect->nRedirect}
                                    {input type="hidden" name="cURL" value=$oRedirect->cURL}
                                {/if}
                                {button type="submit" value="1" block=true variant="primary"}
                                    {lang key='login' section='checkout'}
                                {/button}
                            {/formgroup}

                            <div class="register-or-resetpw top15">
                                <small>
                                   {link class="register pull-left" href="{get_static_route id='registrieren.php'}"}<span class="fa fa-pencil"></span> {lang key='newHere'} {lang key='registerNow'}{/link}
                                   {link class="resetpw ml-3 pull-right" href="{get_static_route id='pass.php'}"}<span class="fa fa-question-circle"></span> {lang key='forgotPassword'}{/link}
                                </small>
                            </div>
                        {/block}
                    </fieldset>
                {/form}
            {/col}
        {/row}
    {/block}
{/block}
