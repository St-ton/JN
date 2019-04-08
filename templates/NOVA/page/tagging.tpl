{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-tagging'}
    {container}
        {include file='snippets/opc_mount_point.tpl' id='opc_tagging_prepend'}
        {block name='page-tagging-tags'}
            {listgroup}
                {foreach $Tagging as $tag}
                    {listgroupitem class="tag"}
                        {link href=$tag->cURLFull}{$tag->cName}{/link} {badge pill=true variant="primary" class="float-right"}{$tag->Anzahl}{/badge}
                    {/listgroupitem}
                {/foreach}
            {/listgroup}
        {/block}
        {include file='snippets/opc_mount_point.tpl' id='opc_tagging_append'}
    {/container}
{/block}
