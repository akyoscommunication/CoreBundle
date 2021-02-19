// import external dependencies
import 'jquery';

import Rgpd from "./modules/Rgpd";

class Front {
    static init() {
        Rgpd.init();
    }
}

jQuery(document).ready(function () {
    Front.init();
});
