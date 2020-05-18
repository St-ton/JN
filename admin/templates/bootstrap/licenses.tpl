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
                    <th>{__('Type')}</th>
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
                        <td>{__($license->getLicense()->getType())}</td>
                        <td>
                            {include file='tpl_inc/licenses_license.tpl' licData=$license->getLicense()}
                        </td>
                    </tr>
                {/foreach}
            </table>
            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-12 col-xl-auto">
                        <button class="btn btn-primary" id="update-all"><i class="fas fa-refresh"></i> {__('Update all')}</button>
                    </div>
                </div>
            </div>
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
        const showUpdateAll = function () {
            const updateBTN = $('#update-all');
            updateBTN.attr('disabled', false);
            updateBTN.find('i').removeClass('fa-spin');
        };
        const hideUpdateAll = function () {
            const updateBTN = $('#update-all');
            updateBTN.attr('disabled', true);
            updateBTN.find('i').addClass('fa-spin');
        }
        var formCount = 0,
            done = 0;
        $('#active-licenses').on('submit', '.update-item-form', function (e) {
            const updateBTN = $(e.target).find('.update-item');
            updateBTN.attr('disabled', true);
            updateBTN.find('i').addClass('fa-spin');
            $.ajax({
                method: 'POST',
                url: '{$shopURL}/admin/licenses.php',
                data: $(e.target).serialize()
            }).done(function (r) {
                let result = JSON.parse(r);
                if (result.id && result.html) {
                    let itemID = '#' + result.id;
                    if (result.action === 'update') {
                        itemID = '#license-item-' + result.id;
                    }
                    $(itemID).replaceWith(result.html);
                    updateBTN.attr('disabled', false);
                    updateBTN.find('i').removeClass('fa-spin');
                }
                ++done;
                if (formCount > 0 && formCount === done) {
                    showUpdateAll();
                }
            });
            return false;
        });
        $('#active-licenses').on('click', '#update-all', function (e) {
            hideUpdateAll();
            done = 0;
            const forms = $('#active-licenses .update-item-form');
            formCount = forms.length;
            forms.submit();
        });
    });
</script>
{include file='tpl_inc/dbupdater_scripts.tpl'}
{include file='tpl_inc/footer.tpl'}
