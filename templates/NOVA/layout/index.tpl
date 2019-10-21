{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-index'}
    {if isset($nFullscreenTemplate) && $nFullscreenTemplate == 1}
        {block name='layout-index-plugin-template'}
            {include file=$cPluginTemplate}
        {/block}
    {else}
        {block name='layout-index-include-header'}
            {if !isset($bAjaxRequest) || !$bAjaxRequest}
                {include file='layout/header.tpl'}
            {else}
                {include file='layout/modal_header.tpl'}
            {/if}
        {/block}

        {block name='layout-index-content'}
            {block name='layout-index-heading'}
                {if !empty($Link->getTitle())}
                    {opcMountPoint id='opc_before_heading'}
                    {container}
                        <h1>{$Link->getTitle()}</h1>
                    {/container}
                {elseif isset($bAjaxRequest) && $bAjaxRequest}
                    {opcMountPoint id='opc_before_heading'}
                    {container}
                        <h1>{if !empty($Link->getMetaTitle())}{$Link->getMetaTitle()}{else}{$Link->getName()}{/if}</h1>
                    {/container}
                {/if}
            {/block}
            {block name='layout-index-include-extension'}
                {container}
                    {include file='snippets/extension.tpl'}
                {/container}
            {/block}

            {block name='layout-index-link-content'}
                {if !empty($Link->getContent())}
                    {opcMountPoint id='opc_before_content'}
                    {container}
                        {$Link->getContent()}
                    {/container}
                {/if}
            {/block}

            {block name='layout-index-link-types'}
                {if $Link->getLinkType() === $smarty.const.LINKTYP_AGB}
                    {block name='layout-index-link-type-tos'}
                        <div id="tos" class="well well-sm">
                            {opcMountPoint id='opc_before_tos'}
                            {if $AGB !== false}
                                {container}
                                    {if $AGB->cAGBContentHtml}
                                        {$AGB->cAGBContentHtml}
                                    {elseif $AGB->cAGBContentText}
                                        {$AGB->cAGBContentText|nl2br}
                                    {/if}
                                {/container}
                            {/if}
                            {opcMountPoint id='opc_after_tos'}
                        </div>
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_WRB}
                    {block name='layout-index-link-type-revocation'}
                        <div id="revocation-instruction" class="well well-sm">
                            {opcMountPoint id='opc_before_revocation'}
                            {if $WRB !== false}
                                {container}
                                    {if $WRB->cWRBContentHtml}
                                        {$WRB->cWRBContentHtml}
                                    {elseif $WRB->cWRBContentText}
                                        {$WRB->cWRBContentText|nl2br}
                                    {/if}
                                {/container}
                            {/if}
                            {opcMountPoint id='opc_after_revocation'}
                        </div>
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_WRB_FORMULAR}
                    {block name='layout-index-link-type-revocation-form'}
                        <div id="revocation-form" class="well well-sm">
                            {opcMountPoint id='opc_before_revocation_form'}
                            {if $WRB !== false}
                                {container}
                                    {if $WRB->cWRBFormContentHtml}
                                        {$WRB->cWRBFormContentHtml}
                                    {elseif $WRB->cWRBFormContentText}
                                        {$WRB->cWRBFormContentText|nl2br}
                                    {/if}
                                {/container}
                            {/if}
                            {opcMountPoint id='opc_after_revocation_form'}
                        </div>
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_DATENSCHUTZ}
                    {block name='layout-index-link-type-data-privacy'}
                        <div id="data-privacy" class="well well-sm">
                            {opcMountPoint id='opc_before_data_privacy'}
                            {if $WRB !== false}
                                {container}
                                    {if $WRB->cDSEContentHtml}
                                        {$WRB->cDSEContentHtml}
                                    {elseif $WRB->cDSEContentText}
                                        {$WRB->cDSEContentText|nl2br}
                                    {/if}
                                {/container}
                            {/if}
                            {opcMountPoint id='opc_after_data_privacy'}
                        </div>
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}
                    {block name='layout-index-include-index'}
                        {include file='page/index.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_VERSAND}
                    {block name='layout-index-include-shipping'}
                        {include file='page/shipping.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_LIVESUCHE}
                    {block name='layout-index-include-livesearch'}
                        {include file='page/livesearch.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_HERSTELLER}
                    {block name='layout-index-include-manufacturers'}
                        {include file='page/manufacturers.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_NEWSLETTERARCHIV}
                    {block name='layout-index-include-newsletter-archive'}
                        {include file='page/newsletter_archive.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_SITEMAP}
                    {block name='layout-index-include-sitemap'}
                        {include file='page/sitemap.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_GRATISGESCHENK}
                    {block name='layout-index-include-free-gift'}
                        {include file='page/free_gift.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_PLUGIN && empty($nFullscreenTemplate)}
                    {block name='layout-index-include-plugin'}
                        {include file=$cPluginTemplate}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_AUSWAHLASSISTENT}
                    {block name='layout-index-include-selection-wizard'}
                        {include file='selectionwizard/index.tpl'}
                    {/block}
                {elseif $Link->getLinkType() === $smarty.const.LINKTYP_404}
                    {block name='layout-index-include-404'}
                        {include file='page/404.tpl'}
                    {/block}
                {/if}
            {/block}
        {/block}

        {block name='layout-index-include-footer'}
            {if !isset($bAjaxRequest) || !$bAjaxRequest}
                {include file='layout/footer.tpl'}
            {else}
                {include file='layout/modal_footer.tpl'}
            {/if}
        {/block}
    {/if}
{/block}
