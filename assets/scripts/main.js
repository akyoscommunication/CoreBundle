// import external dependencies
import 'jquery';
// import 'bootstrap';
import 'jquery-ui/ui/widgets/sortable';

// Import everything from autoload
import "./autoload/**/*";
import initCollectionType from  './modules/collectionType';
import MenuItem from  './modules/MenuItem';

import Seo from "./modules/Seo";
import SubmitForm from "./modules/SubmitForms";

class Core {
    static init() {
        this.toggleSidebar();
        this.initSelect2();
        this.initAjaxPublished();
        MenuItem.init();
        Seo.init();
        SubmitForm.init();
        this.initCKEditorUpdates();
        initCollectionType.initDataPrototype();
    }

    static toggleSidebar() {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    }

    static initCKEditorUpdates() {
        if (typeof CKEDITOR !== 'undefined') {
            for (const instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].on('change', function(e) {
                    this.updateElement();
                })
            }
        }
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
