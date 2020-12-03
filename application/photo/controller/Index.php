<?php
namespace app\photo\controller;

use app\photo\logic\Logic;
use app\photo\model\Photo;
use app\photo\model\User;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return view('login');
    }
    public function login(){
//        接收登录的用户名密码
        $param=input();
//        验证验证码是否正确
        $captcha=$param['captcha'];
        if(!captcha_check($captcha)){
            //验证失败
            $this->error("验证码错误");
        }
//        验证参数
        $result = $this->validate(
           $param,
            [
                'name'  => 'require|token',
                'password'   => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
           $this->error($result);
        }
//        验证用户名密码是否跟表里一致
        $data=User::where('name',$param['name'])
            ->where('password',md5($param['password']))
            ->find();
        if($data){
//            登录成功使用session'记住用户
            session('user',$data,600);
            return redirect('photo');
        }else{
            $this->error('用户名或密码错误');
        }
    }
//    退出登录
    public function loginout(){

        $data=session('user',null);
        if($data){
            return redirect('index');
        }
    }
    public function photo(){
//        查询出图片的信息
        $id=session('user.user_id');
        $data=Photo::where('uid',$id)->paginate(5);
        return view('photo',['data'=>$data]);
    }
    public function file(){
//        接收参数
        $param=input();
//        验证参数
        $result = $this->validate(
            $param,
            [
                'title'  => 'require',
                'cate'   => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
//        文件上传
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('src');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size'=>2000000,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            $param['src']= $info->getSaveName();
//            拼接入库的数组
            $id=session('user.user_id');
            $data=[
                'pid'=>1,
                'uid'=>$id,
                'cate'=>$param['cate'],
                'title'=>$param['title'],
                'src'=>$param['src'],
                'comment'=>20,
                'create'=>date('Y-m-d')
            ];
//            把上传的图片信息入库
            $data=Photo::create($data,true);
           if($data){
               return redirect('photo');
           }else{
               $this->error('上传失败');
           }
        }else{
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }
//    根据关键词搜索图片
    public function search(){
//        接收参数
        $param=input();
//        验证参数
        $result = $this->validate(
            $param,
            [
                'search'  => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $data=Logic::seach($param);
        return view('search',['data'=>$data]);
    }
}
