{if $mailTemplates|count > 0}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">{$heading}</h3>
		</div>
		<div class="panel-body table-responsive">
			<table class="list table">
				<thead>
				<tr>
					<th class="tleft">{__('template')}</th>
					<th>{__('type')}</th>
					<th>{__('active')}</th>
					<th>{__('options')}</th>
				</tr>
				</thead>
				<tbody>
				{foreach $mailTemplates as $template}
					<tr>
						<td>{__('name_'|cat:$template->getModuleID())}</td>
						<td class="tcenter">{$template->getType()}</td>
						<td class="tcenter">
							<h4 class="label-wrap">
								{if $template->getActive()}
									<span class="label label-success success">{__('active')}</span>
								{elseif $template->getHasError()}
									<span class="label label-danger">{__('faulty')}</span>
								{else}
									<span class="label label-info error">{__('inactive')}</span>
								{/if}
							</h4>
						</td>
						<td class="tcenter">
							<form method="post" action="emailvorlagen.php">
								{if $template->getPluginID() > 0}
									<input type="hidden" name="kPlugin" value="{$template->getPluginID()}" />
								{/if}
								{$jtl_token}
								<div class="btn-group">
									<button type="submit" name="preview" value="{$template->getID()}" class="btn btn-default mail">
										<i class="fa fa-envelope"></i> {__('testmail')}
									</button>
									<button type="submit" name="kEmailvorlage" value="{$template->getID()}" class="btn btn-primary" title="{__('modify')}">
										<i class="fa fa-edit"></i>
									</button>
									<button type="submit" name="resetConfirm" value="{$template->getID()}" class="btn btn-danger reset" title="{__('reset')}">
										<i class="fa fa-refresh"></i>
									</button>
								</div>
							</form>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
{/if}
