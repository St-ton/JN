{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='slider'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('slider') cBeschreibung=__('sliderDesc') cDokuURL=__('sliderURL')}

<script src="{$currentTemplateDir}js/slider.js" type="text/javascript"></script>
<div id="content" class="container-fluid">
    {if $action === 'new' || $action === 'edit' }
        {include file='tpl_inc/slider_form.tpl'}
    {elseif $action === 'slides'}
        {include file='tpl_inc/slider_slide_form.tpl'}
    {else}
        <div id="settings">
            <div class="card">
                {if $oSlider_arr|@count == 0}
                    <div class="card-body">
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    </div>
                {else}
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination}
                    <div class="card-header">
                        <div class="card-title">{__('slider')}</div>
                    </div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="tleft" width="50%">{__('name')}</th>
                            <th width="20%">{__('status')}</th>
                            <th width="30%">{__('options')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oSlider_arr as $oSlider}
                            <tr>
                                <td class="tleft">{$oSlider->cName}</td>
                                <td class="tcenter">
                                    <h4 class="label-wrap">
                                    {if $oSlider->bAktiv == 1}
                                        <span class="label label-success">{__('active')}</span>
                                    {else}
                                        <span class="label label-danger">{__('inactive')}</span>
                                    {/if}
                                    </h4>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-default add" href="slider.php?action=slides&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="{__('slides')}"><i class="fa fa-image"></i></a>
                                        <a class="btn btn-default" href="slider.php?action=edit&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="{__('modify')}"><i class="fa fa-edit"></i></a>
                                        <a class="btn btn-danger" href="slider.php?action=delete&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="{__('delete')}"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                {/if}
                <div class="card-footer">
                    <a class="btn btn-primary" href="slider.php?action=new&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> {__('sliderCreate')}</a>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
