{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-scroll-top'}
    {block name='snippets-scroll-top-main'}
        <div class="smoothscroll-top">
            <span class="scroll-top-inner">
                <i class="fa fa-2x fa-arrow-circle-up"></i>
            </span>
        </div>
    {/block}
    {block name='snippets-scroll-top-script'}
        {inline_script}<script>
            {literal}
                $(function(){
                    let lastScrollTop = 0;
                    $(document).on('scroll', function () {
                        let st = $(this).scrollTop();
                        if (st < lastScrollTop){
                            if ($(window).scrollTop() > 100) {
                                $('.smoothscroll-top').addClass('show');
                            } else {
                                $('.smoothscroll-top').removeClass('show');
                            }
                        } else {
                            $('.smoothscroll-top').removeClass('show');
                        }
                        lastScrollTop = st;
                    });

                    $('.smoothscroll-top').on('click', scrollToTop);
                });

                function scrollToTop() {
                    let element = $('body');
                    let offset = element.offset();
                    let offsetTop = offset.top;
                    $('html, body').animate({scrollTop: offsetTop}, 400, 'linear');
                }
            {/literal}
        </script>{/inline_script}
    {/block}
{/block}