<?php

namespace devmary\auth\models;

use Yii;

/**
 * This is the model class for table "google_auth".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $secret_code
 * @property integer $status
 * @property integer $backup_code
 * @property integer $active
 */
class GoogleAuth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'google_auth';
    }


    public function getGoogleAuth(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'secret_code', 'status'], 'required'],
            [['user_id', 'status', 'backup_code', 'active'], 'integer'],
            [['secret_code'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'secret_code' => 'Secret Code',
            'status' => 'Status',
            'backup_code' => 'Backup Code',
            'active' => 'Active',
        ];
    }
}
