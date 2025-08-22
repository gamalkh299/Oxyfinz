<?php

use yii\db\Migration;

class m250822_211917_seed_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $time = time();


        $this->insert('{{%users}}', [
            'first_name'   => 'System',
            'last_name'    => 'Admin',
            'email'        => 'admin@oxyfinz.local',
            'username'     => 'admin',
            'auth_key'     => Yii::$app->security->generateRandomString(),
            'access_token' => Yii::$app->security->generateRandomString(32),
            'password_hash'=> Yii::$app->security->generatePasswordHash('admin123'),
            'role'         => 'admin',
            'status'       => 1,
            'created_at'   => $time,
            'updated_at'   => $time,
        ]);


        $this->insert('{{%users}}', [
            'first_name'   => 'Test',
            'last_name'    => 'User',
            'email'        => 'user@oxyfinz.local',
            'username'     => 'user',
            'auth_key'     => Yii::$app->security->generateRandomString(),
            'access_token' => Yii::$app->security->generateRandomString(32),
            'password_hash'=> Yii::$app->security->generatePasswordHash('user123'),
            'role'         => 'user',
            'status'       => 1,
            'created_at'   => $time,
            'updated_at'   => $time,
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%user}}', ['username' => ['admin', 'user']]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250822_211917_seed_users cannot be reverted.\n";

        return false;
    }
    */
}
