{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-scroll-top'}
    {block name='snippets-scroll-top-main'}
        <div class="smoothscroll-top">
            <span class="scroll-top-inner">
                <i class="fas fa-2x fa-arrow-circle-up"></i>
            </span>
        </div>
    {/block}
    {block name='snippets-scroll-top-script'}
        {inline_script}<script>
            {literal}
                $(function(){
                    let lastScrollTop = 0;
                    $(document).on('scroll', function () {
                        let newScrollTop = $(this).scrollTop();
                        if (newScrollTop < lastScrollTop){
                            if ($(window).scrollTop() > 100) {
                                $('.smoothscroll-top').addClass('show');
                            } else {
                                $('.smoothscroll-top').removeClass('show');
                            }
                        } else {
                            $('.smoothscroll-top').removeClass('show');
                        }
                        lastScrollTop = newScrollTop;
                    });

                    $('.smoothscroll-top').on('click', scrollToTop);
                });

                function scrollToTop() {
                    $('html, body').animate({scrollTop: $('body').offset().top}, 400, 'linear');
                }
            {/literal}
        </script>{/inline_script}
    {/block}
{/block}