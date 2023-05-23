import {sleep} from "./utils.js";

export async function showModal(modal)
{
    modal.classList.add('shown');
    modal.style.opacity = 0;
    await sleep(0);
    modal.style.opacity = 1;
}

export async function showError(msg, title)
{
    if (title) {
        window.errorTitle.innerHTML = title;
    }

    window.errorAlert.innerHTML = title;
    await showModal(window.errorModal);
}

export function enableTooltip(elm)
{
    let document = elm.ownerDocument;
    let Popper   = document.defaultView.Popper;
    let title    = elm.title;
    let tooltip  = document.createElement('div');
    let arrow    = document.createElement('div');

    arrow.classList.add('tooltip-arrow');
    arrow.setAttribute('data-popper-arrow', true);
    tooltip.classList.add('opc-tooltip');
    tooltip.innerText = title;
    tooltip.append(arrow);
    document.body.append(tooltip);
    elm.title = '';

    let popper = Popper.createPopper(elm, tooltip, {modifiers: [{name: 'offset', options: {offset: [0, 8]}}]});

    elm.addEventListener('mouseenter', () => {
        tooltip.classList.add('shown');
        popper.update();
    });

    elm.addEventListener('mouseleave', () => {
        tooltip.classList.remove('shown');
    });
}

export function enableTooltips(parent = document)
{
    for(const tooltipBtn of parent.querySelectorAll('[data-tooltip]')) {
        enableTooltip(tooltipBtn);
    }
}

export function enableTabs(parent = document)
{
    for(const tabBtn of parent.querySelectorAll('[data-tab]')) {
        let tab        = document.getElementById(tabBtn.dataset.tab);
        let allTabBtns = tabBtn.closest('.tabs').querySelectorAll('[data-tab]');

        tabBtn.addEventListener('click', () => {
            for(const tabBtn of allTabBtns) {
                let tab = document.getElementById(tabBtn.dataset.tab);
                tabBtn.classList.remove('active');
                tab.classList.remove('active');
            }

            tabBtn.scrollIntoView();
            tabBtn.classList.add('active');
            tab.classList.add('active');
        });
    }
}

export function enableColorpickers(parent = document)
{
    for(const colorInput of parent.querySelectorAll('input[type=color]')) {
        let colorTextInput = document.createElement('input');
        colorTextInput.classList.add('control');
        colorTextInput.classList.add('color');
        colorInput.parentNode.insertBefore(colorTextInput, colorInput);

        colorInput.addEventListener('input', e => {
            colorTextInput.value = colorInput.value;
        });
    }
}

export function enableCollapses(parent = document)
{
    for(const collapseBtn of parent.querySelectorAll('[data-collapse]')) {
        let collapse = document.getElementById(collapseBtn.dataset.collapse);

        collapse.addEventListener('transitionend', () => {
            if (!collapseBtn.classList.contains('collapsed')) {
                collapse.style.removeProperty('height');
            }
        });

        collapseBtn.addEventListener('click', async() => {
            if (collapseBtn.classList.contains('collapsed')) {
                await collapseShow(collapse);
            } else {
                await collapseHide(collapse);
            }

            collapseBtn.classList.toggle('collapsed');
        });
    }
}

export async function collapseShow(collapse)
{
    let curHeight = collapse.offsetHeight;
    collapse.style.removeProperty('height');
    let orgHeight = collapse.offsetHeight;
    collapse.style.height = curHeight + 'px';
    await sleep(0);
    collapse.style.height = orgHeight + 'px';
}

export async function collapseHide(collapse)
{
    collapse.style.height = collapse.offsetHeight + 'px';
    await sleep(0);
    collapse.style.height = 0;
}