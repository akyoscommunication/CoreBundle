export default function modifier(selector){

    console.log('Start Modifier: '+ selector)

    $(document).on('change', selector, function() {

        const $form = $(this).closest('form');
        var data = {};
        data = $form.serialize();
        console.log('&'+$form.attr('name')+'%5B_token')
        data = data.split('&'+$form.attr('name')+'%5B_token')[0];
        $.ajax({
            url : $form.attr('action'),
            type: 'POST',
            data : data,
            success: function(html) {
                $form.replaceWith(
                    $(html).find('form[name="'+$form.attr('name')+'"]')
                );
                $('.alert-danger').remove();
            }
        });
    });
}
