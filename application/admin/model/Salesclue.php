<?php
/**
 * Created by ghostsf
 * Date: 2016/4/19
 */

namespace app\admin\model;


use think\Model;

/**
 * 销售记录Model
 * Class Salesrecord
 * Author ghostsf
 * Blog www.ghostsf.com
 * @package app\admin\model
 */
class Salesclue extends Model
{

    public function teacher()
    {
        return $this->belongsTo('Teacher','clue_teacher_id');
    }

}