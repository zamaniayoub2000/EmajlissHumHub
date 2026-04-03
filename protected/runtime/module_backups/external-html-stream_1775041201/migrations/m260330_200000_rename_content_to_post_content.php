<?php

use yii\db\Migration;

/**
 * Renomme la colonne 'content' en 'post_content' dans majliss_synced_post.
 *
 * Raison : ContentActiveRecord possède une méthode getContent()
 * qui retourne la relation Content. Un champ DB nommé 'content'
 * la masque, causant "Call to a member function on string".
 */
class m260330_200000_rename_content_to_post_content extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('majliss_synced_post', 'content', 'post_content');
    }

    public function safeDown()
    {
        $this->renameColumn('majliss_synced_post', 'post_content', 'content');
    }
}
