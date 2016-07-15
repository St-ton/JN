<div class="block">
    <form method="get" class="form-inline">
        {foreach $oPagination->cAddGetVar_arr as $cGetVarName => $cGetVarValue}
            <input type="hidden" name="{$cGetVarName}" value="{$cGetVarValue}">
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
                    <button type="submit" class="btn btn-sm{if $oPagination->nPage == $i} btn-primary{else} btn-default{/if}" name="{$oPagination->cID}_nPage" value="{$i}">{$i+1}</button>
                {/for}
                <button type="submit" class="btn btn-sm btn-link" name="{$oPagination->cID}_nPage" value="{$oPagination->nNextPage}"
                    {if $oPagination->nNextPage == $oPagination->nPage} disabled="disabled"{/if}>&raquo;</button>
            {/if}
            <label for="{$oPagination->cID}_nItemsPerPage">
                Eintr&auml;ge pro Seite
            </label>
            <select class="form-control" name="{$oPagination->cID}_nItemsPerPage" id="{$oPagination->cID}_nItemsPerPage" onchange="this.form.submit();">
                {foreach $oPagination->nItemsPerPageOption_arr as $nItemsPerPageOption}
                    <option value="{$nItemsPerPageOption}"{if $oPagination->nItemsPerPage == $nItemsPerPageOption} selected="selected"{/if}>
                        {$nItemsPerPageOption}
                    </option>
                {/foreach}
            </select>
        </div>
    </form>
</div>