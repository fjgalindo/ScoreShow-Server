<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for server responses.
 *
 * @property int $platform
 * @property int $title
 * @property string $link
 *
 * @property Platform $platform0
 * @property Title $title0
 */
class ServerResponse extends \yii\web\Response
{

    function __construct ($code, $extra_info = null){
        $responses = include "server/responses.php";

        parent::__construct();
        $this->format = Yii::$app->response->format;

        if(isset($responses[$code])){
            $response = $responses[$code];
            $this->data = $response['data'];
            $extra_info ? $this->data['extra_info'] = $extra_info : '';
            $this->statusCode = $response['status'];
        }else{
            throw new \yii\web\ServerErrorHttpException("Server error: Trying to send invalid status code $code", 50);
        }

    }

}
