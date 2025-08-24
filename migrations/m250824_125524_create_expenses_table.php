<?php


use yii\db\Migration;

/**
 * Handles the creation of table `{{%expense}}`.
 */
class m250824_125524_create_expenses_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%expense}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'category' => $this->string(64)->notNull(),
            'description' => $this->text()->null(),
            'spent_at' => $this->date()->notNull(),
            'status' => $this->string(16)->notNull()->defaultValue('pending'), // pending|approved|rejected
            'receipt_path' => $this->string()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-expense-user_id', '{{%expense}}', 'user_id');

        // Skip foreign key for SQLite
        if ($this->db->driverName !== 'sqlite') {
            $this->addForeignKey('fk-expense-user_id', '{{%expense}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        }

        $this->createIndex('idx-expense-status', '{{%expense}}', 'status');
        $this->createIndex('idx-expense-spent_at', '{{%expense}}', 'spent_at');
    }

    public function safeDown()
    {
        if ($this->db->driverName !== 'sqlite') {
            $this->dropForeignKey('fk-expense-user_id', '{{%expense}}');
        }

        $this->dropTable('{{%expense}}');
    }
}
