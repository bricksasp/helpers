<?php
namespace bricksasp\helpers\traits;

use Yii;

trait BaseTrait {


    public function getUserId()
    {
        return (int)Yii::$app->user->id;
    }
    
    public function success($data=[],$msg='success')
    {
        return [
            'status' => 200,
            'message' => $msg,
            'data' => $data
        ];
    }

    public function fail($msg='fail',$data=[])
    {
        return [
            'status' => 400,
            'message' => $msg,
            'data' => $data
        ];
    }

    public function wsuccess($data=[], $message='ok')
    {
        return json_encode(['controller' => $this->id, 'action' => $this->action->id, 'status'=>200, 'message'=>$message, 'data'=>$data],JSON_UNESCAPED_UNICODE);
    }

    public function wfail($message='ok', $data=[])
    {
        return json_encode(['controller' => $this->id, 'action' => $this->action->id, 'status'=>400, 'message'=>$message, 'data'=>$data],JSON_UNESCAPED_UNICODE);
    }

}