(function($) {
    $.fn.topScrollbar = function() {
        return this.each(function() {
            let $self = $(this);
            if ($self.prev().hasClass('jquery-top-scrollbar')) $self.prev().remove();

            let scroller = $self.clone().css({
                'position': 'fixed',
                'width': 'auto',
                'visibility': 'hidden',
                'overflow-y': 'auto'
            });

            scroller.appendTo('body');
            let innerWidth = $self[0].scrollWidth;

            scroller.remove();

            if ($self[0].offsetWidth >= innerWidth) return;

            let outer = $('<div class="jquery-top-scrollbar">');
            outer.css({width: $self[0].offsetWidth, height: 15, 'overflow-y': 'hidden'});

            let inner = $('<div>');
            inner.css({width: innerWidth, height: 15});

            $self.before(outer.append(inner));

            outer.scroll(function() {
                $self.scrollLeft(outer.scrollLeft());
            });

            $self.scroll(function() {
                outer.scrollLeft($self.scrollLeft());
            });

            $self.scroll();
        });
    };
})(jQuery);
