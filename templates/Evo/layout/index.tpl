{if isset($nFullscreenTemplate) && $nFullscreenTemplate == 1}
    {include file=$cPluginTemplate}
{else}
    {include file='layout/header.tpl'}
    {if !empty($Link->Sprache->cTitle)}
        <h1>{$Link->Sprache->cTitle}</h1>
    {elseif isset($bAjaxRequest) && $bAjaxRequest}
        <h1>{if !empty($Link->Sprache->cMetaTitle)}{$Link->Sprache->cMetaTitle}{else}{$Link->Sprache->cName}{/if}</h1>
    {/if}

    {include file="snippets/extension.tpl"}

    {if !empty($Link->Sprache->cContent)}
        {$Link->Sprache->cContent}
    {/if}

    {if $Link->nLinkart == 11}
        <div id="tos" class="well well-sm">
            {if $AGB->cAGBContentHtml}
                {$AGB->cAGBContentHtml}
            {elseif $AGB->cAGBContentText}
                {$AGB->cAGBContentText|nl2br}
            {/if}
        </div>
    {elseif $Link->nLinkart == 24}
        <div id="revocation-instruction" class="well well-sm">
            {if $WRB->cWRBContentHtml}
                {$WRB->cWRBContentHtml}
            {elseif $WRB->cWRBContentText}
                {$WRB->cWRBContentText|nl2br}
            {/if}
        </div>
    {elseif $Link->nLinkart == 5}
        {include file='page/index.tpl'}
    {elseif $Link->nLinkart == 6}
        {include file='page/shipping.tpl'}
    {elseif $Link->nLinkart == 14}
        {include file='page/tagging.tpl'}
    {elseif $Link->nLinkart == 15}
        {include file='page/livesearch.tpl'}
    {elseif $Link->nLinkart == 16}
        {include file='page/manufacturers.tpl'}
    {elseif $Link->nLinkart == 18}
        {include file='page/newsletter_archive.tpl'}
    {elseif $Link->nLinkart == 21}
        {include file='page/sitemap.tpl'}
    {elseif $Link->nLinkart == 23}
        {include file='page/free_gift.tpl'}
    {elseif $Link->nLinkart == 25 && empty($nFullscreenTemplate)}
        {include file="$cPluginTemplate"}
    {elseif $Link->nLinkart == 26}
        {include file='productwizard/index.tpl'}
    {elseif $Link->nLinkart == 29}
        {include file='page/404.tpl'}
    {/if}
    {include file='layout/footer.tpl'}
{/if}