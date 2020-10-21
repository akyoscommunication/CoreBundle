import {library, dom} from '@fortawesome/fontawesome-svg-core';
// import the Facebook and Twitter icons
import {fab} from '@fortawesome/free-brands-svg-icons';
import {fas} from '@fortawesome/free-solid-svg-icons';
import {far} from '@fortawesome/free-regular-svg-icons';

export default {
    init() {
        // JavaScript to be fired on all pages
        window.FontAwesomeConfig.searchPseudoElements = true;
        library.add(fab);
        library.add(fas);
        library.add(far);
        dom.watch();
    }
};
