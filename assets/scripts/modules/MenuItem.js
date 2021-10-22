import {jsonFetch} from "../functions/api";

let resultMenuItem = [];
let isParent = null;

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
            update: function (event, ui) {
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
            const lang = $(this).parents('.aky-menuitem-el').data('lang');

            fetch(lang + '/admin/menu/item/' + data + '/edit/' + menu)
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
                                    url: lang + '/admin/menu/item/' + data + '/edit/' + menu,
                                    data: $('#modalEditMenuitem > form[name=menu_item]').serialize(),
                                    success: function (res) {
                                        // console.log(res, 'success');
                                        if (res === 'valid') {
                                            window.location.reload();
                                        } else {
                                            // TODO : error
                                        }
                                    },
                                    error: function (er) {
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
        const _this = this;
        const btn = document.querySelector('#editMenuPosition');
        if (btn) {
            btn.addEventListener('click', async function (e) {
                e.preventDefault();
                const endpoint = this.getAttribute('data-endpoint');
                $('#menuItemsForms > .aky-menuitem-parent > .aky-menuitem').each(function (i) {
                    const result = _this.subItem($(this));
                    resultMenuItem.push(result);
                });
                await jsonFetch(endpoint, {
                    method: 'POST',
                    body: JSON.stringify({
                        resultMenuItem
                    })
                }).then(r => console.log(r))
            });
        }
    }

    static subItem(item) {
        const _this = this;
        let array = {};
        array['parent'] = item.data('id');

        let arrayChild = {};
        const children = item.children('.aky-menuitem-child').children('.aky-menuitem');

        if (children.length > 0) {
            children.each(function (subIndex) {
                let arrayResult = _this.subItem($(this));
                arrayChild[subIndex] = arrayResult;
            });
        }

        array['childs'] = arrayChild;

        return array;
    }
}

export default MenuItem