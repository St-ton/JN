{function name=slide level=0}
    <tr class="text-vcenter" id="slide{$kSlide}">
        <td class="tcenter">
            <input type="hidden" id="aSlide{$kSlide}delete" name="aSlide[{$kSlide}][delete]" value="0" />
            <input type="hidden" name="aSlide[{$kSlide}][cBild]" value="{if isset($oSlide)}{$oSlide->getImage()}{/if}" />
            <input type="hidden" name="aSlide[{$kSlide}][cThumbnail]" value="{if isset($oSlide)}{$oSlide->getThumbnail()}{/if}" />
            <input type="hidden" class="form-control" id="aSlide[{$kSlide}][nSort]" name="aSlide[{$kSlide}][nSort]" value="{if $kSlide}{$smarty.foreach.slide.iteration}{/if}" autocomplete="off" />
            <i class="btn btn-primary fa fa-bars"></i>
        </td>
        <td class="tcenter"><img src="{if isset($oSlide)}{$oSlide->getAbsoluteImage()}{else}templates/bootstrap/gfx/layout/upload.png{/if}" id="img{$kSlide}" onclick="select_image('{$kSlide}');" alt="Slidergrafik" class="slide-image" role="button" /></td>
        <td class="tcenter">
            <input class="form-control margin2" id="cTitel{$kSlide}" type="text" name="aSlide[{$kSlide}][cTitel]" value="{if isset($oSlide)}{$oSlide->getTitle()}{/if}" placeholder="Titel" />
            <input class="form-control margin2" id="cLink{$kSlide}" type="text" name="aSlide[{$kSlide}][cLink]" value="{if isset($oSlide)}{$oSlide->getLink()}{/if}" placeholder="Link" />
        </td>
        <td><textarea class="form-control vheight" id="cText{$kSlide}" name="aSlide[{$kSlide}][cText]" maxlength="255" placeholder="Text">{if isset($oSlide)}{$oSlide->getText()}{/if}</textarea></td>
        <td class="vcenter"><button type="button" onclick="$(this).parent().parent().find('input[name*=\'delete\']').val('1'); $(this).parent().parent().css({ 'display':'none'});sortSlide();" class="slide_delete btn btn-danger btn-block fa fa-trash" title="Löschen"></button></td>
    </tr>
{/function}

<form id="slide{$kSlider}" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="slide_set" />
    {$jtl_token}
    <div id="settings">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#slider#}</h3>
            </div>
            <div class="table-responsive">
                <table id="tableSlide" class="table">
                    <thead>
                    <tr>
                        <th class="tleft"></th>
                        <th width="10%">Bild</th>
                        <th width="40%">Titel / Link</th>
                        <th width="40%">Text</th>
                        <th width="5%"></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name="slide" from=$oSlider->getSlides() item=oSlide}
                        {slide kSlide=$oSlide->getID() oSlide=$oSlide}
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <table class="hidden"><tbody id="newSlide">{slide oSlide=null kSlide='NEU'}</tbody></table>
            <div class="panel-footer">
                <div class="upload_info kcfinder_path">{$shopURL}/{$PFAD_KCFINDER}</div>
                <div class="upload_info shop_url">{$shopURL}</div>
                <div class="btn-group">
                    <button type="button" class="btn btn-default" onclick="window.location.href = 'slider.php';"><i class="fa fa-angle-double-left"></i> zur&uuml;ck</button>
                    <button type="button" class="btn btn-success" onclick="addSlide();"><i class="glyphicon glyphicon-plus"></i> Hinzuf&uuml;gen</button>
                    <button type="button" class="btn btn-danger" onclick="location.reload();"><i class="glyphicon glyphicon-remove"></i> Abbrechen</button>
                    <button type="submit" class="btn btn-primary" id="saveButton"><i class=" fa fa-save"></i> Speichern</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript" src="{$currentTemplateDir}js/jquery.uploadify.js"></script>