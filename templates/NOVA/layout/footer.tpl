{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-footer'}
    {block name='layout-footer-content-all-closingtags'}
        {block name='layout-footer-content-closingtag'}
            {opcMountPoint id='opc_content' title='Default Area'}
            </div>{* /content *}
        {/block}

        {block name='layout-footer-aside'}
            {has_boxes position='left' assign='hasLeftBox'}

            {if ($Einstellungen.template.sidebar_settings.show_sidebar_product_list === 'Y' && $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp
                    || $Einstellungen.template.sidebar_settings.show_sidebar_product_list === 'N')
                && !$bExclusive
                && $hasLeftBox
                && !empty($boxes.left|strip_tags|trim)
            }
                {block name='layout-footer-sidepanel-left'}
                    <aside id="sidepanel_left" class="d-print-none col-12 col-lg-3 order-lg-0 mb-6">
                        {block name='footer-sidepanel-left-content'}{$boxes.left}{/block}
                    </aside>
                {/block}
            {/if}
        {/block}

        {block name='layout-footer-content-row-closingtag'}
            </div>{* /row *}
        {/block}

        {block name='layout-footer-content-wrapper-closingtag'}
            </div>{* /content-wrapper*}
        {/block}
    {/block}

    {block name='layout-footer-main-wrapper-closingtag'}
        </main> {* /mainwrapper *}
    {/block}

    {block name='layout-footer-content'}
        {if !$bExclusive}
            <footer>
                {container class="d-print-none pt-4"}
                    {if $Einstellungen.template.footer.newsletter_footer === 'Y'}
                        {block name='layout-footer-newsletter'}
                            {row class="newsletter-footer" class="text-center text-md-left"}
                                {col cols=12 md=6}
                                    <div class="h5">
                                        {lang key='newsletter' section='newsletter'} {lang key='newsletterSendSubscribe' section='newsletter'}
                                    </div>
                                    <p class="info small">
                                        {lang key='unsubscribeAnytime' section='newsletter'}
                                    </p>
                                {/col}
                                {block name='layout-footer-form'}
                                    {form methopd="post" action="{get_static_route id='newsletter.php'}" class="col-12 col-md-4"}
                                        {block name='layout-footer-form-content'}
                                            {input type="hidden" name="abonnieren" value="2"}
                                            {formgroup label-sr-only="{lang key='emailadress'}" class="mb-0"}
                                                {inputgroup}
                                                    {input type="email" name="cEmail" id="newsletter_email" placeholder="{lang key='emailadress'}" aria=['label' => {lang key='emailadress'}]}
                                                    {inputgroupaddon append=true}
                                                        {button type="submit" variant="secondary"}
                                                            {lang key='newsletterSendSubscribe' section='newsletter'}
                                                        {/button}
                                                    {/inputgroupaddon}
                                                {/inputgroup}
                                            {/formgroup}
                                        {/block}
                                    {/form}
                                {/block}
                            {/row}
                            <hr>
                        {/block}
                    {/if}
                    {block name='layout-footer-boxes'}
                        {getBoxesByPosition position='bottom' assign='footerBoxes'}
                        {if isset($footerBoxes) && count($footerBoxes) > 0}
                            {row id="footer-boxes"}
                                {foreach $footerBoxes as $box}
                                    {col cols=12 sm=6 md=3}
                                        {$box->getRenderedContent()}
                                    {/col}
                                {/foreach}
                            {/row}
                        {/if}
                    {/block}

                    {block name='layout-footer-additional'}
                        {if $Einstellungen.template.footer.socialmedia_footer === 'Y' || $Einstellungen.template.footer.newsletter_footer === 'Y'}
                            {row class="footer-additional"}
                            {if $Einstellungen.template.footer.socialmedia_footer === 'Y'}
                                {block name='layout-footer-socialmedia'}
                                    {col cols=12 class="footer-additional-wrapper"}
                                        {if !empty($Einstellungen.template.footer.facebook)}
                                            {link href="{if $Einstellungen.template.footer.facebook|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.facebook}"
                                                class="btn-social btn-facebook btn" title="Facebook" target="_blank" rel="noopener"}
                                                <i class="fab fa-facebook-f"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.twitter)}
                                            {link href="{if $Einstellungen.template.footer.twitter|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.twitter}"
                                                class="btn-social btn-twitter btn" title="Twitter" target="_blank" rel="noopener"}
                                                <i class="fab fa-twitter"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.googleplus)}
                                            {link href="{if $Einstellungen.template.footer.googleplus|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.googleplus}"
                                                class="btn-social btn-googleplus btn" title="Google+" target="_blank" rel="noopener"}
                                                <i class="fab fa-google-plus-g"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.youtube)}
                                            {link href="{if $Einstellungen.template.footer.youtube|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.youtube}"
                                                class="btn-social btn-youtube btn" title="YouTube" target="_blank" rel="noopener"}
                                                <i class="fab fa-youtube"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.vimeo)}
                                            {link href="{if $Einstellungen.template.footer.vimeo|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.vimeo}"
                                                class="btn-social btn-vimeo btn" title="Vimeo" target="_blank" rel="noopener"}
                                                <i class="fab fa-vimeo-v"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.pinterest)}
                                            {link href="{if $Einstellungen.template.footer.pinterest|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.pinterest}"
                                                class="btn-social btn-pinterest btn" title="Pinterest" target="_blank" rel="noopener"}
                                                <i class="fab fa-pinterest-p"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.instagram)}
                                            {link href="{if $Einstellungen.template.footer.instagram|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.instagram}"
                                                class="btn-social btn-instagram btn" title="Instagram" target="_blank" rel="noopener"}
                                                <i class="fab fa-instagram"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.skype)}
                                            {link href="{if $Einstellungen.template.footer.skype|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.skype}"
                                                class="btn-social btn-skype btn" title="Skype" target="_blank" rel="noopener"}
                                                <i class="fab fa-skype"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.xing)}
                                            {link href="{if $Einstellungen.template.footer.xing|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.xing}"
                                                class="btn-social btn-xing btn" title="Xing" target="_blank" rel="noopener"}
                                                <i class="fab fa-xing"></i>
                                            {/link}
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.linkedin)}
                                            {link href="{if $Einstellungen.template.footer.linkedin|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.linkedin}"
                                                class="btn-social btn-linkedin btn" title="Linkedin" target="_blank" rel="noopener"}
                                                <i class="fab fa-linkedin-in"></i>
                                            {/link}
                                        {/if}
                                    {/col}
                                {/block}
                            {/if}
                            {/row}{* /row footer-additional *}
                        {/if}
                    {/block}{* /footer-additional *}
                    {row}
                        {block name='layout-footer-language'}
                            {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                                {dropdown
                                    id="language-dropdown-footer"
                                    variant="link btn-sm"
                                    class="d-block d-md-none col-6 text-center language-dropdown"
                                    text="<i class='fas fa-language'></i> {lang key='language'}"}
                                    {foreach $smarty.session.Sprachen as $oSprache}
                                        {dropdownitem href="{$oSprache->url}" rel="nofollow" }
                                            {$oSprache->displayLanguage}
                                        {/dropdownitem}
                                    {/foreach}
                                {/dropdown}
                            {/if}
                        {/block}
                        {block name='layout-footer-currency'}
                            {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
                                {dropdown
                                    id="currency-dropdown-footer"
                                    variant="link btn-sm"
                                    class="d-block d-md-none col-6 text-center currency-dropdown"
                                    text="
                                        {if $smarty.session.Waehrung->getCode() === 'EUR'}
                                            <i class='fas fa-euro-sign' title='{$smarty.session.Waehrung->getName()}'></i> {lang key='currency'}
                                        {elseif $smarty.session.Waehrung->getCode() === 'USD'}
                                            <i class='fas fa-dollar-sign' title='{$smarty.session.Waehrung->getName()}'></i> {lang key='currency'}
                                        {elseif $smarty.session.Waehrung->getCode() === 'GBP'}
                                            <i class='fas fa-pound-sign'' title='{$smarty.session.Waehrung->getName()}''></i> {lang key='currency'}
                                        {else}
                                            {$smarty.session.Waehrung->getName()}
                                        {/if}"
                                }
                                    {foreach $smarty.session.Waehrungen as $oWaehrung}
                                        {dropdownitem href=$oWaehrung->getURLFull() rel="nofollow"}
                                            {$oWaehrung->getName()}
                                        {/dropdownitem}
                                    {/foreach}
                                {/dropdown}
                            {/if}
                        {/block}
                    {/row}
                    <div class="footnote-vat">
                        {if $NettoPreise == 1}
                            {lang key='footnoteExclusiveVat' assign='footnoteVat'}
                        {else}
                            {lang key='footnoteInclusiveVat' assign='footnoteVat'}
                        {/if}
                        {if $Einstellungen.global.global_versandhinweis === 'zzgl'}
                            {lang key='footnoteExclusiveShipping' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() assign='footnoteShipping'}
                        {elseif $Einstellungen.global.global_versandhinweis === 'inkl'}
                            {lang key='footnoteInclusiveShipping' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() assign='footnoteShipping'}
                        {/if}
                        {block name='footer-vat-notice'}
                            <p class="pt-4">
                                <span class="footnote-reference">*</span> {$footnoteVat}{if isset($footnoteShipping)}{$footnoteShipping}{/if}
                            </p>
                        {/block}
                    </div>
                {/container}
                {block name='layout-footer-copyright'}
                    <div id="copyright" class="py-3 text-center">
                        {container fluid=true}
                            {row}
                                {assign var=isBrandFree value=JTL\Shop::isBrandfree()}
                                {col class="text-right"}
                                    {if !empty($meta_copyright)}<span itemprop="copyrightHolder">&copy; {$meta_copyright}</span>{/if}
                                    {if $Einstellungen.global.global_zaehler_anzeigen === 'Y'}{lang key='counter'}: {$Besucherzaehler}{/if}
                                {/col}
                                {if !empty($Einstellungen.global.global_fusszeilehinweis)}
                                    {col class="text-left"}
                                        {$Einstellungen.global.global_fusszeilehinweis}
                                    {/col}
                                {/if}
                                {if !$isBrandFree}
                                    {col class="text-right" id="system-credits"}
                                        Powered by {link href="https://jtl-url.de/jtlshop" title="JTL-Shop" target="_blank" rel="noopener nofollow"}JTL-Shop{/link}
                                    {/col}
                                {/if}
                            {/row}
                        {/container}
                    </div>
                {/block}
            </footer>
        {/if}
    {/block}


    {* JavaScripts *}
    {block name='layout-footer-js'}
        {$dbgBarBody}
        {block name='layout-footer-script-jtl-load'}
            {if (!isset($Einstellungen.template.general.use_cron)
                    || $Einstellungen.template.general.use_cron === 'Y') && $smarty.now % 10 === 0}
                <script defer src="includes/cron_inc.php"></script>
            {/if}
        {/block}
        {captchaMarkup getBody=false}
    {/block}
    </body>
    </html>
{/block}
