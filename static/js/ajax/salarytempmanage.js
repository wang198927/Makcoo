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
    $('.SearchForm').each(function () {
        var param = $(this).val().trim();
        if (param == undefined)
            param = '';
        searchParams[$(this).attr('id')] = param;
    });
    return searchParams;
}


/**
 * 页面自加载
 */
$(function () {
    var cellwidth = ($(".box-content.table-responsive").width() - 55) / 11;
    var psval = $('#datatable_salarytempinfo').attr('data-size');
    if (psval == undefined || psval == "") {
        psval = 10;
    }
    /**
     * easyui 生成表格数据
     */
    $('#datatable_salarytempinfo').datagrid({
        striped: true,
        idField:'id',
        remoteSort: false,
        collapsible: true,
        fit: false,
        url: 'admin/Salary/getSalaryTemplates',//数据源路径
        loadMsg: '请等待数据载入....',
        pagination: true,
        rownumbers: true,
        fitColumns: true,
        pageSize: psval,
        pageList: [psval, psval * 2, psval * 3, psval * 4, psval * 5],
        columns: [[{
            field: 'salarytemp_name',
            title: '模板名称',
            align: 'center',
            width: cellwidth,
            sortable: true
        }, {
            field: 'salarytemp_base',
            title: '基本工资',
            align: 'center',
            width: cellwidth,
            sortable: true
        }, {
            field: 'salarytemp_pos',
            title: '岗位工资',
            align: 'center',
            width: cellwidth,
            sortable: true/*,
            formatter: function (value) {
                return value.subject_name;
            }*/
        }, {
            field: 'salarytemp_com',
            title: '通讯补贴',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salarytemp_trans',
            title: '交通补贴',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salarytemp_food',
            title: '餐补',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salarytemp_classhour',
            title: '课时费',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salarytemp_newbonus',
            title: '新单提成比例(%)',
            align: 'center',
            width: cellwidth,
            sortable: true
            }
	/**	,{
      *      field: 'campus',
      *      title: '所在分区',
      *      align: 'center',
      *      width: cellwidth,
      *      sortable: true,
      *      formatter: function (value) {
      *         return value.campus_name;
      *     }
      * }
	  */
		]],
    });

    $('#Search').click(function () {
        $('#datatable_salarytempinfo').datagrid('reload');
    })
    //添加
    $('.addbook').click(function(){
        var content = "";
        $.ajax({
            url: 'admin/salary/addsalarytemp',
            type: 'POST',
            dataType: 'HTML',//返回的数据类型
            success: function (updatemodalhtml) {
                content = updatemodalhtml;
                //弹出添加框
                $.TeachDialog({
                    modalId: null,
                    animation: null,
                    title: '添加薪资模板',
                    content: content,
                    showCloseButton: true,
                    showCloseButtonName: '关闭',
                    CloseButtonAddFunc: function () {
                    },
                    otherButtons: ['添加'],
                    otherButtonStyles: ['btn-primary'],

                   
                    clickButton: function (sender, modal, index) {
                        $.ajax({
                            url: 'admin/Salary/insert',
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
                                    $('#datatable_salarytempinfo').datagrid('reload');
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
    //编辑
    $('.editbook').click(function () {
        var row = $('#datatable_salarytempinfo').datagrid('getSelections');
        if (row.length<1) {
            $.TeachDialog({
                content: '请选择一行数据进行修改！',
            });
            return;
        }
        if (row.length>1) {
            $.TeachDialog({
                content: '只能选择一行进行修改！',
            });
            return;
        }
        var content = "";
        $.ajax({
            url: 'admin/salary/updatesalarytemp',
            data: "id=" + row[0].id,
            type: 'POST',
            dataType: 'HTML',//返回的数据类型
            success: function (updatemodalhtml) {
                content = updatemodalhtml;
                //弹出修改框
                $.TeachDialog({
                    title: '修改薪资模板',
                    content: content,
                    showCloseButton: true,
                    showCloseButtonName: '关闭',
                    otherButtons: ['保存'],
                    otherButtonStyles: ['btn-primary'],
                    clickButton: function (sender, modal, index) {
                        $.ajax({
                            url: 'admin/salary/update',
                            data: $("#updateForm").serialize(),
                            type: 'POST',
                            dataType: 'JSON',
                            success: function (returnData) {
                                if (returnData.status == 0) {
                                    $.TeachDialog({
                                        content: returnData.msg
                                    });
                                } else {
                                    modal.modal('hide');
                                    $.TeachDialog({
                                        content: returnData.msg
                                    });
                                    $('#datatable_salarytempinfo').datagrid('reload');
                                }
                            },
                            error: function () {
                                $.TeachDialog({
                                    content: '系统异常，请联系管理员'
                                });
                            },

                        });

                    },
                });
            },
            error: function () {
                $.TeachDialog({
                    content: '系统异常，请联系管理员'
                });
                return;
            }
        });
    })
    /**
     * 删除
     */
    $('.removebook').click(function () {
        var rows = $('#datatable_salarytempinfo').datagrid('getSelections');
        if (rows.length == 0) {
            $.TeachDialog({
                content: '请至少选择一行数据！'
            });
            return;
        }

        var idsdata = "";
        var selectedstr = "";
        for (var i = 0; i < rows.length; i++) {
            idsdata += rows[i].id + ",";
            selectedstr += rows[i].salarytemp_name + "，";
        }
        idsdata = idsdata.substring(0, idsdata.length - 1);
        selectedstr = selectedstr.substring(0, selectedstr.length - 1);

        $.TeachDialog({
            content: "确认删除以下课程？<br>" + selectedstr,
            showCloseButton: true,
            showCloseButtonName: '取消',
            otherButtons: ['确认'],
            otherButtonStyles: ['btn-danger'],
            clickButton: function (sender, modal, index) {
                modal.modal('hide');
                $.ajax({
                    url: 'Admin/Salary/deleteByIDs', //form action
                    dataType: 'JSON', //返回体类型
                    type: 'POST', // form type
                    data: "ids=" + idsdata,//  请求参数
                    success: function (data) {
                        if (data.status == 1) {
                            $.TeachDialog({
                                content: data.msg
                            });
                            $('#datatable_salarytempinfo').datagrid('reload');
                            $('#datatable_salarytempinfo').datagrid('uncheckAll');
                        } else {
                            $.TeachDialog({
                                content: '删除失败'
                            });
                        }
                    },
                    error: function () {
                        $.TeachDialog({
                            content: '系统异常，请联系管理员'
                        });
                    }
                });
            }
        });
    })

});