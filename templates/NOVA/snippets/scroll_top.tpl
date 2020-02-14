{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-scroll-top'}
    {block name='snippets-scroll-top-main'}
        <div class="smoothscroll-top">
            <span class="scroll-top-inner">
                <i class="fas fa-2x fa-chevron-up"></i>
            </span>
        </div>
    {/block}
    {block name='snippets-scroll-top-script'}
        {inline_script}<script>
            {literal}
                $(function(){
                    var toTopbuttonVisible = false;
                    var $toTopbutton = $(".smoothscroll-top");
                    var toTopbuttonActiveClass = "show";

                    function scrolltoTop() {
                        $(window).scrollTop(0);
                    }

                    function handleVisibilityTopButton() {
                        var currentPosition = $(window).scrollTop();
                        if (currentPosition > 800) {
                            if (!toTopbuttonVisible) {
                                $toTopbutton.addClass(toTopbuttonActiveClass);
                                toTopbuttonVisible = true;
                            }
                        } else if (toTopbuttonVisible) {
                            toTopbuttonVisible = false;
                            $toTopbutton.removeClass(toTopbuttonActiveClass)
                        }
                    }

                    $(window).on('scroll', handleVisibilityTopButton);
                    $toTopbutton.on('click', scrolltoTop);
                    handleVisibilityTopButton();
                });
            {/literal}
        </script>{/inline_script}
    {/block}
{/block}