/**
 * Created by CaryYe on 2019/7/10.
 */

/**
 * Class arrange
 * @constructor
 */
function ScheduleSubmit(contestId)
{
    this.contestId = contestId;
    this.url = "/wap-arrange/arrange?contest_id="+contestId;
    this.btn = $("#submitSchedule");
}

ScheduleSubmit.prototype = {
    /**
     * Init container.
     * @returns {boolean}
     */
    init: function () {
        var _this = this;
        this.btn.click(function () {
            if (_this.validate()) {
                _this.submit();
            }
        });
        $("button[value]").click(function(){
            _this.clearAllButtonStyle();
            $(this).removeClass("btn-info");
            $(this).addClass("btn-success");
        });
    },
    /**
     * @returns {boolean}
     */
    validate: function () {
        var _this = this;
        var len = $("button.btn-success").length;
         if (len != 1) {
             alert("请正确选择赛制!");
             return false;
         }
         return true;
    },
    clearAllButtonStyle: function()
    {
        $("button[value]").each(function() {
            $(this).removeClass("btn-success");
            $(this).addClass("btn-info");
        })
    },
    submit: function () {
        var _this = this;
        var round_count = $("button.btn-success").attr("value");
        var scheduledData = {"round_count": round_count, "contest_id": this.contestId, "skipGroup" : 1};
        
        if (!confirm("确定开赛?")) {
            return false;
        }
        $.ajax({
            type: "POST",
            url: _this.url,
            data: scheduledData,
            cache: false,
            dataType: "json",
            success: function (r) {
                if (r.code == 10001) {
                    alert("开赛成功");
                    var redirectUrl = $("#PathUrl2").val();
                    window.location.href = redirectUrl;
                } else {
                    alert("开赛失败");
                }
            }
        });
    }
}