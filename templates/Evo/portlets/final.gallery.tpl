<div {$attribString} {$styleString}>
    {foreach $properties.gllry_images as $image}
    <div class="{foreach $image['width'] as $gridWidth => $gridCols}{if !empty($gridCols)}col-{$gridWidth}-{$gridCols} {/if}{/foreach}gal-item">
        <div class="box">
            <a href="#" class="gallery_zoom_btn">
                <img {$image['srcStr']} data-desc="{$image['desc']}">
            </a>
        </div>
    </div>
    {/foreach}
    <div class="modal fade" id="gallery_popup" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close"
                            data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="gallery_popup_img" src="" class="img-responsive">
                </div>
                <div class="modal-footer">
                    <h4 id="gallery_popup_desc"></h4>
                </div>
            </div>
        </div>
    </div>
    {if $popupEnabled}
    <script>
        $(function () {
            $(".gallery_zoom_btn").click(function (e) {
                var source = $(this).find("img").attr("src");
                var desc = $(this).find("img").data("desc");
                source = source || this.getAttribute("data-src");
                $("#gallery_popup_img").attr("src", source);
                $("#gallery_popup_desc").text(desc);
                $("#gallery_popup").modal("show");
            });
        });
    </script>
    {/if}
    <style>
        .gal-container{
            padding: 12px;
        }
        .gal-item{
            overflow: hidden;
            padding: 3px;
        }
        .gal-item .box{
            height: {$properties["gllry_height"]}px;
            overflow: hidden;
        }
        .box img{
            height: 100%;
            width: 100%;
            object-fit:cover;
            -o-object-fit:cover;
        }
        .gal-item a:focus{
            outline: none;
        }
        .gal-item a:after{
            content:"\f002";
            font-family: FontAwesome;
            opacity: 0;
            background-color: rgba(0, 0, 0, 0.75);
            position: absolute;
            right: 3px;
            left: 3px;
            top: 3px;
            bottom: 3px;
            text-align: center;
            line-height: {$properties["gllry_height"]}px;
            font-size: 30px;
            color: #fff;
            -webkit-transition: all 0.5s ease-in-out 0s;
            -moz-transition: all 0.5s ease-in-out 0s;
            transition: all 0.5s ease-in-out 0s;
        }
        .gal-item a:hover:after{
            opacity: 1;
        }
        .gal-container .modal.fade .modal-dialog {
            -webkit-transform: scale(0.1);
            -moz-transform: scale(0.1);
            -ms-transform: scale(0.1);
            transform: scale(0.1);
            top: 100px;
            opacity: 0;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            transition: all 0.3s;
        }

        .gal-container .modal.fade.in .modal-dialog {
            -webkit-transform: scale(1);
            -moz-transform: scale(1);
            -ms-transform: scale(1);
            transform: scale(1);
            -webkit-transform: translate3d(0, -100px, 0);
            transform: translate3d(0, -100px, 0);
            opacity: 1;
        }
    </style>
</div>