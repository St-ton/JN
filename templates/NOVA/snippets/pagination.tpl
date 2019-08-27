{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-pagination'}
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

    {row class="pagination-wrapper clearfix mb-3 align-items-end"}
        {col cols="auto" class="ml-auto"}
            {row class="align-items-center"}
                {if $oPagination->getPageCount() > 1}
                    {if in_array('label', $parts) || in_array('pagi', $parts)}
                        {if in_array('label', $parts)}
                            {col cols="auto" class="ml-auto border-right"}
                                {lang key='paginationEntryPagination' printf={$oPagination->getFirstPageItem() + 1}|cat:':::'|cat:{$oPagination->getFirstPageItem() + $oPagination->getPageItemCount()}|cat:':::'|cat:{$oPagination->getItemCount()}}
                            {/col}
                        {/if}
                        {col cols="auto {if $showFilter === true && (in_array('count', $parts) || in_array('sort', $parts))}border-right{/if}"}
                            {buttongroup class="pagination-group"}
                                {if in_array('pagi', $parts)}
                                    {if $oPagination->getPage() > 0}
                                        {link class="btn btn-link" href="{$cThisUrl}?{$oPagination->getId()}_nPage={$oPagination->getPrevPage()}{$cUrlAppend}{$cAnchor}"}
                                            <i class="far fa-long-arrow-left"></i>
                                        {/link}
                                    {/if}
                                    {if $oPagination->getLeftRangePage() > 0}
                                        {link class="page-link"  href="{$cThisUrl}?{$oPagination->getId()}_nPage=0{$cUrlAppend}{$cAnchor}"}
                                            1
                                        {/link}
                                    {/if}
                                    {if $oPagination->getLeftRangePage() > 1}
                                        {button variant="link" href="#" disabled=true}&hellip;{/button}
                                    {/if}
                                    {for $i=$oPagination->getLeftRangePage() to $oPagination->getRightRangePage()}
                                        {link class="page-link {if $oPagination->getPage() === $i}active{elseif $i > 0 && $i < $oPagination->getPageCount() - 1}d-none d-sm-block{/if}" href="{$cThisUrl}?{$oPagination->getId()}_nPage={$i}{$cUrlAppend}{$cAnchor}"}
                                            {$i+1}
                                        {/link}
                                    {/for}
                                    {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 2}
                                        {button variant="link" href="#" disabled=true}&hellip;{/button}
                                    {/if}
                                    {if $oPagination->getRightRangePage() < $oPagination->getPageCount() - 1}
                                        {link class="page-link" href="{$cThisUrl}?{$oPagination->getId()}_nPage={$oPagination->getPageCount() - 1}{$cUrlAppend}{$cAnchor}"}
                                            {$oPagination->getPageCount()}
                                        {/link}
                                    {/if}
                                    {if $oPagination->getPage() < $oPagination->getPageCount() - 1}
                                        {link class="btn btn-link" href="{$cThisUrl}?{$oPagination->getId()}_nPage={$oPagination->getNextPage()}{$cUrlAppend}{$cAnchor}"}
                                            <i class="far fa-long-arrow-left"></i>
                                        {/link}
                                    {/if}
                                {/if}
                            {/buttongroup}
                        {/col}
                    {/if}
                {else}
                    {col cols="auto" class="ml-auto {if $showFilter === true && (in_array('count', $parts) || in_array('sort', $parts))}border-right{/if}"}
                        {lang key='paginationTotalEntries'} {$oPagination->getItemCount()}
                    {/col}
                {/if}

                {if $showFilter === true && (in_array('count', $parts) || in_array('sort', $parts))}
                    {block name='snippets-pagination-form'}
                        {col cols="auto" class="ml-auto pl-0"}
                            {form action="{$cThisUrl}{$cAnchor}" method="get" class="form-inline float-right"}
                                {block name='snippets-pagination-form-content'}
                                    {foreach $cParam_arr as $cParamName => $cParamValue}
                                        {input type="hidden" name=$cParamName value=$cParamValue}
                                    {/foreach}
                                    {if in_array('count', $parts)}
                                        {formgroup class="items-per-page-group ml-3"}
                                            {select name="{$oPagination->getId()}_nItemsPerPage"
                                                    id="{$oPagination->getId()}_nItemsPerPage"
                                                    title="{lang key='paginationEntriesPerPage'}"}
                                                <option disabled>{lang key='paginationEntriesPerPage'}</option>
                                                {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
                                                    <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected="selected"{/if}>
                                                        {$nItemsPerPageOption}
                                                    </option>
                                                {/foreach}
                                                <option value="-1"{if $oPagination->getItemsPerPage() == -1} selected="selected"{/if}>
                                                    {lang key='showAll'}
                                                </option>
                                            {/select}
                                        {/formgroup}
                                    {/if}
                                    {if $oPagination->getSortByOptions()|@count > 0 && in_array('sort', $parts)}
                                        {formgroup class="filter-group ml-3"}
                                            {select name="{$oPagination->getId()}_nSortByDir"
                                                    id="{$oPagination->getId()}_nSortByDir"
                                                    title="{lang key='sorting' section='productOverview'}"}
                                                <option disabled>{lang key='sorting' section='productOverview'}</option>
                                                {foreach $oPagination->getSortByOptions() as $i => $cSortByOption}
                                                    <option value="{$i * 2}"
                                                            {if $i * 2 == $oPagination->getSortByDir()} selected="selected"{/if}>
                                                        {$cSortByOption[1]} {lang key='asc'}
                                                    </option>
                                                    <option value="{$i * 2 + 1}"
                                                            {if $i * 2 + 1 == $oPagination->getSortByDir()} selected="selected"{/if}>
                                                        {$cSortByOption[1]} {lang key='desc'}
                                                    </option>
                                                {/foreach}
                                            {/select}
                                        {/formgroup}
                                    {/if}
                                {/block}
                            {/form}
                        {/col}
                    {/block}
                {/if}
            {/row}
        {/col}
    {/row}
    {block name='snippets-pagination-script'}
        {literal}
            <script type="text/javascript">
                var deferredTasks = window.deferredTasks || [];
                deferredTasks.push(["ready",function (){
                    $('.pagination-wrapper select').on('change', function () {
                        this.form.submit();
                    });
                }]);
            </script>
        {/literal}
    {/block}
{/block}
