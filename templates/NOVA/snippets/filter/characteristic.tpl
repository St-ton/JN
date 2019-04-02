{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

{$is_dropdown = ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
{$limit = $Einstellungen.template.productlist.filter_max_options}
{$collapseInit = false}
{foreach $Merkmal->getOptions() as $attributeValue}
    {assign var=attributeImageURL value=''}
    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && $attributeValue->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALWERTBILD_VORHANDEN}
        {assign var=attributeImageURL value=$attributeValue->getData('cBildURLKlein')}
    {/if}
    {if $is_dropdown}
        {dropdownitem
            class="{if $attributeValue->isActive()}active{/if}"
            href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
            title="{if $Merkmal->getData('cTyp') === 'BILD'}{$attributeValue->getValue()|escape:'html'}{/if}"
        }
            <span class="badge badge-light float-right">{$attributeValue->getCount()}</span>
            <span class="value">
                    <i class="far fa-{if $attributeValue->isActive()}check-{/if}square text-muted"></i>
                {if !empty($attributeImageURL)}
                    {image src=$attributeImageURL alt=$attributeValue->getValue()|escape:'html' class="vmiddle"}
                {/if}
                {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                    <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                {/if}
            </span>
        {/dropdownitem}
    {else}
        {if $attributeValue@iteration > $limit && !$collapseInit}
            <div class="collapse" id="box-collps-{$Merkmal->kMerkmal}" aria-expanded="false">
                {$collapseInit = true}
        {/if}
        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
            {navitem
                class="{if $attributeValue->isActive()}active{/if}"
                href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                title="{if $Merkmal->getData('cTyp') === 'BILD'}{$attributeValue->getValue()|escape:'html'}{/if}"
                router-class="px-0"
            }
                <span class="badge badge-light float-right">{$attributeValue->getCount()}</span>
                <span class="value">
                    <i class="far fa-{if $attributeValue->isActive()}check-{/if}square text-muted"></i>
                    {if !empty($attributeImageURL)}
                        {image src=$attributeImageURL alt=$attributeValue->getValue()|escape:'html' class="vmiddle"}
                    {/if}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                        <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                    {/if}
                </span>
            {/navitem}
        {else}
            {link href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                title="{if $Merkmal->getData('cTyp') === 'BILD'}{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}{/if}"
                data=["toggle"=>"tooltip"]
            }
                {image src=$attributeImageURL alt=$attributeValue->getValue()|escape:'html'
                    title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                    class="vmiddle filter-img"
                }
            {/link}
        {/if}
    {/if}
{/foreach}
{if $Merkmal->getOptions()|count > $limit && !$is_dropdown}
        </div>
    {button
        variant="link"
        role="button"
        class="text-right pr-0"
        data=["toggle"=> "collapse", "target"=>"#box-collps-{$Merkmal->kMerkmal}"]
    }
        {lang key='showAll'} <i class="fas fa-chevron-down"></i>
    {/button}
{/if}
