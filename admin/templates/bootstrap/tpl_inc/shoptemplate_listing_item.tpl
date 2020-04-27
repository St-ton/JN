<tr>
	<td class="text-vcenter text-center" width="140">
		<div class="thumb-box thumb-sm">
			<div class="thumb" style="background-image:url({if $listingItem->getPreview()|strlen > 0}{$shopURL}/{$smarty.const.PFAD_TEMPLATES}{$listingItem->getDir()}/{$listingItem->getPreview()}{else}{$shopURL}/gfx/keinBild.gif{/if})"></div>
		</div>
	</td>
	<td>
		<ul class="list-unstyled">
			<li>
				<h3 style="margin:0">{$listingItem->getName()}</h3>
                {if !empty($listingItem->getDescription())}
					<div class="small">{$listingItem->getDescription()}</div>
                {/if}
				<span class="label label-default">
                    <i class="far fa-folder" aria-hidden="true"></i> {$listingItem->getDir()}
                </span>
	            {if $listingItem->isChild() === true}<span class="label label-info"><i class="fa fa-level-up" aria-hidden="true"></i> <abbr title="{{__('inheritsFrom')}|sprintf:{$listingItem->getParent()}}">{$listingItem->getParent()}</abbr></span>{/if}

                {if isset($oStoredTemplate_arr[$listingItem->getDir()])}
                    {foreach $oStoredTemplate_arr[$listingItem->getDir()] as $oStored}
						<span class="label label-warning"><i class="fal fa-info-circle" aria-hidden="true"></i> <abbr title="{__('originalExists')} ({$oStored->cVersion})">{$oStored->cVersion}</abbr></span>
                    {/foreach}
                {/if}
				<div class="font-size-sm">
                    {if !empty($listingItem->getURL())}<a href="{$listingItem->getURL()}" rel="noopener" target="_blank">{/if}
                        {$listingItem->getAuthor()}
                        {if !empty($listingItem->getURL())}</a>{/if}
				</div>
			</li>
		</ul>
	</td>
	<td class="text-vcenter text-center">
        {if $listingItem->hasError() === true}
			<h4 class="label-wrap">
				<span class="label label-danger">{__('faulty')}</span>
			</h4>
        {elseif $listingItem->isActive()}
			<h4 class="label-wrap">
				<span class="label label-success">{__('activated')}</span>
			</h4>
        {/if}
		{$check = $listingItem->getChecksums()}
        {if $check !== null}
            {if $check === true}
				<span class="badge badge-success">{__('unmodified')}</span>
            {else}
				<span class="badge badge-warning cursor-pointer" title="{__('tplChecksums')}"
				      data-toggle="modal" data-target="#tplModal{$listingItem->getName()}">{__('modified')}</span>
				<div class="modal fade" id="tplModal{$listingItem->getName()}" tabindex="-1" role="dialog"
				     aria-labelledby="tplModal{$listingItem->getName()}Label" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="tplModal{$listingItem->getName()}Label">
                                    {$listingItem->getName()} – {__('modifiedFiles')} ({$check|count})
								</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<div class="table-responsive table-">
									<table class="table table-sm table-borderless">
										<thead>
										<tr>
											<th class="text-left">{__('file')}</th>
											<th class="text-right">{__('lastModified')}</th>
										</tr>
										</thead>
                                        {foreach $check as $file}
											<tr>
												<td class="text-left">{$file->name}</td>
												<td class="text-right">
													<small class="text-muted">{$file->lastModified}</small>
												</td>
											</tr>
                                        {/foreach}
									</table>
								</div>
							</div>
							<div class="modal-footer text-right">
								<button type="button" class="btn btn-primary" data-dismiss="modal">{__('close')}</button>
							</div>
						</div>
					</div>
				</div>
            {/if}
        {/if}
	</td>
{*    <td class="text-vcenter text-center">*}
{*        {$listingItem->getVersion()}*}
{*    </td>*}
	<td class="text-vcenter text-center">
        {$listingItem->getShopVersion()}
	</td>
	<td class="text-vcenter text-center">
        {if $listingItem->hasError()}
			<span class="error"><strong>{__('danger')}:</strong><br />{__('parentTemplateMissing')}.</span>
        {elseif $listingItem->isAvailable()}
            {if !$listingItem->isActive()}
                {if $listingItem->getOptionsCount() > 0 && false}
					<a class="btn btn-primary" href="shoptemplate.php?settings={$listingItem->getDir()}&activate=1&token={$smarty.session.jtl_token}"><i class="fal fa-share"></i> {__('activate')}</a>
                {else}
					<a class="btn btn-primary" href="shoptemplate.php?switch={$listingItem->getDir()}&token={$smarty.session.jtl_token}"><i class="fal fa-share"></i> {__('activate')}</a>
                {/if}
            {else}
                {if $listingItem->getOptionsCount() > 0}
					<a class="btn btn-outline-primary" href="shoptemplate.php?settings={$listingItem->getDir()}&token={$smarty.session.jtl_token}"><i class="fal fa-edit"></i> {__('settings')}</a>
                {/if}
            {/if}
        {/if}
	</td>
</tr>
