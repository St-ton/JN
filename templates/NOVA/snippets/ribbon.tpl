{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-ribbon'}
    {if !empty($Artikel->Preise->Sonderpreis_aktiv)}
        {$sale = $Artikel->Preise->discountPercentage}
    {/if}

    {block name='snippets-ribbon-main'}
        <div class="ribbon ribbon-{$Einstellungen.template.productlist.ribbon_type}
            ribbon-{$Einstellungen.template.productlist.ribbon_position}
            ribbon-{$Artikel->oSuchspecialBild->getType()} productbox-ribbon">
            {block name='snippets-ribbon-content'}
                {lang key='ribbon-'|cat:$Artikel->oSuchspecialBild->getType() section='productOverview' printf=$sale|default:''|cat:'%'}
            {/block}
        </div>
    {/block}
{/block}