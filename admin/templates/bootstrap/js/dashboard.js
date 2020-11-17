$(function () {
    // make each column sortable
    var cols = sortable('.dashboard-col', {
        items: '.widget',
        handle: '.widget-head',
        forcePlaceholderSize: true,
        connectWith: 'connected',
        placeholder: '<li class="widget-placeholder"></li>'
    });

    // add listeners for each column for a sortupdate event
    cols.forEach(function (col, i) {
        col.addEventListener('sortupdate', function (e) {
            var item = e.detail.item;
            var id = $(item).attr('ref');
            var container = e.detail.endparent;
            var containerName = $(container).attr('id');

            $(container).children().each(function (i, widget) {
                var id = $(widget).attr('ref');
                ioCall('setWidgetPosition', [id, containerName, i]);
            });
        });
    });

    $('.widget').each(function (i, widget) {
        var widgetId = $(widget).attr('ref');
        var $widgetContent = $('.widget-content', widget);
        var $widget = $(widget);
        var hidden = $('.widget-hidden', widget).length > 0;

        // add click handler for widgets collapse button
        $('<a href="#" class="btn-sm"><i class="fa fa-chevron-' + (hidden ? 'down' : 'up') + '"></li></a>')
            .on('click', function (e) {
                if ($widgetContent.is(':hidden')) {
                    ioCall('expandWidget', [widgetId, 1], undefined, undefined, undefined, true);
                    $widgetContent.slideDown('fast');
                    $('i', this).attr('class', 'fa fa-chevron-up');
                } else {
                    ioCall('expandWidget', [widgetId, 0], undefined, undefined, undefined, true);
                    $widgetContent.slideUp('fast');
                    $('i', this).attr('class', 'fa fa-chevron-down');
                }
                e.preventDefault();
            })
            .appendTo($('.options', widget));

        // add click handler for widgets close button
        $('<a href="#" class="ml-2 btn-sm"><i class="fal fa-times"></li></a>')
            .on('click', function (e) {
                e.preventDefault();
                ioCall('closeWidget', [widgetId], function (result) {
                    ioCall('getAvailableWidgets');
                    $widget.slideUp('fast');
                });
            })
            .appendTo($('.options', widget));
    });
})