{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{strip}

        {block name='navbar-productsearch'}
            {navform id="search" action="index.php" method="get" class="mx-auto"}
                {inputgroup}
                    {input name="qs" type="text" class="form-control ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                    {inputgroupaddon append=true}
                        {button type="submit" variant="light" name="search" id="search-submit-button" aria=["label"=>"{lang key='search'}"]}
                            <span class="fa fa-search"></span>
                        {/button}
                    {/inputgroupaddon}
                {/inputgroup}
            {/navform}
        {/block}

        {*{block name='navbar-top-user'}
            {if empty($smarty.session.Kunde->kKunde)}
                {navitemdropdown id="login-dropdown" class="d-none d-md-block" right=true text="<i class='fa fa-user'></i> {lang key='login'}"}
                    {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="evo-validate px-4 py-3"}
                    {$jtl_token}
                        <fieldset id="quick-login">
                            <div class="form-group">
                                <input type="email" name="email" id="email_quick" class="form-control"
                                       placeholder="{lang key='emailadress'}" required
                                       autocomplete="quick-login-email"/>
                            </div>
                            <div class="form-group">
                                <input type="password" name="passwort" id="password_quick" class="form-control"
                                       required placeholder="{lang key='password'}"
                                       autocomplete="quick-login-password"/>
                            </div>
                            {if isset($showLoginCaptcha) && $showLoginCaptcha}
                                <div class="form-group text-center">
                                    {captchaMarkup getBody=true}
                                </div>
                            {/if}
                            <div class="form-group">
                                <input type="hidden" name="login" value="1"/>
                                {if !empty($oRedirect->cURL)}
                                    {foreach $oRedirect->oParameter_arr as $oParameter}
                                        <input type="hidden" name="{$oParameter->Name}" value="{$oParameter->Wert}" />
                                    {/foreach}
                                    <input type="hidden" name="r" value="{$oRedirect->nRedirect} "/>
                                    <input type="hidden" name="cURL" value="{$oRedirect->cURL}" />
                                {/if}
                                <button type="submit" id="submit-btn" class="btn btn-primary btn-block">{lang key='login'}</button>
                            </div>
                        </fieldset>
                    {/form}
                    {dropdownitem href="{get_static_route id='pass.php'}" rel="nofollow" title="{lang key='forgotPassword'}"}
                        {lang key='forgotPassword'}
                    {/dropdownitem}
                    {dropdownitem href="{get_static_route id='registrieren.php'}" rel="nofollow" title="{lang key='registerNow'}"}
                        {lang key='newHere'} {lang key='registerNow'}
                    {/dropdownitem}
                {/navitemdropdown}
            {else}
                {navitemdropdown class="d-none d-md-block" right=true text="<i class='fa fa-user'></i> {lang key='hello'} {if $smarty.session.Kunde->cAnrede === 'w'}{lang key='salutationW'}{elseif $smarty.session.Kunde->cAnrede === 'm'}{lang key='salutationM'}{/if} {$smarty.session.Kunde->cNachname}" right="true"}
                    {dropdownitem href="{get_static_route id='jtl.php' secure=true}" rel="nofollow" title="{lang key='myAccount'}"}
                        {lang key='myAccount'}
                    {/dropdownitem}
                    {dropdownitem href="{get_static_route id='jtl.php' secure=true}?logout=1" rel="nofollow" title="{lang key='logOut'}"}
                        {lang key='logOut'}
                    {/dropdownitem}
                {/navitemdropdown}
            {/if}

            {include file='layout/header_shop_nav_compare.tpl' dXs="none" dMd="flex"}
            *}{*{include file='layout/header_shop_nav_wish.tpl'}*}{*
            {include file='basket/cart_dropdown_label.tpl' dXs="none" dMd="flex"}
        {/block}*}

{/strip}
