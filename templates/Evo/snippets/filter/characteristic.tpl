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
            {if $limit!= -1 && $attributeValue@iteration > $limit && !$collapseInit && !$is_dropdown}
                <div class="collapse" id="box-collps-{$Merkmal->kMerkmal}" aria-expanded="false"><ul class="nav nav-list">
                    {$collapseInit = true}
            {/if}
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
</ul>
{if $limit != -1 && $Merkmal->getOptions()|count > $limit && !$is_dropdown}
        </ul></div>
    <button class="btn btn-link pull-right"
            role="button"
            data-toggle="collapse"
            data-target="#box-collps-{$Merkmal->kMerkmal}"
    >
        {lang key='showAll'} <span class="caret"></span>
    </button>
{/if}
