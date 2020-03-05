{$description = $description|default:null}
{$enctype = $enctype|default:null}
{$action = $action|default:($shopURL|cat:$smarty.server.PHP_SELF)}
{$method = $method|default:'post'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('HEADER') cBeschreibung=$description}

<div id="content">
	<div id="settings">
		<form id="model-detail" name="model_detail" method="{$method}" action="{$action}"{if $enctype !== null} enctype="{$enctype}"{/if}>
            {$jtl_token}
			<input type="hidden" name="id" value="{$item->getId()}" />
			<div class="card">
				<div class="card-header">
					<div class="subheading1">{__('generalHeading')}</div>
					<hr class="mb-n3">
				</div>
				<div class="card-body">
                    {include file='tpl_inc/model_item.tpl'}
				</div>
			</div>
			<div class="card-footer save-wrapper">
				<div class="row">
					<div class="ml-auto col-sm-6 col-xl-auto">
						<button type="submit" name="continue" value="1" class="btn btn-outline-primary btn-block" id="save-and-continue">
							<i class="fal fa-save"></i> {__('saveAndContinue')}
						</button>
					</div>
					<div class="col-sm-6 col-xl-auto">
						<button type="submit" value="{__('save')}" class="btn btn-primary btn-block">
							<i class="far fa-save"></i> {__('save')}
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
