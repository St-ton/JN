</div>{* /content *}

{has_boxes position='left' assign='hasLeftBox'}
{if !$bExclusive && $hasLeftBox && isset($boxes) && !empty($boxes.left)}
    {block name="footer-sidepanel-left"}
    <aside id="sidepanel_left"
           class="hidden-print col-xs-12 {if $nSeitenTyp === 2} col-md-4 col-md-pull-8 {/if} col-lg-3 col-lg-pull-9">
        {block name="footer-sidepanel-left-content"}{$boxes.left}{/block}
    </aside>
    {/block}
{/if}
</div>{* /row *}
</div>
</div>{* /container *}
</div>{* /container-wrapper*}

{if !$bExclusive}
    <div class="clearfix"></div>
    <footer id="footer"{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid'} class="container-block"{/if}>
        <div class="container{if $Einstellungen.template.theme.pagelayout === 'full-width'}-fluid{/if}">
            {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout !== 'fluid'}
                <div class="container-block clearfix">
            {/if}

            {load_boxes_raw type='bottom' assign='arrBoxBottom' array=true}
            {if isset($arrBoxBottom) && count($arrBoxBottom) > 0}
                <div class="row" id="footer-boxes">
                    {foreach name=bottomBoxes from=$arrBoxBottom item=box}
                        <div class="col-xs-6 col-md-3">
                            {if isset($box.obj) && isset($box.tpl)}
                                {if $smarty.foreach.bottomBoxes.iteration < 10}
                                    {assign var=oBox value=$box.obj}
                                    {include file=$box.tpl}
                                {/if}
                            {/if}
                        </div>
                    {/foreach}
                </div>
            {/if}

            {block name="footer-additional"}
            {if $Einstellungen.template.footer.socialmedia_footer === 'Y' || $Einstellungen.template.footer.newsletter_footer === 'Y'}
            <div class="row footer-additional">
                {if $Einstellungen.template.footer.newsletter_footer === 'Y'}
                    <div class="col-xs-12 col-md-7 newsletter-footer">
                        <div class="row">
                            {block name="footer-newsletter"}
                                <div class="col-xs-12 col-sm-4">
                                    <h5>{lang key="newsletter" section="newsletter"} {lang key="newsletterSendSubscribe" section="newsletter"}
                                    </h5>
                                    <p class="info small">
                                        {lang key="unsubscribeAnytime" section="newsletter"}
                                    </p>
                                </div>
                                <form method="post" action="newsletter.php" class="form col-xs-12 col-sm-6">
                                    <fieldset>
                                        {$jtl_token}
                                        <input type="hidden" name="abonnieren" value="1"/>
                                        <div class="form-group">
                                            <label class="control-label sr-only" for="newsletter_email">{lang key="emailadress"}</label>
                                            <div class="input-group">
                                                <input type="text" size="20" name="cEmail" id="newsletter_email" class="form-control" placeholder="{lang key="emailadress"}">
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-primary submit">
                                                        <span>{lang key="newsletterSendSubscribe" section="newsletter"}</span>
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
                    <div class="col-xs-12 col-md-5 pull-right">
                        <div class="footer-additional-wrapper pull-right">
                            {block name="footer-socialmedia"}
                                {if !empty($Einstellungen.template.footer.facebook)}
                                    <a href="{if $Einstellungen.template.footer.facebook|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.facebook}" class="btn-social btn-facebook" title="Facebook" target="_blank"><i class="fa fa-facebook-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.twitter)}
                                    <a href="{if $Einstellungen.template.footer.twitter|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.twitter}" class="btn-social btn-twitter" title="Twitter" target="_blank"><i class="fa fa-twitter-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.googleplus)}
                                    <a href="{if $Einstellungen.template.footer.googleplus|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.googleplus}" class="btn-social btn-googleplus" title="Google+" target="_blank"><i class="fa fa-google-plus-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.youtube)}
                                    <a href="{if $Einstellungen.template.footer.youtube|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.youtube}" class="btn-social btn-youtube" title="YouTube" target="_blank"><i class="fa fa-youtube-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.vimeo)}
                                    <a href="{if $Einstellungen.template.footer.vimeo|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.vimeo}" class="btn-social btn-vimeo" title="Vimeo" target="_blank"><i class="fa fa-vimeo-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.pinterest)}
                                    <a href="{if $Einstellungen.template.footer.pinterest|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.pinterest}" class="btn-social btn-pinterest" title="PInterest" target="_blank"><i class="fa fa-pinterest-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.instagram)}
                                    <a href="{if $Einstellungen.template.footer.instagram|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.instagram}" class="btn-social btn-instagram" title="Instagram" target="_blank"><i class="fa fa-instagram"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.skype)}
                                    <a href="{if $Einstellungen.template.footer.skype|strpos:'skype:' !== 0}skype:{$Einstellungen.template.footer.skype}?add{else}{$Einstellungen.template.footer.skype}{/if}" class="btn-social btn-skype" title="Skype" target="_blank"><i class="fa fa-skype"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.xing)}
                                    <a href="{if $Einstellungen.template.footer.xing|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.xing}" class="btn-social btn-xing" title="Xing" target="_blank"><i class="fa fa-xing-square"></i></a>
                                {/if}
                                {if !empty($Einstellungen.template.footer.linkedin)}
                                    <a href="{if $Einstellungen.template.footer.linkedin|strpos:'http' !== 0}https://{/if}{$Einstellungen.template.footer.linkedin}" class="btn-social btn-linkedin" title="Linkedin" target="_blank"><i class="fa fa-linkedin-square"></i></a>
                                {/if}
                            {/block}
                        </div>
                    </div>
                {/if}
            </div>{* /row footer-additional *}
            {/if}
            {/block}{* /footer-additional *}

            <div class="footnote-vat text-center">
                {if $NettoPreise == 1}
                    {lang key="footnoteExclusiveVat" section="global" assign="footnoteVat"}
                {else}
                    {lang key="footnoteInclusiveVat" section="global" assign="footnoteVat"}
                {/if}
                {block name="footer-vat-notice"}
                    <p class="padded-lg-top">
                        <span class="footnote-reference">*</span> {$footnoteVat|replace:'#SHIPPING_LINK#':$oSpezialseiten_arr[6]->cURL}
                    </p>
                {/block}
            </div>
        {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout != 'fluid'}
            </div>
        {/if}
        </div>{* /container *}
        <div id="copyright" {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout != 'boxed'} class="container-block"{/if}>
            {block name="footer-copyright"}
                <div class="container{if $Einstellungen.template.theme.pagelayout === 'full-width'}-fluid{/if}">
                    {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout != 'fluid'}
                        <div class="container-block clearfix">
                    {/if}
                    <ul class="row list-unstyled">
                        <li class="col-xs-12 col-md-3">
                            {if !empty($meta_copyright)}&copy; {$meta_copyright}{/if}
                            {if $Einstellungen.global.global_zaehler_anzeigen === 'Y'}{lang key="counter" section="global"}: {$Besucherzaehler}{/if}
                        </li>
                        <li class="col-xs-12 col-md-6 text-center">
                            {if !empty($Einstellungen.global.global_fusszeilehinweis)}
                                {$Einstellungen.global.global_fusszeilehinweis}
                            {/if}
                        </li>
                        <li class="col-xs-12 col-md-3 text-right" id="system-credits">
                            Powered by <a href="http://jtl-url.de/jtlshop" title="JTL-Shop" target="_blank" rel="nofollow">JTL-Shop</a>
                        </li>
                    </ul>
                     {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout != 'fluid'}
                        </div>
                    {/if}
                </div>
            {/block}
            {if (!isset($Einstellungen.template.general.use_cron) || $Einstellungen.template.general.use_cron === 'Y') && $smarty.now % 10 === 0}
                <img src="includes/cron_inc.php" width="0" height="0" alt="" />
            {/if}
        </div>
    </footer>
{/if}
</div> {* /mainwrapper *}

{* JavaScripts *}
{block name="footer-js"}   
    {assign var="isFluidContent" value=false}
    {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($Link) && $Link->bIsFluid}
        {assign var="isFluidContent" value=true}
    {/if}

    {if !$bExclusive && !$isFluidContent && isset($Einstellungen.template.theme.background_image) && $Einstellungen.template.theme.background_image !== ''}
        {if $Einstellungen.template.theme.background_image === 'custom'}
            {assign var="backstretchImgPath" value=$currentTemplateDir|cat:'themes/'|cat:$Einstellungen.template.theme.theme_default|cat:'/background.jpg'}
        {else}
            {assign var="backstretchImgPath" value=$currentTemplateDir|cat:'themes/base/images/backgrounds/background_'|cat:$Einstellungen.template.theme.background_image|cat:'.jpg'}
        {/if}
        <script>
            $(function() {
                $.backstretch('{$backstretchImgPath}');
            });
        </script>
    {/if} {if !empty($Einstellungen.global.global_google_analytics_id)}
        <script type="text/javascript">
            function gaOptout() {
              document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
              window[disableStr] = true;
            }
            
            var gaProperty = '{$Einstellungen.global.global_google_analytics_id}';
            var disableStr = 'ga-disable-' + gaProperty;
            if (document.cookie.indexOf(disableStr + '=true') > -1) {
              window[disableStr] = true;
            } else {
                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', '{$Einstellungen.global.global_google_analytics_id}']);
                _gaq.push(['_gat._anonymizeIp']);
                _gaq.push(['_trackPageview']);
                (function () {ldelim}
                    var ga = document.createElement('script'),
                        s;
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(ga, s);
                {rdelim})();
            }
        </script>
    {/if}

    <script>
        jtl.load({strip}[
            {* evo js *}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {if isset($cPluginJsHead_arr)}
                    {foreach from=$cPluginJsHead_arr item="cJS"}
                        "{$cJS}?v={$nTemplateVersion}",
                    {/foreach}
                {/if}
            {else}
                {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
                    "asset/plugin_js_head?v={$nTemplateVersion}",
                {/if}
            {/if}
            {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {foreach from=$cJS_arr item="cJS"}
                    "{$cJS}?v={$nTemplateVersion}",
                {/foreach}
                {if isset($cPluginJsBody_arr)}
                    {foreach from=$cPluginJsBody_arr item="cJS"}
                        "{$cJS}?v={$nTemplateVersion}",
                    {/foreach}
                {/if}
            {else}
                "asset/jtl3.js?v={$nTemplateVersion}",
                {if isset($cPluginJsBody_arr) && $cPluginJsBody_arr|@count > 0}
                    "asset/plugin_js_body?v={$nTemplateVersion}",
                {/if}
            {/if}

            {assign var="customJSPath" value=$currentTemplateDir|cat:'/js/custom.js'}
            {if file_exists($customJSPath)}
                "{$customJSPath}?v={$nTemplateVersion}",
            {/if}
        ]{/strip});
    </script>
{/block}
</body>
</html>
