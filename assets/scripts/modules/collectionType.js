class initCollectionType {

    static initDataPrototype(proto = '.collection_prototype', childs = '.card-header__title') {
        const _this = this;

        const collectionHolder = $(proto);
        collectionHolder.after('<button id="add_component" class="btn btn-outline-primary">Ajouter un champ</button>');
        const addFieldLink = $('#add_component');
        collectionHolder.data('index', collectionHolder.children('.form-group').length);

        collectionHolder.children(childs).each(function() {
            _this.addCloneFormDeleteLink($(this));
        });

        addFieldLink.on('click', function(e) {
            e.preventDefault();
            _this.addCloneForm(collectionHolder);
        });
    }

    static addCloneForm($collectionHolder) {
        // get the new index
        const index = $collectionHolder.data('index');

        // Get the data-prototype explained earlier
        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        const $prototype = $($collectionHolder.data('prototype').replace(/__name__/g, index));

        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);

        // add a delete link to the new form
        this.addCloneFormDeleteLink($prototype);

        // Display the form in the page
        $collectionHolder.append($prototype);
    }

    static addCloneFormDeleteLink($newForm) {
        const $deleteFormLink = $('<a href="#" class="btn btn-outline-danger">Supprimer ce champ <i class="fas fa-times"></i></a>');
        $newForm.append($deleteFormLink);

        $deleteFormLink.on('click', function(e) {
            e.preventDefault();

            // remove the div for the invitationProduct form
            $newForm.remove();
        });
    }
}

export default initCollectionType