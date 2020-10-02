{include file='tpl_inc/header.tpl'}
<div id="content">
    {if isset($recommendation)}
        <div class="row">
            <div class="col-md-4 pr-md-4 pr-0">
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('plugin')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <img width="160" height="160" src="{$recommendation->getPreviewImage()}" loading="lazy">
                            </div>
                            <div class="col-auto align-self-end">
                                <div><a href="{$recommendation->getManufacturer()->getProfileURL()}">{$recommendation->getTitle()}</a></div>
                                <div>{__('manufacturer')}: {$recommendation->getManufacturer()->getName()}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        {$recommendation->getDescription()}
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            {foreach $recommendation->getImages() as $image}
                <div class="col-md text-center pr-md-4 pr-0">
                    <img src="{$image}" class="object-fit-cover mb-md-0 mb-2" loading="lazy">
                </div>
            {/foreach}
        </div>
        <div class="row">
            <div class="col-md-6 pr-md-4 pr-0">
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('yourAdvantages')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tbody>
                                {foreach  $recommendation->getBenefits() as $benefit}
                                    <tr>
                                        <td width="20px"><i class="fal fa-check text-success"></i></td>
                                        <td>{$benefit}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('installationGuide')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {$recommendation->getSetupDescription()}
                        <div class="row">
                            <div class="col-sm-6 col-xl-auto">
                                <form method="post">
                                    <button type="submit" name="action" value="install" class="btn btn-primary btn-block" {if $hasLicense}disabled{/if}>
                                        {if $hasLicense}{__('pluginHasLicense')}{else}{__('installPlugin')}{/if}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
