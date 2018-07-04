{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

<h1>{if !empty($oRedirect->cName)}{$oRedirect->cName}{else}{lang key='loginTitle' section='login'}{/if}</h1>
{if !$bCookieErlaubt}
    <div class="alert alert-danger hidden" id="no-cookies-warning" style="display:none;">
     <strong>{lang key='noCookieHeader' section='errorMessages'}</strong>
     <p>{lang key='noCookieDesc' section='errorMessages'}</p>
    </div>
    <script type="text/javascript">
       $(function() {ldelim}
           if (navigator.cookieEnabled === false) {ldelim}
               $('#no-cookies-warning').show();
           {rdelim}
       {rdelim});
    </script>
{elseif empty($cHinweis)}
    <div class="alert alert-info">{lang key='loginDesc' section='login'} {if isset($oRedirect) && $oRedirect->cName}{lang key='redirectDesc1' section='global'} {$oRedirect->cName} {lang key='redirectDesc2' section='global'}.{/if}</div>
{/if}

{include file='snippets/extension.tpl'}

<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        {block name='login-form'}
        <div class="panel-wrap">
            <form id="login_form" action="{get_static_route id='jtl.php'}" method="post" role="form" class="evo-validate">
                {$jtl_token}
                <fieldset>
                    <legend>{lang key='loginForRegisteredCustomers' section='checkout'}</legend>
                    <div class="form-group float-label-control required">
                        <label for="email" class="control-label">{lang key='emailadress' section='global'}</label>
                        <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="{lang key='emailadress' section='global'}*"
                        required
                        />
                    </div>
                    <div class="form-group float-label-control required">
                        <label for="password" class="control-label">{lang key='password' section='account data'}</label>
                        <input
                        type="password"
                        name="passwort"
                        id="password"
                        class="form-control"
                        placeholder="{lang key='password' section='account data'}"
                        required
                        />
                    </div>

                    {if isset($showLoginCaptcha) && $showLoginCaptcha}
                        <div class="form-group text-center float-label-control required">
                            {captchaMarkup getBody=true}
                        </div>
                    {/if}

                    <div class="form-group">
                        <input type="hidden" name="login" value="1" />
                        {if !empty($oRedirect->cURL)}
                            {foreach $oRedirect->oParameter_arr as $oParameter}
                                <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
                            {/foreach}
                            <input type="hidden" name="r" value="{$oRedirect->nRedirect}" />
                            <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
                        {/if}
                        <input type="submit" value="{lang key='login' section='checkout'}" class="btn btn-primary btn-block submit"/>
                    </div>

                    <div class="clearfix"></div>
                    <div class="register-or-resetpw top15">
                        <small>
                           <a class="register pull-left" href="{get_static_route id='registrieren.php'}"><span class="fa fa-pencil"></span> {lang key='newHere' section='global'} {lang key='registerNow' section='global'}</a>
                           <a class="resetpw  pull-right" href="{get_static_route id='pass.php'}"><span class="fa fa-question-circle"></span> {lang key='forgotPassword' section='global'}</a>
                        </small>
                    </div>
                </fieldset>
            </form>
        </div>
        {/block}
    </div>
</div>