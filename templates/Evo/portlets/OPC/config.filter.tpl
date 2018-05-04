<h2>Verfügbare Filter</h2>
<div id="filters-available"></div>

<h2>Aktive Filter</h2>
<div id="filters-enabled"></div>

<input type="hidden" name="filter" value="{$instance->getProperty('filter')|json_encode}">

<script>
    var $filtersAvailable = $('#filters-available');
    var $filtersEnabled   = $('#filters-enabled');

    enableFilters($(''));
    //opc.setPropertiesCallback(saveFilterProperties);
    updateFiltersAvailable();

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

        for(filterTerm in filters) {
            var filterSubset = filters[filterTerm];

            console.log(filterTerm, filterSubset);

            if(filterSubset.length > 0) {
                var $filterSubCat = $('<div>').append('<h3>' + filterTerm + '</h3>').appendTo($filtersAvailable);

                for(var i=0; i<filterSubset.length; i++) {
                    var filter = filterSubset[i];

                    addFilterAvailableButton(filter, $filterSubCat);
                }
            }
        }
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

    function saveFilterProperties(props)
    {
        props.filters = getFiltersEnabled().map(
            function (filter) {
                return {
                    className: filter.className,
                    name: filter.name,
                    value: filter.value,
                }
            }
        );
    }
</script>