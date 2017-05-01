/**
 * 数据库备份操作
 */

$(function () {
    function showpocess(url, data) {
        var process = $('#process').modal('show').on('shown.bs.modal', function () {
            var body = process.find('.modal-body');
            var ajax = new XMLHttpRequest();
            if (data) {
                ajax.open('POST', url);
            } else {
                ajax.open('GET', url);
            }
            ajax.addEventListener('readystatechange', function () {
                if (ajax.status == 200) {
                    if (ajax.readyState == 3) {
                        body.html(ajax.responseText);
                    } else if (ajax.readyState == 4) {
                        process.find('[data-dismiss="modal"]').attr('disabled', false);
                        process.find('.btn.btn-secondary').text('关闭窗口');
                    }
                }
            });
            ajax.send(data);
            process.find('[data-dismiss="modal"]').attr('disabled', true);
            process.find('.btn.btn-secondary').text('操作中');
        }).on('hidden.bs.modal', function () {
            location.reload(true);
        });;
    }
    $('[data-url-all]').on('click', function () {
        url = this.dataset.urlAll;
        showpocess(url);
    });
    $('[data-url').on('click', function () {
        url = this.dataset.url;
        var form=new FormData(document.querySelector('form'));
        form.append('name','dxkite');
        console.log(form);
        showpocess(url,form);
    });
});