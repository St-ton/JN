{block name='snippets-ribbon'}
    {if !empty($Artikel->Preise->Sonderpreis_aktiv)}
        {$sale = $Artikel->Preise->discountPercentage}
    {/if}

    {block name='snippets-ribbon-main'}
        {if isset($Artikel->oSuchspecialBild)}
            {if $Artikel->oSuchspecialBild->getType() === $smarty.const.SEARCHSPECIALS_CUSTOMBADGE}
                {assign var=customBadge value=$Artikel->oSuchspecialBild->getCssAndText()}
                <div class="ribbon ribbon-custom productbox-ribbon{if $customBadge->class !== ''}{$customBadge->class}{/if}"
                    {if $customBadge->style !== ''} style="{$customBadge->style}"{/if}>
                    {block name='snippets-ribbon-content'}
                        {$customBadge->text}
                    {/block}
                </div>
            {else}
                <div class="ribbon
                ribbon-{$Artikel->oSuchspecialBild->getType()} productbox-ribbon">
                    {block name='snippets-ribbon-content'}
                        {lang key='ribbon-'|cat:$Artikel->oSuchspecialBild->getType() section='productOverview' printf=$sale|default:''|cat:'%'}
                    {/block}
                </div>
            {/if}
        {/if}
    {/block}
{/block}
