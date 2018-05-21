<?php

namespace app\admin\controller;

use app\admin\model\Coursetype;
use app\admin\model\Ordertype;
use app\admin\model\Teacher;
use app\admin\validate\SalesClueValidate;
use app\admin\model\Campus;
use app\admin\model\Salesclue;
use think\Db;

/**
 * 教师Controller
 * Class TeacherController
 * Author ghostsf
 * Blog www.ghostsf.com
 * @package app\admin\controller
 */
class SalesclueController extends CommonController {



    /**
     * 获得数据
     * Author mww
     */
    public function getSalesClue()
    {
        $rows = $_POST['rows'];
        $page = $_POST['page'];
        if(empty($_POST['starttime'])){
            $start = 0;
            unset($_POST['starttime']);
        }else{
            $start = $_POST['starttime'];
            unset($_POST['starttime']);
        };
        if(empty($_POST['endtime'])){
            $end = '2100-12-31';
            unset($_POST['endtime']);
        }else{
            $end = $_POST['endtime'];
            unset($_POST['endtime']);
        };
        if(empty($_POST['starttime2'])){
            $start2 = 0;
            unset($_POST['starttime2']);
        }else{
            $start2 = $_POST['starttime2'];
            unset($_POST['starttime2']);
        };
        if(empty($_POST['endtime2'])){
            $end2 = '2100-12-31';
            unset($_POST['endtime2']);
        }else{
            $end2 = $_POST['endtime2'];
            unset($_POST['endtime2']);
        };
        //过滤结束==============================
        $path = $this->getDataByCampusid($_POST);
        $searchPath = $this->searchNotLike($path,$_POST,'clue_status');
        if(isset($searchPath['campusid'])){
            $searchPath['salesclue.clue_campusid'] = $searchPath["campusid"];
            unset($searchPath["campusid"]);
        }
        if(isset($searchPath['teacher_name'])){
            $searchPath['teacher.teacher_name'] = $searchPath["teacher_name"];
            unset($searchPath["teacher_name"]);
        }
        $salesclue = Salesclue::with("teacher")->where($searchPath)->where('clue_last_time','between',"{$start},{$end}")->where('clue_next_time','between',"{$start2},{$end2}")->order("clue_next_time desc")->limit($rows * ($page - 1), $rows)->select();
        $total = Salesclue::with("teacher")->where($searchPath)->where('clue_last_time','between',"{$start},{$end}")->where('clue_next_time','between',"{$start2},{$end2}")->count();


        //$salesrecord = Db::table('ew_salesrecord')->alias('a')->join('ew_teacher c ','a.sales_teacherid=c.id','left')->where($searchPath)->where('a.sales_day','between',"{$start},{$end}")->limit($rows * ($page - 1), $rows)->select();
        //$total = Salesrecord::with("ordertype,coursetype")->where($searchPath)->where('sales_day','between',"{$start},{$end}")->count();
        $data['total'] = $total;
        $data['rows'] = $salesclue;
        return json_encode($data);
    }



    public function addsalesclue()
    {
        return $this->fetch("salesrecord/sales_clue_add");
    }
    /**
     * 插入
     */
    public function insert()
    {
        $returnArr = $this->salesClueValidCheck($_POST);
        $errStrTotal=$returnArr['errStrTotal'];
        $arr=$returnArr['arr'];
        $registrationModel = Db::name("Salesclue");
        if ($errStrTotal!="") {
            $returnData['status'] = 0;
            $returnData['msg'] =$errStrTotal;
            return json_encode($returnData);
        } else {
            $registrationModel->insert($arr);
            $returnData['status'] = 1;
            $returnData['msg'] = "成功";
            return json_encode($returnData);
        }
    }

    /**
     * 销售记录数据合法性检查
     * @return mixed 包含错误信息和校验转换后的销售记录数据
     */

    public function salesClueValidCheck($arr=[]){

        //先查基本合法性
        $validata = new SalesClueValidate();
        if(!$validata->check($arr))
        {
            $errStrTotal=$validata->getError();
        }
        else{
            $errStrTotal="";
        }
        $campusid = session('loginSession')['campusid'];
        //查输入的员工和学生在库里有没有
        if( null != $a = Db::table("ew_teacher")->field("id")->where(["teacher_name"=>$arr["teacher_name"],"campusid"=>$campusid])->select()){
            $arr["clue_teacher_id"]=$a[0]['id'];
            unset($arr["teacher_name"]);
        }else{
            $errStrTotal = '咨询人员不存在;<br>';
        }
        $returnArr['errStrTotal']=$errStrTotal; //错误信息
        $returnArr['arr']=$arr; //销售记录信息
        return $returnArr;

    }

    /**
     * 更新教师数据
     * @return mixed
     */
    public function update() {
        $returnArr = $this->salesRecordValidCheck($_POST);
        $errStrTotal=$returnArr['errStrTotal'];
        $arr=$returnArr['arr'];
        $registrationModel = Db::name("Salesrecord");
        if ($errStrTotal!="") {
            $returnData['status'] = 0;
            $returnData['msg'] =$errStrTotal;
            return json_encode($returnData);
        } else {
            $registrationModel->update($arr);
            $returnData['status'] = 1;
            $returnData['msg'] = "修改成功";
            return json_encode($returnData);
        }
    }

    /**
     * 根据ID获得教师信息json数据
     */
    public function getTeacherById() {
        $teacher_id = input('teacher_id', '');
        $where = array();
        if (isNotNull($teacher_id)) {
            $where['id'] = $teacher_id;
        }
        $teachers = Teacher::where($where)->limit(1)->find();

        return json_encode($teachers);
    }

    /**
     * 	修改页面的显示
     */
    public function updatemodal() {
        $campusid = session("loginSession")['campusid'];
        $sales_orderid = input('sales_orderid');
        //获取要修改的老师信息
        $salesrecord= Salesrecord::with("teacher,student")->where(["sales_orderid" => $sales_orderid])->find();
        $ordertype = Ordertype::where(["campusid" => $campusid])->select();
        $coursetype = Coursetype::where(["campusid" => $campusid])->select();
        $this->assign("salesrecord", $salesrecord);
        $this->assign("ordertypes", $ordertype);
        $this->assign("coursetypes", $coursetype);
        return $this->fetch('salesrecord/update');
    }

    /**
     * 删除老师信息
     */
    public function deleteByIDs() {
        $ids = input('ids');
        $ids = rtrim($ids, ",");
        $map['sales_orderid'] = array('in', $ids);
        $deleteinfo = Salesrecord::where($map)->delete();
        if ($deleteinfo) {
            return json_encode(array("status" => 1, "msg" => "删除成功！"));
        } else {
            return json_encode(array("status" => 0, "msg" => "删除失败！"));
        }
    }

    /*
     * 导出老师excel表格
     */
    public function export()
    {
        $campusid = session('loginSession')['campusid'];
        $loginsession = session('loginSession');
        $campus = Campus::get($campusid);
        $xlsName = $campus->campus_name . "校区销售记录表";
        $xlsCell = array(
            array("sales_orderid","销售单号"),
            array("teacher_name","销售员工"),
            array("sales_ordertypename","订单类型"),
            array("sales_money","销售金额"),
            array("student_name","学生姓名"),
            array("sales_coursetypename","课程类型"),
            array("sales_day","销售日期"),
            );

        //根据查询参数获取导出数据
        if(empty($_GET['starttime'])){
            $start = 0;
            unset($_GET['starttime']);
        }else{
            $start = $_GET['starttime'];
            unset($_GET['starttime']);
        };
        if(empty($_GET['endtime'])){
            $end = '2100-12-31';
            unset($_GET['endtime']);
        }else{
            $end = $_GET['endtime'];
            unset($_GET['endtime']);
        };
        //过滤结束==============================
        $path = $this->getDataByCampusid($_GET);
        $searchPath = $this->searchNotLike($path,$_GET,'sales_ordertypename','sales_coursetypename');
        if(isset($searchPath['campusid'])){
            $searchPath['salesrecord.sales_campusid'] = $searchPath["campusid"];
            unset($searchPath["campusid"]);
        }
        if(isset($searchPath['teacher_name'])){
            $searchPath['teacher.teacher_name'] = $searchPath["teacher_name"];
            unset($searchPath["teacher_name"]);
        }
        if(isset($searchPath['student_name'])){
            $searchPath['student.student_name'] = $searchPath["student_name"];
            unset($searchPath["student_name"]);
        }
        $xlsData = Salesrecord::with("teacher,student")->where($searchPath)->where('sales_day','between',"{$start},{$end}")->order("sales_day desc")->select();
        for($i=0;$i<sizeof($xlsData);$i++)
        {
        $xlsData[$i]['teacher_name']=$xlsData[$i]['teacher']['teacher_name'];
        unset($xlsData[$i]['teacher']['teacher_name']);
        $xlsData[$i]['student_name']=$xlsData[$i]['student']['student_name'];
        unset($xlsData[$i]['student']['student_name']);
        }
        $this->exportExcel($xlsName, $xlsCell, $xlsData);
    }
    /*
     * 销售记录导入
     */
    public function import()
    {
        $files = request()->file('Filedata');
        $file = $files->move('static/plugins/AjaxUpload/Upload');
        $fil = $file->getPathname();
        $array = $this->read($fil);
        //dump($array);
         $arr = array();
         $errStrTotal = '';
       // $campusid = $_POST["campusid"];
        $campusid = session('loginSession')['campusid'];
        for($i=2;$i<count($array)+1;$i++){
            foreach($array[$i] as $k=>$v){
                if($k==0){
                    $array[$i]['sales_teacherid'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==1){
                    $array[$i]['sales_ordertypename'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==2){
                    $array[$i]['sales_money'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==3){
                    $array[$i]['sales_studentid'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==4){
                    $array[$i]['sales_coursetypename'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==5){
                    $array[$i]['sales_day'] = $v;
                    unset($array[$i][$k]);
                }
                /*if($k==6){
                    $array[$i]['teacher_subject_id'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==7){
                    $array[$i]['teacher_telphone'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==8){
                    $array[$i]['teacher_email'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==9){
                    $array[$i]['teacher_qq'] = $v;
                    unset($array[$i][$k]);
                }*/
                $array[$i]['sales_campusid'] = $campusid;
            }
           $arr[$i-2]=$array[$i];
           
        }
         for($i=0;$i<count($arr);$i++){
                $errStr='第'.$i.'行数据';
                if( null != $a = Db::table("ew_teacher")->field("id")->where(["teacher_name"=>$arr[$i]["sales_teacherid"],"campusid"=>$campusid])->select()){
                    $arr[$i]["sales_teacherid"]=$a[0]['id'];
                }else{
                    $errStrTotal = $errStrTotal.$errStr.'销售员工不存在\n';
/*                    return $errStr.'销售员工不存在';*/
                }
                if( null != $a = Db::table("ew_student")->field("id")->where(["student_name"=>$arr[$i]["sales_studentid"],"campusid"=>$campusid])->select()){
                    $arr[$i]["sales_studentid"]=$a[0]['id'];
                }else{
                    $errStrTotal = $errStrTotal.$errStr.'学员不存在\n';
                   // return $errStr.'学员不存在';
                }
                if( null != $a = Db::table("ew_ordertype")->field("order_typename")->where(["order_typename"=>$arr[$i]["sales_ordertypename"],"campusid"=>$campusid])->select()){
                     $arr[$i]["sales_ordertypename"]=$a[0]['order_typename'];
                 }else{
                    $errStrTotal = $errStrTotal.$errStr.'销售单类型不存在\n';
                     //return $errStr.'销售单类型不存在';
                 }
                 if( null != $a = Db::table("ew_coursetype")->field("coursetype_name")->where(["coursetype_name"=>$arr[$i]["sales_coursetypename"],"campusid"=>$campusid])->select()){
                     $arr[$i]["sales_coursetypename"]=$a[0]['coursetype_name'];
                 }else{
                     $errStrTotal = $errStrTotal.$errStr.'销售课程周期类型不存在\n';
                     //return $errStr.'销售课程周期类型不存在';
                 }
             $arr[$i]["sales_orderid"]=$this->build_order_no($arr[$i]["sales_teacherid"]);

        }

        if($errStrTotal!='')
        {
            return $errStrTotal;
        }
       
        for($k=0;$k<count($arr);$k++){
            M("Salesrecord")->insertGetId($arr[$k]);

        }
        
        return '1';
    }



}
