{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('Licenses') cBeschreibung=__('pageDesc') cDokuURL=__('https://www.jtl-software.de')}

<div id="content">
	<div class="card" id="active-licenses">
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
						<td>
							{include file='tpl_inc/licenses_referenced_item.tpl' license=$license}
						</td>
						<td>
                            {include file='tpl_inc/licenses_license.tpl' licData=$license->getLicense()}
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

<script type="text/javascript">
	$(document).ready(function () {
		$('#active-licenses').on('submit', '.update-item-form', function (e) {
			var updateBTN = $(e.target).find('.update-item');
			updateBTN.attr('disabled', true);
			updateBTN.find('i').addClass('fa-spin');
			$.ajax({
				method: 'POST',
                url: '{$shopURL}/admin/licenses.php',
				data: $(e.target).serialize()
			}).done(function (r) {
				var result = JSON.parse(r);
				console.log('got result: ', result);
				if (result.id && result.html) {
					var itemID = '#' + result.id;
					if (result.action === 'update') {
						itemID = '#license-item-' + result.id;
					}
					$(itemID).replaceWith(result.html);
					updateBTN.attr('disabled', false);
					updateBTN.find('i').removeClass('fa-spin');
				} else {
					console.log('Done!', r, result);
				}
			});
			return false;
		});
	});
</script>
{include file='tpl_inc/dbupdater_scripts.tpl'}
{include file='tpl_inc/footer.tpl'}
