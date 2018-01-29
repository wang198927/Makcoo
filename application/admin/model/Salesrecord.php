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
class Salesrecord extends Model
{

    public function student()
    {
        return $this->belongsTo('Student','sales_studentid');
    }

    public function teacher()
    {
        return $this->belongsTo('Teacher','sales_teacherid');
    }

    public function ordertype()
    {
        return $this->belongsTo('Ordertype','sales_ordertypeid');
    }

    public function coursetype()
    {
        return $this->belongsTo('Coursetype','sales_coursetypeid');
    }

}