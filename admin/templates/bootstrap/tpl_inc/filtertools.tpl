{if !isset($cParam_arr)}
    {assign var=cParam_arr value=[]}
{/if}

<div class="toolbar well well-sm">
    <div class="container-fluid toolbar-container">
        <form method="get">
            {foreach $cParam_arr as $cParamName => $cParamValue}
                <input type="hidden" name="{$cParamName}" value="{$cParamValue}">
            {/foreach}
            <div class="toolbar-row">
                <div class="col-md-11 toolbar-col">
                    <div class="toolbar-row">
                        {foreach $oFilter->getFields() as $oField}
                            {if $oField->getType() === 'text'}
                                {if $oField->isCustomTestOp()}
                                    <div class="col-md-2 toolbar-col">
                                        <label for="{$oFilter->getId()}_{$oField->getId()}">{$oField->getTitle()}</label>
                                        <select class="form-control"
                                                name="{$oFilter->getId()}_{$oField->getId()}_op"
                                                id="{$oFilter->getId()}_{$oField->getId()}_op">
                                            {if $oField->getDataType() == 0}
                                                <option value="1"{if $oField->getTestOp() == 1} selected{/if}>{__('contains')}</option>
                                                <option value="2"{if $oField->getTestOp() == 2} selected{/if}>{__('startsWith')}</option>
                                                <option value="3"{if $oField->getTestOp() == 3} selected{/if}>{__('endsWith')}</option>
                                                <option value="4"{if $oField->getTestOp() == 4} selected{/if}>{__('isEqual')}</option>
                                                <option value="9"{if $oField->getTestOp() == 9} selected{/if}>{__('isNotEqual')}</option>
                                            {elseif $oField->getDataType() == 1}
                                                <option value="4"{if $oField->getTestOp() == 4} selected{/if}>=</option>
                                                <option value="9"{if $oField->getTestOp() == 9} selected{/if}>!=</option>
                                                <option value="5"{if $oField->getTestOp() == 5} selected{/if}>&lt;</option>
                                                <option value="6"{if $oField->getTestOp() == 6} selected{/if}>&gt;</option>
                                                <option value="7"{if $oField->getTestOp() == 7} selected{/if}>&lt;=</option>
                                                <option value="8"{if $oField->getTestOp() == 8} selected{/if}>&gt;=</option>
                                            {/if}
                                        </select>
                                    </div>
                                    <div class="col-md-2 toolbar-col">
                                        <label>&nbsp;</label>
                                {else}
                                    <div class="col-md-2 toolbar-col">
                                        <label for="{$oFilter->getId()}_{$oField->getId()}">{$oField->getTitle()}</label>
                                {/if}
                                        <input type="{if $oField->getDataType() == 1}number{else}text{/if}"
                                               class="form-control" name="{$oFilter->getId()}_{$oField->getId()}"
                                               id="{$oFilter->getId()}_{$oField->getId()}"
                                               value="{$oField->getValue()}" placeholder="{$oField->getTitle()}"
                                               {if $oField->getTitleLong() !== ''}data-toggle="tooltip"
                                               data-placement="bottom" title="{$oField->getTitleLong()}"{/if}>
                                    </div>
                            {elseif $oField->getType() === 'select'}
                                <div class="col-md-2 toolbar-col">
                                    <label for="{$oFilter->getId()}_{$oField->getId()}">{$oField->getTitle()}</label>
                                    <select class="form-control"
                                            name="{$oFilter->getId()}_{$oField->getId()}"
                                            id="{$oFilter->getId()}_{$oField->getId()}"
                                            {if $oField->getTitleLong() !== ''}data-toggle="tooltip"
                                            data-placement="bottom" title="{$oField->getTitleLong()}"{/if}
                                            {if $oField->bReloadOnChange}onchange="$('#{$oFilter->getId()}_btn_filter').click()"{/if}>
                                        {foreach $oField->getOptions() as $i => $oOption}
                                            <option value="{$i}"{if $i == (int)$oField->getValue()} selected{/if}>
                                                {$oOption->getTitle()}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                            {elseif $oField->getType() === 'daterange'}
                                <div class="col-md-3 toolbar-col">
                                    <label for="{$oFilter->getId()}_{$oField->getId()}">{__($oField->getTitle())}</label>
                                    <input type="text"  class="form-control"
                                           name="{$oFilter->getId()}_{$oField->getId()}"
                                           id="{$oFilter->getId()}_{$oField->getId()}">
                                    <script>
                                        $(function () {
                                            var $datepicker = $('#{$oFilter->getId()}_{$oField->getId()}');
                                            $datepicker.daterangepicker({
                                                locale: {
                                                    format: '{__('datepickerFormat')}',
                                                    separator: '{__('datepickerSeparator')}',
                                                    applyLabel: '{__('apply')}',
                                                    cancelLabel: '{__('cancel')}',
                                                    customRangeLabel: '{__('datepickerCustom')}',
                                                    daysOfWeek: ['{__('sundayShort')}', '{__('mondayShort')}',
                                                        '{__('tuesdayShort')}', '{__('wednesdayShort')}',
                                                        '{__('thursdayShort')}', '{__('fridayShort')}',
                                                        '{__('saturdayShort')}'
                                                    ],
                                                    monthNames: ['{__('january')}', '{__('february')}', '{__('march')}',
                                                        '{__('april')}', '{__('may')}', '{__('june')}', '{__('july')}',
                                                        '{__('august')}', '{__('september')}', '{__('october')}',
                                                        '{__('november')}', '{__('december')}'
                                                    ],
                                                    firstDay: 1
                                                },
                                                alwaysShowCalendars: true,
                                                autoUpdateInput: false,
                                                autoApply: false,
                                                parentEl: 'html',
                                                applyClass: 'btn btn-primary',
                                                cancelClass: 'btn btn-danger',
                                                ranges: {
                                                    '{__('datepickerToday')}': [moment(), moment()],
                                                    '{__('datepickerYesterday')}': [
                                                        moment().subtract(1, 'days'),
                                                        moment().subtract(1, 'days')
                                                    ],
                                                    '{__('datepickerThisWeek')}': [
                                                        moment().startOf('week').add(1, 'day'),
                                                        moment().endOf('week').add(1, 'day')
                                                    ],
                                                    '{__('datepickerLastWeek')}': [
                                                        moment().subtract(1, 'week').startOf('week').add(1, 'day'),
                                                        moment().subtract(1, 'week').endOf('week').add(1, 'day')
                                                    ],
                                                    '{__('datepickerThisMonth')}': [
                                                        moment().startOf('month'),
                                                        moment().endOf('month')
                                                    ],
                                                    '{__('datepickerLastMonth')}': [
                                                        moment().subtract(1, 'month').startOf('month'),
                                                        moment().subtract(1, 'month').endOf('month')
                                                    ],
                                                    '{__('datepickerThisYear')}': [moment().startOf('year'), moment().endOf('year')],
                                                    '{__('datepickerLastYear')}': [
                                                        moment().subtract(1, 'year').startOf('year'),
                                                        moment().subtract(1, 'year').endOf('year')
                                                    ]
                                                }
                                            });
                                            $datepicker.on('apply.daterangepicker', function(ev, picker) {
                                                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - '
                                                        + picker.endDate.format('DD.MM.YYYY'));
                                            });
                                            var curDateRange = '{$oField->getValue()}'.split(' - ');
                                            if (curDateRange.length === 2) {
                                                $datepicker.val(curDateRange[0] + ' - ' + curDateRange[1]);
                                                $datepicker.data('daterangepicker').setStartDate(curDateRange[0]);
                                                $datepicker.data('daterangepicker').setEndDate(curDateRange[1]);
                                            }
                                        });
                                    </script>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
                <div class="col-md-1 toolbar-col tright">
                    <label>&nbsp;</label>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" name="action" value="{$oFilter->getId()}_filter"
                                title="Filter anwenden" id="{$oFilter->getId()}_btn_filter">
                            <i class="fa fa-search"></i>
                        </button>
                        <button type="submit" class="btn btn-default" name="action" value="{$oFilter->getId()}_resetfilter"
                                title="Filter zurücksetzen" id="{$oFilter->getId()}_btn_resetfilter">
                            <i class="fa fa-eraser"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
