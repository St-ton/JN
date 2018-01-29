<div id="filter" class="tab-pane fade" role="tabpanel">

    <h2>Verfügbare Filter</h2>
    <div id="filters-available"></div>

    <h2>Aktive Filter</h2>
    <div id="filters-enabled"></div>

    <script>
        var $filtersAvailable = $('#filters-available');
        var $filtersEnabled   = $('#filters-enabled');

        editor.setPropertiesCallback(saveFilterProperties);
        updateFiltersAvailable();

        function saveFilterProperties(props)
        {
            props.filtersEnabled = getFiltersEnabled().map(
                function (filter) {
                    return {
                        className: filter.className,
                        value: filter.value,
                    }
                }
            );
        }

        function getFiltersEnabled()
        {
            return $filtersEnabled.find('button').map(getElementFilterData).toArray();
        }

        function getElementFilterData()
        {
            return $(this).data('filter');
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
            ioCall('getProductFilterOptions', [getFiltersEnabled()], renderFiltersAvailable);
        }

        function renderFiltersAvailable(filters)
        {
            clearFiltersAvailable();

            if(filters.length === 0) {
                $filtersAvailable.html('No more filters available');
            } else {
                filters.forEach(addFilterAvailableButton);
            }
        }

        function clearFiltersAvailable()
        {
            $filtersAvailable.empty();
        }

        function addFilterAvailableButton(filter)
        {
            $('<button class="btn btn-xs btn-primary" type="button">')
                .data('filter', filter)
                .html(filter.name + ' (' + filter.count + ')')
                .click(enableFilter.bind(this, filter))
                .appendTo($filtersAvailable);
        }

        function enableFilter(filter)
        {
            $('<button class="btn btn-xs btn-primary" type="button">')
                .data('filter', filter)
                .html(filter.name)
                .click(disableFilter)
                .appendTo($filtersEnabled);

            updateFiltersAvailable();
        }

        function disableFilter()
        {
            $(this).remove();
            updateFiltersAvailable();
        }
    </script>
</div>