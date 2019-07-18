/**
 * Created by CaryYe on 2019/7/9.
 */
function exchange(obj) {
    if ($("button[osid][disabled]").length >= 1) {
        var target_osid = $("button[osid][disabled]:first").attr("osid");
        var osid = $(obj).attr("osid");
        $("button[osid]").removeAttr("disabled");
        $.post("/admin/schedule/exchange", {id1: osid, id2: target_osid}, function(data) {
            if (parseInt(data["code"]) == 10001) {
                window.location.reload();
            } else {
                alert('cc');
                alert(data["message"]);
            }
        }, "json");
    } else {
        $(obj).attr("disabled", "disabled");
    }
}