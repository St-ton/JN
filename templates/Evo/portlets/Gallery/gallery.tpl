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
                             title="{$image['img_attr']['title']}">
                    </a>
                {else}
                    <img srcset="{$image['img_attr']['srcset']}"
                         sizes="{$image['img_attr']['srcsizes']}"
                         src="{$image['img_attr']['src']}"
                         data-desc="{$image['desc']}"
                         alt="{$image['img_attr']['alt']}"
                         title="{$image['img_attr']['title']}">
                {/if}
            </div>
        </div>
    {/foreach}
    <div class="modal fade" id="gllry_popup_{$instance->getProperty('uid')}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close"
                            data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="gllry_popup_img_{$instance->getProperty('uid')}" src="" class="img-responsive">
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
                    var source = $(this).find("img").attr("src");
                    var desc = $(this).find("img").data("desc");
                    source = source || this.getProperty("data-src");
                    $("#gllry_popup_img_{$instance->getProperty('uid')}").attr("src", source);
                    $("#gllry_popup_desc_{$instance->getProperty('uid')}").text(desc);
                    $("#gllry_popup_{$instance->getProperty('uid')}").modal("show");
                });
            });
        </script>
    {/if}
</div>