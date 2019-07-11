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
    <div class="slide-entry">
        <div class="slide-btns">
            <span class="btn-slide-mover"
                 title="{__('entryMove')}" style="cursor: move">
                <i class="fas fa-arrows-alt-v fa-fw"></i>
            </span>
            <button type="button" onclick="" title="Copy">
                <i class="far fa-clone fa-fw"></i>
            </button>
            <button type="button" onclick="removeSlide_{$propname}()"
                    title="{__('entryDelete')}">
                <i class="far fa-trash-alt fa-fw"></i>
            </button>
        </div>
        <div class="slide-image-col">
            {$imgUrl = $slideData.url|default:'templates/bootstrap/gfx/layout/upload.png'}
            <div style="background-image: url('{$imgUrl}')" class="slide-image-btn"
                 onclick="opc.gui.openElFinder(elfinderCallback_{$propname}.bind(this), 'Bilder')">

            </div>
            <input type="hidden" name="{$propname}[#SORT#][url]" value="{$slideData.url|default:''}">
        </div>
        <div class="slide-props">
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

<label>{$propdesc.label}</label>

<div class="slides-container">
    <div id="{$propname}-slides">
        {foreach $propval as $slideData}
            {slideEntry slideData=$slideData}
        {/foreach}
    </div>
    <button type="button" class="opc-btn-primary opc-small-btn add-slide-btn" onclick="addSlide_{$propname}()"
            title="{__('imageAdd')}">
        <i class="fas fa-plus fa-fw"></i>
    </button>
    <div style="display: none" id="{$propname}-slide-blueprint">
        {slideEntry}
    </div>
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
        image.css('background-image', 'url("' + url + '")');
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