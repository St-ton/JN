{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-filter-characteristics'}
    {$is_dropdown = ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
    {foreach $Merkmal->getOptions() as $attributeValue}
        {assign var=attributeImageURL value=''}
        {if ($Merkmal->getData('cTyp') === 'BILD' || $Merkmal->getData('cTyp') === 'BILD-TEXT')
            && $attributeValue->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALWERTBILD_VORHANDEN
        }
            {assign var=attributeImageURL value=$attributeValue->getData('cBildURLKlein')}
        {/if}
        {if $is_dropdown}
            {block name='snippets-filter-characteristics-dropdown'}
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
                        <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                    </span>
                {/dropdownitem}
            {/block}
        {else}
            {block name='snippets-filter-characteristics-nav'}
                {if {$Merkmal->getData('cTyp')} === 'TEXT'}
                    {navitem
                        class="{if $attributeValue->isActive()}active{/if}"
                        href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                        title="{$attributeValue->getValue()|escape:'html'}"
                        router-class="px-0"
                    }
                        <span class="value">
                            <i class="far fa-{if $attributeValue->isActive()}check-{/if}square text-muted"></i>
                            {if !empty($attributeImageURL)}
                                {image src=$attributeImageURL alt=$attributeValue->getValue()|escape:'html' class="vmiddle"}
                            {/if}
                            <span class="word-break">{$attributeValue->getValue()|escape:'html'} ({$attributeValue->getCount()})</span>
                        </span>
                    {/navitem}
                {elseif $Merkmal->getData('cTyp') === 'BILD'}
                    {link href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                        title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                        data=["toggle"=>"tooltip"]
                    }
                        {image src=$attributeImageURL alt=$attributeValue->getValue()|escape:'html'
                            title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                            class="vmiddle filter-img"
                        }
                    {/link}
                {else}
                    {link href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"
                        title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                        data=["toggle"=>"tooltip"]
                    }
                        {image src=$attributeImageURL alt=$attributeValue->getValue()|escape:'html'
                            title="{$attributeValue->getValue()|escape:'html'}: {$attributeValue->getCount()}"
                            class="vmiddle filter-img"
                        }
                        <span class="word-break">
                            {$attributeValue->getValue()|escape:'html'} ({$attributeValue->getCount()})
                        </span>
                    {/link}
                {/if}
            {/block}
        {/if}
    {/foreach}
{/block}
