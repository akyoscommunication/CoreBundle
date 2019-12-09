class SubmitForm {
    static init() {
        $('#submitForms').click(function () {
            let valid = false;
            $('form:not(".not-submit")').each(function (i) {
                $.post(
                    $(this).attr('action'),
                    $(this).serialize(),
                    function (res) {
                        console.log(res);
                        if (res === 'valid') {
                            valid = true;
                        }
                        if (i+1 === $('form:not(".not-submit")').length) {
                            console.log(valid);
                            if (valid === true) {
                                window.location.reload();
                            }
                        }
                    }
                );
            });
        });
    }
}

export default SubmitForm