{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='slider'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('slider') cBeschreibung=__('sliderDesc') cDokuURL=__('sliderURL')}

<script src="{$currentTemplateDir}js/slider.js" type="text/javascript"></script>
<div id="content">
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
                    <div class="card-header">
                        <div class="subheading1">{__('slider')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {include file='tpl_inc/pagination.tpl' pagination=$pagination}
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
                                            <a class="btn btn-link px-2" href="slider.php?action=delete&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="{__('delete')}">
                                                <span class="icon-hover">
                                                    <span class="fal fa-trash-alt"></span>
                                                    <span class="fas fa-trash-alt"></span>
                                                </span>
                                            </a>
                                            <a class="btn btn-link px-2 add" href="slider.php?action=slides&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="{__('slides')}">
                                                <span class="icon-hover">
                                                    <span class="fal fa-images"></span>
                                                    <span class="fas fa-images"></span>
                                                </span>
                                            </a>
                                            <a class="btn btn-link px-2" href="slider.php?action=edit&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="{__('modify')}">
                                                <span class="icon-hover">
                                                    <span class="fal fa-edit"></span>
                                                    <span class="fas fa-edit"></span>
                                                </span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {/if}
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-primary btn-block" href="slider.php?action=new&token={$smarty.session.jtl_token}">
                                <i class="fa fa-share"></i> {__('sliderCreate')}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
