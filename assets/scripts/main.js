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
        this.initDataPrototype();
        this.initCKEditorUpdates();
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

    static initDataPrototype() {
        const collectionHolder = $('.collection_prototype');
        collectionHolder.after('<button id="add_component" class="btn btn-outline-primary">Ajouter un champ</button>');
        const addFieldLink = $('#add_component');
        collectionHolder.data('index', collectionHolder.children('.form-group').length);

        collectionHolder.children('.card-header__title').each(function() {
            initCollectionType.addCloneFormDeleteLink($(this));
        });

        addFieldLink.on('click', function(e) {
            e.preventDefault();
            initCollectionType.addCloneForm(collectionHolder);
        });
    }
}

jQuery(document).ready(function () {
    Core.init();
});
