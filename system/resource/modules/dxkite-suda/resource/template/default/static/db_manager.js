/**
 * 数据库备份操作
 */

$(function () {
    function showpocess(url, data) {
        var process = $('#process').modal('show').on('shown.bs.modal', function () {
            var body = process.find('.modal-body');
            body.text("正在操作中...");
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
    $('[data-op-url]').on('click', function () {
        url = this.dataset.opUrl;
        var form=new FormData(document.querySelector('form'));
        showpocess(url,form);
        
    });
    $('[data-url').on('click', function () {
        url = this.dataset.url;
        showpocess(url);
    });
});