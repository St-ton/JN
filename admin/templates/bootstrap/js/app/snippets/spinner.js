export const startSpinner = () => {
    $('body').append('<div class="ajax-spinner"><i class="fa fa-spinner fa-pulse"></i></div>');
};

export const stopSpinner = () => {
    $('body').find('.ajax-spinner').remove();
};
