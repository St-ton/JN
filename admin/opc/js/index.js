import "./gui.js";
import {Editor} from "./Editor.js";

window.opc = new Editor(JSON.parse(window.editorConfig.innerText));