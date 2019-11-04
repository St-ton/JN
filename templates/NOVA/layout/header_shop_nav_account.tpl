{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-shop-nav-account'}
    {navitemdropdown tag="li"
        aria=['expanded' => 'false']
        router-aria=['label' => {lang key='myAccount'}]
        no-caret=true
        right=true
        text='<span class="fas fa-user"></span>'}
        {if empty($smarty.session.Kunde->kKunde)}
            {block name='layout-header-shop-nav-account-logged-out'}
                <div class="dropdown-body">
                    {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="evo-validate lg-min-w-lg"}
                        {block name='layout-header-shop-nav-account-form-content'}
                            <fieldset id="quick-login">
                                {block name='layout-header-nav-account-form-email'}
                                    {formgroup}
                                        {input type="email" name="email" id="email_quick" size="sm"
                                               placeholder="{lang key='emailadress'}" required=true
                                               autocomplete="quick-login username"}
                                    {/formgroup}
                                {/block}
                                {block name='layout-header-nav-account-form-password'}
                                    {formgroup class="mb-5"}
                                        {input type="password" name="passwort" id="password_quick" size="sm"
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
                                    {formgroup}
                                        {input type="hidden" name="login" value="1"}
                                        {if !empty($oRedirect->cURL)}
                                            {foreach $oRedirect->oParameter_arr as $oParameter}
                                                {input type="hidden" name=$oParameter->Name value=$oParameter->Wert}
                                            {/foreach}
                                            {input type="hidden" name="r" value=$oRedirect->nRedirect}
                                            {input type="hidden" name="cURL" value=$oRedirect->cURL}
                                        {/if}
                                        {button type="submit" size="sm" id="submit-btn" block=true variant="primary"}{lang key='login'}{/button}
                                    {/formgroup}
                                {/block}
                            </fieldset>
                        {/block}
                    {/form}
                    {block name='layout-header-nav-account-link-forgot-password'}
                        {link href="{get_static_route id='pass.php'}" rel="nofollow" title="{lang key='forgotPassword'}"}
                            {lang key='forgotPassword'}
                        {/link}
                    {/block}
                </div>
                {block name='layout-header-nav-account-link-register'}
                    <div class="dropdown-footer bg-gray-light">
                        {link href="{get_static_route id='registrieren.php'}" rel="nofollow" title="{lang key='registerNow'}"}
                            {lang key='newHere'} <span class="text-decoration-underline">{lang key='registerNow'}</span>
                        {/link}
                    </div>
                {/block}
            {/block}
        {else}
            {block name='layout-header-shop-nav-account-logged-in'}
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
                {dropdownitem href="{get_static_route id='jtl.php' secure=true}?logout=1" rel="nofollow" title="{lang key='logOut'}" class="mb-2"}
                    {lang key='logOut'}
                {/dropdownitem}
            {/block}
        {/if}
    {/navitemdropdown}
{/block}
