{assign var=cParam_arr value=$cParam_arr|default:[]}
{assign var=cUrlAppend value=$cParam_arr|http_build_query}
{* parts list to display: label, pagination, items-per-page-options, sort-options *}
{assign var=parts value=$parts|default:['label','pagi','count','sort']}

{if !empty($cAnchor)}
    {assign var=cAnchor value='#'|cat:$cAnchor}
{else}
    {assign var=cAnchor value=''}
{/if}
{assign var=showFilter value=$showFilter|default:true}

{if !empty($cUrlAppend)}
    {assign var=cUrlAppend value='&'|cat:$cUrlAppend}
{/if}

{assign var=cThisUrl value=$cThisUrl|default:''}

{get_static_route id=$cThisUrl assign=cThisUrl}

<div class=pagination-wrapper>
    {if $oPagination->getPageCount() > 1}
        {if in_array('label', $parts) || in_array('pagi', $parts)}
            <div class="form-group pagination-group">
                {if in_array('label', $parts)}
                    <span class="text-muted">
                        {lang key='paginationEntryPagination' section='global' printf={$oPagination->getFirstPageItem() + 1}|cat:':::'|cat:{$oPagination->getFirstPageItem() + $oPagination->getPageItemCount()}|cat:':::'|cat:{$oPagination->getItemCount()}}
                    </span>
                {/if}

                {if in_array('pagi', $parts)}
                    <ul class="pagination btn-group">
                        <li>
                            <a {if $oPagination->getPrevPage() != $oPagination->getPage()}
                                href="{$cThisUrl}?{$oPagination->getId()}_nPage={$oPagination->getPrevPage()}{$cUrlAppend}{$cAnchor}"
                            {/if}>&laquo;</a>
                        </li>
                        {if $oPagination->getLeftRangePage() > 0}
                            <li>
                                <a href="{$cThisUrl}?{$oPagination->getId()}_nPage=0{$cUrlAppend}{$cAnchor}">1</a>
                            </li>
                        {/if}
                        {if $oPagination->getLeftRangePage() > 1}
                            <li>
                                <a>&hellip;</a>
                            </li>
                        {/if}
                        {for $i=$oPagination->getLeftRangePage() to $oPagination->getRightRangePage()}
                            <li class="{if $oPagination->getPage() === $i}active{elseif $i > 0 && $i < $oPagination->getPageCount() - 1}hidden-xs{/if}">
                                <a href="{$cThisUrl}?{$oPagination->getId()}_nPage={$i}{$cUrlAppend}{$cAnchor}">{$i+1}</a>
                            </li>
                        {/for}
                        {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 2}
                            <li>
                                <a>&hellip;</a>
                            </li>
                        {/if}
                        {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 1}
                            <li>
                                <a href="{$cThisUrl}?{$oPagination->getId()}_nPage={$oPagination->getPageCount() - 1}{$cUrlAppend}{$cAnchor}">{$oPagination->getPageCount()}</a>
                            </li>
                        {/if}
                        <li>
                            <a {if $oPagination->getNextPage() != $oPagination->getPage()}
                                href="{$cThisUrl}?{$oPagination->getId()}_nPage={$oPagination->getNextPage()}{$cUrlAppend}{$cAnchor}"
                            {/if}>&raquo;</a>
                        </li>
                    </ul>
                {/if}
            </div>
        {/if}
    {/if}

    {if $showFilter === true && (in_array('count', $parts) || in_array('sort', $parts))}
        <form action="{$cThisUrl}{$cAnchor}" method="get" class="form-inline">
            {foreach $cParam_arr as $cParamName => $cParamValue}
                <input type="hidden" name="{$cParamName}" value="{$cParamValue}" />
            {/foreach}
            {if in_array('count', $parts)}
                <div class="form-group items-per-page-group">
                    <label for="{$oPagination->getId()}_nItemsPerPage">
                        {lang key='paginationEntriesPerPage' section='global'}
                    </label>
                    <select class="form-control" name="{$oPagination->getId()}_nItemsPerPage" id="{$oPagination->getId()}_nItemsPerPage">
                        {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
                            <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected="selected"{/if}>
                                {$nItemsPerPageOption}
                            </option>
                        {/foreach}
                        <option value="-1"{if $oPagination->getItemsPerPage() == -1} selected="selected"{/if}>
                            {lang key='showAll' section='global'}
                        </option>
                    </select>
                </div>
            {/if}
            {if $oPagination->getSortByOptions()|@count > 0 && in_array('sort', $parts)}
                <div class="form-group filter-group">
                    <label for="{$oPagination->getId()}_nSortBy">{lang key='sorting' section='productOverview'}</label>
                    <select class="form-control" name="{$oPagination->getId()}_nSortBy" id="{$oPagination->getId()}_nSortBy">
                        {foreach $oPagination->getSortByOptions() as $i => $cSortByOption}
                            <option value="{$i}"{if $i == $oPagination->getSortBy()} selected="selected"{/if}>
                                {$cSortByOption[1]}
                            </option>
                        {/foreach}
                    </select>
                    <select class="form-control" name="{$oPagination->getId()}_nSortDir" id="{$oPagination->getId()}_nSortDir">
                        <option value="0"{if $oPagination->getSortDir() == 0} selected{/if}>{lang key='asc' section='global'}</option>
                        <option value="1"{if $oPagination->getSortDir() == 1} selected{/if}>{lang key='desc' section='global'}</option>
                    </select>
                </div>
            {/if}
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-refresh"></i>
            </button>
            {if $oPagination->getPageCount() == 1}
                <div class="form-group">
                    <label>{lang key='paginationTotalEntries' section='global'} {$oPagination->getItemCount()}</label>
                </div>
            {/if}
        </form>
    {/if}
</div>