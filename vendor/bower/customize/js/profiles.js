$(document).ready(function() {
    $("#multipleDelete").click(function () {
        if(!confirm("确认删除选中人员？")) return false;
        var keys = $("#w0").yiiGridView("getSelectedRows");
        console.log(keys);
        $.post('/admin/user/deleteall',
            {
                arr_id: keys,

            });
        window.location.reload();

    })
})