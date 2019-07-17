<h2>{__('availableFilters')}</h2>
<div id="filters-available"></div>

<h2>{__('activeFilters')}</h2>
<div id="filters-enabled"></div>

<input type="hidden" id="config-{$propname}" name="{$propname}" value="{$propval|json_encode|htmlentities}"
       data-prop-type="json">

<script>
    var $filtersAvailable = $('#filters-available');
    var $filtersEnabled   = $('#filters-enabled');

    enableFilters(JSON.parse($('[name="{$propname}"]').val()));
    opc.setConfigSaveCallback(saveFilterProperties);

    function enableFilters(filters)
    {
        filters.forEach(enableFilter.bind(this, false));
        updateFiltersAvailable();
    }

    function enableFilter(doPostUpdate, filter)
    {
        $('<button class="btn btn-sm btn-danger" type="button">')
            .data('filter', filter)
            .html(filter.name)
            .click(disableFilter)
            .appendTo($filtersEnabled);

        if(doPostUpdate) {
            updateFiltersAvailable();
        }
    }

    function disableFilter()
    {
        $(this).remove();
        updateFiltersAvailable();
    }

    function updateFiltersAvailable()
    {
        showFilterLoaderSpinner();
        loadFiltersAvailable();
    }

    function showFilterLoaderSpinner()
    {
        $filtersAvailable.html('<div><i class="fa fa-spinner fa-pulse fa-2x"></i></div>');
    }

    function loadFiltersAvailable()
    {
        opc.io.getFilterOptions(getFiltersEnabled()).then(renderFiltersAvailable);
    }

    function getFiltersEnabled()
    {
        return $filtersEnabled.find('button').map(getElementFilterData).toArray();
    }

    function getElementFilterData()
    {
        return $(this).data('filter');
    }

    function renderFiltersAvailable(filters)
    {
        clearFiltersAvailable();

        if(filters.length === 0) {
            $filtersAvailable.html('{__('noMoreFilters')}');
        } else {
            filters.forEach(function(filter) {
                if (filter.options.length > 0) {
                    var $filterSubCat = $('<div>').append('<h3>' + filter.name + '</h3>').appendTo($filtersAvailable);

                    filter.options.forEach(function(option) {
                        addFilterAvailableButton(option, $filterSubCat);
                    });
                }
            });
        }
    }

    function clearFiltersAvailable()
    {
        $filtersAvailable.empty();
    }

    function addFilterAvailableButton(filter, target)
    {
        $('<button class="btn btn-sm btn-primary" type="button">')
            .data('filter', filter)
            .html(filter.name + ' (' + filter.count + ')')
            .click(enableFilter.bind(this, true, filter))
            .appendTo(target);
    }

    function saveFilterProperties()
    {
        $('[name="{$propname}"]').val(JSON.stringify(getFiltersEnabled()));
    }
</script>
