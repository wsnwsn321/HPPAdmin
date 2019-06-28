/** CaryYe on 2019/6/27 */
function enroll(userId, contestId) {
    if (!confirm("你确定要替此用户报名 ?")) {
        return false;
    }
    var url = "/api/enrollments";
    $.post(url, {contest_id: contestId, user_id: userId}, function(data) {
        if (parseInt(data["code"]) == 10001) {
            window.location.reload();
        } else {
            alert(data["message"]);
        }
    });
}

function cancel(enrollmentId) {
    if (!confirm("你确定从报名列表中移除该用户 ?")) {
        return false;
    }
    var deleteUrl = "/api/enrollments/"+enrollmentId;
    $.ajax({
        url: deleteUrl,
        type: 'DELETE',
        boolean: false,
        success: function(data) {
            if (parseInt(data["code"]) == 10001) {
                window.location.reload();
            } else {
                alert(data["message"]);
            }
        }
    });
}