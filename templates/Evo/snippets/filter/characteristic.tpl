{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var='is_dropdown' value=false}
{if ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
    {assign var='is_dropdown' value=true}
{/if}

<ul {if $is_dropdown}class="dropdown-menu" role="menu" {elseif isset($class)}class="{$class}" {else}class="nav nav-list"{/if}>
    {foreach $Merkmal->getOptions() as $attributeValue}
        {assign var=attributeImageURL value=''}
        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && $attributeValue->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALWERTBILD_VORHANDEN}
            {assign var=attributeImageURL value=$attributeValue->getData('cBildURLKlein')}
        {/if}

        {if $attributeValue->isActive()}
            <li class="active">
                <a rel="nofollow" href="{if !empty($attributeValue->getURL())}{$attributeValue->getURL()}{else}#{/if}"{if $Merkmal->getData('cTyp') === 'BILD'} title="{$attributeValue->getValue()|escape:'html'}"{/if}>
                    <span class="badge pull-right">{$attributeValue->getCount()}</span>
                    <span class="value">
                        <i class="fa fa-check-square-o text-muted"></i>
                        {if !empty($attributeImageURL)}
                            <img src="{$attributeImageURL}" alt="{$attributeValue->getValue()|escape:'html'}" class="vmiddle" />
                        {/if}
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                            <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                        {/if}
                    </span>
                </a>
            </li>
        {else}
            <li>
                <a rel="nofollow" href="{$attributeValue->getURL()}"{if $Merkmal->getData('cTyp') === 'BILD'} title="{$attributeValue->getValue()|escape:'html'}"{/if}>
                    <span class="badge pull-right">{$attributeValue->getCount()}</span>
                    <span class="value">
                        <i class="fa fa-square-o text-muted"></i>
                        {if !empty($attributeImageURL)}
                            <img src="{$attributeImageURL}" alt="{$attributeValue->getValue()|escape:'html'}" class="vmiddle" />
                        {/if}
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                            <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                        {/if}
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
    {if ($Merkmal->getOptions()|count < $Merkmal->getCount())}
        <li>
            <button class="btn-link btn-block filter-show-all" name="attributeValue" value='{getFilterParams filter_val=$Merkmal->getValue() filter_class=$Merkmal->getClassName()}' rel="nofollow">
                <span class="badge pull-right">99+</span>
                <span class="value">alle anzeigen</span>
            </button>
        </li>
    {/if}
</ul>
