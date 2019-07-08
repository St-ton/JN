{foreach $filters as $filter}
    <label>{$filter.name}</label>
    <div class="filters-section">
        <div class="filters-section-inner">
            {foreach $filter.options as $option}
                <button type="button" class="filter-option" data-filter="{$option|json_encode|htmlentities}">
                    <i class="far fa-square"></i> {$option.name} ({$option.count})
                </button>
            {/foreach}
        </div>
    </div>
{/foreach}