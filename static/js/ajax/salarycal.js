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
    var cellwidth = ($(".box-content.table-responsive").width() - 55) / 10;
    var psval = $('#datatable_salaryinfo').attr('data-size');
    if (psval == undefined || psval == "") {
        psval = 10;
    }
    /**
     * easyui 生成表格数据
     */
    $('#datatable_salaryinfo').datagrid({
        striped: true,
        //idField:'teacher_id',
        remoteSort: false,
        collapsible: true,
        fit: false,
        url: '',//数据源路径
        loadMsg: '请等待数据载入....',
        pagination: true,
        rownumbers: true,
        fitColumns: true,
        pageSize: psval,
        pageList: [psval, psval * 2, psval * 3, psval * 4, psval * 5],
        columns: [[{
            field: 'teacher_name',
            title: '员工姓名',
            align: 'center',
            width: cellwidth,
            sortable: true
        }, {
            field: 'total',
            title: '合计',
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
            field: 'classhour_money',
            title: '课时费',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: '新单',
            title: '新单提成',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'demo课',
            title: 'demo课提成',
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
        onBeforeLoad: function(param) {
            param = getSearchParams(param);
        }
    });

    //计算全部员工薪资
    $('#CalAll').click(function () {
        var searchParams = getSearchParams();
        //必须选择薪资计算起始、终止时间
        if(searchParams['starttime']==undefined||searchParams['starttime']==''||searchParams['endtime']==undefined||searchParams['endtime']==''){
            $.TeachDialog({
                content: '请选择起始日期和终止日期',

            });
            return;
        }
        $('#datatable_salaryinfo').datagrid({url:'admin/Salary/getSalaryInfo'});
        $('#datatable_salaryinfo').datagrid('load');
    })
/*    //计算符合条件的员工薪资
    $('#CalPerson').click(function () {
        $('#datatable_salaryinfo').datagrid({url:'admin/Salary/getPersonSalaryInfo'});
        $('#datatable_salaryinfo').datagrid('load');
    })*/

    // 给上传按钮增加上传动作
    $("#export").click(function()
    {
        var searchParams = getSearchParams();
        //必须选择薪资计算起始、终止时间
        if(searchParams['starttime']==undefined||searchParams['starttime']==''||searchParams['endtime']==undefined||searchParams['endtime']==''){
            $.TeachDialog({
                content: '请选择起始日期和终止日期',

            });
            return;
        }
        window.location.href = "admin/salary/export?starttime="+searchParams['starttime']+"&endtime="+searchParams['endtime'];
    });





});