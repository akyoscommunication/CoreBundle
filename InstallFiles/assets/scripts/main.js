// Import everything from autoload
import './autoload/_bootstrap';
import utils from "./common/utils";
import {WOW} from "wowjs/dist/wow.min";
import Sliders from "./modules/Sliders";

const wow = new WOW({
    live: false
});


// Load Events
$(document).ready(() => {
    setTimeout(() => {
        Sliders.init();
        utils.init();
    }, 200);
});
