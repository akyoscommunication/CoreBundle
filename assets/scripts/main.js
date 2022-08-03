// import external dependencies
import 'jquery';
import 'bootstrap';
import 'jquery-ui/ui/widgets/sortable';

// Import everything from autoload
import "./autoload/**/*";
import initCollectionType from './modules/collectionType';
import MenuItem from './modules/MenuItem';

import Seo from "./modules/Seo";
import SubmitForm from "./modules/SubmitForms";
import FixCKEditor from "./modules/FixCKEditor";
import Export from "./modules/Export";

class Core {
    static init() {
        this.toggleSidebar();
        this.initSelect2();
        this.initAjaxPublished();
        this.tooltip();
        this.clearSearch();
        MenuItem.init();
        Seo.init();
        SubmitForm.init();
        FixCKEditor.init();
        Export.init();
        initCollectionType.initDataPrototype();
    }

    static toggleSidebar() {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    }

    static initSelect2() {
        $('.js-select2').each(function () {
            let options = {
                width: '100%'
            };

            const modalParent = $(this).parents('.modal');
            if (modalParent.length) {
                options.dropdownParent = modalParent
            }

            $(this).select2(options);
        })

    }

    static initAjaxPublished() {
        $('.custom-switch-published').on('change', function () {
            $(this).parents('.custom-switch-form').submit();
        });
    }

    static tooltip() {
        $('[data-toggle="tooltip"]').tooltip()
    }

    static clearSearch() {
        $('.icon-close').click(function () {
            $(this).parents('form').find('input').val('');
        })
        $('.icon-search').click(function () {
            $(this).parents('form').submit();
        })
    }
}

jQuery(document).ready(function () {
    Core.init();
});
