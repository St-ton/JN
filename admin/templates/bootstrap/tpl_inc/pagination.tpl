{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

{assign var=cUrlAppend value=$cParam_arr|http_build_query}

{if isset($cAnchor)}
    {assign var=cUrlAppend value=$cUrlAppend|cat:'#'|cat:$cAnchor}
{/if}

<div class="block">
    {if $oPagination->nPageCount > 1}
        <div class="form-group">
            <label>
                Eintr&auml;ge {$oPagination->nFirstPageItem + 1}
                - {$oPagination->nFirstPageItem + $oPagination->nPageItemCount}
                von {$oPagination->nItemCount}
            </label>

            <ul class="pagination btn-group">
                <li>
                    <a {if $oPagination->nPrevPage != $oPagination->nPage}href="?{$oPagination->cId}_nPage={$oPagination->nPrevPage}&{$cUrlAppend}"{/if}>&laquo;</a>
                </li>
                {if $oPagination->nLeftRangePage > 0}
                    <li>
                        <a href="?{$oPagination->cId}_nPage=0&{$cUrlAppend}">1</a>
                    </li>
                {/if}
                {if $oPagination->nLeftRangePage > 1}
                    <li>
                        <a>&hellip;</a>
                    </li>
                {/if}
                {for $i=$oPagination->nLeftRangePage to $oPagination->nRightRangePage}
                    <li class="{if $oPagination->nPage == $i}active{/if}">
                        <a href="?{$oPagination->cId}_nPage={$i}&{$cUrlAppend}">{$i+1}</a>
                    </li>
                {/for}
                {if $oPagination->nRightRangePage < $oPagination->nPageCount - 2}
                    <li>
                        <a>&hellip;</a>
                    </li>
                {/if}
                {if $oPagination->nRightRangePage < $oPagination->nPageCount - 1}
                    <li>
                        <a href="?{$oPagination->cId}_nPage={$oPagination->nPageCount - 1}&{$cUrlAppend}">{$oPagination->nPageCount}</a>
                    </li>
                {/if}
                <li>
                    <a {if $oPagination->nNextPage != $oPagination->nPage}href="?{$oPagination->cId}_nPage={$oPagination->nNextPage}&{$cUrlAppend}"{/if}>&raquo;</a>
                </li>
            </ul>
        </div>
    {/if}

    <form action="{if isset($cAnchor)}#{$cAnchor}{/if}" method="get" class="form-inline">
        {foreach $cParam_arr as $cParamName => $cParamValue}
            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
        {/foreach}
        <div class="form-group">
            <label for="{$oPagination->cId}_nItemsPerPage">
                Eintr&auml;ge/Seite
            </label>
            <select class="form-control" name="{$oPagination->cId}_nItemsPerPage" id="{$oPagination->cId}_nItemsPerPage">
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
                <label for="{$oPagination->cId}_nSortBy">
                    Sortierung
                </label>
                <select class="form-control" name="{$oPagination->cId}_nSortBy" id="{$oPagination->cId}_nSortBy">
                    {foreach $oPagination->cSortByOption_arr as $i => $cSortByOption}
                        <option value="{$i}"{if $i === (int)$oPagination->nSortBy} selected="selected"{/if}>
                            {$cSortByOption[1]}
                        </option>
                    {/foreach}
                </select>
                <select class="form-control" name="{$oPagination->cId}_nSortDir" id="{$oPagination->cId}_nSortDir">
                    <option value="0"{if $oPagination->nSortDir == 0} selected{/if}>aufsteigend</option>
                    <option value="1"{if $oPagination->nSortDir == 1} selected{/if}>absteigend</option>
                </select>
            </div>
        {/if}
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-refresh"></i>
        </button>
        {if $oPagination->nPageCount == 1}
            <div class="form-group">
                <label>Eintr&auml;ge gesamt: {$oPagination->nItemCount}</label>
            </div>
        {/if}
    </form>
</div>