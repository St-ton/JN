<h2>Verfügbare Filter</h2>
<div id="filters-available"></div>

<h2>Aktive Filter</h2>
<div id="filters-enabled"></div>

<input type="hidden" name="{$propname}" value="{$prop|json_encode|htmlentities}" data-prop-type="filter">

<script>
    var $filtersAvailable = $('#filters-available');
    var $filtersEnabled   = $('#filters-enabled');

    enableFilters(JSON.parse($('[name="{$propname}"]').val()));
    opc.setConfigSaveCallback(saveFilterProperties);
    //updateFiltersAvailable();

    function enableFilters(filters)
    {
        filters.forEach(enableFilter.bind(this, false));
        updateFiltersAvailable();
    }

    function enableFilter(doPostUpdate, filter)
    {
        $('<button class="btn btn-xs btn-danger" type="button">')
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
        opc.io.getFilterOptions(getFiltersEnabled(), renderFiltersAvailable);
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

//            if(filters.length === 0) {
//                $filtersAvailable.html('No more filters available');
//            } else {
//                filters.forEach(addFilterAvailableButton);
//            }

        filters.forEach(function(filter) {
            log(filter);
            var $filterSubCat = $('<div>').append('<h3>' + filter.name + '</h3>').appendTo($filtersAvailable);

            filter.options.forEach(function(option) {
                addFilterAvailableButton(option, $filterSubCat);
            });
        });
    }

    function clearFiltersAvailable()
    {
        $filtersAvailable.empty();
    }

    function addFilterAvailableButton(filter, target)
    {
        $('<button class="btn btn-xs btn-primary" type="button">')
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