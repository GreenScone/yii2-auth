<?php

use yii\db\Migration;

/**
 * Handles the creation of table `google_auth`.
 */
class m180201_121501_create_google_auth_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('google_auth', [
            'id' => $this->primaryKey()->notNull(),
            'user_id' => $this->integer(11)->notNull(),
            'secret_code' => $this->string(32)->notNull(),
            'status' => $this->boolean()->notNull(),
            'backup_code' => $this->integer(32)->notNull(),
            'active' => $this->boolean()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('google_auth');
    }
}
