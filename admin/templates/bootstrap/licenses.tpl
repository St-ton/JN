{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('Licenses') cBeschreibung=__('pageDesc') cDokuURL=__('https://www.jtl-software.de')}

<div id="content">
	<div class="card">
		<div class="card-header">{__('Active licenses')}</div>
		<div class="card-body">
			<table class="table table-striped">
				<thead>
				<tr>
					<th>{__('ID')}</th>
					<th>{__('Name')}</th>
					<th>{__('Installed')}</th>
					<th>{__('Subscription')}</th>
				</tr>
				</thead>
	            {foreach $licenses->getActive() as $license}
					<tr>
						<td>{$license->getID()}</td>
						<td>{$license->getName()}</td>
						<td><i class="far {if $license->isInstalled()}fa-check-circle{else}fa-circle{/if}"></i></td>
						<td>
							{$licData = $license->getLicense()}
	                        {__($licData->getType())}
							{if $licData->getSubscription() !== null}, {__('Valid until %s', $licData->getSubscription()->getValidUntil()->format('d.m.Y'))}
							{elseif $licData->getValidUntil() !== null}, {__('Valid until %s', $licData->getValidUntil()->format('d.m.Y'))}
							{/if}
						</td>
					</tr>
	            {/foreach}
			</table>
		</div>
	</div>
	<div class="card">
		<div class="card-header">{__('Unbound licenses')}</div>
		<div class="card-body">
			<table class="table table-striped">
				<thead>
				<tr>
					<th>{__('ID')}</th>
					<th>{__('Name')}</th>
					<th>{__('Developer')}</th>
					<th>{__('Links')}</th>
				</tr>
				</thead>
	            {foreach $licenses->getUnbound() as $license}
					<tr>
						<td>{$license->getID()}</td>
						<td>{$license->getName()}</td>
						<td><a href="{$license->getVendor()->getHref()}" rel="noopener">{$license->getVendor()->getName()}</a></td>
						<td>
							{foreach $license->getLinks() as $link}
								<a class="btn btn-default" href="{$link->getHref()}" title="{__($link->getRel())}">{__($link->getRel())}</a>
							{/foreach}
						</td>
					</tr>
	            {/foreach}
			</table>
		</div>
	</div>
</div>

{include file='tpl_inc/dbupdater_scripts.tpl'}
{include file='tpl_inc/footer.tpl'}
