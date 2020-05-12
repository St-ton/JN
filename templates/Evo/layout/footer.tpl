{block name='consent-manager'}
    {include file='snippets/consent_manager.tpl'}
    <script>
        $(window).on('load', function () {
            const CM = new ConsentManager({
                version: 1
            });
            var trigger = document.querySelectorAll('.trigger');
            var triggerCall = function (e) {
                e.preventDefault();
                let type = e.target.dataset.consent;
                if (CM.getSettings(type) === false) {
                    CM.openConfirmationModal(type, function () {
                        let data = CM._getLocalData();
                        if (data === null) {
                            data = { settings: {} };
                        }
                        data.settings[type] = true;
                        document.dispatchEvent(new CustomEvent('consent.updated', { detail: data.settings }));
                    });
                }
            }
            for (let i = 0; i < trigger.length; ++i) {
                trigger[i].addEventListener('click', triggerCall)
            }
            document.addEventListener('consent.updated', function (e) {
                $.post('{$ShopURLSSL}/', {
                        'action': 'updateconsent',
                        'jtl_token': '{$smarty.session.jtl_token}',
                        'data': e.detail
                    }
                );
            });
        });
    </script>
{/block}
{block name='content-all-closingtags'}
    {block name='content-closingtag'}
        {opcMountPoint id='opc_content'}
    </div>{* /content *}
    {/block}

    {block name='aside'}
    {has_boxes position='left' assign='hasLeftBox'}
    {if !$bExclusive && $hasLeftBox && !empty($boxes.left|strip_tags|trim)}
        {block name='footer-sidepanel-left'}
        <aside id="sidepanel_left" class="hidden-print col-xs-12 {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE} col-md-4 col-md-pull-8 {/if} col-lg-3 col-lg-pull-9">
            {block name='footer-sidepanel-left-content'}{$boxes.left}{/block}
        </aside>
        {/block}
    {/if}
    {/block}

    {block name='content-row-closingtag'}
    </div>{* /row *}
    {/block}

    {block name='content-container-block-closingtag'}
    </div>{* /container-block *}
    {/block}

    {block name='content-container-closingtag'}
    </div>{* /container *}
    {/block}

    {block name='content-wrapper-closingtag'}
    </div>{* /content-wrapper*}
    {/block}
{/block}
{block name='footer'}
{if !$bExclusive}
    <div class="clearfix"></div>
    <footer id="footer"{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid'} class="container-block"{/if}>
        <div class="hidden-print container{if $Einstellungen.template.theme.pagelayout === 'full-width'}-fluid{/if}">
            {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                <div class="container-block clearfix">
            {/if}
            {block name='footer-boxes'}
            {getBoxesByPosition position='bottom' assign='footerBoxes'}
            {if isset($footerBoxes) && count($footerBoxes) > 0}
                <div class="row" id="footer-boxes">
                    {foreach $footerBoxes as $box}
                        <div class="{block name='footer-boxes-class'}col-xs-12 col-sm-6 col-md-3{/block}">
                            {$box->getRenderedContent()}
                        </div>
                    {/foreach}
                </div>
            {/if}
            {/block}

            {block name='footer-additional'}
            {if $Einstellungen.template.footer.socialmedia_footer === 'Y' || $Einstellungen.template.footer.newsletter_footer === 'Y'}
            <div class="row footer-additional">
                {if $Einstellungen.template.footer.newsletter_footer === 'Y'}
                    <div class="{block name='footer-newsletter-class'}col-xs-12 col-md-7 newsletter-footer{/block}">
                        <div class="row">
                            {block name='footer-newsletter'}
                                <div class="col-xs-12 col-sm-4">
                                    <h5>{lang key='newsletter' section='newsletter'} {lang key='newsletterSendSubscribe' section='newsletter'}
                                    </h5>
                                    <p class="info small">
                                        {lang key='unsubscribeAnytime' section='newsletter' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getURL()}
                                    </p>
                                </div>
                                <form method="post" action="{get_static_route id='newsletter.php'}" class="form col-xs-12 col-sm-6">
                                    <fieldset>
                                        {$jtl_token}
                                        <input type="hidden" name="abonnieren" value="2"/>
                                        <div class="form-group">
                                            <label class="control-label sr-only" for="newsletter_email">{lang key='emailadress'}</label>
                                            <div class="input-group">
                                                <input type="email" size="20" name="cEmail" id="newsletter_email" class="form-control" placeholder="{lang key='emailadress'}">
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary submit">
                                                        <span>{lang key='newsletterSendSubscribe' section='newsletter'}</span>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            {/block}
                        </div>
                    </div>
                {/if}

                {if $Einstellungen.template.footer.socialmedia_footer === 'Y'}
                    <div class="{block name='footer-socialmedia-class'}col-xs-12 col-md-5 pull-right{/block}">
                        <div class="footer-additional-wrapper pull-right">
                            {block name='footer-socialmedia'}
                                {if !empty($Einstellungen.template.footer.facebook)}
                                    <a href="{if $Einstellungen.template.footer.facebook|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.facebook}" class="btn-social btn-facebook" title="Facebook" target="_blank" rel="noopener"><i class="fa fa-facebook-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.twitter)}
                                    <a href="{if $Einstellungen.template.footer.twitter|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.twitter}" class="btn-social btn-twitter" title="Twitter" target="_blank" rel="noopener"><i class="fa fa-twitter-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.youtube)}
                                    <a href="{if $Einstellungen.template.footer.youtube|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.youtube}" class="btn-social btn-youtube" title="YouTube" target="_blank" rel="noopener"><i class="fa fa-youtube-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.vimeo)}
                                    <a href="{if $Einstellungen.template.footer.vimeo|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.vimeo}" class="btn-social btn-vimeo" title="Vimeo" target="_blank" rel="noopener"><i class="fa fa-vimeo-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.pinterest)}
                                    <a href="{if $Einstellungen.template.footer.pinterest|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.pinterest}" class="btn-social btn-pinterest" title="PInterest" target="_blank" rel="noopener"><i class="fa fa-pinterest-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.instagram)}
                                    <a href="{if $Einstellungen.template.footer.instagram|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.instagram}" class="btn-social btn-instagram" title="Instagram" target="_blank" rel="noopener"><i class="fa fa-instagram"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.skype)}
                                    <a href="{if $Einstellungen.template.footer.skype|strpos:'skype:' !== 0}skype:{$Einstellungen.template.footer.skype}?add{else}{$Einstellungen.template.footer.skype}{/if}" class="btn-social btn-skype" title="Skype" target="_blank" rel="noopener"><i class="fa fa-skype"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.xing)}
                                    <a href="{if $Einstellungen.template.footer.xing|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.xing}" class="btn-social btn-xing" title="Xing" target="_blank" rel="noopener"><i class="fa fa-xing-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.linkedin)}
                                    <a href="{if $Einstellungen.template.footer.linkedin|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.linkedin}" class="btn-social btn-linkedin" title="Linkedin" target="_blank" rel="noopener"><i class="fa fa-linkedin-square"></i></a>
                                {/if}
                            {/block}
                        </div>
                    </div>
                {/if}
            </div>{* /row footer-additional *}
            {/if}
            {/block}{* /footer-additional *}
            <div class="row">
                {block name='footer-language'}
                {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                    <div class="language-dropdown dropdown visible-xs col-xs-6 text-center">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="{lang key='selectLang'}">
                            <i class="fa fa-language"></i>
                            {lang key='language'}
                            <span class="caret"></span>
                        </a>
                        <ul id="language-dropdown-small" class="dropdown-menu dropdown-menu-right">
                            {foreach $smarty.session.Sprachen as $Sprache}
                                {if $Sprache->kSprache == $smarty.session.kSprache}
                                    <li class="active lang-{$lang} visible-xs"><a>{$Sprache->displayLanguage}</a></li>
                                {/if}
                            {/foreach}
                            {foreach $smarty.session.Sprachen as $oSprache}
                                {if $oSprache->kSprache != $smarty.session.kSprache}
                                    <li>
                                        <a href="{$oSprache->cURL}" class="link_lang {$oSprache->cISO}" rel="nofollow">{$oSprache->displayLanguage}</a>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                {/block}
                {block name='footer-currency'}
                {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
                    <div class="currency-dropdown dropdown visible-xs col-xs-6 text-center">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            {if $smarty.session.Waehrung->getCode() === 'EUR'}
                                <i class="fa fa-eur" title="{$smarty.session.Waehrung->getName()}"></i>
                            {elseif $smarty.session.Waehrung->getCode() === 'USD'}
                                <i class="fa fa-usd" title="{$smarty.session.Waehrung->getName()}"></i>
                            {elseif $smarty.session.Waehrung->getCode() === 'GBP'}
                                <i class="fa fa-gbp" title="{$smarty.session.Waehrung->getName()}"></i>
                            {/if}
                            {lang key='currency'} <span class="caret"></span>
                        </a>
                        <ul id="currency-dropdown-small" class="dropdown-menu dropdown-menu-right">
                            {foreach $smarty.session.Waehrungen as $oWaehrung}
                                <li>
                                    <a href="{$oWaehrung->getURL()}" rel="nofollow">{$oWaehrung->getName()}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                {/block}
            </div>
            <div class="footnote-vat text-center">
                {if $NettoPreise == 1}
                    {lang key='footnoteExclusiveVat' section='global' assign='footnoteVat'}
                {else}
                    {lang key='footnoteInclusiveVat' section='global' assign='footnoteVat'}
                {/if}
                {if $Einstellungen.global.global_versandhinweis === 'zzgl'}
                    {lang key='footnoteExclusiveShipping' section='global' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() assign='footnoteShipping'}
                {elseif $Einstellungen.global.global_versandhinweis === 'inkl'}
                    {lang key='footnoteInclusiveShipping' section='global' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() assign='footnoteShipping'}
                {/if}
                {block name='footer-vat-notice'}
                    <p class="padded-lg-top">
                        <span class="footnote-reference">*</span> {$footnoteVat}{if isset($footnoteShipping)}{$footnoteShipping}{/if}
                    </p>
                {/block}
            </div>
        {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
            </div>
        {/if}
        </div>{* /container *}
        <div id="copyright" {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'boxed'} class="container-block"{/if}>
            {block name='footer-copyright'}
                <div class="container{if $Einstellungen.template.theme.pagelayout === 'full-width'}-fluid{/if}">
                    {assign var=isBrandFree value=\JTL\Shop::isBrandfree()}
                    {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                        <div class="container-block clearfix">
                    {/if}
                    <ul class="row list-unstyled">
                        <li class="col-xs-12 col-md-3">
                            {if !empty($meta_copyright)}<span itemprop="copyrightHolder">&copy; {$meta_copyright}</span>{/if}
                            {if $Einstellungen.global.global_zaehler_anzeigen === 'Y'}{lang key='counter' section='global'}: {$Besucherzaehler}{/if}
                        </li>
                        {if !empty($Einstellungen.global.global_fusszeilehinweis)}
                        <li class="col-xs-12 {if $isBrandFree}col-md-9{else}col-md-6{/if} text-center">
                            {$Einstellungen.global.global_fusszeilehinweis}
                        </li>
                        {/if}
                        {if !$isBrandFree}
                            <li class="col-xs-12 col-md-3 text-right" id="system-credits">
                                Powered by <a href="https://jtl-url.de/jtlshop" title="JTL-Shop" target="_blank" rel="noopener nofollow">JTL-Shop</a>
                            </li>
                        {/if}
                    </ul>
                     {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                        </div>
                    {/if}
                </div>
            {/block}
        </div>
    </footer>
{/if}
{/block}

{block name='main-wrapper-closingtag'}
</div> {* /mainwrapper *}
{/block}

{* JavaScripts *}
{block name='footer-js'}
    {assign var='isFluidContent' value=isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($Link) && $Link->getIsFluid()}

    {if !$bExclusive && !$isFluidContent && isset($Einstellungen.template.theme.background_image) && $Einstellungen.template.theme.background_image !== ''}
        {if $Einstellungen.template.theme.background_image === 'custom'}
            {assign var='backstretchImgPath' value=$ShopURL|cat:'/'|cat:$currentTemplateDir|cat:'themes/'|cat:$Einstellungen.template.theme.theme_default|cat:'/background.jpg'}
        {else}
            {assign var='backstretchImgPath' value=$ShopURL|cat:'/'|cat:$currentTemplateDir|cat:'themes/base/images/backgrounds/background_'|cat:$Einstellungen.template.theme.background_image|cat:'.jpg'}
        {/if}
        <script>
            $(window).on("load", function (e) {
                $.backstretch('{$backstretchImgPath}');
            });
        </script>
    {/if}
    {$dbgBarBody}
    <script>
        jtl.load({strip}[
            {* evo js *}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {if isset($cPluginJsHead_arr)}
                    {foreach $cPluginJsHead_arr as $cJS}
                        "{$ShopURL}/{$cJS}?v={$nTemplateVersion}",
                    {/foreach}
                {/if}
            {else}
                {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
                    "{$ShopURL}/asset/plugin_js_head?v={$nTemplateVersion}",
                {/if}
            {/if}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {foreach $cJS_arr as $cJS}
                    "{$ShopURL}/{$cJS}?v={$nTemplateVersion}",
                {/foreach}
                {if isset($cPluginJsBody_arr)}
                    {foreach $cPluginJsBody_arr as $cJS}
                        "{$ShopURL}/{$cJS}?v={$nTemplateVersion}",
                    {/foreach}
                {/if}
            {else}
                "{$ShopURL}/asset/jtl3.js?v={$nTemplateVersion}",
                {if isset($cPluginJsBody_arr) && $cPluginJsBody_arr|@count > 0}
                    "{$ShopURL}/asset/plugin_js_body?v={$nTemplateVersion}",
                {/if}
            {/if}

            {assign var='customJSPath' value=$currentTemplateDir|cat:'/js/custom.js'}
            {if file_exists($customJSPath)}
                "{$ShopURL}/{$customJSPath}?v={$nTemplateVersion}",
            {/if}
        ]{/strip});
    </script>
    {captchaMarkup getBody=false}
{/block}
</body>
</html>
