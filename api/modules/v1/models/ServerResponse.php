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
    public $code;

    public function __construct($code, $message = "", $extra_info = null)
    {
        $responses = include "server/responses.php";

        parent::__construct();
        $this->format = Yii::$app->response->format;
        $this->setCode($code);

        if (isset($responses[$code])) {
            $response = $responses[$code];
            $this->data = $response['data'];
            $message && $this->data['status_message'] = $message;
            $extra_info && $this->data['extra_info'] = $extra_info;
            $this->statusCode = $response['status'];
        } else {
            throw new \yii\web\ServerErrorHttpException("Server error: Trying to send invalid status code $code", 50);
        }
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

}
