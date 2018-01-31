<?php
namespace app\admin\validate;

use think\Validate;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/7
 * Time: 17:28
 */
class SalesrecordValidate extends Validate
{
    protected $rule = [
        'sales_teachername' => 'require',
        'sales_money'=>'require|egt:0',
        'sales_studentname' => 'require',
        'sales_day' => 'require',

    ];
    protected $message = [
        'sales_teachername.require' => '销售员工必须填写',
        'sales_money.egt' => '销售金额不能为负数',
        'sales_money.require' => '销售金额必须填写',
        'sales_studentname.require' => '学员姓名必须填写',
        'sales_day.require' => '销售日期必须选择',
    ];
}