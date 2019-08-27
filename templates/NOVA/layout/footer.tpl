{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-footer'}
    {block name='layout-footer-content-all-closingtags'}
        {block name='layout-footer-content-closingtag'}
            {include file='snippets/opc_mount_point.tpl' id='opc_content' title='Default Area'}
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
                                    {col cols=12 class="footer-additional-wrapper col-auto mx-auto"}
                                        <ul class="list-unstyled d-flex flex-row flex-wrap">
                                        {if !empty($Einstellungen.template.footer.facebook)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.facebook|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.facebook}"
                                                    class="btn-icon-secondary btn-facebook btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Facebook'}"] title="Facebook" target="_blank" rel="noopener"}
                                                    <span class="fab fa-facebook-f fa-fw fa-lg"></span>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.twitter)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.twitter|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.twitter}"
                                                    class="btn-icon-secondary btn-twitter btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Twitter'}"] title="Twitter" target="_blank" rel="noopener"}
                                                    <i class="fab fa-twitter fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.googleplus)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.googleplus|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.googleplus}"
                                                    class="btn-icon-secondary btn-googleplus btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Google+'}"] title="Google+" target="_blank" rel="noopener"}
                                                    <i class="fab fa-google-plus-g fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.youtube)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.youtube|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.youtube}"
                                                    class="btn-icon-secondary btn-youtube btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='YouTube'}"] title="YouTube" target="_blank" rel="noopener"}
                                                    <i class="fab fa-youtube fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.vimeo)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.vimeo|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.vimeo}"
                                                    class="btn-icon-secondary btn-vimeo btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Vimeo'}"]  title="Vimeo" target="_blank" rel="noopener"}
                                                    <i class="fab fa-vimeo-v fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.pinterest)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.pinterest|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.pinterest}"
                                                    class="btn-icon-secondary btn-pinterest btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Pinterest'}"]  title="Pinterest" target="_blank" rel="noopener"}
                                                    <i class="fab fa-pinterest-p fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.instagram)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.instagram|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.instagram}"
                                                    class="btn-icon-secondary btn-instagram btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Instagram'}"]  title="Instagram" target="_blank" rel="noopener"}
                                                    <i class="fab fa-instagram fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.skype)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.skype|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.skype}"
                                                    class="btn-icon-secondary btn-skype btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Skype'}"]  title="Skype" target="_blank" rel="noopener"}
                                                    <i class="fab fa-skype fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.xing)}
                                            <li class="mr-2">
                                                {link href="{if $Einstellungen.template.footer.xing|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.xing}"
                                                    class="btn-icon-secondary btn-xing btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Xing'}"]  title="Xing" target="_blank" rel="noopener"}
                                                    <i class="fab fa-xing fa-fw fa-lg"></i>
                                                {/link}
                                            </li>
                                        {/if}
                                        {if !empty($Einstellungen.template.footer.linkedin)}
                                            <li class="mr-0">
                                                {link href="{if $Einstellungen.template.footer.linkedin|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.linkedin}"
                                                    class="btn-icon-secondary btn-linkedin btn" aria=['label'=>"{lang key='visit_us_on' section='aria' printf='Linkedin'}"]  title="Linkedin" target="_blank" rel="noopener"}
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
            {block name='layout-footer-jquery'}
                <script>
                    (function () {
                        var done = false;
                        var script = document.createElement("script"),
                        head = document.head || document.documentElement;
                        script.src = '{$ShopURL}/{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/jquery-3.4.1.min.js';
                        script.type = 'text/javascript';
                        script.async = false;
                        script.onload = script.onreadystatechange = function() {
                            if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                                done = true;

                                // Process async variable
                                window.deferredTasks = window.deferredTasks || [];

                                while(deferredTasks.length) { // there is some syncing to be done
                                    var obj = deferredTasks.shift();
                                    if (obj[0] === "ready") {
                                        $(obj[1]);
                                    } else if (obj[0] === "load"){
                                        $(window).on("load", obj[1]);
                                    }
                                }

                                window.deferredTasks = {
                                    push: function(param)
                                    {
                                        if (param[0] === "ready") {
                                            $(param[1]);
                                        } else if (param[0] === "load"){
                                            $(window).on("load", param[1]);
                                        }
                                    }
                                };

                                // End of processing
                                script.onload = script.onreadystatechange = null;

                                if (head && script.parentNode) {
                                    head.removeChild(script);
                                }
                            }
                        };

                        head.appendChild(script);
                    })();

                     // helper function to load scripts.
                    function loadScript(url)
                    {
                        var script = document.createElement("script");
                        script.type = 'text/javascript';
                        script.async = false;
                        script.src = url;

                        var s = document.getElementsByTagName("script")[0];
                        s.parentNode.insertBefore(script, s);
                    }

                    {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                        {if isset($cPluginJsHead_arr)}
                            {foreach $cPluginJsHead_arr as $cJS}
                                loadScript("{$ShopURL}/{$cJS}?v={$nTemplateVersion}");
                            {/foreach}
                        {/if}
                    {else}
                        {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
                            loadScript("{$ShopURL}/asset/plugin_js_head?v={$nTemplateVersion}");
                        {/if}
                    {/if}
                    {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                        {foreach $cJS_arr as $cJS}
                            loadScript("{$ShopURL}/{$cJS}?v={$nTemplateVersion}");
                        {/foreach}
                        {if isset($cPluginJsBody_arr)}
                            {foreach $cPluginJsBody_arr as $cJS}
                                loadScript("{$ShopURL}/{$cJS}?v={$nTemplateVersion}");
                            {/foreach}
                        {/if}
                    {else}
                        loadScript("{$ShopURL}/asset/jtl3.js?v={$nTemplateVersion}");
                        {if isset($cPluginJsBody_arr) && $cPluginJsBody_arr|@count > 0}
                            loadScript("{$ShopURL}/asset/plugin_js_body?v={$nTemplateVersion}");
                        {/if}
                    {/if}

                    {assign var=customJSPath value=$currentTemplateDir|cat:'/js/custom.js'}
                    {if file_exists($customJSPath)}
                        loadScript("{$ShopURL}/{$customJSPath}?v={$nTemplateVersion}");
                    {/if}

                    {assign var=availableLocale value=array('ar','az', 'bg','ca', 'cr', 'cs', 'da', 'de', 'el', 'es','et', 'fa','fi', 'fr', 'gl',
                    'he','hu','id','it','ja','ka','kr','kz', 'lt', 'nl','no', 'pl', 'pt', 'ro','ru','sk','sl','sv','th','tr', 'uk','uz','vi','zh')}

                    {if isset($smarty.session.currentLanguage->cISO639) && $smarty.session.currentLanguage->cISO639|in_array:$availableLocale}
                        {assign var=uploaderLang value=$smarty.session.currentLanguage->cISO639}
                    {else}
                        {assign var=uploaderLang value='LANG'}
                    {/if}

                    loadScript("{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/fileinput/fileinput.min.js");
                    loadScript("{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/fileinput/themes/fas/theme.min.js");
                    loadScript("{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/fileinput/locales/{$uploaderLang}.js");
                </script>
            {/block}
        {/block}
        {captchaMarkup getBody=false}
    {/block}
    </body>
    </html>
{/block}
