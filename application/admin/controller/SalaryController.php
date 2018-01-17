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
        /*        $searchPath = $this->searchNotLike($path,$_POST,'course_grade_id','course_subject_id');
                if(isset($searchPath['campusid'])){
                    $searchPath['course.campusid'] = $searchPath["campusid"];
                    unset($searchPath["campusid"]);
                }*/
        $salaryinfos = Db::query('select a.id ,a.teacher_name,sum(c.records_classhour) as classhour,d.salarytemp_name,d.salarytemp_newbonus,d.salarytemp_demobonus from ew_teacher a 
left join ew_classrecords c on a.id = c.records_teacherid
left join ew_salarytemp d on a.teacher_salarytemp_id = d.id
group by a.id');
        $salesinfos = Db::query('select a.id ,c.sales_ordertypename,sum(c.sales_money) as money from ew_teacher a 
left join ew_salesrecord c on a.id = c.sales_teacherid
group by a.id,c.sales_ordertypename');
          for ($i=0;$i<sizeof($salaryinfos);$i++)
          {
              for ($j=0;$j<sizeof($salesinfos);$j++)
              {
                  if($salaryinfos[$i]['id'] ==$salesinfos[$j]['id'] )
                  {
                      $tempordertype = $salesinfos[$j]['sales_ordertypename'];
                      $salaryinfos[$i][$tempordertype]=$salesinfos[$j]['money'];
                      if($tempordertype=='新单')
                      {
                          $salaryinfos[$i][$tempordertype]=$salaryinfos[$i][$tempordertype]*$salaryinfos[$i]['salarytemp_newbonus']/100;
                      }
                      elseif ($tempordertype=='demo课')
                      {
                          $salaryinfos[$i][$tempordertype]=$salaryinfos[$i][$tempordertype]*$salaryinfos[$i]['salarytemp_demobonus']/100;
                      }
                  }
              }
          }
//        $temp1 = $salaryinfos[0]['teacher_name'];
//        $temp2 = $salaryinfos[0]['salarytemp']['salarytemp_name'];
        $total = sizeof($salaryinfos);

        $data['total'] = $total;
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
        $salarytemps = Salarytemp::where($path)->limit($rows * ($page - 1), $rows)->select();
        $total = Salarytemp::where($path)->count();
        $data['total'] = $total;
        $data['rows'] = $salarytemps;
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