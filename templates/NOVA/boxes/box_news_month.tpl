{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-news-month'}
    {card class="box box-monthlynews mb-7" id="sidebox{$oBox->getID()}" title="{lang key='newsBoxMonthOverview'}"}
        {block name='boxes-box-news-month-content'}
            {nav vertical=true}
                {foreach $oBox->getItems() as $newsMonth}
                    {if $newsMonth@index === 10}{break}{/if}
                    {block name='boxes-box-news-month-news-link'}
                        {navitem href=$newsMonth->cURL  title=$newsMonth->cName}
                            <span class="value">
                                <i class="far fa-newspaper mr-2"></i>
                                {$newsMonth->cName}
                                <span class="badge badge-light float-right">{$newsMonth->nAnzahl}</span>
                            </span>
                        {/navitem}
                    {/block}
                {/foreach}
            {/nav}
        {/block}
    {/card}
{/block}
