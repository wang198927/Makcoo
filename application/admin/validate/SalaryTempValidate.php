<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Created by PhpStorm.
 * User: wangdm
 * Date: 2018/1/13
 * Time: 10:49
 */
class SalaryTempValidate extends Validate
{
    protected $rule = [
        'salarytemp_name' => 'require',
        'salarytemp_newbonus' => 'between:0,100',
        'salarytemp_base' => 'egt:0',
        'salarytemp_pos' => 'egt:0',
        'salarytemp_com' => 'egt:0',
        'salarytemp_trans' => 'egt:0',
        'salarytemp_food' => 'egt:0',
        'salarytemp_classhour' => 'egt:0',
    ];
    protected $message = [
        'salarytemp_name.require' => '请填写薪资模板名称',
        'salarytemp_newbonus.between'=>'新单提成比例须在0-100之间',
        'salarytemp_base.egt' => '基本工资不能为负数',
        'salarytemp_pos.egt' => '岗位工资不能为负数',
        'salarytemp_com.egt' => '通讯补贴不能为负数',
        'salarytemp_trans.egt' => '交通补贴不能为负数',
        'salarytemp_food.egt' => '餐补不能为负数',
        'salarytemp_classhour.egt' => '课时费不能为负数',

    ];
}