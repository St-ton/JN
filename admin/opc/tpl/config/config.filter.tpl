<div id="filter-{$propname}" class="product-filter-config">
    <div class="active-filters">
        <label>{__('activeFilters')}</label>
        <div class="filters-enabled"></div>
    </div>

    <div class="available-filters">
        {*<label>{__('availableFilters')}</label>*}
        <div class="filters-available"></div>
    </div>

    <input type="hidden" id="config-{$propname}" name="{$propname}" value="{$propval|json_encode|htmlentities}"
           data-prop-type="json">

    <script>
        $(() => {
            let filtersAvailable = $('#filter-{$propname} .filters-available');
            let filtersEnabled   = $('#filter-{$propname} .filters-enabled');
            let configInput      = $('#config-{$propname}');
            let cntr             = Math.floor(Math.random() * 0xffFFffFF);

            enableFilters(JSON.parse(configInput.val()));
            opc.setConfigSaveCallback(saveFilterProperties);

            function enableFilters(filters)
            {
                filters.forEach(enableFilter.bind(this, false));
                updateFiltersAvailable();
            }

            function enableFilter(doPostUpdate, filter)
            {
                $('<div><input type="checkbox" id="filter-check-' + cntr + '">' +
                        '<label for="filter-check-' + cntr + '">' +
                            filter.name +
                        '</label></div>')
                    .data('filter', filter)
                    .click(disableFilter)
                    .appendTo(filtersEnabled);

                // $('<button class="btn btn-xs btn-danger" type="button">')
                //     .data('filter', filter)
                //     .html(filter.name)
                //     .click(disableFilter)
                //     .appendTo(filtersEnabled);

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
                filtersAvailable.html('<div><i class="fa fa-spinner fa-pulse fa-2x"></i></div>');
            }

            function loadFiltersAvailable()
            {
                opc.io.getFilterOptions(getFiltersEnabled()).then(renderFiltersAvailable);
            }

            function getFiltersEnabled()
            {
                return filtersEnabled.find('button').map(getElementFilterData).toArray();
            }

            function getElementFilterData()
            {
                return $(this).data('filter');
            }

            function renderFiltersAvailable(filters)
            {
                clearFiltersAvailable();

                if(filters.length === 0) {
                    filtersAvailable.html('{__('noMoreFilters')}');
                } else {
                    filters.forEach(function(filter) {
                        if (filter.options.length > 0) {
                            let filterSubCat = $('<div>')
                                .append('<label>' + filter.name + '</label>')
                                .appendTo(filtersAvailable);

                            filter.options.forEach(function(option) {
                                addFilterAvailableButton(option, filterSubCat);
                            });
                        }
                    });
                }
            }

            function clearFiltersAvailable()
            {
                filtersAvailable.empty();
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
        });
    </script>
</div>