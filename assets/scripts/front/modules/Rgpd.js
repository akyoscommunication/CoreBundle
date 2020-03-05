import '../../../tarteaucitronjs/tarteaucitron';

class Rgpd {
    static init() {
        const akyCookiesgestion = $('#akyCookiesGestion');
        if(akyCookiesgestion) {
            akyCookiesgestion.removeClass('hidden');
            $(window).on('scroll', function () {
                if ($(window).scrollTop() + $(window).height() == $(document).height()) {
                    akyCookiesgestion.addClass('active');
                } else {
                    akyCookiesgestion.removeClass('active');
                }
            });
            akyCookiesgestion.click(function() {
                tarteaucitron.userInterface.openPanel();
            })
            
            if(akyCookiesgestion.data('ua').length) {
                tarteaucitron.user.analyticsUa = akyCookiesgestion.data('ua');
                tarteaucitron.user.analyticsMore = function () { /* add here your optionnal ga.push() */ };
                (tarteaucitron.job = tarteaucitron.job || []).push("analytics");
            }
    
            if(akyCookiesgestion.data('gtm').length) {
                tarteaucitron.user.googletagmanagerId = akyCookiesgestion.data('gtm');
                (tarteaucitron.job = tarteaucitron.job || []).push("googletagmanager");
            }
        }
    }
}

export default Rgpd