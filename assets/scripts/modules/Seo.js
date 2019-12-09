import Toast from "./Toast";
class Seo {
    static init() {
        $('form[name=seo]').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                method: 'POST',
                url: '/admin/seo/submit/'+$(this).parents('.content').data('type')+'/'+$(this).parents('.content').data('typeid'),
                data: $(this).serialize(),
                success: function (res) {
                    console.log(res, 'success');
                    if ( res === 'valid'){
                        window.location.reload();
                    } else {
                        // TODO : error
                        new Toast('Problème de..', 'error', 'oui', 600 );
                    }
                },
                error: function(er) {
                    console.log(er, 'error');
                }
            })
        })
    }
}

export default Seo