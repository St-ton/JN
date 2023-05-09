<div class="widget-custom-data">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th class="text-center">{__('Today')}</th>
                    <th class="text-center">{__('Yesterday')}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{__('Sales revenues')}</td>
                    <td class="text-center">{$oStatToday->fUmsatz}</td>
                    <td class="text-center">{$oStatYesterday->fUmsatz}</td>
                </tr>
                <tr>
                    <td>{__('Visitors')}</td>
                    <td class="text-center">{$oStatToday->nBesucher}</td>
                    <td class="text-center">{$oStatYesterday->nBesucher}</td>
                </tr>
                <tr>
                    <td>{__('New registered customers')}</td>
                    <td class="text-center">{$oStatToday->nNeuKunden}</td>
                    <td class="text-center">{$oStatYesterday->nNeuKunden}</td>
                </tr>
                <tr>
                    <td>{__('Number orders')}</td>
                    <td class="text-center">{$oStatToday->nAnzahlBestellung}</td>
                    <td class="text-center">{$oStatYesterday->nAnzahlBestellung}</td>
                </tr>
                <tr>
                    <td>{__('Visitors per order')}</td>
                    <td class="text-center">{$oStatToday->nBesucherProBestellung}</td>
                    <td class="text-center">{$oStatYesterday->nBesucherProBestellung}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
