{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{container}
    {include file='snippets/opc_mount_point.tpl' id='opc_tagging_prepend'}
    {listgroup}
        {foreach $Tagging as $tag}
            {listgroupitem class="tag"}
                {link href="{$tag->cURLFull}"}{$tag->cName}{/link} <span class="badge-pill badge-primary float-right">{$tag->Anzahl}</span>
            {/listgroupitem}
        {/foreach}
    {/listgroup}
    {include file='snippets/opc_mount_point.tpl' id='opc_tagging_append'}
{/container}
