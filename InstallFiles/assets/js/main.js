import 'bootstrap'

import Utils from './common/utils'
import Sliders from './components/Sliders'

import './../css/main.scss';

$(document).ready(() => {
    Utils.init();
    Sliders.init();
});
