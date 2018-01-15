<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation">
        <a aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">
            General
        </a>
    </li>
    <li role="presentation">
        <a aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">
            Animation
        </a>
    </li>
    <li role="presentation" class="">
        <a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design">
            Style
        </a>
    </li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-row-layout-lg">Layout <i class="fa fa-desktop"></i></label>
                    <a title="more" class="pull-right" role="button" data-toggle="collapse"
                       href="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                        <i class="fa fa-gears"></i>
                    </a>
                    <input type="text" id="config-row-layout-lg" name="layout-lg" class="form-control" placeholder="6+6"
                           value="{$properties['layout-lg']}">
                    <span class="help-block">
                        Geben Sie die Spaltenbreiten in der Form '6+3+3' oder '4+4+4' an. Werte von 1 bis 12 sind möglich.
                    </span>
                </div>
                <div class="collapse" id="collapseLayouts">
                    <span class="help-block">
                        Hier können Sie für die unterschiedlichen Gerätegrößen eine alternative Aufteilung angeben.
                        Bitte geben Sie die gleiche Anzahl an Spalten an.
                    </span>
                    <label for="config-row-layout-md">Layout <i class="fa fa-laptop"></i></label>
                    <input type="text" id="config-row-layout-md" name="layout-md" class="form-control"
                           value="{$properties['layout-md']}">

                    <label for="config-row-layout-sm">Layout <i class="fa fa-tablet"></i></label>
                    <input type="text" id="config-row-layout-sm" name="layout-sm" class="form-control"
                           value="{$properties['layout-sm']}">

                    <label for="config-row-layout-xs">Layout <i class="fa fa-mobile"></i></label>
                    <input type="text" id="config-row-layout-xs" name="layout-xs" class="form-control"
                           value="{$properties['layout-xs']}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-heading-class">Class</label>
                    <input name="attr[class]" value="{$properties.attr['class']}" class="form-control"
                           id="config-heading-class">
                </div>
            </div>
        </div>
    </div>
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>
