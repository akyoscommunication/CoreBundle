// import external dependencies
import 'jquery';
// import 'bootstrap';
import 'jquery-ui/ui/widgets/sortable';

// Import everything from autoload
import "./autoload/**/*";
import initCollectionType from  './modules/collectionType';
import MenuItem from  './modules/MenuItem';

import Seo from "./modules/Seo";
import Rgpd from "./front/modules/Rgpd";
import SubmitForm from "./modules/SubmitForms";
import FixCKEditor from "./modules/FixCKEditor";

class Core {
    static init() {
        this.toggleSidebar();
        this.initSelect2();
        this.initAjaxPublished();
        MenuItem.init();
        Seo.init();
        Rgpd.init();
        SubmitForm.init();
        FixCKEditor.init();
        initCollectionType.initDataPrototype();
    }

    static toggleSidebar() {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    }

    static initSelect2() {
        $('.js-select2').select2({
            width: '100%'
        });
    }

    static initAjaxPublished() {
        $('.custom-switch-published').on('change', function () {
            $(this).parents('.custom-switch-form').submit();
        });
    }
}

jQuery(document).ready(function () {
    Core.init();
});
