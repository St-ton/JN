{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-trustbadge'}
    {if $Einstellungen.template.trustedshops.show_trustbadge === 'Y'}
        {ts_data assign=tsData}
        {if $tsData.tsId !== '' && $tsData.nAktiv == true}
            {block name='snippets-trustbadge-script'}
                <script type="text/javascript">
                    {literal}
                    (function () {
                        var _tsid = '{/literal}{$tsData.tsId}{literal}';
                        _tsConfig = {
                            'yOffset': '{/literal}{$Einstellungen.template.trustedshops.trustbadge_yoffset}{literal}',
                            'variant': '{/literal}{$Einstellungen.template.trustedshops.trustbadge_variant}{literal}'
                        };
                        var _ts = document.createElement('script');
                        _ts.type = 'text/javascript';
                        _ts.async = true;
                        _ts.charset = 'utf-8';
                        _ts.src = '//widgets.trustedshops.com/js/' + _tsid + '.js';
                        var __ts = document.getElementsByTagName('script')[0];
                        __ts.parentNode.insertBefore(_ts, __ts);
                    })();
                    {/literal}
                </script>
            {/block}
            {block name='snippets-trustbadge-noscript'}
                <noscript>
                    <div>
                        {link href="https://www.trustedshops.de/shop/certificate.php?shop_id={$tsData.tsId}"}
                            {image
                                alt="Trusted-Shops-Trust-Badge"
                                title=" Klicken Sie auf das G&uuml;tesiegel, um die G&uuml;ltigkeit zu pr&uuml;fen!"
                                src="//widgets.trustedshops.com/images/badge.png"
                                style="position:fixed;bottom:0;right:0;"
                            }
                        {/link}
                    </div>
                </noscript>
            {/block}
        {/if}
    {/if}
{/block}
