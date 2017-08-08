{if !isset($itemClass)}
    {assign var=itemClass value=''}
{/if}
<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {if false && isset($filter->oMerkmalWerte_arr)}
        {foreach $filter->oMerkmalWerte_arr as $MerkmalWert}
            {if $MerkmalWert->nAktiv}
                <li class="active">
                    <a rel="nofollow" href="{if !empty($MerkmalWert->cURL)}{$MerkmalWert->cURL}{else}#{/if}"{if $filter->cTyp === 'BILD'} title="{$MerkmalWert->cWert}"{/if}>
                        <span class="value">
                            <i class="fa fa-check-square-o text-muted"></i>
                            {if $MerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif' && $filter->cTyp !== 'TEXT'}
                                <img src="{$MerkmalWert->cBildpfadKlein}" alt="{$MerkmalWert->cWert|escape:'html'}" class="vmiddle" />
                            {/if}
                            {if $filter->cTyp !== 'BILD'}
                                <span class="word-break">{$MerkmalWert->cWert|escape:'html'}</span>
                            {/if}
                            <span class="badge pull-right">{$MerkmalWert->nAnzahl}</span>
                        </span>
                    </a>
                </li>
            {else}
                <li>
                    <a rel="nofollow" href="{$MerkmalWert->cURL}"{if $filter->cTyp === 'BILD'} title="{$MerkmalWert->cWert|escape:'html'}"{/if}>
                        <span class="value">
                            <i class="fa fa-square-o text-muted"></i>
                            {if $MerkmalWert->cBildpfadKlein !== 'gfx/keinBild_kl.gif' && $filter->cTyp !== 'TEXT'}
                                <img src="{$MerkmalWert->cBildpfadKlein}" alt="{$MerkmalWert->cWert|escape:'html'}" class="vmiddle" />
                            {/if}
                            {if $filter->cTyp !== 'BILD'}
                                <span class="word-break">{$MerkmalWert->cWert|escape:'html'}</span>
                            {/if}
                            <span class="badge pull-right">{$MerkmalWert->nAnzahl}</span>
                        </span>
                    </a>
                </li>
            {/if}
        {/foreach}
    {else}
        {if is_array($filter)}
            {*@todo! - catch this - search filters will be arrays.*}
            <pre>{$filter|@var_dump}</pre>
        {elseif $filter->isInitialized() && $filter->getType() !== $filter::FILTER_TYPE_OR}
            <li>
                <a href="{$filter->getUnsetFilterURL()}" rel="nofollow" class="active {$itemClass}">
                    <span class="value">
                        <i class="fa fa-check-square-o text-muted"></i> {$filter->getName()}
                    </span>
                </a>
            </li>
        {else}
            {foreach $filter->getOptions() as $filterOption}
                <li>
                    <a rel="nofollow" href="{$filterOption->getURL()}" class="{$itemClass}">
                        <span class="value">
                            {if $filter->getIcon() !== null}
                                <i class="fa {$filter->getIcon()}"></i>
                            {else}
                                <i class="fa {if $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>
                            {/if}
                            {if $filter->getClassName() === 'FilterItemRating'}
                                {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                            {/if}
                            {$filterOption->getName()}<span class="badge pull-right">{$filterOption->getCount()}</span>
                        </span>
                    </a>
                </li>
            {/foreach}
        {/if}
    {/if}
</ul>