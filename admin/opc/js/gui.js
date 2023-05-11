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

export function enableTooltips(elms)
{
    for(const elm of elms) {
        enableTooltip(elm);
    }
}