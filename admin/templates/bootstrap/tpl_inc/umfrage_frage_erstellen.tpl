<script type="text/javascript">
    var i = {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}Number({$oUmfrageFrage->oUmfrageFrageAntwort_arr|@count}) + 1{else}1{/if},
    im = {if isset($oUmfrageFrage->oUmfrageMatrixOption_arr) && $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}Number({$oUmfrageFrage->oUmfrageMatrixOption_arr|@count}) + 1{else}1{/if};

function addInputRow() {ldelim}
    var row, cell_1, input1, input2, label1, label2, paragraph;
    row = document.getElementById('formtable').insertRow(i);
    row.id = '' + i;
    cell_1 = row.insertCell(0);

    paragraph = document.createElement('p');
    paragraph.className = 'form-inline';

    input1 = document.createElement('input');
    input1.type = 'text';
    input1.name = 'cNameAntwort[]';
    input1.className = 'form-control';
    input1.id = 'cNameAntwort_' + i;

    input2 = document.createElement('input');
    input2.type = 'text';
    input2.name = 'nSortAntwort[]';
    input2.className = 'form-control';
    input2.id = 'nSortAntwort_' + i;
    input2.style.width = '40px';

    label1 = document.createElement('label');
    label1.setAttribute('for', 'cNameAntwort_' + i);
    label1.innerHTML = 'Antwort ' + i;
    label1.style.paddingRight = '5px';

    label2 = document.createElement('label');
    label2.setAttribute('for', 'nSortAntwort_' + i);
    label2.innerHTML = '  {__('umfrageQSort')}:';
    label2.style.paddingLeft = '5px';
    label2.style.paddingRight = '5px';

    paragraph.appendChild(label1);
    paragraph.appendChild(input1);

    paragraph.appendChild(label2);
    paragraph.appendChild(input2);
    cell_1.appendChild(paragraph);

    i += 1;
{rdelim}

function addInputRowOption() {ldelim}
    var row, cell_1, input1, input2, myTex1, myText2, paragraph, label1, label2;
    row = document.getElementById('formtableOption').insertRow(im);
    row.id = im;

    cell_1 = row.insertCell(0);

    label1 = document.createElement('label');
    label2 = document.createElement('label');
    label1.for = 'cNameOption_' + im;
    label1.style = 'padding-right: 5px;';
    label2.for = 'cSortOption_' + im;
    label2.style = 'padding-left: 5px; padding-right: 5px;';

    paragraph = document.createElement('p');
    paragraph.className = 'form-inline';

    input1 = document.createElement('input');
    input1.type = 'text';
    input1.name = 'cNameOption[]';
    input1.className = 'form-control';
    input1.id = 'cNameOption_' + im;

    input2 = document.createElement('input');
    input2.type = 'text';
    input2.name = 'nSortOption[]';
    input2.className = 'form-control';
    input2.id = 'nSortOption_' + im;
    input2.style.width = '40px';

    myText1 = document.createTextNode('Option ' + im);
    myText2 = document.createTextNode('  {__('umfrageQSort')}');

    label1.appendChild(myText1);
    label2.appendChild(myText2);

    paragraph.appendChild(label1);
    paragraph.appendChild(input1);

    paragraph.appendChild(label2);
    paragraph.appendChild(input2);

    cell_1.appendChild(paragraph);

    im += 1;
{rdelim}

function resetteFormtable() {ldelim}
    var table, row, cell_1;
    document.getElementById('question-options').innerHTML = "";
    table = document.createElement('table');
    table.id = "formtableOption";
    im = 1;
    row = table.insertRow(0);
    cell_1 = row.insertCell(0);
    cell_1.className = "left";
    cell_1.id = "buttonsOption";
    document.getElementById('question-options').appendChild(table);

    document.getElementById('question-answers').innerHTML = "";
    table = document.createElement('table');
    table.id = "formtable";
    i = 1;
    row = table.insertRow(0);

    cell_1 = row.insertCell(0);
    cell_1.className = "left";
    cell_1.id = "buttons";
    document.getElementById('question-answers').appendChild(table);
{rdelim}

function checkSelect(selectBox) {ldelim}
    var row, cell_1, input1, input2, myText1, myText2, button, label1, label2, paragraph;
    switch(Number(selectBox.selectedIndex))
    {ldelim}
        case 0:
            resetteFormtable();
            break;
        case 1:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;

            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addAnswer')}';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 2:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addAnswer')}';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 3:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;

            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addAnswer')}';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 4:
            resetteFormtable();
            row = document.getElementById('formtable').insertRow(i);
            row.id = i;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addAnswer')}';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 5:
            resetteFormtable();
            break;
        case 6:
            resetteFormtable();
            break;
        case 7:
            resetteFormtable();
            row = document.getElementById('formtableOption').insertRow(im);
            row.id = im;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameOption[]';
            input1.className = 'form-control field';
            input1.id = 'cNameOption_' + im;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortOption[]';
            input2.className = 'form-control field';
            input2.id = 'nSortOption_' + im;
            input2.style.width = '40px';

            myText1 = document.createTextNode('Option ' + im);
            myText2 = document.createTextNode('  {__('umfrageQSort')}');

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameOption_' + im);
            label1.innerHTML = 'Option ' + im;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortOption_' + im);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addOption')}';
            button.onclick = function() {ldelim} addInputRowOption(); {rdelim};

            document.getElementById('buttonsOption').appendChild(button);

            im += 1;

            row = document.getElementById('formtable').insertRow(i);
            row.id = i;

            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control field';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control field';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addAnswer')}';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 8:
            resetteFormtable();
            row = document.getElementById('formtableOption').insertRow(i);
            row.id = im;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameOption[]';
            input1.className = 'form-control';
            input1.id = 'cNameOption_' + im;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortOption[]';
            input2.className = 'form-control';
            input2.id = 'nSortOption_' + im;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameOption_' + i);
            label1.innerHTML = 'Option ' + im;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortOption_' + im);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addOption')}';
            button.onclick = function() {ldelim} addInputRowOption(); {rdelim};

            document.getElementById('buttonsOption').appendChild(button);

            im += 1;

            row = document.getElementById('formtable').insertRow(i);
            row.id = '' + i;
            cell_1 = row.insertCell(0);

            paragraph = document.createElement('p');
            paragraph.className = 'form-inline';

            input1 = document.createElement('input');
            input1.type = 'text';
            input1.name = 'cNameAntwort[]';
            input1.className = 'form-control';
            input1.id = 'cNameAntwort_' + i;

            input2 = document.createElement('input');
            input2.type = 'text';
            input2.name = 'nSortAntwort[]';
            input2.className = 'form-control';
            input2.id = 'nSortAntwort_' + i;
            input2.style.width = '40px';

            label1 = document.createElement('label');
            label1.setAttribute('for', 'cNameAntwort_' + i);
            label1.innerHTML = 'Antwort ' + i;
            label1.style.paddingRight = '5px';

            label2 = document.createElement('label');
            label2.setAttribute('for', 'nSortAntwort_' + i);
            label2.innerHTML = '  {__('umfrageQSort')}';
            label2.style.paddingLeft = '5px';
            label2.style.paddingRight = '5px';

            paragraph.appendChild(label1);
            paragraph.appendChild(input1);

            paragraph.appendChild(label2);
            paragraph.appendChild(input2);

            cell_1.appendChild(paragraph);

            button = document.createElement('button');
            button.type = 'button';
            button.name = 'button';
            button.setAttribute('class', 'btn btn-primary');
            button.innerHTML = '{__('addAnswer')}';
            button.onclick = function() {ldelim} addInputRow(); {rdelim};

            document.getElementById('buttons').appendChild(button);

            i += 1;
            break;
        case 9:
            resetteFormtable();
            break;
        case 10:
            resetteFormtable();
            break;
    {rdelim}
{rdelim}
</script>

<div id="page">
    <div id="content">
        {*<div id="welcome" class="post">*}
            {*<h2 class="title"><span>{__('umfrageEnterQ')}</span></h2>*}
        {*</div>*}
        {if isset($oUmfrageFrage_arr) && $oUmfrageFrage_arr|@count > 0}
        <div id="payment" class="card">
            <div id="tabellenLivesuche" class="table-responsive card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{__('umfrageQ')}</th>
                            <th>{__('umfrageQType')}</th>
                            <th>{__('sorting')}</th>
                        </tr>
                    </thead>
                    <tbody>
                    {foreach $oUmfrageFrage_arr as $oUmfrageFrageTMP}
                    <tr>
                        <td>{$oUmfrageFrageTMP->cName}</td>
                        <td>{$oUmfrageFrageTMP->cTyp}</td>
                        <td>{$oUmfrageFrageTMP->nSort}</td>
                    </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        <br />
        {/if}

        <div class="content">
            <form name="umfrage" id="umfrage" method="post" action="umfrage.php">
                {$jtl_token}
                <input type="hidden" name="umfrage" value="1" />
                <input type="hidden" name="umfrage_frage_speichern" value="1" />
                <input type="hidden" name="kUmfrage" value="{$kUmfrageTMP}" />
                {if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}
                <input type="hidden" name="umfrage_frage_edit_speichern" value="1" />
                <input type="hidden" name="kUmfrageFrage" value="{$oUmfrageFrage->kUmfrageFrage}" />
                {/if}
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('umfrageEnterQ')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('umfrageQ')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="cName" name="cName" type="text"  value="{if isset($oUmfrageFrage->cName)}{$oUmfrageFrage->cName}{/if}" />
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cTypSelect">{__('umfrageType')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select name="cTyp" id="cTypSelect" class="custom-select combo" onchange="checkSelect(this);">
                                    <option {if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}{else}selected{/if}></option>
                                    <option value="{\JTL\Survey\QuestionType::MULTI_SINGLE}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::MULTI_SINGLE}selected{/if}>{__('questionTypeMultipleChoiceOne')}</option>
                                    <option value="{\JTL\Survey\QuestionType::MULTI}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::MULTI}selected{/if}>{__('questionTypeMultipleChoiceMany')}</option>
                                    <option value="{\JTL\Survey\QuestionType::SELECT_SINGLE}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::SELECT_SINGLE}selected{/if}>{__('questionTypeSelectboxOne')}</option>
                                    <option value="{\JTL\Survey\QuestionType::SELECT_MULTI}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::SELECT_MULTI}selected{/if}>{__('questionTypeSelectboxMany')}</option>
                                    <option value="{\JTL\Survey\QuestionType::TEXT_SMALL}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::TEXT_SMALL}selected{/if}>{__('questionTypeTextSmall')}</option>
                                    <option value="{\JTL\Survey\QuestionType::TEXT_BIG}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::TEXT_BIG}selected{/if}>{__('questionTypeTextBig')}</option>
                                    <option value="{\JTL\Survey\QuestionType::MATRIX_SINGLE}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::MATRIX_SINGLE}selected{/if}>{__('questionTypeMatrixOne')}</option>
                                    <option value="{\JTL\Survey\QuestionType::MATRIX_MULTI}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::MATRIX_MULTI}selected{/if}>{__('questionTypeMatrixMany')}</option>
                                    <option value="{\JTL\Survey\QuestionType::TEXT_STATIC}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::TEXT_STATIC}selected{/if}>{__('questionTypeDivider')}</option>
                                    <option value="{\JTL\Survey\QuestionType::TEXT_PAGE_CHANGE}" {if isset($oUmfrageFrage->cTyp) && $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::TEXT_PAGE_CHANGE}selected{/if}>{__('questionTypeDividerNewPage')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nSort">{__('sorting')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <input class="form-control" id="nSort" name="nSort" type="number"  value="{if isset($oUmfrageFrage->nSort)}{$oUmfrageFrage->nSort}{/if}" />
                            </div>
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=__('pollSortHint')}
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nFreifeld">{__('umfrageQFreeField')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nFreifeld" name="nFreifeld" class="custom-select combo">
                                    <option value="1" {if isset($oUmfrageFrage->nFreifeld) && $oUmfrageFrage->nFreifeld == 1}selected{/if}>{__('yes')}</option>
                                    <option value="0" {if !isset($oUmfrageFrage->nFreifeld) || (isset($oUmfrageFrage->nFreifeld) && $oUmfrageFrage->nFreifeld == 0)}selected{/if}>{__('no')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="nNotwendig">{__('umfrageQEssential')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select id="nNotwendig" name="nNotwendig" class="custom-select combo">
                                    <option value="1" {if isset($oUmfrageFrage->nNotwendig) && $oUmfrageFrage->nNotwendig == 1}selected{/if}>{__('yes')}</option>
                                    <option value="0" {if isset($oUmfrageFrage->nNotwendig) && $oUmfrageFrage->nNotwendig == 0}selected{/if}>{__('no')}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="cBeschreibung">{__('description')}:</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <textarea id="cBeschreibung" class="ckeditor" name="cBeschreibung" rows="15" cols="60">{if isset($oUmfrageFrage->cBeschreibung)}{$oUmfrageFrage->cBeschreibung}{/if}</textarea>
                            </div>
                        </div>
                    </div>
                    <div id="question-options">
                        <table id="formtableOption" class="kundenfeld">
                            <tr>
                                <td id="buttonsOption">
                                    {if isset($oUmfrageFrage->oUmfrageMatrixOption_arr) && $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}
                                        <button name="button" type="button" value="Option hinzufügen" onclick="addInputRowOption();" class="btn btn-primary"><i class="fa fa-share"></i> {__('addOption')}</button>
                                    {/if}
                                </td>
                            </tr>
                            {if isset($oUmfrageFrage->oUmfrageMatrixOption_arr) && $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}
                                {foreach $oUmfrageFrage->oUmfrageMatrixOption_arr as $oUmfrageMatrixOption}
                                    <input name="kUmfrageMatrixOption[]" type="hidden" value="{$oUmfrageMatrixOption->kUmfrageMatrixOption}" />
                                    <tr>
                                        <td>{__('option')} {$oUmfrageMatrixOption@iteration}<input name="cNameOption[]" class="form-control" type="text" value="{$oUmfrageMatrixOption->cName}" /> {__('umfrageQSort')} <input name="nSortOption[]" class="form-control"  type="text" value="{$oUmfrageMatrixOption->nSort}" style="width: 40px;"></td>
                                    </tr>
                                {/foreach}
                            {/if}
                        </table>
                    </div>

                    <div id="question-answers">
                        <table id="formtable" class="kundenfeld table">
                            <tr>
                                <td id="buttons">
                                    {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
                                        <button class="btn btn-default" name="button" value="Antwort hinzufügen" type="button" onclick="addInputRow();"><i class="fa fa-share"></i> {__('addAnswer')}</button>
                                    {/if}
                                </td>
                            </tr>
                            {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
                                {foreach $oUmfrageFrage->oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort}
                                <tr>
                                    <td>
                                        <p class="form-inline">
                                            <input name="kUmfrageFrageAntwort[]" type="hidden" value="{$oUmfrageFrageAntwort->kUmfrageFrageAntwort}" />
                                            <label for="cNameAntwort-{$oUmfrageFrageAntwort@index}">{__('umfrageQASing')} {$oUmfrageFrageAntwort@iteration}:</label>
                                            <input class="form-control" id="cNameAntwort-{$oUmfrageFrageAntwort@index}" name="cNameAntwort[]"  type="text" value="{$oUmfrageFrageAntwort->cName}" />
                                            <label class="pr-2" for="nSortAntwort-{$oUmfrageFrageAntwort@index}">{__('umfrageQSort')}:</label>
                                            <input id="nSortAntwort-{$oUmfrageFrageAntwort@index}" name="nSortAntwort[]"  type="text" class="form-control" value="{$oUmfrageFrageAntwort->nSort}" style="width: 40px;" />
                                        </p>
                                    </td>
                                </tr>
                                {/foreach}
                            {/if}
                        </table>
                    </div>
                    <div class="card-footer save-wrapper">
                        <div class="row">
                            {if isset($oUmfrageFrageTMP->kUmfrage)}
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <a class="btn btn-outline-primary btn-block"
                                       href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&ud=1&kUmfrage={$oUmfrageFrageTMP->kUmfrage}&tab=umfrage">{__('goBack')}</a>
                                </div>
                            {/if}
                            {if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}
                                <div class="col-sm-6 col-xl-auto">
                                    <button class="btn btn-primary btn-block" name="speichern" type="submit" value="{__('save')}">
                                        {__('saveWithIcon')}
                                    </button>
                                </div>
                            {else}
                                <div class="{if !isset($oUmfrageFrageTMP->kUmfrage)}ml-auto{/if} col-sm-6 col-xl-auto">
                                    <button class="btn btn-outline-primary btn-block" name="nocheinefrage" type="submit" value="{__('umfrageAnotherQ')}">
                                        <i class="fa fa-share"></i> {__('umfrageAnotherQ')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button class="btn btn-primary btn-block" name="speichern" type="submit" value="{__('umfrageSaveQ')}">
                                        <i class="fa fa-save"></i> {__('umfrageSaveQ')}
                                    </button>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{if isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0}
<script type="text/javascript">
    var input_hidden_cTyp = document.createElement('input');
    document.getElementById("cTypSelect").disabled = true;
    input_hidden_cTyp.type = 'hidden';
    input_hidden_cTyp.name = 'cTyp';
    input_hidden_cTyp.value = '{$oUmfrageFrage->cTyp}';
    document.getElementById('umfrage').appendChild(input_hidden_cTyp);
</script>
{/if}