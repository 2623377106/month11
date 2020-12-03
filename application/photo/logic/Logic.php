<?php


namespace app\photo\logic;


use app\photo\model\Photo;

class Logic
{
//    封装搜索的代码
    static public function seach($param){
        $data=Photo::where("title like '%{$param['search']}%'")->paginate(5,['query'=>$param['search']],$param);
        if($data){
            return $data;
        }
    }
}