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

            {if $smarty.const.PAGE_ARTIKELLISTE === $nSeitenTyp
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
            <footer id="footer">
                {container class="d-print-none pt-4"}
                    {if $Einstellungen.template.footer.newsletter_footer === 'Y'}
                        {block name='layout-footer-newsletter'}
                            {row class="newsletter-footer" class="text-center text-md-left align-items-center"}
                                {col cols=12 lg=6}
                                    <div class="h2">
                                        {lang key='newsletter' section='newsletter'} {lang key='newsletterSendSubscribe' section='newsletter'}
                                    </div>
                                    <p class="info">
                                        {lang key='unsubscribeAnytime' section='newsletter'}
                                    </p>
                                {/col}
                                {col cols=12 lg=6}
                                    {block name='layout-footer-form'}
                                        {form methopd="post" action="{get_static_route id='newsletter.php'}"}
                                            {block name='layout-footer-form-content'}
                                                {input type="hidden" name="abonnieren" value="2"}
                                                {formgroup label-sr-only="{lang key='emailadress'}" class="mb-0"}
                                                    {inputgroup}
                                                        {input type="email" name="cEmail" id="newsletter_email" placeholder="{lang key='emailadress'}" aria=['label' => {lang key='emailadress'}]}
                                                        {inputgroupaddon append=true}
                                                            {button type='submit' variant='dark' class='min-w-sm'}
                                                                {lang key='newsletterSendSubscribe' section='newsletter'}
                                                            {/button}
                                                        {/inputgroupaddon}
                                                    {/inputgroup}
                                                {/formgroup}
                                            {/block}
                                        {/form}
                                    {/block}
                                {/col}
                            {/row}
                            <hr>
                        {/block}
                    {/if}
                    {block name='layout-footer-boxes'}
                        {getBoxesByPosition position='bottom' assign='footerBoxes'}
                        {if isset($footerBoxes) && count($footerBoxes) > 0}
                            {row id='footer-boxes' class='mt-4 mt-lg-7'}
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
                                    {col cols=12 class="footer-additional-wrapper col-auto mx-auto"}
                                        <ul class="list-unstyled d-flex flex-row flex-wrap">
                                        {if !empty($Einstellungen.template.footer.facebook)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.facebook|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.facebook}"
                                                    class="btn-icon-secondary btn-facebook btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Facebook'}"] title="Facebook" target="_blank" rel="noopener"}
                                                    <span class="fab fa-facebook-f fa-fw fa-lg"></span>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.twitter)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.twitter|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.twitter}"
                                                    class="btn-icon-secondary btn-twitter btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Twitter'}"] title="Twitter" target="_blank" rel="noopener"}
                                                    <i class="fab fa-twitter fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.youtube)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.youtube|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.youtube}"
                                                    class="btn-icon-secondary btn-youtube btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='YouTube'}"] title="YouTube" target="_blank" rel="noopener"}
                                                    <i class="fab fa-youtube fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.vimeo)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.vimeo|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.vimeo}"
                                                    class="btn-icon-secondary btn-vimeo btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Vimeo'}"]  title="Vimeo" target="_blank" rel="noopener"}
                                                    <i class="fab fa-vimeo-v fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.pinterest)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.pinterest|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.pinterest}"
                                                    class="btn-icon-secondary btn-pinterest btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Pinterest'}"]  title="Pinterest" target="_blank" rel="noopener"}
                                                    <i class="fab fa-pinterest-p fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.instagram)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.instagram|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.instagram}"
                                                    class="btn-icon-secondary btn-instagram btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Instagram'}"]  title="Instagram" target="_blank" rel="noopener"}
                                                    <i class="fab fa-instagram fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.skype)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.skype|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.skype}"
                                                    class="btn-icon-secondary btn-skype btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Skype'}"]  title="Skype" target="_blank" rel="noopener"}
                                                    <i class="fab fa-skype fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.xing)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.xing|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.xing}"
                                                    class="btn-icon-secondary btn-xing btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Xing'}"]  title="Xing" target="_blank" rel="noopener"}
                                                    <i class="fab fa-xing fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.linkedin)}
                                            <li>
                                                {link href="{if $Einstellungen.template.footer.linkedin|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.linkedin}"
                                                    class="btn-icon-secondary btn-linkedin btn btn-sm" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Linkedin'}"]  title="Linkedin" target="_blank" rel="noopener"}
                                                    <i class="fab fa-linkedin-in fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        </ul>
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
                            <span class="small">* {$footnoteVat}{if isset($footnoteShipping)}{$footnoteShipping}{/if}</span>
                        {/block}
                    </div>
                {/container}
                {block name='layout-footer-copyright'}
                    <div id="copyright" class="mt-3">
                        {container id="copyright" fluid=true class='py-3 font-size-sm text-center"'}
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
        {captchaMarkup getBody=false}
    {/block}
    </body>
    </html>
{/block}
