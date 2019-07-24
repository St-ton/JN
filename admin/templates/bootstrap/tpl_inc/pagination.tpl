{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

{assign var=cUrlAppend value=$cParam_arr|http_build_query}

{if isset($cAnchor)}
    {assign var=cUrlAppend value=$cUrlAppend|cat:'#'|cat:$cAnchor}
{/if}

{assign var=bItemsAvailable value=$pagination->getItemCount() > 0}
{assign var=bMultiplePages value=$pagination->getPageCount() > 1}
{assign var=bSortByOptions value=$pagination->getSortByOptions()|@count > 0}

{function pageButtons}
    <label>
        {if $bMultiplePages}
            {__('entries')} {$pagination->getFirstPageItem() + 1}
            - {$pagination->getFirstPageItem() + $pagination->getPageItemCount()}
            {__('of')} {$pagination->getItemCount()}
        {else}
            {__('allEntries')}
        {/if}
    </label>
    {if $bMultiplePages}
        <ul class="pagination">
            <li>
                <a {if $pagination->getPrevPage() != $pagination->getPage()}href="?{$pagination->getId()}_nPage={$pagination->getPrevPage()}&{$cUrlAppend}"{/if}>&laquo;</a>
            </li>
            {if $pagination->getLeftRangePage() > 0}
                <li>
                    <a href="?{$pagination->getId()}_nPage=0&{$cUrlAppend}">1</a>
                </li>
            {/if}
            {if $pagination->getLeftRangePage() > 1}
                <li>
                    <a>&hellip;</a>
                </li>
            {/if}
            {for $i=$pagination->getLeftRangePage() to $pagination->getRightRangePage()}
                <li{if $pagination->getPage() == $i} class="active"{/if}>
                    <a href="?{$pagination->getId()}_nPage={$i}&{$cUrlAppend}">{$i+1}</a>
                </li>
            {/for}
            {if $pagination->getRightRangePage() < $pagination->getPageCount() - 2}
                <li>
                    <a>&hellip;</a>
                </li>
            {/if}
            {if $pagination->getRightRangePage() < $pagination->getPageCount() - 1}
                <li>
                    <a href="?{$pagination->getId()}_nPage={$pagination->getPageCount() - 1}&{$cUrlAppend}">{$pagination->getPageCount()}</a>
                </li>
            {/if}
            <li>
                <a {if $pagination->getNextPage() != $pagination->getPage()}href="?{$pagination->getId()}_nPage={$pagination->getNextPage()}&{$cUrlAppend}"{/if}>&raquo;</a>
            </li>
        </ul>
    {else}
        <ul class="pagination">
            <li>
                <a>{$pagination->getItemCount()}</a>
            </li>
        </ul>
    {/if}
{/function}

{function itemsPerPageOptions}
    <label for="{$pagination->getId()}_nItemsPerPage">{__('entriesPerPage')}</label>
    <select class="form-control" name="{$pagination->getId()}_nItemsPerPage" id="{$pagination->getId()}_nItemsPerPage"
            onchange="this.form.submit()">
        {foreach $pagination->getItemsPerPageOptions() as $nItemsPerPageOption}
            <option value="{$nItemsPerPageOption}"{if $pagination->getItemsPerPage() == $nItemsPerPageOption} selected="selected"{/if}>
                {if $nItemsPerPageOption === -1}
                    {__('all')}
                {else}
                    {$nItemsPerPageOption}
                {/if}
            </option>
        {/foreach}
    </select>
{/function}

{function sortByDirOptions}
    <label for="{$pagination->getId()}_nSortByDir">{__('sorting')}</label>
    <select class="form-control" name="{$pagination->getId()}_nSortByDir" id="{$pagination->getId()}_nSortByDir"
            onchange="this.form.submit()">
        {foreach $pagination->getSortByOptions() as $i => $cSortByOption}
            <option value="{$i * 2}"
                    {if $i * 2 == $pagination->getSortByDir()} selected="selected"{/if}>
                {$cSortByOption[1]} {__('ascending')}
            </option>
            <option value="{$i * 2 + 1}"
                    {if $i * 2 + 1 == $pagination->getSortByDir()} selected="selected"{/if}>
                {$cSortByOption[1]} {__('descending')}
            </option>
        {/foreach}
    </select>
{/function}

{if $bItemsAvailable}
    <div class="toolbar well well-sm">
        <div class="container-fluid toolbar-container">
            <div class="row toolbar-row">
                <div class="col-md-{if $bSortByOptions}8{else}10{/if} toolbar-col">
                    {pageButtons}
                </div>
                <div class="col-md-{if $bSortByOptions}4{else}2{/if} toolbar-col">
                    <form action="{if isset($cAnchor)}#{$cAnchor}{/if}" method="get" name="{$pagination->getId()}" id="{$pagination->getId()}">
                        {foreach $cParam_arr as $cParamName => $cParamValue}
                            <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
                        {/foreach}
                        <div class="row toolbar-row">
                            <div class="col-md-{if $bSortByOptions}4{else}12{/if} toolbar-col">
                                {itemsPerPageOptions}
                            </div>
                            {if $bSortByOptions}
                                <div class="col-md-8 toolbar-col">
                                    {sortByDirOptions}
                                </div>
                            {/if}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/if}
