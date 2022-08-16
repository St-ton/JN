{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('imageTitle') cBeschreibung=__('bilderDesc') cDokuURL=__('bilderURL')}
<div id="content">
    <form method="post" action="{$adminURL}{$route}">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1">
        <div id="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('imageSizes')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive card-body">
                    <table class="list table table-border-light table-images">
                        <thead>
                        <tr>
                            <th class="text-left">{__('type')}</th>
                            <th class="text-center">{__('xs')} <small>{__('widthXHeight')}</small></th>
                            <th class="text-center">{__('sm')} <small>{__('widthXHeight')}</small></th>
                            <th class="text-center">{__('md')} <small>{__('widthXHeight')}</small></th>
                            <th class="text-center">{__('lg')} <small>{__('widthXHeight')}</small></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $indices as $idx => $name}
                        <tr>
                            <td class="text-left">{$name}</td>
                            {foreach $sizes as $size}
                            <td class="text-center">
                                <div class="input-group form-counter min-w-sm">
                                    {$optIdx = 'bilder_'|cat:$idx|cat:'_'|cat:$size|cat:'_breite'}
                                    {if !isset($imgConf.$optIdx)}
                                        {$optIdx = 'bilder_'|cat:$idx|cat:'_breite'}
                                    {/if}
                                    <input size="4" class="form-control" type="number" name="{$optIdx}" value="{$imgConf.$optIdx}" />
                                </div>
                                <span class="cross-sign text-center">x</span>
                                <div class="input-group form-counter min-w-sm">
                                    {$optIdx = 'bilder_'|cat:$idx|cat:'_'|cat:$size|cat:'_hoehe'}
                                    {if !isset($imgConf.$optIdx)}
                                        {$optIdx = 'bilder_'|cat:$idx|cat:'_hoehe'}
                                    {/if}
                                    <input size="4" class="form-control" type="number" name="{$optIdx}" value="{$imgConf.$optIdx}" />
                                </div>
                            </td>
                            {/foreach}
                        </tr>
                        {/foreach}

                        </tbody>
                    </table>
                </div>
            </div>
            {foreach $sections as $section}
                {foreach $section->getSubsections() as $subsection}
                    {include file='tpl_inc/config_heading.tpl' subsection=$subsection idx=$subsection@index}
                    {foreach $subsection->getItems() as $cnf}
                        {if strpos($cnf->getValueName(), 'hoehe') === false && strpos($cnf->getValueName(), 'breite') === false}
                            {if $cnf->isConfigurable()}
                                {include file='tpl_inc/config_item.tpl'}
                            {/if}
                        {/if}
                    {/foreach}
                {/foreach}
            {/foreach}
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto submit">
                        <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
