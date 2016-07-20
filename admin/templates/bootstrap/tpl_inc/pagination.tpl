{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

<div class="block">
    <form method="get" class="form-inline">
        {foreach $cParam_arr as $cParamName => $cParamValue}
            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
        {/foreach}
        {if $oPagination->nPageCount > 1}
            <div class="form-group">
                <label>
                    Eintr&auml;ge {$oPagination->nFirstItem + 1}
                    - {$oPagination->nFirstItem + $oPagination->nPageItemCount}
                    von {$oPagination->nItemCount}
                </label>
                <div class="btn-group">
                    <button type="submit" class="btn btn-sm btn-link" name="{$oPagination->cID}_nPage" value="{$oPagination->nPrevPage}"
                        {if $oPagination->nPrevPage == $oPagination->nPage} disabled="disabled"{/if}>&laquo;</button>
                    {if $oPagination->nLeftRangePage > 0}
                        <button type="submit" class="btn btn-sm btn-default" name="{$oPagination->cID}_nPage" value="0">1</button>
                    {/if}
                    {if $oPagination->nLeftRangePage > 1}
                        </div><label>...</label><div class="btn-group">
                    {/if}
                    {for $i=$oPagination->nLeftRangePage to $oPagination->nRightRangePage}
                        <button type="{if $oPagination->nPage == $i}button{else}submit{/if}"
                                class="btn btn-sm{if $oPagination->nPage == $i} btn-primary{else} btn-default{/if}"
                                name="{$oPagination->cID}_nPage" value="{$i}">{$i+1}</button>
                    {/for}
                    {if $oPagination->nRightRangePage < $oPagination->nPageCount - 2}
                        </div><label>...</label><div class="btn-group">
                    {/if}
                    {if $oPagination->nRightRangePage < $oPagination->nPageCount - 1}
                        <button type="submit" class="btn btn-sm btn-default" name="{$oPagination->cID}_nPage" value="{$oPagination->nPageCount - 1}">
                            {$oPagination->nPageCount}
                        </button>
                    {/if}
                    <button type="submit" class="btn btn-sm btn-link" name="{$oPagination->cID}_nPage" value="{$oPagination->nNextPage}"
                        {if $oPagination->nNextPage == $oPagination->nPage} disabled="disabled"{/if}>&raquo;</button>
                </div>
            </div>
        {/if}
        <div class="form-group">
            <label for="{$oPagination->cID}_nItemsPerPage">
                Eintr&auml;ge pro Seite
            </label>
            <select class="form-control" name="{$oPagination->cID}_nItemsPerPage" id="{$oPagination->cID}_nItemsPerPage">
                {foreach $oPagination->nItemsPerPageOption_arr as $nItemsPerPageOption}
                    <option value="{$nItemsPerPageOption}"{if $oPagination->nItemsPerPage == $nItemsPerPageOption} selected="selected"{/if}>
                        {$nItemsPerPageOption}
                    </option>
                {/foreach}
                <option value="-1"{if $oPagination->nItemsPerPage == -1} selected="selected"{/if}>
                    alle
                </option>
            </select>
        </div>
        {if $oPagination->cSortByOption_arr|@count > 0}
            <div class="form-group">
                <label for="{$oPagination->cID}_nSortBy">
                    Sortieren nach
                </label>
                <select class="form-control" name="{$oPagination->cID}_nSortBy" id="{$oPagination->cID}_nSortBy">
                    {foreach $oPagination->cSortByOption_arr as $i => $cSortByOption}
                        <option value="{$i}"{if $i === (int)$oPagination->nSortBy} selected="selected"{/if}>
                            {$cSortByOption[1]}
                        </option>
                    {/foreach}
                </select>
                <select class="form-control" name="{$oPagination->cID}_cSortDir" id="{$oPagination->cID}_cSortDir">
                    <option value="asc"{if $oPagination->cSortDir === 'asc'} selected{/if}>aufsteigend</option>
                    <option value="desc"{if $oPagination->cSortDir === 'desc'} selected{/if}>absteigend</option>
                </select>
            </div>
        {/if}
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-refresh"></i>
        </button>
        {if $oPagination->nPageCount == 1}
            <div class="form-group">
                <label>Eintr&auml;ge gesamt: {$oPagination->nPageCount}</label>
            </div>
        {/if}
    </form>
</div>