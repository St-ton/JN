{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-news-categories'}
    {card class="box box-newscategories mb-7" id="sidebox{$oBox->getID()}" title="{lang key='newsBoxCatOverview'}"}
        {block name='boxes-box-news-categories-content'}
            {nav vertical=true}
                {foreach $oBox->getItems() as $oNewsKategorie}
                    {navitem href=$oNewsKategorie->cURLFull title=$oNewsKategorie->cName}
                        {$oNewsKategorie->cName} <span class="badge badge-light float-right">{$oNewsKategorie->nAnzahlNews}</span>
                    {/navitem}
                {/foreach}
            {/nav}
        {/block}
    {/card}
{/block}
