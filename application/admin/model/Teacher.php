<?php
/**
 * Created by PhpStorm.
 * User: mac1
 * Date: 16/6/10
 * Time: 上午10:37
 */

namespace app\admin\model;


use think\Model;
use think\Db;

/**
 * 老师
 * Class Teacher
 * @package app\admin\model
 */
class Teacher extends Model
{
    public function grade()
    {
        return $this->belongsTo('Grade','teacher_grade_id');
    }

    public function subject()
    {
        return $this->belongsTo('Subject','teacher_subject_id');
    }
    public function salarytemp()
    {
        return $this->belongsTo('Salarytemp','teacher_salarytemp_id');
    }

    /*
     * 查每个员工基本工资
     * */
    public function queryTeacherBaseSalary($rows,$page)
    {
        $salaryinfos = Db::query('select a.id as teacher_id,a.teacher_name,d.* from ew_teacher a 
                        left join ew_salarytemp d on a.teacher_salarytemp_id = d.id
                        group by a.id
                        order by a.id
                        limit :offset,:row',['offset'=>$rows * ($page - 1),'row'=>$rows]);
        return $salaryinfos;
    }

    /*
     * 查每个员工基本工资(不限行数)
     * */
    public function queryTeacherBaseSalaryNoLimit()
    {
        $salaryinfos = Db::query('select a.id as teacher_id,a.teacher_name,d.* from ew_teacher a 
                        left join ew_salarytemp d on a.teacher_salarytemp_id = d.id
                        group by a.id
                        order by a.id');
        return $salaryinfos;
    }

    /*
     * 查每个员工课时数
     * */
    public function queryTeacherClassHour($starttime,$endtime)
    {
        $classsalaryinfos = Db::query('select a.id as teacher_id,a.teacher_name,sum(c.records_classhour) as classhour from ew_teacher a 
                            left join ew_classrecords c on a.id = c.records_teacherid
                            where c.records_endtime between :starttime and :endtime
                            group by a.id
                            order by a.id',['starttime'=>$starttime,'endtime'=>$endtime]);
        return $classsalaryinfos;
    }

    /*
     * 查每个员工各类销售额
     * */
    public function queryTeacherSalesMoney($starttime,$endtime)
    {
        $salesinfos = Db::query('select a.id ,c.sales_ordertypename,sum(c.sales_money*0.1) as money from ew_teacher a 
                            left join ew_salesrecord c on a.id = c.sales_teacherid
                            where c.sales_day between :starttime and :endtime
                            group by a.id,c.sales_ordertypename',['starttime'=>$starttime,'endtime'=>$endtime]);
        return $salesinfos;
    }



}