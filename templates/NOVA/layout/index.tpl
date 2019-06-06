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
            {container}
                {block name='layout-index-heading'}
                    {if !empty($Link->getTitle())}
                        <h1>{$Link->getTitle()}</h1>
                    {elseif isset($bAjaxRequest) && $bAjaxRequest}
                        <h1>{if !empty($Link->getMetaTitle())}{$Link->getMetaTitle()}{else}{$Link->getName()}{/if}</h1>
                    {/if}
                {/block}
                {block name='layout-index-include-extension'}
                    {include file='snippets/extension.tpl'}
                {/block}
            {/container}

            {*{include file='snippets/opc_mount_point.tpl' id='opc_link_content_prepend'}*}

            {block name='layout-index-link-content'}
                {if !empty($Link->getContent())}
                    {container}
                        {$Link->getContent()}
                    {/container}
                {/if}
            {/block}

            {*{include file='snippets/opc_mount_point.tpl' id='opc_link_content_append'}*}

            {block name='layout-index-link-types'}
                {container}
                    {if $Link->getLinkType() === $smarty.const.LINKTYP_AGB}
                        <div id="tos" class="well well-sm">
                            {*{include file='snippets/opc_mount_point.tpl' id='opc_tos_prepend'}*}
                            {if $AGB !== false}
                                {if $AGB->cAGBContentHtml}
                                    {$AGB->cAGBContentHtml}
                                {elseif $AGB->cAGBContentText}
                                    {$AGB->cAGBContentText|nl2br}
                                {/if}
                            {/if}
                            {include file='snippets/opc_mount_point.tpl' id='opc_tos_append'}
                        </div>
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_WRB}
                        <div id="revocation-instruction" class="well well-sm">
                            {include file='snippets/opc_mount_point.tpl' id='opc_revocation_prepend'}
                            {if $WRB !== false}
                                {if $WRB->cWRBContentHtml}
                                    {$WRB->cWRBContentHtml}
                                {elseif $WRB->cWRBContentText}
                                    {$WRB->cWRBContentText|nl2br}
                                {/if}
                            {/if}
                            {include file='snippets/opc_mount_point.tpl' id='opc_revocation_append'}
                        </div>
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}
                        {include file='page/index.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_VERSAND}
                        {include file='page/shipping.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_TAGGING}
                        {include file='page/tagging.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_LIVESUCHE}
                        {include file='page/livesearch.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_HERSTELLER}
                        {include file='page/manufacturers.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_NEWSLETTERARCHIV}
                        {include file='page/newsletter_archive.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_SITEMAP}
                        {include file='page/sitemap.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_GRATISGESCHENK}
                        {include file='page/free_gift.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_PLUGIN && empty($nFullscreenTemplate)}
                        {include file=$cPluginTemplate}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_AUSWAHLASSISTENT}
                        {include file='selectionwizard/index.tpl'}
                    {elseif $Link->getLinkType() === $smarty.const.LINKTYP_404}
                        {include file='page/404.tpl'}
                    {/if}
                {/container}
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
