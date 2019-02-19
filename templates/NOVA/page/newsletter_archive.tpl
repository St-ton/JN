{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{include file='snippets/opc_mount_point.tpl' id='opc_nl_archive_toptags_prepend'}
<div id="toptags">{lang key='newsletterhistory'}</div>
{include file='snippets/opc_mount_point.tpl' id='opc_nl_archive_toptags_append'}
{card class="newsletter"}
    {row class="font-weight-bold border-bottom"}
        {col cols=7}{lang key='newsletterhistorysubject'}{/col}
        {col cols=5}{lang key='newsletterhistorydate'}{/col}
    {/row}
    {foreach $oNewsletterHistory_arr as $oNewsletterHistory}
    {row class="content_{$oNewsletterHistory@iteration % 2}"}
        {col cols=7}
            {link href="{get_static_route id='newsletter.php'}?show={$oNewsletterHistory->kNewsletterHistory}"}{$oNewsletterHistory->cBetreff}{/link}
        {/col}
        {col cols=5}{$oNewsletterHistory->Datum}{/col}
    {/row}
    {/foreach}
{/card}
{include file='snippets/opc_mount_point.tpl' id='opc_nl_archive_append'}
