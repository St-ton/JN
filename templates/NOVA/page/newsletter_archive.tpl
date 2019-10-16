{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-newsletter-archive'}
    {container}
        {opcMountPoint id='opc_before_newsletter'}
        {block name='page-newsletter-archive-toptags'}
            <div id="toptags">{lang key='newsletterhistory'}</div>
        {/block}
        {block name='page-newsletter-archive-content'}
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
        {/block}
    {/container}
{/block}
