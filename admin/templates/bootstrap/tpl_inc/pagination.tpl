{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

{assign var=cUrlAppend value=$cParam_arr|http_build_query}

{if isset($cAnchor)}
    {assign var=cUrlAppend value=$cUrlAppend|cat:'#'|cat:$cAnchor}
{/if}

{assign var=bItemsAvailable value=$oPagination->getItemCount() > 0}
{assign var=bMultiplePages value=$oPagination->getPageCount() > 1}
{assign var=bSortByOptions value=$oPagination->getSortByOptions()|@count > 0}

{if $bItemsAvailable}
    <div class="toolbar well well-sm">
        <div class="container-fluid toolbar-container">
            <div class="toolbar-row">
                <div class="col-md-{if $bSortByOptions}6{else}10{/if} toolbar-col">
                    <label>
                        {if $bMultiplePages}
                            Eintr&auml;ge {$oPagination->getFirstPageItem() + 1} - {$oPagination->getFirstPageItem() + $oPagination->getPageItemCount()} von {$oPagination->getItemCount()}
                        {else}
                            Eintr&auml;ge gesamt:
                        {/if}
                    </label>
                    <div class="toolbar-row">
                        <div class="col-md-12 toolbar-col">
                            {if $bMultiplePages}
                                <ul class="pagination">
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
                            {else}
                                <ul class="pagination">
                                    <li>
                                        <a>{$oPagination->getItemCount()}</a>
                                    </li>
                                </ul>
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="col-md-{if $bSortByOptions}6{else}2{/if} toolbar-col">
                    <form action="{if isset($cAnchor)}#{$cAnchor}{/if}" method="get" name="{$oPagination->getId()}" id="{$oPagination->getId()}">
                        {foreach $cParam_arr as $cParamName => $cParamValue}
                            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
                        {/foreach}
                        <div class="toolbar-row">
                            <div class="col-md-{if $bSortByOptions}3{else}8{/if} toolbar-col">
                                <label>Eintr&auml;ge/Seite</label>
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
                            {if $bSortByOptions}
                                <div class="col-md-5 toolbar-col">
                                    <label>Sortierung</label>
                                    <select class="form-control" name="{$oPagination->getId()}_nSortBy" id="{$oPagination->getId()}_nSortBy">
                                        {foreach $oPagination->getSortByOptions() as $i => $cSortByOption}
                                            <option value="{$i}"{if $i == $oPagination->getSortBy()} selected="selected"{/if}>
                                                {$cSortByOption[1]}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-md-3 toolbar-col">
                                    <label>&nbsp;</label>
                                    <select class="form-control" name="{$oPagination->getId()}_nSortDir" id="{$oPagination->getId()}_nSortDir">
                                        <option value="0"{if $oPagination->getSortDir() == 0} selected{/if}>aufsteigend</option>
                                        <option value="1"{if $oPagination->getSortDir() == 1} selected{/if}>absteigend</option>
                                    </select>
                                </div>
                            {/if}
                            <div class="col-md-{if $bSortByOptions}1{else}4{/if} toolbar-col tright">
                                <label>&nbsp;</label>
                                <div class="toolbar-row">
                                    <div class="col-md-12 toolbar-col">
                                        <button type="submit" class="btn btn-primary" title="{#refresh#}">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/if}

<script>
    {literal}
        function pagiResort (pagiId, nSortBy, nSortDir)
        {
            $('#' + pagiId + '_nSortBy').val(nSortBy);
            $('#' + pagiId + '_nSortDir').val(nSortDir);
            $('form#' + pagiId).submit();
        }
    {/literal}
</script>