<div id="unbound-licenses">
    {if isset($bindErrorMessage)}
        <div class="alert alert-danger">
            {__($bindErrorMessage)}
        </div>
    {/if}
    <div class="card">
        <div class="card-header">
            {__('Unbound licenses')}
            <hr class="mb-n3">
        </div>
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
                            {if $link->getRel() === 'setBinding'}
                                {form class='set-binding-form' style='display:inline-block'}
                                    <input type="hidden" name="action" value="setbinding">
                                    <input type="hidden" name="url" value="{$link->getHref()}">
                                    <input type="hidden" name="method" value="{$link->getMethod()|default:'POST'}">
                                    <button type="submit" class="btn btn-default set-binding" data-link="{$link->getHref()}" href="#" title="{__($link->getRel())}">
                                        <i class="fa fa-link"></i> {__($link->getRel())}
                                    </button>
                                {/form}
                            {else}
                                <a class="btn btn-default" href="{$link->getHref()}" title="{__($link->getRel())}">
                                    {if $link->getRel() === 'itemDetails'}<i class="fa fa-info"></i> {/if}
                                    {__($link->getRel())}
                                </a>
                            {/if}
                        {/foreach}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>
