class Export {
    static init() {
        console.log('Init Export')
        this.initOnChangeEntity()
        this.initOnChangeParams()
        this.initClickDownload()
        $("#akyos-step-sortable").sortable()
    }

    static initOnChangeEntity() {
        $("#step-1-entity").change(function () {
            console.log(this.value)
            $.ajax({
                method: 'POST',
                url: '/admin/export/entity/params',
                data: {
                    'entity': this.value
                }
            }).done(data => {
                $('#step-2-fields').html('');
                data.forEach(el => {
                    $('#step-2-fields').append('<option value="' + el.name + '">' + el.name + '</option>')
                })
                $('#akyos-step-2').attr('hidden', false);
            })
        })
    }

    static initOnChangeParams() {
        $("#step-2-fields").change(function () {
            let params = $(this).val()
            $('#akyos-step-sortable > li').each((index, value) => {
                if (!params.includes($(value).html())) {
                    $(value).remove()
                } else {
                    params = params.filter(e => e !== $(value).html());
                }
            })
            params.forEach(e => {
                $('#akyos-step-sortable').append('<li class="ui-state-default">' + e + '</li>')
            })
            $('#akyos-step-3').attr('hidden', false)
            $('#akyos-step-4').attr('hidden', false)
        })
    }

    static initClickDownload() {
        $("#akyos-step-dl").click(function () {

            let rows = []
            $('#akyos-step-sortable > li').each((index, value) => {
                rows.push($(value).html())
            })

            $.ajax({
                method: 'POST',
                url: '/admin/export/dl',
                data: {
                    'entity': $("#step-1-entity").val(),
                    'rows': rows
                }
            }).done(data => {

                let downloadLink = document.createElement("a");
                let fileData = ['\ufeff' + data];

                console.log(fileData)

                let blobObject = new Blob(fileData, {
                    type: "text/csv;charset=utf-8;"
                });

                let url = URL.createObjectURL(blobObject);
                downloadLink.href = url;
                downloadLink.download = "export.csv";

                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            })
        })
    }
}

export default Export