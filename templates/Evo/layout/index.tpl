{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($nFullscreenTemplate) && $nFullscreenTemplate == 1}
    {include file=$cPluginTemplate}
{else}
    {block name='header'}
        {if !isset($bAjaxRequest) || !$bAjaxRequest}
            {include file='layout/header.tpl'}
        {else}
            {include file='layout/modal_header.tpl'}
        {/if}
    {/block}

    {block name='content'}
        {if !empty($Link->getTitle())}
            {include file='snippets/opc_mount_point.tpl' id='opc_before_heading'}
            <h1>{$Link->getTitle()}</h1>
        {elseif isset($bAjaxRequest) && $bAjaxRequest}
            {include file='snippets/opc_mount_point.tpl' id='opc_before_heading'}
            <h1>{if !empty($Link->getMetaTitle())}{$Link->getMetaTitle()}{else}{$Link->getName()}{/if}</h1>
        {/if}

        {include file='snippets/extension.tpl'}

        {if !empty($Link->getContent())}
            {include file='snippets/opc_mount_point.tpl' id='opc_before_content'}
            {$Link->getContent()}
        {/if}

        {if $Link->getLinkType() === $smarty.const.LINKTYP_AGB}
            <div id="tos" class="well well-sm">
                {include file='snippets/opc_mount_point.tpl' id='opc_before_tos'}
                {if $AGB !== false}
                    {if $AGB->cAGBContentHtml}
                        {$AGB->cAGBContentHtml}
                    {elseif $AGB->cAGBContentText}
                        {$AGB->cAGBContentText|nl2br}
                    {/if}
                {/if}
                {include file='snippets/opc_mount_point.tpl' id='opc_after_tos'}
            </div>
        {elseif $Link->getLinkType() === $smarty.const.LINKTYP_WRB}
            <div id="revocation-instruction" class="well well-sm">
                {include file='snippets/opc_mount_point.tpl' id='opc_before_revocation'}
                {if $WRB !== false}
                    {if $WRB->cWRBContentHtml}
                        {$WRB->cWRBContentHtml}
                    {elseif $WRB->cWRBContentText}
                        {$WRB->cWRBContentText|nl2br}
                    {/if}
                {/if}
                {include file='snippets/opc_mount_point.tpl' id='opc_after_revocation'}
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
            {include file='productwizard/index.tpl'}
        {elseif $Link->getLinkType() === $smarty.const.LINKTYP_404}
            {include file='page/404.tpl'}
        {/if}

        {include file='snippets/opc_mount_point.tpl' id='opc_content'}
    {/block}

    {block name='footer'}
        {if !isset($bAjaxRequest) || !$bAjaxRequest}
            {include file='layout/footer.tpl'}
        {else}
            {include file='layout/modal_footer.tpl'}
        {/if}
    {/block}
{/if}
