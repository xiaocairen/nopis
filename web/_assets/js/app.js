
/***********************************************/
/*************   window.Modal   ****************/
/***********************************************/
window.Modal = function () {
    var alr;
    var reg = new RegExp("\\[([^\\[\\]]*?)\\]", 'igm');
    var ahtml = '<div class="modal-dialog modal-sm">'
                +'<div class="modal-content">'
                    +'<div class="modal-header">'
                        +'<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'
                        +'<h4 class="modal-title"><i class="fa fa-exclamation-circle"></i> [Title]</h4>'
                    +'</div>'
                    +'<div class="modal-body">'
                        +'<p>[Message]</p>'
                    +'</div>'
                    +'<div class="modal-footer">'
                        +'<button type="button" class="btn btn-primary ok" data-dismiss="modal">[BtnOk]</button>'
                        +'<button type="button" class="btn btn-default cancel" data-dismiss="modal">[BtnCancel]</button>'
                    +'</div>'
                +'</div>'
            +'</div>';

    var _alert = function (id, options) {
        alr = $(id);
        alr.removeClass().addClass('modal fade');
        alr.html(ahtml);
        alr.find('.ok').removeClass('btn-success').addClass('btn-primary');
        alr.find('.cancel').hide();
        _dialog(options);

        return {
            on: function (callback) {
                if (callback && callback instanceof Function) {
                    alr.find('.ok').click(function () {
                        callback(true);
                    });
                }
            }
        };
    };
    var _confirm = function (id, options) {
        alr = $(id);
        alr.removeClass().addClass('modal fade');
        alr.html(ahtml);
        alr.find('.ok').removeClass('btn-primary').addClass('btn-success');
        alr.find('.cancel').show();
        _dialog(options);

        return {
            on: function (callback) {
                if (callback && callback instanceof Function) {
                    alr.find('.ok').click(function () {
                        callback(true);
                    });
                    alr.find('.cancel').click(function () {
                        callback(false);
                    });
                }
            }
        };
    };

    var _dialog = function (options) {
        var ops = {
            msg: "提示内容",
            title: "操作提示",
            btnok: "确定",
            btncl: "取消"
        };

        $.extend(ops, options);

        var html = alr.html().replace(reg, function (node, key) {
            return {
                Title: ops.title,
                Message: ops.msg,
                BtnOk: ops.btnok,
                BtnCancel: ops.btncl
            }[key];
        });

        alr.html(html);
        alr.modal({
            width: 500,
            backdrop: 'static'
        });
    };

    return {
        alert: _alert,
        confirm: _confirm
    };

}();
// bootstrap modal can move.
var btModalMoveEx = function () {
    function moveEx($this) {
        var $head = $this.find(".modal-header"), $dialog = $this.find(".modal-dialog");
        var move = {isMove: false, left: 0, top: 0};
        $this.on("mousemove", function (e) {
            if (!move.isMove)
                return;
            $dialog.offset({top: e.pageY - move.top, left: e.pageX - move.left});
        }).on("mouseup", function () {
            move.isMove = false;
        });
        $head.on("mousedown", function (e) {
            move.isMove = true;
            var offset = $dialog.offset();
            move.left = e.pageX - offset.left;
            move.top = e.pageY - offset.top;
        });
    }

    var old = $.fn.modal;
    $.fn.modal = function (o, _r) {
        var $this = $(this);
        if (!$this.attr("isbindmv")) {
            $this.attr("isbindmv", "1");
            moveEx($this);
        }
        return old.call(this, o, _r);
    };
}();

