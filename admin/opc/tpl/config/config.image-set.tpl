{$useColumns = $propdesc.useColumns|default:false}
{$useLinks   = $propdesc.useLinks|default:false}
{$useTitles  = $propdesc.useTitles|default:false}

{function slideSize size='xs' fa='mobile'}
    <div class="input-group" style="width: 24%">
        <div class="input-group-addon" style="min-width:auto; padding: 0">
            <i class="fa fa-{$fa} fa-fw"></i>
        </div>
        <input type="text" class="form-control" placeholder="{$size}" style="width: 100%"
               name="{$propname}[#SORT#][{$size}]" value="{$slideData.$size}">
    </div>
{/function}

{function slideEntry
    slideData=['xs' => '', 'sm' => '', 'md' => '', 'lg' => '', 'desc' => '', 'url' => '', 'link' => '', 'title' => '']
}
    <div class="row slide-entry" style="margin-bottom: 1em;">
        <div class="col-xs-2" style="width: 17%">
            <div class="btn-group">
                <div type="button" class="btn btn-primary btn-sm btn-slide-mover"
                     title="{__('entryMove')}" style="cursor: move">
                    <i class="fa fa-bars"></i>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSlide_{$propname}()"
                        title="{__('entryDelete')}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
        <div class="col-xs-3" style="width: 24%">
            {$imgUrl = $slideData.url|default:'templates/bootstrap/gfx/layout/upload.png'}
            <img src="{$imgUrl}" alt="Bild-Wähler" class="img-responsive"
                 onclick="opc.gui.openElFinder(elfinderCallback_{$propname}.bind(this), 'Bilder')"
                 style="cursor: pointer" title="{__('imageSelect')}">
            <input type="hidden" name="{$propname}[#SORT#][url]" value="{$slideData.url|default:''}">
        </div>
        <div class="col-xs-7" style="width: 59%">
            {if $useTitles}
                <input type="text" class="form-control" placeholder="{__('title')}"
                       name="{$propname}[#SORT#][title]" value="{$slideData.title|default:''}">
            {/if}
            <input type="text" class="form-control" placeholder="{__('alternativeText')}"
                   name="{$propname}[#SORT#][alt]" value="{$slideData.alt|default:''}">
            <input type="text" class="form-control" placeholder="{__('description')}"
                   name="{$propname}[#SORT#][desc]" value="{$slideData.desc|default:''}">
            {if $useLinks}
                <input type="text" class="form-control" placeholder="{__('link')}"
                       name="{$propname}[#SORT#][link]" value="{$slideData.link|default:''}">
            {/if}
            {if $useColumns}
                <div class="form-inline">
                    {slideSize size='xs' fa='mobile'}
                    {slideSize size='sm' fa='tablet'}
                    {slideSize size='md' fa='laptop'}
                    {slideSize size='lg' fa='desktop'}
                </div>
            {/if}
        </div>
    </div>
{/function}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{$propdesc.label}</h3>
    </div>
    <div class="panel-body" id="{$propname}-slides">
        {foreach $propval as $slideData}
            {slideEntry slideData=$slideData}
        {/foreach}
    </div>
    <div class="panel-footer">
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="addSlide_{$propname}()">
                <i class="fal fa-plus"></i> {__('imageAdd')}
            </button>
        </div>
    </div>
</div>

<div class="hidden" id="{$propname}-slide-blueprint">
    {slideEntry}
</div>

<script>
    opc.setConfigSaveCallback(saveImageSet_{$propname});

    $(function () {
        $('#{$propname}-slides').sortable({
            handle: '.btn-slide-mover'
        });
    });

    function elfinderCallback_{$propname}(url)
    {
        var image = $(this);
        image.attr('src', url);
        image.siblings('input').val(url);
    }

    function addSlide_{$propname}()
    {
        $('#{$propname}-slides').append(
            $('#{$propname}-slide-blueprint').children().clone()
        );
    }

    function removeSlide_{$propname}()
    {
        $(event.target).closest('.slide-entry').remove();
    }

    function saveImageSet_{$propname}()
    {
        $('#{$propname}-slides').children().each(function(i, slide)
        {
            slide = $(slide);
            slide.find('input').each(function(j, input)
            {
                input = $(input);
                var name = input.attr('name');
                if (name === '{$propname}[#SORT#][url]') {
                    var val = input.val();
                    if (val === '') {
                        slide.remove();
                        return;
                    }
                }
                name = name.replace(/#SORT#/, i);
                input.attr('name', name);
            });
        });
        $('#{$propname}-slide-blueprint').remove();
    }
</script>