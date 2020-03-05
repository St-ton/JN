{include file='tpl_inc/seite_header.tpl' cTitel=__('order') cBeschreibung=__('pageDesc') cDokuURL=__('docURL')}
{$select = $select|default:true}
{$edit = $edit|default:true}
{$delete = $delete|default:false}
{$save = $save|default:false}
{$activate = $activate|default:false}
{$enable = $enable|default:false}
{$disable = $disable|default:false}
{$action = $action|default:($shopURL|cat:$smarty.server.PHP_SELF)}
{$search = $search|default:false}
{$searchQuery = $searchQuery|default:null}
{$pagination = $pagination|default:null}
{$method = $method|default:'post'}

<div id="content">
    {if $items->count() > 0}
		<div class="card">
			<div class="card-header">
				<div class="subheading1">{__('modelHeader')}</div>
				<hr class="mb-n3">
			</div>
			<div class="card-body">
				{if $search === true}
					<div class="search-toolbar mb-3">
						<form name="datamodel" method="post" action="{$action}">
	                        {$jtl_token}
							<input type="hidden" name="Suche" value="1" />
							<div class="form-row">
								<label class="col-sm-auto col-form-label" for="modelsearch">{__('search')}:</label>
								<div class="col-sm-auto mb-2">
									<input class="form-control" name="cSuche" type="text" value="{if $searchQuery !== null}{$searchQuery}{/if}" id="modelsearch" />
								</div>
								<span class="col-sm-auto">
	                                <button name="submitSuche" type="submit" class="btn btn-primary btn-block"><i class="fal fa-search"></i></button>
	                            </span>
							</div>
						</form>
					</div>
                {/if}
                {if $searchQuery !== null}
	                {$params = ['cSuche'=>$searchQuery]}
                {else}
	                {$params = null}
                {/if}
				{if $pagination !== null}
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination cParam_arr=$params}
                {/if}
				<form name="modelform" method="{$method}" action="{$action}">
                    {$jtl_token}
                    {if $search !== null}
						<input type="hidden" name="cSuche" value="{$search}" />
                    {/if}
					{$first = $items->first()}
					<div class="table-responsive">
						<table class="table table-striped table-align-top">
							<thead>
							<tr>
								{if $select === true}
									<th class="check">&nbsp;</th>
                                {/if}
								{foreach $first->getAttributes() as $attr}
                                    {$type = $attr->getDataType()}
                                    {if $attr->getInputConfig()->isHidden() === false && (strpos($type, "\\") === false || !class_exists($type))}
										<th>{__({$attr->getName()})}</th>
                                    {/if}
								{/foreach}
								{if $edit === true}
									<th class="text-center">&nbsp;</th>
								{/if}
							</tr>
							</thead>
							<tbody>
							{foreach $items as $item}
								<tr>
	                                {if $select === true}
										<td class="check">
											<div class="custom-control custom-checkbox">
												<input class="custom-control-input" name="mid[{$item@index}]" type="checkbox" value="{$item->getId()}" id="mid-{$item->getId()}" />
												<label class="custom-control-label" for="mid-{$item->getId()}"></label>
											</div>
										</td>
									{/if}
									{foreach $item->getAttributes() as $attr}
										{$type = $attr->getDataType()}
                                        {if $attr->getInputConfig()->isHidden() === false && (strpos($type, "\\") === false || !class_exists($type))}
											<td>{$item->getAttribValue($attr->getName())}</td>
										{/if}
									{/foreach}
	                                {if $edit === true}
										<td class="text-center">
											<div class="btn-group">
												<a href="{$action}?action=detail&id={$item->getId()}&token={$smarty.session.jtl_token}"
												   class="btn btn-link px-2"
												   title="{__('modify')}"
												   data-toggle="tooltip">
	                                                            <span class="icon-hover">
	                                                                <span class="fal fa-edit"></span>
	                                                                <span class="fas fa-edit"></span>
	                                                            </span>
												</a>
											</div>
										</td>
	                                {/if}
								</tr>
	                        {/foreach}
                            </tbody>
                        </table>
					</div>
					<div class="save-wrapper">
						<div class="row">
                            {if $select === true}
	                            <div class="col-sm-6 col-xl-auto text-left">
									<div class="custom-control custom-checkbox">
										<input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
										<label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
									</div>
								</div>
							{/if}
                            {if $delete === true}
								<div class="ml-auto col-sm-6 col-xl-auto">
									<button name="delete" type="submit" value="{__('delete')}" class="btn btn-danger btn-block">
										<i class="fas fa-trash-alt"></i> {__('delete')}
									</button>
								</div>
							{/if}
                            {if $activate === true}
								<div class="ml-auto col-sm-6 col-xl-auto">
									<button name="activate" type="submit" value="{__('activate')}" class="btn btn-primary btn-block">
										<i class="fas fa-thumbs-up"></i> {__('activate')}
									</button>
								</div>
							{/if}
                            {if $save === true}
								<div class="ml-auto col-sm-6 col-xl-auto">
									<button name="save" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
										<i class="fal fa-save"></i> {__('save')}
									</button>
								</div>
							{/if}
                            {if $disable === true}
								<div class="ml-auto col-sm-6 col-xl-auto">
									<button name="disable" type="submit" value="{__('disable')}" class="btn btn-warning btn-block">
										<i class="fa fa-close"></i> {__('disable')}
									</button>
								</div>
							{/if}
                            {if $enable === true}
								<div class="ml-auto col-sm-6 col-xl-auto">
									<button name="enable" type="submit" value="{__('enable')}" class="btn btn-primary btn-block">
										<i class="fa fa-check"></i> {__('enable')}
									</button>
								</div>
							{/if}
						</div>
					</div>
				</form>
			</div>
		</div>
    {else}
		<div class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noDataAvailable')}</div>
    {/if}
</div>
