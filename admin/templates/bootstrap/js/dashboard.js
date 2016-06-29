$(function () {
    sortable('.column', {
        items: '.widget',
        handle: '.widget-head',
        forcePlaceholderSize: true,
        connectWith: 'connected',
        placeholder: '<li class="widget-placeholder"></li>'
    });

    $('.widget').each(function (i, widget) {
        var widgetId = $(widget).attr('ref');
        var $widgetContent = $('.widget-content', widget);
        var $widget = $(widget);

        $('<a href="#"><i class="fa fa-chevron-circle-up"></li></a>')
            .click(function (e) {
                if ($widgetContent.is(':hidden')) {
                    xajax_expandWidgetAjax(widgetId, 1);
                    $widgetContent.slideDown('fast');
                } else {
                    xajax_expandWidgetAjax(widgetId, 0);
                    $widgetContent.slideUp('fast');
                }
                e.preventDefault();
            })
            .appendTo($('.options', widget));

        $('<a href="#"><i class="fa fa-times"></li></a>')
            .click(function (e) {
                xajax_closeWidgetAjax(widgetId);
                $widget.slideUp('fast');
                e.preventDefault();
            })
            .appendTo($('.options', widget));
    });
})