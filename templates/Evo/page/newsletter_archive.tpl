{opcMountPoint id='opc_before_newsletter'}

<div id="toptags">{lang key='newsletterhistory' section='global'}</div>
<table class="newsletter">
    <tr class="head">
        <th>{lang key='newsletterhistorysubject' section='global'}</th>
        <th>{lang key='newsletterhistorydate' section='global'}</th>
    </tr>
    {foreach $oNewsletterHistory_arr as $oNewsletterHistory}
    <tr class="content_{$oNewsletterHistory@iteration % 2}">
        <td class="left_td">
            <a href="{$oNewsletterHistory->cURLFull}">{$oNewsletterHistory->cBetreff}</a>
        </td>
        <td class="right_td">{$oNewsletterHistory->Datum}</td>
    </tr>
    {/foreach}
</table>
