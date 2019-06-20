{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{$is_dropdown = false}
{$limit = $Einstellungen.template.productlist.filter_max_options}
{$collapseInit = false}
{if ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
    {$is_dropdown = true}
{/if}

<ul {if $is_dropdown}class="dropdown-menu" role="menu" {elseif isset($class)}class="{$class}" {else}class="nav nav-list"{/if}>
    {foreach $Merkmal->getOptions() as $attributeValue}
        {assign var=attributeImageURL value=''}
        {if ($Merkmal->getData('cTyp') === 'BILD' || $Merkmal->getData('cTyp') === 'BILD-TEXT')
            && $attributeValue->getData('cBildpfadKlein') !== $smarty.const.BILD_KEIN_MERKMALWERTBILD_VORHANDEN
        }
            {assign var=attributeImageURL value=$attributeValue->getData('cBildURLKlein')}
        {/if}

        {if $limit != -1 && $attributeValue@iteration > $limit && !$collapseInit && !$is_dropdown}
            <div class="collapse {if $Merkmal->isActive()} in{/if}" id="box-collps-{$Merkmal->kMerkmal}" aria-expanded="false">
                <ul class="nav nav-list">
                {$collapseInit = true}
        {/if}
        {if $Merkmal->getData('cTyp') === 'BILD'}
            <li class="{if $attributeValue->isActive()}active{/if}">
                <a rel="nofollow" href="{$attributeValue->getURL()}" title="{$attributeValue->getValue()|escape:'html'}">
                    <span class="badge pull-right">{$attributeValue->getCount()}</span>
                    <span class="value">
                        <i class="fa {if $attributeValue->isActive()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                        {if !empty($attributeImageURL)}
                            <img src="{$attributeImageURL}" alt="{$attributeValue->getValue()|escape:'html'}" class="vmiddle" />
                        {/if}
                    </span>
                </a>
            </li>
        {elseif $Merkmal->getData('cTyp') === 'BILD-TEXT'}
            <li class="{if $attributeValue->isActive()}active{/if}">
                <a rel="nofollow" href="{$attributeValue->getURL()}" title="{$attributeValue->getValue()|escape:'html'}">
                    <span class="badge pull-right">{$attributeValue->getCount()}</span>
                    <span class="value">
                        <i class="fa {if $attributeValue->isActive()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                        {if !empty($attributeImageURL)}
                            <img src="{$attributeImageURL}" alt="{$attributeValue->getValue()|escape:'html'}" class="vmiddle" />
                        {/if}
                        <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                    </span>
                </a>
            </li>
        {else}
            <li class="{if $attributeValue->isActive()}active{/if}">
                <a rel="nofollow" href="{$attributeValue->getURL()}" title="{$attributeValue->getValue()|escape:'html'}">
                    <span class="badge pull-right">{$attributeValue->getCount()}</span>
                    <span class="value">
                        <i class="fa {if $attributeValue->isActive()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                        <span class="word-break">{$attributeValue->getValue()|escape:'html'}</span>
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
    {if $limit != -1 && $Merkmal->getOptions()|count > $limit && !$is_dropdown}
            </ul>
        </div>
        <button class="btn btn-link pull-right"
                role="button"
                data-toggle="collapse"
                data-target="#box-collps-{$Merkmal->kMerkmal}"
        >
            {lang key='showAll'} <span class="caret"></span>
        </button>
    {/if}
</ul>
