{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{include file='snippets/opc_mount_point.tpl' id='opc_tagging_prepend'}
<ul class="list-unstyled">
    {foreach $Tagging as $tag}
        <li class="tag"><a href="{$tag->cURLFull}">{$tag->cName}</a> <span class="badge pull-right">{$tag->Anzahl}</span></li>
    {/foreach}
</ul>
{include file='snippets/opc_mount_point.tpl' id='opc_tagging_append'}
