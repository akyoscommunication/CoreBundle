// import external dependencies
import 'jquery';

import Rgpd from "./modules/Rgpd";
import LazyLoad from "../../../../FileManagerBundle/assets/scripts/modules/lazyLoad";

class Front {
	static init() {
		LazyLoad.init();
		console.log('changed');
		Rgpd.init();
	}
}

jQuery(document).ready(function () {
	Front.init();
});
