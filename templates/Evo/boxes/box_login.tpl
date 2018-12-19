{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<section class="panel panel-default box box-login" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{if empty($smarty.session.Kunde)}{lang key='login'}{else}{lang key='hello'}, {$smarty.session.Kunde->cVorname} {$smarty.session.Kunde->cNachname}{/if}</div>
    </div>
    <div class="box-body panel-body">
        {if empty($smarty.session.Kunde->kKunde)}
            <form action="{get_static_route id='jtl.php' secure=true}" method="post" class="form box_login evo-validate">
                <input type="hidden" name="login" value="1" />
                {$jtl_token}
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
                {if isset($showLoginCaptcha) && $showLoginCaptcha}
                    <div class="form-group text-center float-label-control">
                        {captchaMarkup getBody=true}
                    </div>
                {/if}

                <div class="form-group">
                    {if !empty($oRedirect->cURL)}
                        {foreach $oRedirect->oParameter_arr as $oParameter}
                            <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
                        {/foreach}
                        <input type="hidden" name="r" value="{$oRedirect->nRedirect}" />
                        <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
                    {/if}
                    <input type="submit" value="{lang key='login' section='checkout'}" class="btn btn-primary btn-block submit" />
                </div>
                <ul class="register-or-resetpw nav">
                    <li>
                        <a class="resetpw pull-left btn-block" href="{get_static_route id='pass.php' secure=true}">
                            <span class="fa fa-question-circle"></span> {lang key='forgotPassword'}
                        </a>
                    </li>
                    <li>
                        <a class="register pull-left btn-block" href="{get_static_route id='registrieren.php'}">
                            <span class="fa fa-pencil"></span> {lang key='newHere'} {lang key='registerNow'}
                        </a>
                    </li>
                </ul>
            </form>
        {else}
            <a href="{get_static_route id='jtl.php'}" class="btn btn-default btn-block btn-sm btn-account">{lang key='myAccount'}</a>
            <a href="{get_static_route id='jtl.php'}?logout=1&token={$smarty.session.jtl_token}" class="btn btn-block btn-sm btn-warning btn-logout">{lang key='logOut'}</a>
        {/if}
    </div>
</section>
