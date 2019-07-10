{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-news-month'}
    {card class="box box-monthlynews mb-7" id="sidebox{$oBox->getID()}" title="{lang key='newsBoxMonthOverview'}"}
        {block name='boxes-box-news-month-content'}
            {nav vertical=true}
                {foreach $oBox->getItems() as $oNewsMonatsUebersicht}
                    {block name='boxes-box-news-month-news-link'}
                        {navitem href=$oNewsMonatsUebersicht->cURL  title=$oNewsMonatsUebersicht->cName}
                            <span class="value">
                                <i class="far fa-newspaper mr-2"></i>
                                {$oNewsMonatsUebersicht->cName}
                                <span class="badge badge-light float-right">{$oNewsMonatsUebersicht->nAnzahl}</span>
                            </span>
                        {/navitem}
                    {/block}
                {/foreach}
            {/nav}
        {/block}
    {/card}
{/block}
