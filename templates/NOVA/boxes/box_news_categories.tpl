{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-news-categories'}
    {card class="box box-newscategories mb-7" id="sidebox{$oBox->getID()}" title="{lang key='newsBoxCatOverview'}"}
        <hr class="mt-0 mb-4">
        {block name='boxes-box-news-categories-content'}
            {nav vertical=true}
                {foreach $oBox->getItems() as $oNewsKategorie}
                    {navitem}
                        {link href=$oNewsKategorie->cURLFull title=$oNewsKategorie->cName}
                            <span class="value">
                                {$oNewsKategorie->cName} <span class="badge badge-light float-right">{$oNewsKategorie->nAnzahlNews}</span>
                            </span>
                        {/link}
                    {/navitem}
                {/foreach}
            {/nav}
        {/block}
    {/card}
{/block}
