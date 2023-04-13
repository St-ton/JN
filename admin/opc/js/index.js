import "./gui.js";
import {Editor} from "./Editor.js";

const editorConfig = JSON.parse(window.editorConfig.innerText);

window.opc = new Editor(editorConfig);

window.opc.init();