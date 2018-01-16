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
        //idField:'id',
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
            field: 'salaryinfo_name',
            title: '员工姓名',
            align: 'center',
            width: cellwidth,
            sortable: true
        }, {
            field: 'salaryinfo_name',
            title: '合计',
            align: 'center',
            width: cellwidth,
            sortable: true
        }, {
            field: 'salaryinfo_base',
            title: '基本工资',
            align: 'center',
            width: cellwidth,
            sortable: true
        }, {
            field: 'salaryinfo_pos',
            title: '岗位工资',
            align: 'center',
            width: cellwidth,
            sortable: true/*,
            formatter: function (value) {
                return value.subject_name;
            }*/
        }, {
            field: 'salaryinfo_com',
            title: '通讯补贴',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salaryinfo_trans',
            title: '交通补贴',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salaryinfo_food',
            title: '餐补',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salaryinfo_classhour',
            title: '课时费',
            align: 'center',
            width: cellwidth,
            sortable: true
        },{
            field: 'salaryinfo_newbonus',
            title: '提成',
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
        $('#datatable_salaryinfo').datagrid({url:'admin/Salary/getSalaryInfo'});
        $('#datatable_salaryinfo').datagrid('load');
    })
    //计算符合条件的员工薪资
    $('#CalPerson').click(function () {
        $('#datatable_salaryinfo').datagrid({url:'admin/Salary/getSalaryInfo'});
        $('#datatable_salaryinfo').datagrid('load');
    })





});