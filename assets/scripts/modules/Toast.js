class Toast {
    constructor(title ='new Toast!', status = 'info', message = 'new Toast!', duration = 600) {
        this.title = title;
        this.status = status;
        this.message = message;
        this.duration = duration;

        this.createToast();
    }

    createToastContainer() {
        let $htmlContainer = '';

        $htmlContainer += '<div class="aky-toast-container">';
        $htmlContainer += '</div>';

        $('body').append($htmlContainer);
    }

    createToast() {
        const containerToast = $('.aky-toast-container');

        if (!(containerToast.length > 0)){
            this.createToastContainer();
        }

        let ico;

        switch (this.status) {
            case 'info':
                ico = '<i class="fas fa-info"></i>';
                break;
            case 'error':
                ico = '<i class="fas fa-exclamation"></i>';
                break;
            case 'success':
                ico = '<i class="fas fa-check"></i>';
                break;
        }

        let $html = '';

        $html += '<div class="aky-toast aky-toast-'+this.status+'">';
            $html += '<div class="aky-toast-el aky-toast-ico">';
                $html += ico;
            $html += '</div>';
            $html += '<div class="aky-toast-el aky-toast-message">';
                $html += '<b>'+this.title+'</b><br>';
                $html += this.message;
            $html += '</div>';
            $html += '<div class="aky-toast-el aky-toast-close">';
                $html += '<i class="fas fa-times"></i>';
            $html += '</div>';
        $html += '</div>';


        $('body').find('.aky-toast-container').append($html);
        this.deleteToast();
        setTimeout(function () {
            $('body').find('.aky-toast-container .aky-toast:first-child').slideDown('slow', function () {
                $(this).remove();
            });
        }, this.duration);
    }

    deleteToast() {
        const close = $('.aky-toast-close');

        close.off('click');
        close.on('click', function () {
            $(this).parents('.aky-toast').slideDown('slow', function () {
                $(this).remove();
            });
        });
    }
}

export default Toast