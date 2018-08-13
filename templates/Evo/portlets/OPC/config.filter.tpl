<h2>Aktive Filter</h2>
<div id="filters-enabled"></div>

<h2>Verfügbare Filter</h2>
<div id="filters-available"></div>

<input type="hidden" name="{$propname}" value="{$prop|json_encode|htmlentities}" data-prop-type="filter">

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

        if(filters.length === 0) {
            $filtersAvailable.html('No more filters available');
        } else {
            filters.forEach(function(filter, index) {
                if (filter.options.length > 0) {
                    // var $filterSubCat = $('<div>').append('<h3>' + filter.name + '</h3>').appendTo($filtersAvailable);
                    var fil_name = filter.name,
                        panel_open = '';

                    if (fil_name.length == 0) {
                        fil_name = '<i class="fa fa-chevron-right"></i>';
                    }
                    if (index == 0) {
                        panel_open = ' in';
                    } else {
                        panel_open = '';
                    }

                    var $filterSubCat = $('<div class="panel panel-default">')
                        .append('<div class="panel-heading" role="tab" id="heading' + index + '"><h4 class="panel-title"><a role="button" data-toggle="collapse" href="#collapse' + index + '" aria-expanded="true" aria-controls="collapseOne">' + fil_name + '</a></h4></div>')
                        .append('<div id="collapse' + index + '" class="panel-collapse collapse' + panel_open + '" role="tabpanel" aria-labelledby="heading' + index + '"><div class="panel-body">')
                        .appendTo($filtersAvailable);

                    filter.options.forEach(function(option) {
                        if (option.options != undefined && option.options.length > 0) {
                            $('<div>').html(option.name).appendTo($filterSubCat.find('.panel-body'));
                            option.options.forEach(function(option) {
                                addFilterAvailableButton(option, $filterSubCat.find('.panel-body'))
                            });
                        } else {
                            addFilterAvailableButton(option, $filterSubCat.find('.panel-body'));
                        }
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