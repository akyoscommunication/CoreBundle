class SubmitForm {
    static init() {
        var that = this;
        $('#submitForms').click( function () {
            const forms = [...document.querySelectorAll('form:not(.not-submit)')];
            console.log(forms);
            forms.reduce((previous, next) => previous.then(() => { return that.post(next); }), that.initSubmit())
                .then(() => {})
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
            const formData = new FormData(form);
            $.ajax({
                url : $(form).attr('action'),
                type: $(form).attr('method'),
                data : formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    console.log(form);
                    console.log(res);
                    resolve(res);
                },
                error: function(xhr, desc, err) {
                    alert(err)
                }
            });
        });
    }
}

export default SubmitForm