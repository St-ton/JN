<template id="template_dropTarget">
    <div class="opc-droptarget">
        <div class="opc-droptarget-hover">
            <img src="{$shopUrl}/admin/opc/gfx/icon-drop-target.svg" class="opc-droptarget-icon"
                 alt="Drop Target">
            <span>{__('dropPortletHere')}</span>
            <i class="opc-droptarget-info fas fa-info-circle" data-toggle="tooltip" data-placement="left"></i>
        </div>
    </div>
</template>

<template id="template_portletToolbar">
    <div id="portletToolbar" class="opc-portlet-toolbar">
        <button type="button" class="opc-toolbar-btn opc-label" id="portletLabel"></button>
        <button type="button" class="opc-toolbar-btn" id="btnConfig" title="{__('editSettings')}">
            <i class="fas fa-pen"></i>
        </button>
        <button type="button" class="opc-toolbar-btn" id="btnClone" title="{__('copySelect')}">
            <i class="far fa-clone"></i>
        </button>
        <button type="button" class="opc-toolbar-btn" id="btnBlueprint" title="{__('saveTemplate')}">
            <i class="far fa-star"></i>
        </button>
        <button type="button" class="opc-toolbar-btn" id="btnParent" title="{__('goUp')}">
            <i class="fas fa-level-up-alt"></i>
        </button>
        <button type="button" class="opc-toolbar-btn" id="btnTrash" title="{__('deleteSelect')}">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>