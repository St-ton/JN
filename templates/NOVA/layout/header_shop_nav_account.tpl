{block name='layout-header-shop-nav-account'}
    {navitemdropdown tag="li"
        aria=['expanded' => 'false']
        router-aria=['label' => {lang key='myAccount'}]
        no-caret=true
        right=true
        text='<span class="fas fa-user"></span>'
        class="account-icon-dropdown"}
        {if JTL\Session\Frontend::getCustomer()->getID() === 0}
            {block name='layout-header-shop-nav-account-logged-out'}
                <div class="dropdown-body lg-min-w-lg">
                    {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="jtl-validate" slide=true}
                        {block name='layout-header-shop-nav-account-form-content'}
                            <fieldset id="quick-login">
                                {block name='layout-header-nav-account-form-email'}
                                    {formgroup label-for="email_quick" label={lang key='emailadress'}}
                                        {input type="email" name="email" id="email_quick" size-class="sm"
                                               placeholder=" " required=true
                                               autocomplete="email"}
                                    {/formgroup}
                                {/block}
                                {block name='layout-header-nav-account-form-password'}
                                    {formgroup label-for="password_quick" label={lang key='password'} class="account-icon-dropdown-pass"}
                                        {input type="password" name="passwort" id="password_quick" size-class="sm"
                                               required=true placeholder=" "
                                               autocomplete="current-password"}
                                    {/formgroup}
                                {/block}
                                {block name='layout-header-nav-account-form-captcha'}
                                    {if isset($showLoginCaptcha) && $showLoginCaptcha}
                                        {formgroup class="simple-captcha-wrapper"}
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
                    <div class="dropdown-footer">
                        {lang key='newHere'}
                        {link href="{get_static_route id='registrieren.php'}" rel="nofollow" title="{lang key='registerNow'}"}
                            {lang key='registerNow'}
                        {/link}
                    </div>
                {/block}
            {/block}
        {else}
            {block name='layout-header-shop-nav-account-logged-in'}
                {get_static_route id='jtl.php' secure=true assign='secureAccountURL'}
                {dropdownitem href=$secureAccountURL rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myAccount'}
                {/dropdownitem}
                {dropdownitem href="{$secureAccountURL}?bestellungen=1" rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myOrders'}
                {/dropdownitem}
                {dropdownitem href="{$secureAccountURL}?editRechnungsadresse=1" rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myPersonalData'}
                {/dropdownitem}
                {dropdownitem href="{$secureAccountURL}?editLieferadresse=1" rel="nofollow" title="{lang key='myAccount'}"}
                    {lang key='myShippingAddresses'}
                {/dropdownitem}
                {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
                    {dropdownitem href="{$secureAccountURL}#my-wishlists" rel="nofollow" title="{lang key='myAccount'}"}
                        {lang key='myWishlists'}
                    {/dropdownitem}
                {/if}
                {dropdowndivider}
                {dropdownitem href="{$secureAccountURL}?logout=1" rel="nofollow" title="{lang key='logOut'}" class="account-icon-dropdown-logout"}
                    {lang key='logOut'}
                {/dropdownitem}
            {/block}
        {/if}
    {/navitemdropdown}
{/block}
