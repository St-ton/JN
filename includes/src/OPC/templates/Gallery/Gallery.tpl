{if $isPreview}
    {$data = ['portlet' => $instance->getDataAttribute()]}
{/if}

{$galleryHeight = $instance->getProperty('height')}

{row
    id=$instance->getUid()
    class='img-gallery'
    data=$data|default:null
    style=$instance->getStyleString()
}
    {foreach $instance->getProperty('images') as $key => $image}
        {$image.xs = $image.xs|default:12}
        {$image.sm = $image.sm|default:$image.xs}
        {$image.md = $image.md|default:$image.sm}
        {$image.lg = $image.lg|default:$image.md}
        {$imgAttribs = $instance->getImageAttributes($image.url, '', '')}

        {col cols=$image.xs sm=$image.sm md=$image.md lg=$image.lg class="img-gallery-item"
                style='height:'|cat:$galleryHeight|cat:'px'}
            {if $isPreview}
                {image
                    class='img-gallery-img'
                    srcset=$imgAttribs.srcset
                    sizes=$imgAttribs.srcsizes
                    src=$imgAttribs.src
                    alt=$imgAttribs.alt
                    title=$imgAttribs.title}
            {else}
                <a href="#" class="img-gallery-btn" style="height: {$instance->getProperty('height')}px">
                    {image
                        class='img-gallery-img'
                        srcset=$imgAttribs.srcset
                        sizes=$imgAttribs.srcsizes
                        src=$imgAttribs.src
                        data=['index' => $key, 'desc' => $image.desc]
                        alt=$imgAttribs.alt
                        title=$imgAttribs.title}
                    <i class="img-gallery-zoom fa fa-search fa-2x"></i>
                </a>
            {/if}
        {/col}
    {/foreach}
{/row}

{if $isPreview === false}
    {modal size='lg' id='gllry_popup_'|cat:$instance->getUid() class='fade img-gallery-modal'}
        <div class="img-gallery-slider">
            {foreach $instance->getProperty('images') as $image}
                {$imgAttribs = $instance->getImageAttributes($image.url, '', '')}
                {image
                    data=['lazy' => $imgAttribs.src,
                          'srcset' => $imgAttribs.srcset,
                          'sizes' => $imgAttribs.srcsizes,
                          'desc' => $image.desc]
                    alt=$imgAttribs.alt
                    title=$imgAttribs.title}
            {/foreach}
        </div>
        <div class="clearfix"></div>
        <h4 id="gllry_popup_desc_{$instance->getUid()}"></h4>
    {/modal}
    <script>
        $(function () {
            var uid = '{$instance->getUid()}';

            $("#" + uid + " .img-gallery-btn").click(function (e)
            {
                e.preventDefault();
                var desc = $(this).find("img").data("desc");
                var current = $(this).find("img").data("index");
                var slider = $("#gllry_popup_" + uid + " .img-gallery-slider");
                var popup = $("#gllry_popup_" + uid);

                $("#gllry_popup_desc_" + uid).text(desc);
                popup
                    .off('shown.bs.modal.gllry')
                    .on('shown.bs.modal.gllry', function (e)
                    {
                        $("#gllry_popup_" + uid + " .img-gallery-slider:not(.slick-initialized)")
                            .slick({
                                dots: true,
                                initialSlide: current,
                                lazyLoad: 'anticipated',
                                infinite: true,
                                slidesToShow: 1,
                                adaptiveHeight: false,
                            });

                        slider.slick("slickGoTo", current);
                        slider.off("afterChange.gllry")
                            .on("afterChange.gllry", function(event, slick, direction) {
                                var newdesc = $("#gllry_popup_" + uid + " .img-gallery-slider .slick-current img")
                                    .data('desc');
                                $("#gllry_popup_desc_" + uid + "").text(newdesc);
                            });
                    });
                popup.modal("show");
            });
        });
    </script>
{/if}