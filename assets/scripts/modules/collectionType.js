class initCollectionType {

    static initDataPrototype(proto = '.collection_prototype', children = '.card-header__title') {
        const _this = this;

        const collectionHolder = $(proto);
        collectionHolder.parent('.col-sm-10').addClass('d-flex flex-column');
        const buttonAddLabel = collectionHolder.data('button_add');
        const buttonDeleteLabel = collectionHolder.data('button_delete');
        collectionHolder.after('<button id="add_component" class="btn btn-sm btn-outline-primary ml-auto">' + (buttonAddLabel ? buttonAddLabel : 'Ajouter un champ ') + ' <i class="fas fa-plus"></i></button>');
        const addFieldLink = $('#add_component');
        collectionHolder.attr('data-index', collectionHolder.children('.form-group').length);

        collectionHolder.children('.form-group').each(function () {
            _this.addCloneFormDeleteLink($(this), buttonDeleteLabel);
        });

        addFieldLink.on('click', function (e) {
            e.preventDefault();
            _this.addCloneForm(collectionHolder, buttonDeleteLabel);
        });
    }

    static addCloneForm($collectionHolder, deleteLabel) {
        // get the new index
        const index = $collectionHolder.data('index');

        // Get the data-prototype explained earlier
        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        const $prototype = $($collectionHolder.data('prototype').replace(/__name__/g, index));

        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);

        // add a delete link to the new form
        this.addCloneFormDeleteLink($prototype, deleteLabel);

        // Display the form in the page
        $collectionHolder.append($prototype);

        if ($prototype.find('.js-select2').length) {
            $('.js-select2').select2({
                width: '100%'
            });
        }
    }

    static addCloneFormDeleteLink($newForm, label) {
        const $deleteFormLink = $('<a href="#" class="btn btn-sm btn-outline-danger ml-auto">' + (label ? label : 'Supprimer ce champ <i class="fas fa-times"></i>') + '</a>');
        $newForm.append($deleteFormLink);

        $deleteFormLink.on('click', function (e) {
            e.preventDefault();

            // remove the div for the invitationProduct form
            $newForm.remove();
        });
    }
}

export default initCollectionType