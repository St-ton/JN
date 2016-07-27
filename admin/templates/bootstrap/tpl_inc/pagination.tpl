{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

{assign var=cUrlAppend value=$cParam_arr|http_build_query}

{if isset($cAnchor)}
    {assign var=cUrlAppend value=$cUrlAppend|cat:'#'|cat:$cAnchor}
{/if}

<div class="block">
    {if $oPagination->getPageCount() > 1}
        <div class="form-group">
            <label>
                Eintr&auml;ge {$oPagination->getFirstPageItem() + 1}
                - {$oPagination->getFirstPageItem() + $oPagination->getPageItemCount()}
                von {$oPagination->getItemCount()}
            </label>

            <ul class="pagination btn-group">
                <li>
                    <a {if $oPagination->getPrevPage() != $oPagination->getPage()}href="?{$oPagination->getId()}_nPage={$oPagination->getPrevPage()}&{$cUrlAppend}"{/if}>&laquo;</a>
                </li>
                {if $oPagination->getLeftRangePage() > 0}
                    <li>
                        <a href="?{$oPagination->getId()}_nPage=0&{$cUrlAppend}">1</a>
                    </li>
                {/if}
                {if $oPagination->getLeftRangePage() > 1}
                    <li>
                        <a>&hellip;</a>
                    </li>
                {/if}
                {for $i=$oPagination->getLeftRangePage() to $oPagination->getRightRangePage()}
                    <li{if $oPagination->getPage() == $i} class="active"{/if}>
                        <a href="?{$oPagination->getId()}_nPage={$i}&{$cUrlAppend}">{$i+1}</a>
                    </li>
                {/for}
                {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 2}
                    <li>
                        <a>&hellip;</a>
                    </li>
                {/if}
                {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 1}
                    <li>
                        <a href="?{$oPagination->getId()}_nPage={$oPagination->getPageCount() - 1}&{$cUrlAppend}">{$oPagination->getPageCount()}</a>
                    </li>
                {/if}
                <li>
                    <a {if $oPagination->getNextPage() != $oPagination->getPage()}href="?{$oPagination->getId()}_nPage={$oPagination->getNextPage()}&{$cUrlAppend}"{/if}>&raquo;</a>
                </li>
            </ul>
        </div>
    {/if}

    <form action="{if isset($cAnchor)}#{$cAnchor}{/if}" method="get" class="form-inline">
        {foreach $cParam_arr as $cParamName => $cParamValue}
            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
        {/foreach}
        <div class="form-group">
            <label for="{$oPagination->getId()}_nItemsPerPage">
                Eintr&auml;ge/Seite
            </label>
            <select class="form-control" name="{$oPagination->getId()}_nItemsPerPage" id="{$oPagination->getId()}_nItemsPerPage">
                {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
                    <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected="selected"{/if}>
                        {$nItemsPerPageOption}
                    </option>
                {/foreach}
                <option value="-1"{if $oPagination->getItemsPerPage() == -1} selected="selected"{/if}>
                    alle
                </option>
            </select>
        </div>
        {if $oPagination->getSortByOptions()|@count > 0}
            <div class="form-group">
                <label for="{$oPagination->getId()}_nSortBy">
                    Sortierung
                </label>
                <select class="form-control" name="{$oPagination->getId()}_nSortBy" id="{$oPagination->getId()}_nSortBy">
                    {foreach $oPagination->getSortByOptions() as $i => $cSortByOption}
                        <option value="{$i}"{if $i == $oPagination->getSortBy()} selected="selected"{/if}>
                            {$cSortByOption[1]}
                        </option>
                    {/foreach}
                </select>
                <select class="form-control" name="{$oPagination->getId()}_nSortDir" id="{$oPagination->getId()}_nSortDir">
                    <option value="0"{if $oPagination->getSortDir() == 0} selected{/if}>aufsteigend</option>
                    <option value="1"{if $oPagination->getSortDir() == 1} selected{/if}>absteigend</option>
                </select>
            </div>
        {/if}
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-refresh"></i>
        </button>
        {if $oPagination->getPageCount() == 1}
            <div class="form-group">
                <label>Eintr&auml;ge gesamt: {$oPagination->getItemCount()}</label>
            </div>
        {/if}
    </form>
</div>