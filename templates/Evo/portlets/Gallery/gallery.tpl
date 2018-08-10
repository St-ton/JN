<div id="{$instance->getProperty('uid')}" {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}}{/if}>
    {foreach $instance->getProperty('gllry_images') as $image}
        <div class="{foreach $image['width'] as $gridWidth => $gridCols}{if !empty($gridCols)}col-{$gridWidth}-{$gridCols} {/if}{/foreach}gllry-item">
            <div class="box" style="height: {$instance->getProperty('gllry_height')}px;">
                {if $isPreview === false}
                    <a href="#" class="gllry_zoom_btn" style="line-height: {$instance->getProperty('gllry_height')}px">
                        <img srcset="{$image['img_attr']['srcset']}"
                             sizes="{$image['img_attr']['srcsizes']}"
                             src="{$image['img_attr']['src']}"
                             data-desc="{$image['desc']}"
                             alt="{$image['img_attr']['alt']}"
                             title="{$image['img_attr']['title']}"
                             data-index="{$image@iteration}">
                    </a>
                {else}
                    <img srcset="{$image['img_attr']['srcset']}"
                         sizes="{$image['img_attr']['srcsizes']}"
                         src="{$image['img_attr']['src']}"
                         data-desc="{$image['desc']}"
                         alt="{$image['img_attr']['alt']}"
                         title="{$image['img_attr']['title']}"
                         data-index="{$image@iteration}">
                {/if}
            </div>
        </div>
    {/foreach}
    <div class="modal fade" id="gllry_popup_{$instance->getProperty('uid')}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header text-right">
                    <button type="button" class="btn btn-default btn-sm"
                            data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="gllry-slider">
                        {foreach $instance->getProperty('gllry_images') as $image}
                            <img data-lazy="{$image['img_attr']['src']}"
                                 data-srcset="{$image['img_attr']['srcset']}"
                                 data-sizes="{$image['img_attr']['srcsizes']}"
                                 data-desc="{$image['desc']}"
                                 alt="{$image['img_attr']['alt']}"
                                 title="{$image['img_attr']['title']}">
                        {/foreach}
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="modal-footer">
                    <h4 id="gllry_popup_desc_{$instance->getProperty('uid')}"></h4>
                </div>
            </div>
        </div>
    </div>
    {if $isPreview === false}
        <script>
            $(function () {
                $("#{$instance->getProperty('uid')} .gllry_zoom_btn").click(function (e) {
                    e.preventDefault();
                    var desc = $(this).find("img").data("desc");
                    var current = $(this).find("img").data("index")-1;

                    $("#gllry_popup_desc_{$instance->getProperty('uid')}").text(desc);
                    $("#gllry_popup_{$instance->getProperty('uid')}").modal("show");

                    $("#gllry_popup_{$instance->getProperty('uid')}").off('shown.bs.modal.gllry').on('shown.bs.modal.gllry', function (e) {
                        $("#gllry_popup_{$instance->getProperty('uid')} .gllry-slider:not(.slick-initialized)").slick({
                            dots: true,
                            initialSlide: current,
                            lazyLoad: 'anticipated',
                            infinite: true,
                            slidesToShow: 1,
                            adaptiveHeight: false,
                        });

                        $("#gllry_popup_{$instance->getProperty('uid')} .gllry-slider").slick("slickGoTo",current);

                        $("#gllry_popup_{$instance->getProperty('uid')} .gllry-slider").off("afterChange.gllry").on("afterChange.gllry", function(event, slick, direction) {
                            var newdesc = $("#gllry_popup_{$instance->getProperty('uid')} .gllry-slider .slick-current img").data('desc');
                            $("#gllry_popup_desc_{$instance->getProperty('uid')}").text(newdesc);
                        });
                    });
                });
            });
        </script>
    {/if}
</div>