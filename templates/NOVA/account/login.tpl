{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{if !empty($oRedirect->cName)}{$oRedirect->cName}{else}{lang key='loginTitle' section='login'}{/if}</h1>
{if !$bCookieErlaubt}
    {alert variant="danger" class="d-none" id="no-cookies-warning"}
        <strong>{lang key='noCookieHeader' section='errorMessages'}</strong>
        <p>{lang key='noCookieDesc' section='errorMessages'}</p>
    {/alert}
    <script type="text/javascript">
       $(function() {ldelim}
           if (navigator.cookieEnabled === false) {ldelim}
               $('#no-cookies-warning').show();
           {rdelim}
       {rdelim});
    </script>
{elseif !$alertNote}
    {alert variant="info"}{lang key='loginDesc' section='login'} {if isset($oRedirect) && $oRedirect->cName}{lang key='redirectDesc1'} {$oRedirect->cName} {lang key='redirectDesc2'}.{/if}{/alert}
{/if}

{include file='snippets/extension.tpl'}

{row}
    {col sm=8 offset-sm=2}
        {block name='login-form'}
        {card}
            {form id="login_form" action="{get_static_route id='jtl.php'}" method="post" role="form" class="evo-validate"}
                {$jtl_token}
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

                    {formgroup}
                        {input type="hidden" name="login" value="1"}
                        {if !empty($oRedirect->cURL)}
                            {foreach $oRedirect->oParameter_arr as $oParameter}
                                {input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}"}
                            {/foreach}
                            {input type="hidden" name="r" value="{$oRedirect->nRedirect}"}
                            {input type="hidden" name="cURL" value="{$oRedirect->cURL}"}
                        {/if}
                        {input type="submit" value="{lang key='login' section='checkout'}" class="btn btn-primary btn-block submit"}
                    {/formgroup}

                    <div class="register-or-resetpw top15">
                        <small>
                           {link class="register pull-left" href="{get_static_route id='registrieren.php'}"}<span class="fa fa-pencil"></span> {lang key='newHere'} {lang key='registerNow'}{/link}
                           {link class="resetpw  pull-right" href="{get_static_route id='pass.php'}"}<span class="fa fa-question-circle"></span> {lang key='forgotPassword'}{/link}
                        </small>
                    </div>
                </fieldset>
            {/form}
        {/card}
        {/block}
    {/col}
{/row}
