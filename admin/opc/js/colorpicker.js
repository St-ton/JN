const colorpickerSrc = `
    <div class="colorpicker-config">
        <div class="colorpicker-satval">
            <div class="colorpicker-area">
                <div class="colorpicker-guide"></div>
            </div>
        </div>
        <div class="colorpicker-hue colorpicker-range-wrapper">
            <div class="colorpicker-range">
                <div class="colorpicker-guide"></div>
            </div>
        </div>
        <div class="colorpicker-alpha colorpicker-range-wrapper">
            <div class="colorpicker-range">
                <div class="colorpicker-guide"></div>
            </div>
        </div>
    </div>
    <div class="colorpicker-history"></div>
</div>
`;

let colorHistory = [];

function getCssColorRgba(cssColor)
{
    let div = document.createElement("div");
    div.style.color = cssColor;
    document.body.append(div);
    let rgba = getComputedStyle(div).color;
    div.remove();

    let r,g,b,a;

    if(rgba.startsWith("rgba")) {
        [r,g,b,a] = rgba.slice(5,-1).split(",").map(Number);
    }
    else if(rgba.startsWith("rgb")) {
        [r,g,b,a=1] = rgba.slice(4,-1).split(",").map(Number);
    }
    else {
        throw "unsupported color value";
    }

    return [r / 255, g / 255, b / 255, a];
}

function clamp(val, min, max)
{
    return Math.max(min, Math.min(max, val));
}

function rgbToHsv(r, g, b)
{
    let val    = Math.max(r,g,b)
    let chroma = val - Math.min(r,g,b);
    let sat    = val === 0 ? 0 : chroma / val;

    let hue = 60 * (
        chroma === 0 ? 0 :
        val === r  ? ((g-b) / chroma % 6 + 6) % 6 :
        val === g  ? (b-r) / chroma + 2 :
        val === b  ? (r-g) / chroma + 4 :
        0
    );

    return [hue, sat, val];
}

function hsvToRgb(h, s, v)
{
    let hueRed   = Math.min(Math.max(2 - h / 60, h / 60 - 4, 0), 1);
    let hueGreen = Math.max(Math.min(h / 60, 4 - h / 60, 1), 0);
    let hueBlue  = Math.max(Math.min(h / 60 - 2, 6 - h / 60, 1), 0);
    let red      = 255 * (1 - s + hueRed * s) * v | 0;
    let green    = 255 * (1 - s + hueGreen * s) * v | 0;
    let blue     = 255 * (1 - s + hueBlue * s) * v | 0;
    return [red, green, blue];
}

export function enableColorpicker(colorInput)
{
    let colorInputWrapper = document.createElement("div");
    colorInput.after(colorInputWrapper);
    colorInput.classList.add("color-input");
    colorInput.type = "text";
    colorInputWrapper.classList.add("color-input-wrapper");
    colorInputWrapper.append(colorInput);

    let colorDisplay = document.createElement("div");
    colorDisplay.classList.add("color-display");
    colorInputWrapper.append(colorDisplay);

    let colorpicker = document.createElement("div");
    colorpicker.classList.add("colorpicker");
    colorpicker.innerHTML = colorpickerSrc;
    colorInputWrapper.append(colorpicker);

    let satval      = colorpicker.querySelector(".colorpicker-satval");
    let satvalArea  = satval.querySelector(".colorpicker-area");
    let satvalGuide = satval.querySelector(".colorpicker-guide");
    let hue         = colorpicker.querySelector(".colorpicker-hue");
    let hueRange    = hue.querySelector(".colorpicker-range");
    let hueGuide    = hue.querySelector(".colorpicker-guide");
    let alpha       = colorpicker.querySelector(".colorpicker-alpha");
    let alphaRange  = alpha.querySelector(".colorpicker-range");
    let alphaGuide  = alpha.querySelector(".colorpicker-guide");
    let history     = colorpicker.querySelector(".colorpicker-history")

    updateColorFromInput();

    function updateColorFromPicker()
    {
        let style = getComputedStyle(colorpicker);
        colorDisplay.style.color = style.getPropertyValue("--full-color");
        colorInput.value = colorDisplay.style.color;
    }

    function updateColorFromInput()
    {
        let [r, g, b, a] = getCssColorRgba(colorInput.value);
        let [h, s, v]    = rgbToHsv(r, g, b);
        colorpicker.style.setProperty("--hue", h);
        colorpicker.style.setProperty("--sat", s);
        colorpicker.style.setProperty("--val", v);
        colorpicker.style.setProperty("--alpha", a);
        colorDisplay.style.color = colorInput.value;
    }

    function updatePickerPlacement()
    {
        let rect = colorpicker.getBoundingClientRect();
        let vh   = window.innerHeight;

        if(rect.bottom > vh) {
            colorpicker.classList.add("above");
        }
        else if(rect.top < 0) {
            colorpicker.classList.remove("above");
        }
    }

    function addPresetColor(color)
    {
        if(!colorHistory.includes(color)) {
            colorHistory.push(color);
        }
    }

    function updateColorHistory()
    {
        let [r, g, b, a] = getCssColorRgba(colorInput.value);

        let color = (
            (r * 255 | 0) + "," +
            (g * 255 | 0) + "," +
            (b * 255 | 0) + "," +
            a
        );

        addPresetColor(color);
    }

    function renderColorHistory()
    {
        history.innerHTML = "";

        for(let color of colorHistory) {
            let rgba   = color.split(",").map(Number);
            let preset = document.createElement("div");

            preset.classList.add("colorpicker-preset");
            preset.style.color = "rgb(" + rgba.join(',') + ")";
            preset.rgba = rgba;

            preset.onclick = () => {
                colorInput.value = preset.style.color;
                updateColorFromInput();
            };

            history.append(preset);
        }
    }

    if(colorInput.dataset.presets) {
        let list = JSON.parse(colorInput.dataset.presets);

        for(let color of list) {
            addPresetColor(color.join(","));
        }
    }

    colorpicker.onmousedown = e => {
        e.preventDefault();
    };

    colorInput.onfocus = () => {
        updateColorFromInput();
        updatePickerPlacement();
        renderColorHistory();
    };

    colorInput.onblur = () => {
        updateColorHistory();
    };

    colorInput.oninput = () => {
        updateColorFromInput();
    };

    window.addEventListener("scroll", () => {
        updatePickerPlacement();
    });

    satvalArea.onmousedown = satvalGuide.onmousedown = e => {
        function move(e)
        {
            e.preventDefault();
            let rect = satvalArea.getBoundingClientRect();
            let x    = clamp((e.clientX - rect.x) / rect.width, 0, 1);
            let y    = clamp((e.clientY - rect.y) / rect.height, 0, 1);
            let sat  = x;
            let val  = 1 - y;

            colorpicker.style.setProperty("--sat", sat);
            colorpicker.style.setProperty("--val", val);
            updateColorFromPicker();
        }

        function up(e)
        {
            e.preventDefault();
            window.removeEventListener("mousemove", move);
            window.removeEventListener("mouseup", up);
        }

        window.addEventListener("mousemove", move);
        window.addEventListener("mouseup", up);
        move(e);
    };

    hueRange.onmousedown = hueGuide.onmousedown = e => {
        function move(e)
        {
            e.preventDefault();
            let rect = hueRange.getBoundingClientRect();
            let hue  = 360 * clamp((e.clientX - rect.x) / rect.width, 0, 1);

            colorpicker.style.setProperty("--hue", hue);
            updateColorFromPicker();
        }

        function up(e)
        {
            e.preventDefault();
            window.removeEventListener("mousemove", move);
            window.removeEventListener("mouseup", up);
        }

        window.addEventListener("mousemove", move);
        window.addEventListener("mouseup", up);
        move(e);
    };

    alphaRange.onmousedown = alphaGuide.onmousedown = e => {
        function move(e)
        {
            e.preventDefault();
            let rect  = alphaRange.getBoundingClientRect();
            let alpha = clamp((e.clientX - rect.x) / rect.width, 0, 1);

            colorpicker.style.setProperty("--alpha", alpha);
            updateColorFromPicker();
        }

        function up(e)
        {
            e.preventDefault();
            window.removeEventListener("mousemove", move);
            window.removeEventListener("mouseup", up);
        }

        window.addEventListener("mousemove", move);
        window.addEventListener("mouseup", up);
        move(e);
    };
}

export function enableColorpickers(parent = document)
{
    for(const colorInput of parent.querySelectorAll('[data-colorpicker]')) {
        enableColorpicker(colorInput);
    }
}