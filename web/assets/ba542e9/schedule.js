/** CaryYe on 2019/7/5 */

/**
 * Class arrange
 * @constructor
 */
function ScheduleArrange(ec, contestId, gameId)
{
    this.enroll_count = ec;
    this.contestId = contestId;
    this.gameId = gameId;
    this.input = $("#NumberPerGroup");
    this.btn = $("#submitBtn");
    this.url = "/wap-arrange/arrange?contest_id="+contestId;
}

ScheduleArrange.prototype = {
    /**
     * Init container.
     * @returns {boolean}
     */
    init: function () {
        var _this = this;
        //this.validate();
        this.btn.click(function () {
            if (_this.validate()) {
                _this.submit();
            }
        });
        this.input.blur(function () {
            _this.validate();
        });
        /*$("button[value]").click(function(){
         _this.clearAllButtonStyle();
         $(this).removeClass("submit-input-01");
         $(this).addClass("submit-input-02");
         });*/
    },
    /**
     * @returns {boolean}
     */
    validate: function () {
        var _this = this;
        var c = parseInt(this.input.val());
        if (c <= 0 || isNaN(c)) {
            alert("必须要有分组数!");
            return false;
        }
        if (this.enroll_count / c < 2) {
            alert("分组后每组的人数必须大于 2 !");
            return false;
        }
        $("#group").val(Math.ceil(_this.enroll_count / c));

        /* var len = $("button.submit-input-02").length;
         if (len != 1) {
         alert("请正确选择赛制!");
         return false;
         } */
        return true;
    },
    /*clearAllButtonStyle: function()
     {
     $("button[value]").each(function() {
     $(this).removeClass("submit-input-02");
     $(this).addClass("submit-input-01");
     })
     },*/
    submit: function () {
        var _this = this;
        //var round_count = $("button.submit-input-02").attr("value");
        //var scheduledData = {"amount": this.input.val(), "round_count": round_count, "contest_id": this.contestId};
        var scheduledData = {"amount": this.input.val(), "preset": "group"};
        if (!confirm("确定分组?")) {
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
                    alert("分组成功");
                    var redirectUrl = $("#PathUrl2").val();
                    window.location.href = redirectUrl;
                } else {
                    alert("分组失败");
                }
            }
        });
    }
}