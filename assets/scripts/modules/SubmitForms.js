class SubmitForm {
    static init() {
        var that = this;
        $('#submitForms').click( function () {
            const forms = [...document.querySelectorAll('form:not(.not-submit)')];
            console.log(forms);
            forms.reduce((previous, next) => previous.then(() => { return that.post(next); }), that.initSubmit())
                .then(() => {window.location.reload();})
                .catch( (err) => {console.log(err);});
        });
    }

    static initSubmit() {
        return new Promise( (resolve, reject) => {
            $('body').append('<div class="submitLoader"><i class="fas fa-spinner"></i></div>');
            resolve();
        });
    }

    static post(form) {
        return new Promise( (resolve, reject) => {
            $.post(
                $(form).attr('action'),
                $(form).serialize(),
                function (res) {
                    console.log(form);
                    console.log(res);
                    resolve(res);
                }
            ).fail(function(res) {
                reject(res)
                alert(res.responseText)
            });
        });
    }
}

export default SubmitForm