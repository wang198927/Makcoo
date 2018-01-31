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
function CourseTypeList() {
    $.ajax({
        url: 'admin/Salesrecord/getCourseTypeJson',
        dataType: 'JSON',
        success: function(data) {
            $("#sales_coursetypename").empty();
            $("#sales_coursetypename").append("<option value=''>课程周期类型</option>");
            $.each(data, function(i, d) {
                $("#sales_coursetypename").append('<option value="' + d.coursetype_name + '">' + d.coursetype_name + '</option>');
            });
        },
        error: function() {
            $.TeachDialog({
                content: '加载学生类型数据失败',
            });
        }
    });
}



/**
 * 页面自加载
 */

$(function() {
    //加载下拉框数据
    OrderTypeList();
    CourseTypeList();

    var cellwidth = ($(".box-content.table-responsive").width() - 55) / 11;
    var psval = $('#datatable_salesrecord').attr('data-size');
    if (psval == undefined || psval == "") {
        psval = 10;
    }
    /**
     * easyui 生成表格数据
     */
    $('#datatable_salesrecord').datagrid({
        singleSelect: false, //允许选择多行
        striped: true,
        idField: 'sales_orderid',
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
                    field: 'sales_orderid',
                    title: '销售单号',
                    align: 'center',
                    width: cellwidth,
                    sortable: true
                }, {
                    field: 'teacher',
                    title: '销售员工',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                    formatter: function(teacher) {
                        return teacher.teacher_name;
                    }
                }, {
                    field: 'sales_ordertypename',
                    title: '销售单类型',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                }, {
                    field: 'sales_money',
                    title: '销售金额',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                }, {
                    field: 'student',
                    title: '学生姓名',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                    formatter: function(student) {
                        return student.student_name;
                    }
                }, {
                    field: 'sales_coursetypename',
                    title: '课程周期类型',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                }, {
                    field: 'sales_day',
                    title: '销售日期',
                    align: 'center',
                    width: cellwidth,
                    sortable: true,
                }]],
        onBeforeLoad: function(param) {
            param = getSearchParams(param);
        },
    });


    $('#Search').click(function() {
        $('#datatable_salesrecord').datagrid({url:'admin/Salesrecord/getSalesRecord'});
        $('#datatable_salesrecord').datagrid('reload');
    })

    /*
     *新增
     */
    $('#add').click(function(){
        var content = "";
        $.ajax({
            url: 'admin/Salesrecord/addsalesreord',
            type: 'POST',
            dataType: 'HTML',//返回的数据类型
            success: function (updatemodalhtml) {
                content = updatemodalhtml;
                //弹出添加框
                $.TeachDialog({
                    modalId: null,
                    animation: null,
                    title: '添加销售记录',
                    content: content,
                    showCloseButton: true,
                    showCloseButtonName: '关闭',
                    CloseButtonAddFunc: function () {
                    },
                    otherButtons: ['添加'],
                    otherButtonStyles: ['btn-primary'],


                    clickButton: function (sender, modal, index) {
                        $.ajax({
                            url: 'admin/Salesrecord/insert',
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
                                    $('#datatable_salesrecord').datagrid('reload');
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
        var rows = $('#datatable_salesrecord').datagrid('getSelections');
        if (rows.length != 1) {
            $.TeachDialog({
                content: '请选择一行数据！',
            });
            return;
        }

        var content = "";
        $.ajax({
            url: 'admin/Salesrecord/updatemodal',
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
                                    $('#datatable_salesrecord').datagrid('reload');
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
        var rows = $('#datatable_salesrecord').datagrid('getSelections');
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
                                    $('#datatable_salesrecord').datagrid('reload');
                                    $('#datatable_salesrecord').datagrid('uncheckAll');
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