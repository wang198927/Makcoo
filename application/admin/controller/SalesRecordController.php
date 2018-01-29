<?php

namespace app\admin\controller;

use app\admin\model\Teacher;
use app\admin\validate\TeacherValidate;
use app\admin\model\Campus;
use app\admin\model\Salesrecord;
use think\Db;

/**
 * 教师Controller
 * Class TeacherController
 * Author ghostsf
 * Blog www.ghostsf.com
 * @package app\admin\controller
 */
class SalesrecordController extends CommonController {



    /**
     * 获得数据
     * Author mww
     */
    public function getSalesRecord()
    {
        $rows = $_POST['rows'];
        $page = $_POST['page'];
        $status = '';
        //过滤日期范围
        $start = "";
        $end = "";
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
        //过滤结束==============================
        $path = $this->getDataByCampusid($_POST);
        $searchPath = $this->searchNotLike($path,$_POST,'sales_ordertypeid','sales_coursetypeid');
        if(isset($searchPath['campusid'])){
            $searchPath['salesrecord.sales_campusid'] = $searchPath["campusid"];
            unset($searchPath["campusid"]);
        }
        $salesrecord = Salesrecord::with("student,teacher,ordertype,coursetype")->where($searchPath)->where('sales_day','between',"{$start},{$end}")->limit($rows * ($page - 1), $rows)->select();
        $total = Salesrecord::with("student,teacher,ordertype,coursetype")->where($searchPath)->where('sales_day','between',"{$start},{$end}")->count();
        $data['total'] = $total;
        $data['rows'] = $salesrecord;
        return json_encode($data);
    }
    /**
     * 插入老师数据
     * @return mixed
     */
    public function insert() {
        //获取登陆的校长的校区id
        $data = session("loginSession")['campusid'];
        $registrationModel =Db::name("Teacher");
        $validata = new TeacherValidate();
        if($_POST['teacher_befulldate']=="")$_POST["teacher_befulldate"]=time();
        if (!$validata->check($_POST)) {
            $returnData['status'] = 0;
            $returnData['msg'] = $validata->getError();
            return json_encode($returnData);
        } else if( count(Db::name("Teacher")->select())<5){
            $_POST['campusid'] = $data;
            $id = $registrationModel->insertGetId($_POST);
            $b = sprintf("%06d", $id);
            Db::name("user")->insert(["campusid" => $data, "username" => $b, "password" => $b, "typeid" => 2]);
            $returnData['status'] = 1;
            $returnData['msg'] = "<center><b style='color:blue;'>报名成功</b> <br/>账号密码为<b style='color:red;'>{$b}</b></center>";
            return json_encode($returnData);
		}else{
			$returnData['status'] = 0;
            $returnData['msg'] = "最多只能添加5位老师";
			return json_encode($returnData);
		}
    }

    /**
     * 更新教师数据
     * @return mixed
     */
    public function update() {

        $registrationModel = Db::name("Teacher");
        $validata = new TeacherValidate();
        if (!$validata->check($_POST)) {
            $returnData['status'] = 0;
            $returnData['msg'] = $validata->getError();
            return json_encode($returnData);
        } else {
            $registrationModel->update($_POST);
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
        $id = input('id');
        //获取要修改的老师信息
        $teacher = Teacher::get($id);
        $grades = Grade::where(["campusid" => $campusid])->select();
        $subjects = Subject::where(["campusid" => $campusid])->select();
        $salarytemp = Salarytemp::where(["campusid" => $campusid])->select();

        $this->assign("teacher", $teacher);
        $this->assign("grades", $grades);
        $this->assign("subjects", $subjects);
        $this->assign("salarytemps", $salarytemp);
        return $this->fetch('teacher/update');
    }

    /**
     * 删除老师信息
     */
    public function deleteByIDs() {
        $ids = input('ids');
        $ids = rtrim($ids, ",");
        $map['id'] = array('in', $ids);
        $deleteinfo = Teacher::where($map)->delete();
        if ($deleteinfo) {
            return json_encode(array("status" => 1, "msg" => "删除成功！"));
        } else {
            return json_encode(array("status" => 0, "msg" => "删除失败！"));
        }
    }

    public function arr_index($arrs) {
        $arr_index = array();
        foreach ($arrs as $arr) {
            $arr_index[$arr['id']] = $arr;
        }
        return $arr_index;
    }
    /*
     * 导出老师excel表格
     */
    public function export()
    {
        $campusid = session('loginSession')['campusid'];
        $campus = Campus::get($campusid);
        $xlsName = $campus->campus_name . "校区老师信息汇总表";
        $xlsCell = array(
            array("teacher_name","姓名"),
            array("teacher_gender","性别"),
            array("teacher_idcard","身份证"),
            array("teacher_bankaccount","银行卡号"),
            array("teacher_jobtype","在职类型"),
            array("grade_name","年级"),
            array("subject_name","科目"),
            array("teacher_telphone","电话"),
            array("teacher_email","邮箱"),
            array("teacher_qq","qq"),
            array("teacher_joindate","入职日期"),
            array("teacher_befulldate","转正日期"),
            array("teacher_status","是否在职"),
            array("teacher_remark","备注")
            );
        $join = [
            ["ew_grade grade","teacher.teacher_grade_id = grade.id"],
            ["ew_subject subject","teacher.teacher_subject_id = subject.id" ]
        ];
        $xlsData = Db::table('ew_teacher')->alias('teacher')->join($join)->order('teacher_joindate asc')->select();
         for ($i = 0; $i < count($xlsData); $i++) {
            if ($xlsData[$i]['teacher_gender'] == 0) {
                $xlsData[$i]['teacher_gender'] = "男";
            } else {
                $xlsData[$i]['teacher_gender'] = "女";
            }
            if ($xlsData[$i]['teacher_jobtype'] == 0) {
                $xlsData[$i]['teacher_jobtype'] = "兼职";
            } else {
                $xlsData[$i]['teacher_jobtype'] = "全职";
            }
            if($xlsData[$i]['teacher_status']==0){
                $xlsData[$i]['teacher_status'] = "离职";
            }else{
                $xlsData[$i]['teacher_status'] = "在职";
            }
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
                    $array[$i]['sales_ordertypeid'] = $v;
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
                    $array[$i]['sales_coursetypeid'] = $v;
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
                $array[$i]['campusid'] = $campusid;
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
                if( null != $a = Db::table("ew_ordertype")->field("id")->where(["order_typename"=>$arr[$i]["sales_ordertypeid"],"campusid"=>$campusid])->select()){
                     $arr[$i]["sales_ordertypeid"]=$a[0]['id'];
                 }else{
                    $errStrTotal = $errStrTotal.$errStr.'销售单类型不存在\n';
                     //return $errStr.'销售单类型不存在';
                 }
                 if( null != $a = Db::table("ew_coursetype")->field("id")->where(["coursetype_name"=>$arr[$i]["sales_coursetypeid"],"campusid"=>$campusid])->select()){
                     $arr[$i]["sales_coursetypeid"]=$a[0]['id'];
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


    /*
    获取订单流水号
    */
    public function build_order_no($teacherId)
    {
        return 'XS'.date('Ymd').$teacherId.substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 6), 1))), 0, 6);
    }


    /*
    ajax获得销售单类型JSON数据
    */
    public function getOrderTypeJson()
    {
        if($this->redis()){
            if($this->redis->EXISTS('DgetGJSON'))
                return $this->redis->get("DgetGJSON");

        }
        $campusid = session("loginSession")['campusid'];
        $list = Db::name("ordertype")->where(["campusid"=>$campusid])->select();
        if($this->redis()) {
            $this->redis->set("DgetGJSON",json_encode($list));

        }
        return json_encode($list);
    }


    /*
    ajax获得销售课程周期类型JSON数据
    */
    public function getCourseTypeJson()
    {
        if($this->redis()){
            if($this->redis->EXISTS('DgetGJSON'))
                return $this->redis->get("DgetGJSON");

        }
        $campusid = session("loginSession")['campusid'];
        $list = Db::name("coursetype")->where(["campusid"=>$campusid])->select();
        if($this->redis()) {
            $this->redis->set("DgetGJSON",json_encode($list));

        }
        return json_encode($list);
    }
}
