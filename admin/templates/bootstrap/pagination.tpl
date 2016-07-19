{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

<div class="block">
    <form method="get" class="form-inline">
        {foreach $cParam_arr as $cParamName => $cParamValue}
            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
        {/foreach}
        <div class="form-group">
            {if $oPagination->nPageCount > 1}
                <label>
                    Eintr&auml;ge {$oPagination->nFirstItem + 1}
                    - {$oPagination->nFirstItem + $oPagination->nPageItemCount}
                    von {$oPagination->nItemCount}
                </label>
                <button type="submit" class="btn btn-sm btn-link" name="{$oPagination->cID}_nPage" value="{$oPagination->nPrevPage}"
                    {if $oPagination->nPrevPage == $oPagination->nPage} disabled="disabled"{/if}>&laquo;</button>
                {for $i=0 to $oPagination->nPageCount-1}
                    <button type="{if $oPagination->nPage == $i}button{else}submit{/if}"
                            class="btn btn-sm{if $oPagination->nPage == $i} btn-primary{else} btn-default{/if}"
                            name="{$oPagination->cID}_nPage" value="{$i}">{$i+1}</button>
                {/for}
                <button type="submit" class="btn btn-sm btn-link" name="{$oPagination->cID}_nPage" value="{$oPagination->nNextPage}"
                    {if $oPagination->nNextPage == $oPagination->nPage} disabled="disabled"{/if}>&raquo;</button>
            {/if}
            <label for="{$oPagination->cID}_nItemsPerPage">
                Eintr&auml;ge pro Seite
            </label>
            <select class="form-control" name="{$oPagination->cID}_nItemsPerPage" id="{$oPagination->cID}_nItemsPerPage" {*onchange="this.form.submit();"*}>
                {foreach $oPagination->nItemsPerPageOption_arr as $nItemsPerPageOption}
                    <option value="{$nItemsPerPageOption}"{if $oPagination->nItemsPerPage == $nItemsPerPageOption} selected="selected"{/if}>
                        {$nItemsPerPageOption}
                    </option>
                {/foreach}
            </select>
            {if $oPagination->cSortByOption_arr|@count > 0}
                <label for="{$oPagination->cID}_nSortBy">
                    Sortieren nach
                </label>
                <select class="form-control" name="{$oPagination->cID}_nSortBy" id="{$oPagination->cID}_nSortBy" {*onchange="this.form.submit();"*}>
                    {foreach $oPagination->cSortByOption_arr as $i => $cSortByOption}
                        <option value="{$i}"{if $i === (int)$oPagination->nSortBy} selected="selected"{/if}>
                            {$cSortByOption[1]}
                        </option>
                    {/foreach}
                </select>
                <select class="form-control" name="{$oPagination->cID}_cSortDir" id="{$oPagination->cID}_cSortDir" {*onchange="this.form.submit();"*}>
                    <option value="asc">aufsteigend</option>
                    <option value="desc">absteigend</option>
                </select>
            {/if}
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </form>
</div>