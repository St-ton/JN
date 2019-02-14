{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card class="box box-monthlynews mb-7" id="sidebox{$oBox->getID()}" title="{lang key='newsBoxMonthOverview'}"}
    <hr class="mt-0 mb-4">
    {nav vertical=true}
        {foreach $oBox->getItems() as $oNewsMonatsUebersicht}
            {navitem}
                {link href="{$oNewsMonatsUebersicht->cURL}"  title="{$oNewsMonatsUebersicht->cName}"}
                    <span class="value">
                        <i class="far fa-newspaper mr-2"></i>
                        {$oNewsMonatsUebersicht->cName}
                        <span class="badge badge-light float-right">{$oNewsMonatsUebersicht->nAnzahl}</span>
                    </span>
                {/link}
            {/navitem}
        {/foreach}
    {/nav}
{/card}
