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
        //查输入的员工在库里有没有
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
        $returnArr = $this->salesClueValidCheck($_POST);
        $errStrTotal=$returnArr['errStrTotal'];
        $arr=$returnArr['arr'];
        $registrationModel = Db::name("Salesclue");
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
     * 	修改页面的显示
     */
    public function updatemodal() {
        $campusid = session("loginSession")['campusid'];
        $clue_id = input('id');
        //获取要修改的老师信息
        $salesclue= Salesclue::with("teacher")->where(["clue_id" => $clue_id])->find();
        $this->assign("salesclue", $salesclue);
        return $this->fetch('salesrecord/sales_clue_update');
    }

    /**
     * 删除线索信息
     */
    public function deleteByIDs() {
        $ids = input('ids');
        $ids = rtrim($ids, ",");
        $map['clue_id'] = array('in', $ids);
        $deleteinfo = Salesclue::where($map)->delete();
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
        $campus = Campus::get($campusid);
        $xlsName = $campus->campus_name . "招生线索表";
        $xlsCell = array(
            array("clue_student_name","学生姓名"),
            array("clue_student_age","学生年龄"),
            array("clue_student_sex","学生性别"),
            array("clue_telephone","联系电话"),
            array("clue_last_time","最后联系时间"),
            array("clue_last_content","最后联系内容"),
            array("clue_next_time","下次联系时间"),
            array("clue_status","线索状态"),
            array("teacher_name","咨询人员"),
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
        if(empty($_GET['starttime2'])){
            $start2 = 0;
            unset($_GET['starttime2']);
        }else{
            $start2 = $_GET['starttime2'];
            unset($_GET['starttime2']);
        };
        if(empty($_GET['endtime2'])){
            $end2 = '2100-12-31';
            unset($_GET['endtime2']);
        }else{
            $end2 = $_GET['endtime2'];
            unset($_GET['endtime2']);
        };
        //过滤结束==============================
        $path = $this->getDataByCampusid($_GET);
        $searchPath = $this->searchNotLike($path,$_GET,'clue_status');
        if(isset($searchPath['campusid'])){
            $searchPath['clue_campusid'] = $searchPath["campusid"];
            unset($searchPath["campusid"]);
        }
        if(isset($searchPath['teacher_name'])){
            $searchPath['teacher.teacher_name'] = $searchPath["teacher_name"];
            unset($searchPath["teacher_name"]);
        }
        $xlsData = Salesclue::with("teacher")->where($searchPath)->where('clue_last_time','between',"{$start},{$end}")->where('clue_next_time','between',"{$start2},{$end2}")->order("clue_next_time desc")->select();
        for($i=0;$i<sizeof($xlsData);$i++)
        {
            $xlsData[$i]['teacher_name']=$xlsData[$i]['teacher']['teacher_name'];
            unset($xlsData[$i]['teacher']['teacher_name']);
            if($xlsData[$i]['clue_student_sex']=='0'){
                $xlsData[$i]['clue_student_sex']='女';
            }else{
                $xlsData[$i]['clue_student_sex']='男';
            }
            if($xlsData[$i]['clue_status']=='0'){
                $xlsData[$i]['clue_status']='未确认';
            }else if ($xlsData[$i]['clue_status']=='1'){
                $xlsData[$i]['clue_status']='有效';
            }else{
                $xlsData[$i]['clue_status']='无效';
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
                    $array[$i]['clue_student_name'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==1){
                    $array[$i]['clue_student_age'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==2){
                    $array[$i]['clue_student_sex'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==3){
                    $array[$i]['clue_telephone'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==4){
                    $array[$i]['clue_last_time'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==5){
                    $array[$i]['clue_last_content'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==6){
                    $array[$i]['clue_next_time'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==7){
                    $array[$i]['clue_status'] = $v;
                    unset($array[$i][$k]);
                }
                if($k==8){
                    $array[$i]['teacher_name'] = $v;
                    unset($array[$i][$k]);
                }
                $array[$i]['clue_campusid'] = $campusid;
            }
           $arr[$i-2]=$array[$i];
           
        }
         for($i=0;$i<count($arr);$i++){
             $errStr='第'.$i.'行数据';
             $returnArr = $this->salesClueValidCheck($arr[$i]);
             $arr[$i] = $returnArr['arr'];
             if($returnArr['errStrTotal']!='') {
                 $errStrTotal = $errStrTotal.$errStr.$returnArr['errStrTotal'];
             }
             if($arr[$i]['clue_student_sex']=='女'){
                 $arr[$i]['clue_student_sex']='0';
             }else if ($arr[$i]['clue_student_sex']=='男'){
                 $arr[$i]['clue_student_sex']='1';
             }else{
                 $errStrTotal = $errStrTotal.$errStr."学生性别错误";
             }
             if($arr[$i]['clue_status']=='未确认'){
                 $arr[$i]['clue_status']='0';
             }else if ($arr[$i]['clue_status']=='有效'){
                 $arr[$i]['clue_status']='1';
             }else if ($arr[$i]['clue_status']=='无效'){
                 $arr[$i]['clue_status']='2';
             }else{
                 $errStrTotal = $errStrTotal.$errStr."线索状态错误";
             }
        }

        if($errStrTotal!='')
        {
            return $errStrTotal;
        }
       
        for($k=0;$k<count($arr);$k++){
            M("Salesclue")->insertGetId($arr[$k]);

        }
        
        return '1';
    }



}
