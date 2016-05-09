{strip}
{has_boxes position='left' assign='hasLeftBox'}
{if !empty($Brotnavi) && !$bExclusive && !$bAjaxRequest && $nSeitenTyp != 18 && $nSeitenTyp != 3 && $nSeitenTyp != 9 && $nSeitenTyp != 10 && $nSeitenTyp != 11 && $nSeitenTyp != 38 }
    <div class="breadcrumb-wrapper hidden-xs">
        <div class="row">
            <div class="col-xs-12">
                <ul id="breadcrumb" class="breadcrumb">
                    {foreach name=navi from=$Brotnavi item=oItem}
                        {if $smarty.foreach.navi.first}
                            <li class="breadcrumb-item first">
                                <a href="{$oItem->url}" title="{$oItem->name|escape:"quotes"}"><span class="fa fa-home"></span></a>
                            </li>
                        {elseif $smarty.foreach.navi.last}
                            <li class="breadcrumb-item last">
                                {if $oItem->name !== null}
                                    {$oItem->name}
                                {elseif isset($Suchergebnisse->SuchausdruckWrite)}
                                    {$Suchergebnisse->SuchausdruckWrite}
                                {/if}
                            </li>
                        {else}
                            <li class="breadcrumb-item">
                                <a href="{$oItem->url}" title="{$oItem->name|escape:"quotes"}">{$oItem->name}</a>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
{/if}
{/strip}