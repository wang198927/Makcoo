<?php
namespace app\admin\validate;

use think\Validate;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/7
 * Time: 17:28
 */
class SalesClueValidate extends Validate
{
    protected $rule = [
        'clue_student_name' => 'require',
        'clue_telephone'=>'require|max:11|min:11',
        'clue_student_age' => 'require',
        'clue_last_time' => 'require',

    ];
    protected $message = [
        'clue_telephone.require' => '联系电话必须填写',
        'clue_telephone.max' => '电话最多不能超过11位',
        'clue_telephone.min' => '电话最低不能少于11位',
        'clue_student_age.require' => '学生年龄必须填写',
        'clue_student_name.require' => '学员姓名必须填写',
        'clue_last_time.require' => '最后联系日期必须选择',
    ];

}