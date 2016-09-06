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

{function sortControls}
    {if $oPagination->getSortBy() !== $nSortBy}
        <a href="#" onclick="pagiResort('{$oPagination->getId()}', {$nSortBy}, 0);return false;"><i class="fa fa-unsorted"></i></a>
    {elseif $oPagination->getSortDirSpecifier() === 'DESC'}
        <a href="#" onclick="pagiResort('{$oPagination->getId()}', {$nSortBy}, 0);return false;"><i class="fa fa-sort-desc"></i></a>
    {elseif $oPagination->getSortDirSpecifier() === 'ASC'}
        <a href="#" onclick="pagiResort('{$oPagination->getId()}', {$nSortBy}, 1);return false;"><i class="fa fa-sort-asc"></i></a>
    {/if}
{/function}