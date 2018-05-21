/**
 * 获得搜索参数
 * @param params
 * @returns {Object}
 */
function getSearchParams(params) {

    var searchParams = new Object();
    if (params != undefined) {
        searchParams = params;
    }
    $('.SearchForm').each(function() {
        var param = $(this).val();

        if (param == undefined)
            param = '';
        searchParams[$(this).attr('id')] = param;
    });
    return searchParams;
}

/**
 * ajax 获得年级信息
 * @constructor
 */
function OrderTypeList() {
    $.ajax({
        url: 'admin/Salesrecord/getOrderTypeJson',
        dataType: 'JSON',
        success: function(data) {
            $("#sales_ordertypename").empty();
            $("#sales_ordertypename").append("<option value=''>选择销售单类型</option>");
            $.each(data, function(i, d) {
                $("#sales_ordertypename").append('<option value="' + d.order_typename + '">' + d.order_typename + '</option>');
            });
        },
        error: function() {
            $.TeachDialog({
                content: '加载销售单类型数据失败',
            });
        }
    });
}

/**
 * ajax 获得科目信息
 * @constructor
 */
function ClueStatusList() {

    $("#clue_status").empty();
    $("#clue_status").append("<option value=''>线索状态</option>");
    $("#clue_status").append("<option value='0'>未确认</option>");
    $("#clue_status").append("<option value='1'>有效</option>");
    $("#clue_status").append("<option value='2'>无效</option>");

}



/**
 * 页面自加载
 */

$(function() {
    //加载下拉框数据
    ClueStatusList();

    var cellwidth = ($(".box-content.table-responsive").width() - 55) / 11;
    var psval = $('#datatable_salesclue').attr('data-size');
    if (psval == undefined || psval == "") {
        psval = 10;
    }
    /**
     * easyui 生成表格数据
     */
    $('#datatable_salesclue').datagrid({
        singleSelect: false, //允许选择多行
        striped: true,
        idField: 'id',
        remoteSort: false,
        collapsible: true,
        fit: false,
        url: '', //数据源路径
        loadMsg: '请等待数据载入....',
        pagination: true,
        rownumbers: true,
        fitColumns: true,
        pageSize: psval,
        pageList: [psval, psval * 2, psval * 3, psval * 4, psval * 5],
        columns: [[{
                    field: 'ck',
                    checkbox: true, //复选框
                    align: 'center',
                    sortable: true,
                    width: cellwidth,
                }, {
                    field: 'clue_student_name',
                    title: '学生姓名',
                    align: 'center',
                    width: cellwidth-20,
                    sortable: true
                }, {
                    field: 'clue_student_age',
                    title: '学生年龄',
                    align: 'center',
                    width: cellwidth-60,
                    sortable: true
                },{
                    field: 'clue_student_sex',
                    title: '学生性别',
                    align: 'center',
                    width: cellwidth-60,
                    sortable: true,
                    formatter: function(clue_student_sex) {
                        if(clue_student_sex==0){
                            return '女';
                        }else{
                            return '男';
                        }
                    }
                },{
                    field: 'clue_telephone',
                    title: '联系电话',
                    align: 'center',
                    width: cellwidth,
                    sortable: true
                },{
                    field: 'clue_last_time',
                    title: '最后联系时间',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                }, {
                    field: 'clue_last_content',
                    title: '最后联系内容',
                    align: 'center',
                    width: cellwidth+200,
                    sortable: true,
                }, {
                    field: 'clue_next_time',
                    title: '下次联系时间',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                }, {
                    field: 'clue_status',
                    title: '线索状态',
                    align: 'center',
                    width: cellwidth-30,
                    sortable: true,
                    formatter: function(clue_status) {
                        if(clue_status==0){
                            return '未确认';
                        }else if(clue_status==1){
                            return '有效';
                        }else{
                            return '无效';
                        }
                     }
                },{
                    field: 'teacher',
                    title: '咨询人员',
                    align: 'center',
                    width: cellwidth-30,
                    sortable: true,
                    formatter: function(teacher) {
                        return teacher.teacher_name;
                    }
                }]],
        onBeforeLoad: function(param) {
            param = getSearchParams(param);
        },
    });


    $('#Search').click(function() {
        $('#datatable_salesclue').datagrid({url:'admin/Salesclue/getSalesClue'});
        $('#datatable_salesclue').datagrid('reload');
    })

    /*
     *新增
     */
    $('#add').click(function(){
        var content = "";
        $.ajax({
            url: 'admin/Salesclue/addsalesclue',
            type: 'POST',
            dataType: 'HTML',//返回的数据类型
            success: function (updatemodalhtml) {
                content = updatemodalhtml;
                //弹出添加框
                $.TeachDialog({
                    modalId: null,
                    animation: null,
                    title: '添加招生线索',
                    content: content,
                    showCloseButton: true,
                    showCloseButtonName: '关闭',
                    CloseButtonAddFunc: function () {
                    },
                    otherButtons: ['添加'],
                    otherButtonStyles: ['btn-primary'],


                    clickButton: function (sender, modal, index) {
                        $.ajax({
                            url: 'admin/Salesclue/insert',
                            data: $("#addForm").serialize(),
                            type: 'POST',
                            dataType: 'JSON',
                            success: function (returnData) {
                                if (returnData.status == 0) {
                                    $.TeachDialog({
                                        content: returnData.msg,

                                    });
                                } else {
                                    modal.modal('hide');
                                    $.TeachDialog({
                                        content: returnData.msg,
                                        showCloseButton: false,
                                    });
                                    $('#datatable_salesclue').datagrid('reload');
                                }
                            },
                            error: function () {
                                $.TeachDialog({
                                    content: '系统异常，请联系管理员',
                                });
                            },

                        });

                    },
                });
            },
            error: function () {
                $.TeachDialog({
                    content: '获取数据失败，无法进行修改',
                });
                return;
            }
        });
    });
    /*
     *编辑
     */
    $('#edit').click(function() {
        var rows = $('#datatable_salesclue').datagrid('getSelections');
        if (rows.length != 1) {
            $.TeachDialog({
                content: '请选择一行数据！',
            });
            return;
        }

        var content = "";
        $.ajax({
            url: 'admin/Salesclue/updatemodal',
            data: "sales_orderid=" + rows[0].sales_orderid,
            dataType: "HTML", //返回数据类型
            type: 'POST',
            success: function(updatemodalhtml) {
                content = updatemodalhtml;
                //弹出修改框
                $.TeachDialog({
                    modalId: null,
                    animation: null,
                    title: '订单信息',
                    content: content,
                    showCloseButton: true,
                    showCloseButtonName: '关闭',
                    CloseButtonAddFunc: function() {
                    },
                    otherButtons: ['修改'],
                    otherButtonStyles: [],
                    bootstrapModalOption: {
                        backdrop: 'static'
                    },
                    largeSize: false,
                    smallSize: false,
                    dialogShow: function() {
                    },
                    dialogShown: function() {
                    },
                    dialogHide: function() {
                    },
                    dialogHidden: function() {
                    },
                    clickButton: function(sender, modal, index) {
                        $.ajax({
                            url: 'admin/Salesrecord/update',
                            data: $("#updateForm").serialize(),
                            type: 'POST',
                            dataType: 'JSON',
                            success: function(returnData) {
                                if (returnData.status == 0) {
                                    $.TeachDialog({
                                        content: returnData.msg,
                                        showCloseButton: false,
                                        CloseButtonAddFunc: function() {

                                        },
                                    });
                                } else {
                                    modal.modal('hide');

                                    $.TeachDialog({
                                        content: returnData.msg,
                                        showCloseButton: false,
                                    });
                                    $('#datatable_salesclue').datagrid('reload');
                                }
                            },
                            error: function() {
                                $.TeachDialog({
                                    content: '添加失败',
                                });
                            }
                        });
                    },
                });
            }
        });

    })
    /**
     * 删除
     */
    $('#del').click(function() {
        var rows = $('#datatable_salesclue').datagrid('getSelections');
        alert(rows.length);
        if (rows.length == 0) {
            $.TeachDialog({
                content: '请至少选择一行数据！',
            });
            return;
        }
        var idsdata = "";
        var selectedstr = "";
        for (var i = 0; i < rows.length; i++) {
            idsdata += rows[i].sales_orderid + ",";
        }
        idsdata = idsdata.substring(0, idsdata.length - 1);
        //selectedstr = selectedstr.substring(0, selectedstr.length - 1);
        $.TeachDialog({
            content: "确认删除以下订单？<br>" + idsdata,
            showCloseButton: true,
            showCloseButtonName: '取消',
            otherButtons: ['确认'],
            otherButtonStyles: ['btn-danger'],
            clickButton: function(sender, modal, index) {
                modal.modal('hide');
                $.ajax({
                    url: 'Admin/Salesrecord/deleteByIDs', //form action
                    dataType: 'JSON', //返回体类型
                    type: 'POST', // form type
                    data: "ids=" + idsdata, //  请求参数
                    success: function(data) {
                        if (data.status == 1) {
                            $.TeachDialog({
                                content: data.msg,
                                CloseButtonAddFunc: function() {
                                    $('#datatable_salesclue').datagrid('reload');
                                    $('#datatable_salesclue').datagrid('uncheckAll');
                                }
                            });
                        } else {
                            $.TeachDialog({
                                content: '删除失败',
                            });
                        }
                    },
                    error: function() {
                        $.TeachDialog({
                            content: '删除失败',
                        });
                    }
                });
            }
        })



    })
    /*
     * 导入老师信息excel表
     */
    var uploadOption =
            {
                // 提交url
                action: "admin/salesrecord/import",
                // 服务端接收的名称
                name: "Filedata",
                // 自动提交
                autoSubmit: false,
                // 选择文件之后…
                onChange: function(file, extension) {
                    if (new RegExp(/(xls)/i).test(extension)) {
                         $("#state").val(file);
                    } else {
                        $.ajax({
                            url: 'admin/admin/alertlog',
                            data: "t=3" ,
                            type: 'POST',
                            dataType: 'HTML', //返回的数据类型
                            success: function(updatemodalhtml) {
                                content = updatemodalhtml;
                                //弹出修改框
                                $.TeachDialog({
                                    modalId: null,
                                    animation: null,
                                    title: '系统消息',
                                    content: content,
                                    showCloseButton: true,
                                    showCloseButtonName: '关闭',
                                    CloseButtonAddFunc: function() {
                                    },

                                    otherButtonStyles: [],
                                    bootstrapModalOption: {
                                        backdrop: 'static'
                                    },
                                    largeSize: false,
                                    smallSize: false,
                                });
                            },
                            error: function() {
                                $.TeachDialog({
                                    content: '获取数据失败，无法进行修改',
                                });
                                return;
                            }
                        });
                    }
                },
                // 开始上传文件
                onSubmit: function(file, extension) {
                    $("#state").val("正在上传" + file + "..");
                },
                // 上传完成之后
                onComplete: function(file, response) {
                    if (response.substr(0,1)=='1')
                    {
                        $.ajax({
                            url: 'admin/admin/alertlog',
                            data: "t=2" ,
                            type: 'POST',
                            dataType: 'HTML', //返回的数据类型
                            success: function(updatemodalhtml) {
                                content = updatemodalhtml;
                                //弹出修改框
                                $.TeachDialog({
                                    modalId: null,
                                    animation: null,
                                    title: '系统消息',
                                    content: content,
                                    showCloseButton: true,
                                    showCloseButtonName: '关闭',
                                    CloseButtonAddFunc: function() {
                                    },

                                    otherButtonStyles: [],
                                    bootstrapModalOption: {
                                        backdrop: 'static'
                                    },
                                    largeSize: false,
                                    smallSize: false,
                                });
                            },
                            error: function() {
                                $.TeachDialog({
                                    content: '获取数据失败，无法进行修改',
                                });
                                return;
                            }
                        });
                                            }
                    else{
                        $.TeachDialog({
                            modalId: null,
                            animation: null,
                            title: '系统消息',
                            content: response,
                            showCloseButton: true,
                            showCloseButtonName: '关闭',
                            CloseButtonAddFunc: function() {
                            },

                            otherButtonStyles: [],
                            bootstrapModalOption: {
                                backdrop: 'static'
                            },
                            largeSize: false,
                            smallSize: false,
                        });
                    }
                   $("#state").val("");
                }
            }

    // 初始化excel上传
    var oAjaxUpload = new AjaxUpload('#selector', uploadOption);

    // 给上传按钮增加上传动作
    $("#up").click(function()
    {
		if($("#state").val().length==0){
            $.ajax({
                url: 'admin/admin/alertlog',
                data: "t=1" ,
                type: 'POST',
                dataType: 'HTML', //返回的数据类型
                success: function(updatemodalhtml) {
                    content = updatemodalhtml;
                    //弹出修改框
                    $.TeachDialog({
                        modalId: null,
                        animation: null,
                        title: '系统消息',
                        content: content,
                        showCloseButton: true,
                        showCloseButtonName: '关闭',
                        CloseButtonAddFunc: function() {
                        },

                        otherButtonStyles: [],
                        bootstrapModalOption: {
                            backdrop: 'static'
                        },
                        largeSize: false,
                        smallSize: false,
                    });
                },
                error: function() {
                    $.TeachDialog({
                        content: '获取数据失败，无法进行修改',
                    });
                    return;
                }
            });
		}
        oAjaxUpload.submit();
    });


    // 给导出按钮增加导出动作
    $("#export").click(function()
    {
        var searchParams = getSearchParams();

        window.location.href = "admin/salesrecord/export?sales_orderid="+searchParams['sales_orderid']+"&teacher_name="+searchParams['teacher_name']
            +"&student_name="+searchParams['student_name']+"&sales_ordertypename="+searchParams['sales_ordertypename']+"&sales_coursetypename="+searchParams['sales_coursetypename']
            +"&starttime="+searchParams['starttime']+"&endtime="+searchParams['endtime'];
    });

});