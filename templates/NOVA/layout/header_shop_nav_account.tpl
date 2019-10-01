{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-shop-nav-account'}
    {if empty($smarty.session.Kunde->kKunde)}
        {block name='layout-header-shop-nav-account-logged-out'}
            {collapse id="nav-account-collapse" tag="div"  data=["parent"=>"#main-nav-wrapper"] class="mt-md-2 py-0 w-100 min-w-lg"}
                {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="evo-validate px-5 pt-5 pb-3"}
                    {block name='layout-header-shop-nav-account-form-content'}
                        <fieldset id="quick-login">
                            {block name='layout-header-nav-account-form-email'}
                                {formgroup}
                                    {input type="email" name="email" id="email_quick"
                                           placeholder="{lang key='emailadress'}" required=true
                                           autocomplete="quick-login username"}
                                {/formgroup}
                            {/block}
                            {block name='layout-header-nav-account-form-password'}
                                {formgroup}
                                    {input type="password" name="passwort" id="password_quick"
                                           required=true placeholder="{lang key='password'}"
                                           autocomplete="quick-login current-password"}
                                {/formgroup}
                            {/block}
                            {block name='layout-header-nav-account-form-captcha'}
                                {if isset($showLoginCaptcha) && $showLoginCaptcha}
                                    {formgroup class="text-center"}
                                        {captchaMarkup getBody=true}
                                    {/formgroup}
                                {/if}
                            {/block}
                            {block name='layout-header-shop-nav-account-form-submit'}
                                {formgroup class="mb-0"}
                                    {input type="hidden" name="login" value="1"}
                                    {if !empty($oRedirect->cURL)}
                                        {foreach $oRedirect->oParameter_arr as $oParameter}
                                            {input type="hidden" name=$oParameter->Name value=$oParameter->Wert}
                                        {/foreach}
                                        {input type="hidden" name="r" value=$oRedirect->nRedirect}
                                        {input type="hidden" name="cURL" value=$oRedirect->cURL}
                                    {/if}
                                    {button type="submit" id="submit-btn" block=true variant="primary"}{lang key='login'}{/button}
                                {/formgroup}
                            {/block}
                        </fieldset>
                    {/block}
                {/form}
                {block name='layout-header-nav-account-links'}
                    {link href="{get_static_route id='pass.php'}" rel="nofollow" title="{lang key='forgotPassword'}" class="d-block px-5 pt-0 pb-2"}
                        {lang key='forgotPassword'}
                    {/link}
                    {link href="{get_static_route id='registrieren.php'}" rel="nofollow" title="{lang key='registerNow'}" class="d-block px-5 py-3 bg-info"}
                        {lang key='newHere'} <span class="text-decoration-underline">{lang key='registerNow'}</span>
                    {/link}
                {/block}
            {/collapse}
        {/block}
    {else}
        {block name='layout-header-shop-nav-account-logged-in'}
            {collapse id="nav-account-collapse" tag="div"  data=["parent"=>"#main-nav-wrapper"] class="mt-md-2"}
                {dropdownitem href="{get_static_route id='jtl.php' secure=true}" rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myAccount'}
                {/dropdownitem}
                {dropdownitem href="{get_static_route id='jtl.php' secure=true}?bestellungen=1" rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myOrders'}
                {/dropdownitem}
                {dropdownitem href="{get_static_route id='jtl.php' secure=true}?editRechnungsadresse=1" rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myPersonalData'}
                {/dropdownitem}
                {dropdownitem href="{get_static_route id='jtl.php' secure=true}#my-wishlists" rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myWishlists'}
                {/dropdownitem}
                {dropdowndivider}
                {dropdownitem href="{get_static_route id='jtl.php' secure=true}?logout=1" rel="nofollow" title="{lang key='logOut'}"}
                    {lang key='logOut'}
                {/dropdownitem}
            {/collapse}
        {/block}
    {/if}
{/block}
