<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%caches}}`.
 */
class m210501_211906_create_yii2_telegram_bot_scelation_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%yii2_telegram_bot_scelation_state}}', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->integer(40)->notNull(),
            'state' => $this->json()->notNull(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'status' => $this->smallInteger(2)
        ]);

        // creates index for column `chat_id`
        $this->createIndex(
            '{{%idx-yii2_telegram_bot_scelation_state-chat_id}}',
            '{{%yii2_telegram_bot_scelation_state}}',
            'chat_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%yii2_telegram_bot_scelation_state}}');
    }
}
