<?php
/**
 * Created by ghostsf
 * Date: 2016/4/21
 */

namespace app\admin\controller;

use app\admin\model\Course;
use app\admin\model\Grade;
use app\admin\model\Subject;
use app\admin\model\Campus;
use app\admin\model\Salarytemp;
use app\admin\model\Teacher;
use think\Controller;
use app\admin\validate\SalaryTempValidate;
use think\Db;


/**
 * 课程Controller
 * Class Course
 * @package app\admin\controller
 */
class SalaryController extends CommonController
{

    /**
     * 获得薪资计算结果json数据
     */
    public function getSalaryInfo()
    {
        $rows = $_POST['rows'];
        $page = $_POST['page'];
        $path = $this->getDataByCampusid($_POST);
/*                $searchPath = $this->searchNotLike($path,$_POST,'teacher_name','starttime','endtime');
                if(isset($searchPath['campusid'])){
                    $searchPath['course.campusid'] = $searchPath["campusid"];
                    unset($searchPath["campusid"]);
                }*/
        unset($_POST['name']);
        //查每个员工基本工资
        $salaryinfos = Db::query('select a.id as teacher_id,a.teacher_name,d.* from ew_teacher a 
left join ew_salarytemp d on a.teacher_salarytemp_id = d.id
group by a.id
order by a.id
limit :offset,:row',['offset'=>$rows * ($page - 1),'row'=>$rows]);
        //获取总行数
        $totalrows=sizeof(Db::query('select a.id as teacher_id,a.teacher_name,d.* from ew_teacher a 
left join ew_salarytemp d on a.teacher_salarytemp_id = d.id
group by a.id
order by a.id'));

        //查每个员工课时数
        $classsalaryinfos = Db::query('select a.id as teacher_id,a.teacher_name,sum(c.records_classhour) as classhour from ew_teacher a 
left join ew_classrecords c on a.id = c.records_teacherid
where c.records_endtime between :starttime and :endtime
group by a.id
order by a.id',['starttime'=>$_POST['starttime'],'endtime'=>$_POST['endtime']]);

        //查每个员工各类销售额
        $salesinfos = Db::query('select a.id ,c.sales_ordertypename,sum(c.sales_money*0.1) as money from ew_teacher a 
left join ew_salesrecord c on a.id = c.sales_teacherid
where c.sales_day between :starttime and :endtime
group by a.id,c.sales_ordertypename',['starttime'=>$_POST['starttime'],'endtime'=>$_POST['endtime']]);

          for ($i=0;$i<sizeof($salaryinfos);$i++)
          {
              $salaryinfos[$i]['classhour_money']=0;
              //计算课时费
              for($k=0;$k<sizeof($classsalaryinfos);$k++)
              {
                  //遍历找到课时查询结果中该员工的课时数，计算课时费
                  if($salaryinfos[$i]['teacher_id'] ==$classsalaryinfos[$k]['teacher_id'] )
                  {
                      $salaryinfos[$i]['classhour_money']=$classsalaryinfos[$k]['classhour']*$salaryinfos[$i]['salarytemp_classhour'];
                  }
              }
              $salaryinfos[$i]['新单']=0;
              $salaryinfos[$i]['demo课']=0;
              //计算不包含销售提成的总工资
              $salaryinfos[$i]['total']=$salaryinfos[$i]['salarytemp_base']+$salaryinfos[$i]['salarytemp_pos']+$salaryinfos[$i]['salarytemp_com']+$salaryinfos[$i]['salarytemp_trans']+
              $salaryinfos[$i]['salarytemp_food']+$salaryinfos[$i]['classhour_money'];
              //循环每个员工计算各类销售提成
              for ($j=0;$j<sizeof($salesinfos);$j++)
              {
                  if($salaryinfos[$i]['teacher_id'] ==$salesinfos[$j]['id'] )
                  {
                      $tempordertype = $salesinfos[$j]['sales_ordertypename'];
                      $salaryinfos[$i][$tempordertype]=$salesinfos[$j]['money'];
                      if($tempordertype=='新单')
                      {
                          $salaryinfos[$i][$tempordertype]=$salaryinfos[$i][$tempordertype]*$salaryinfos[$i]['salarytemp_newbonus']/100;  //提成比例需要换成%
                          $salaryinfos[$i]['total']=$salaryinfos[$i]['total']+$salaryinfos[$i][$tempordertype];
                      }
                      elseif ($tempordertype=='demo课')
                      {
                          $salaryinfos[$i][$tempordertype]=$salaryinfos[$i][$tempordertype]*$salaryinfos[$i]['salarytemp_demobonus']/100;
                          $salaryinfos[$i]['total']=$salaryinfos[$i]['total']+$salaryinfos[$i][$tempordertype];
                      }
                      else
                      {
                          //todo 其他销售类统计
                      }
                  }
              }
          }
//        $temp1 = $salaryinfos[0]['teacher_name'];
//        $temp2 = $salaryinfos[0]['salarytemp']['salarytemp_name'];

        $data['total'] = $totalrows;
        $data['rows'] = $salaryinfos;
        return json_encode($data);
    }


    /**
     * 获得薪资模板json数据
     */
    public function getSalaryTemplates()
    {
        $rows = $_POST['rows'];
        $page = $_POST['page'];
        $path = $this->getDataByCampusid($_POST);
/*        $searchPath = $this->searchNotLike($path,$_POST,'course_grade_id','course_subject_id');
        if(isset($searchPath['campusid'])){
            $searchPath['course.campusid'] = $searchPath["campusid"];
            unset($searchPath["campusid"]);
        }*/
        $salaryTemps = Salarytemp::where($path)->limit($rows * ($page - 1), $rows)->select();
        $total = Salarytemp::where($path)->count();
        $data['total'] = $total;
        $data['rows'] = $salaryTemps;
        return json_encode($data);
    }
    /**
     * 显示添加页
     */
    public function addsalarytemp()
    {
        return $this->fetch("salary/add");
    }
    /**
     * 插入
     */
    public function insert()
    {

        $_POST['campusid'] = session('loginSession')['campusid'];

        $registrationModel = M("Salarytemp");
        $validata = new SalaryTempValidate();
        if (!$validata->check($_POST)) {
            $returnData['status'] = 0;
            $returnData['msg'] = $validata->getError();
            return json_encode($returnData);
        } else {
            $registrationModel->insert($_POST);
            $returnData['status'] = 1;
            $returnData['msg'] = "成功";
            return json_encode($returnData);
        }
    }
    /**
     * 显示修改页
     */
    public function updatesalarytemp()
    {
        $param = $this->getDataByCampusid();
        $id = input('id', '');
        $salarytemp = Salarytemp::get($id);
        $this->assign("salarytemp", $salarytemp);
        return $this->fetch("salary/update");
    }
    /**
     * 修改
     */
    public function update()
    {
        $registrationModel = M("Salarytemp");
        $validata = new SalaryTempValidate();
        if (!$validata->check($_POST)) {
            $returnData['status'] = 0;
            $returnData['msg'] = $validata->getError();
            return json_encode($returnData);
        } else {
            //$_POST['course_total'] = $_POST['course_unitprice']*$_POST['course_periodnum'];
            $registrationModel->update($_POST);
            $returnData['status'] = 1;
            $returnData['msg'] = "修改成功";
            return json_encode($returnData);
        }
    }
    /**
     * 删除
     */
    public function deleteByIDs()
    {
        $ids = input('ids');
        $ids = rtrim($ids, ",");
        $map['id'] = array('in', $ids);
        $deleteinfo = Db::name('Salarytemp')->where($map)->delete();
        if ($deleteinfo) {
            return json_encode(array("status" => 1, "msg" => "删除成功！"));
        } else {
            return json_encode(array("status" => 0, "msg" => "删除失败！"));
        }
    }

}