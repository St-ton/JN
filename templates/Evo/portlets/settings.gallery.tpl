<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation"><a aria-controls="images" data-toggle="tab" role="tab" id="images-tab" href="#images">Images</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="tabpanel" aria-labelledby="general-tab">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="gllry_height">Höhe der Vorschaubilder</label>
                    <input type="number" id="gllry_height" name="gllry_height" class="form-control" value="{$properties['gllry_height']}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="gllry-id">ID</label>
                    <input type="text" id="gllry-id" name="attr[id]" class="form-control" value="{$properties.attr['id']}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="gllry-class">Class name</label>
                    <input type="text" id="gllry-class" name="attr[class]" class="form-control" value="{$properties.attr['class']}">
                </div>
            </div>
        </div>
    </div>
    <div id="images" class="tab-pane fade" role="tabpanel" aria-labelledby="images-tab">
        {function name=slide level=0}
            <tr class="text-vcenter" id="{$kSlide}">
                <td class="tcenter">
                    <input id="{$kSlide}-url" type="hidden" name="gllry_images[{$kSlide}][url]"
                           value=""/>
                    <input type="hidden" class="form-control" id="gllry_images[{$kSlide}][nSort]"
                           name="gllry_images[{$kSlide}][nSort]" value="{if $kSlide}{$smarty.foreach.slide.iteration}{/if}"
                           autocomplete="off"/>
                    <i class="btn btn-primary fa fa-bars"></i>
                </td>
                <td class="tcenter"><img
                            src="templates/bootstrap/gfx/layout/upload.png"
                            id="{$kSlide}-img" onclick="editor.onOpenKCFinder(kcfinderCallback.bind(this, '{$kSlide}'));"
                            alt="Slidergrafik" class="img-responsive" role="button"/></td>
                <td class="tcenter">
                    <input class="form-control margin2" id="desc{$kSlide}" type="text"
                           name="gllry_images[{$kSlide}][desc]" value=""
                           placeholder="description"/>
                </td>
                <td><i class="fa fa-desktop"></i> <a title="more" class="pull-right" role="button" data-toggle="collapse"
                                                     href="#collapseLayouts_{$kSlide}" aria-expanded="false" aria-controls="collapseLayouts_{$kSlide}">
                        <i class="fa fa-gears"></i>
                    </a><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                           name="gllry_images[{$kSlide}][width][lg]" value=""
                           placeholder="with in number of colums"/>

                    <div class="collapse" id="collapseLayouts_{$kSlide}">
                        <span class="help-block">
                            Hier können Sie für die unterschiedlichen Gerätegrößen eine alternative Aufteilung angeben.
                        </span>
                        <i class="fa fa-laptop"></i><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                                                           name="gllry_images[{$kSlide}][width][md]" value=""
                                                           placeholder="with in number of colums"/>
                        <i class="fa fa-tablet"></i><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                                                           name="gllry_images[{$kSlide}][width][sm]" value=""
                                                           placeholder="with in number of colums"/>
                        <i class="fa fa-mobile"></i><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                                                           name="gllry_images[{$kSlide}][width][xs]" value=""
                                                           placeholder="with in number of colums"/>
                    </div>

                <td class="vcenter">
                    <button type="button" onclick="$(this).parent().parent().remove();sortSlide();"
                            class="slide_delete btn btn-danger btn-block fa fa-trash" title="L&ouml;schen"></button>
                </td>
            </tr>
        {/function}
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">fügen Sie hier die einzelnen Bilder hinzu</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="tableSlide" class="table">
                            <thead>
                            <tr>
                                <th class="tleft"></th>
                                <th width="20%">Bild</th>
                                <th width="35%">Beschreibung</th>
                                <th width="35%">Breite</th>
                                <th width="5%"></th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$properties['gllry_images'] item=slide}
                                {if !empty($slide['url'])}
                                    <tr class="text-vcenter" id="slide{$slide.nSort}">
                                        <td class="tcenter">
                                            <input id="gllry_image{$slide.nSort}-url" type="hidden" name="gllry_images[slide{$slide.nSort}][url]"
                                                   value="{if isset($slide['url'])}{$slide['url']}{/if}"/>
                                            <input type="hidden" class="form-control" id="gllry_images[slide{$slide.nSort}][nSort]"
                                                   name="gllry_images[slide{$slide.nSort}][nSort]" value="{$slide.nSort}"
                                                   autocomplete="off"/>
                                            <i class="btn btn-primary fa fa-bars"></i>
                                        </td>
                                        <td class="tcenter"><img
                                                    src="{if isset($slide['url'])}{$slide['url']}{else}templates/bootstrap/gfx/layout/upload.png{/if}"
                                                    id="gllry_image{$slide.nSort}-img" onclick="editor.onOpenKCFinder(kcfinderCallback.bind(this, '{$slide.nSort}'));"
                                                    alt="image for gallery" class="img-responsive" role="button"/></td>
                                        <td class="tcenter">
                                            <input class="form-control margin2" id="desc{$slide.nSort}" type="text"
                                                   name="gllry_images[slide{$slide.nSort}][desc]" value="{if isset($slide['desc'])}{$slide['desc']}{/if}"
                                                   placeholder="description"/>
                                        </td>
                                        <td>
                                            <i class="fa fa-desktop"></i><a title="more" class="pull-right" role="button" data-toggle="collapse"
                                                                            href="#collapseLayouts_{$slide.nSort}" aria-expanded="false" aria-controls="collapseLayouts_{$slide.nSort}">
                                                <i class="fa fa-gears"></i>
                                            </a><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                                                    name="gllry_images[slide{$slide.nSort}][width][lg]" value="{if isset($slide['width']['lg'])}{$slide['width']['lg']}{/if}"
                                                    placeholder="with in number of colums"/>

                                            <div class="collapse" id="collapseLayouts_{$slide.nSort}">
                                                <span class="help-block">
                                                    Hier können Sie für die unterschiedlichen Gerätegrößen eine alternative Aufteilung angeben.
                                                </span>
                                                <i class="fa fa-laptop"></i><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                                                       name="gllry_images[slide{$slide.nSort}][width][md]" value="{if isset($slide['width']['md'])}{$slide['width']['md']}{/if}"
                                                       placeholder="with in number of colums"/>
                                                <i class="fa fa-tablet"></i><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                                                       name="gllry_images[slide{$slide.nSort}][width][sm]" value="{if isset($slide['width']['sm'])}{$slide['width']['sm']}{/if}"
                                                       placeholder="with in number of colums"/>
                                                <i class="fa fa-mobile"></i><input class="form-control margin2" id="width{$slide.nSort}" type="number"
                                                       name="gllry_images[slide{$slide.nSort}][width][xs]" value="{if isset($slide['width']['xs'])}{$slide['width']['xs']}{/if}"
                                                   placeholder="with in number of colums"/>
                                            </div>
                                        </td>
                                        <td class="vcenter">
                                            <button type="button" onclick="$(this).parent().parent().remove();sortSlide();"
                                                    class="slide_delete btn btn-danger btn-block fa fa-trash" title="L&ouml;schen"></button>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <table class="hidden"><tbody id="newSlide">{slide oSlide=null kSlide='NEU'}</tbody></table>
                    <div class="panel-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" onclick="addSlide();"><i class="glyphicon glyphicon-plus"></i> Hinzuf&uuml;gen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function kcfinderCallback(id, url) {
                $('#'+id+'-url').val(url);
                $('#'+id+'-img').attr('src', url);
            }

            var count = {if isset($properties.slides)}{$properties.slides|@count}{else}0{/if};
            function addSlide(slide) {
                var new_slide = $('#newSlide').html();
                new_slide = new_slide.replace(/NEU/g, "slide"+count);
                $('#tableSlide tbody').append( new_slide );
                count++;
                sortSlide();
            }

            function sortSlide() {
                $("input[name*='\[nSort\]']").each(function(index) {
                    $(this).val(index+1);
                });
            }
            $(function(){
                $("#tableSlide tbody ").sortable({
                    containerSelector: 'table',
                    itemPath: '> tbody',
                    itemSelector: 'tr',
                    opacity : '0',
                    axis : "y",
                    cursor: "move",
                    stop : function(item) {
                        sortSlide();
                    }
                });
            });
        </script>
    </div>
    {include file='./settings.tabcontent.style.tpl'}
</div>
