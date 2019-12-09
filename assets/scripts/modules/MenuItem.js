class MenuItem {
    static init() {
        this.changePosition();
        this.ajaxModal();
        this.ajaxMenuForm();
    }
    static changePosition() {
        const sort = $(".aky-menuitem-connectedSortable");
        sort.sortable({
            connectWith: ".aky-menuitem-connectedSortable",
            update: function(event, ui) {
                $('.aky-menuitem-parent > .aky-menuitem').each(function (i) {
                    $(this).attr('data-position', i);
                });
                $('.aky-menuitem-child > .aky-menuitem').each(function (i) {
                    $(this).attr('data-position', i);
                });
            },
        });
    }
    static ajaxModal() {
        $('.btn-modal-menuitem').click(function (e) {
            e.preventDefault();
            const data = $(this).parents('.aky-menuitem-el').data('id');
            const menu = $(this).parents('.aky-menuitem-el').data('menu');

            fetch('/admin/menu/item/'+data+'/edit/'+menu)
                .then(function (res) {
                    return res.text()
                        .then(function (response) {
                            const modal = $('#modalEditMenuitem');
                            modal.html(response);
                            modal.attr('data-id', data);
                            // $('#modalEditMenuitem > form').attr('name', 'menu_item_ajax');
                        })
                        .then(function () {
                            $('#modalEditMenuitem .btn-update-item').click(function (e) {
                                e.preventDefault();
                                const data = $('#modalEditMenuitem').data('id');

                                $.ajax({
                                    method: 'POST',
                                    url: '/admin/menu/item/'+data+'/edit/'+menu,
                                    data: $('#modalEditMenuitem > form[name=menu_item]').serialize(),
                                    success: function (res) {
                                        // console.log(res, 'success');
                                        if ( res === 'valid'){
                                            window.location.reload();
                                        } else {
                                            // TODO : error
                                        }
                                    },
                                    error: function(er) {
                                        console.log(er, 'error');
                                    }
                                });
                            });
                        });
                })
        });

        // $('#modal').on('hidden.bs.modal', function (e) {
        //     $('#modalEditMenuitem').html('<img class="loader" border="0" src="http://www.pictureshack.us/images/16942_Preloader_10.gif" alt="loader" width="128" height="128">');
        // })
    }
    static ajaxMenuForm() {
        $('#editMenuPosition').click(function (e) {
            e.preventDefault();
            let arrayMenuItem = [];
            $('#menuItemsForms > .aky-menuitem-parent > .aky-menuitem').each(function (i) {
                let arrayParent = {};
                let arrayChild = {};
                $(this).children('.aky-menuitem-child').children('.aky-menuitem').each(function (subIndex) {
                    arrayChild[subIndex] = $(this).data('id');
                });
                arrayParent['parent'] = $(this).children('.aky-menuitem-el').data('id');
                arrayParent['childs'] = arrayChild;
                arrayMenuItem.push(arrayParent);
            });
            $.ajax({
                method: 'POST',
                url: '/admin/menu/'+$('#menuItemsForms').data('menu')+'/item/change-position',
                data: {
                    data: arrayMenuItem,
                },
                success: function (res) {
                    console.log(res, 'success');
                    if ( res === 'valid'){
                        window.location.reload();
                    } else {
                        // TODO : error
                    }
                },
                error: function(er) {
                    console.log(er, 'error');
                }
            });
        });
    }
}

export default MenuItem