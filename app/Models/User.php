<?php
namespace App\Models;


use App\DB\DBTable;
use WeDevs\ORM\Eloquent\Model;
use WeDevs\ORM\Eloquent\Facades\DB;


class User extends Model
{
    protected $table = "users";
    private static  $table_name = 'users';
   
    private static  $format = ['%s','%s','%s'];

    protected $fillable = ['*'];

    /**
     * Disable created_at and update_at columns, unless you have those.
     */
    public $timestamps = false;

    /** Everything below this is best done in an abstract class that custom tables extend */

    /**
     * Set primary key as ID, because WordPress
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Make ID guarded -- without this ID doesn't save.
     *
     * @var string
     */
    // protected $guarded = ['id','img_link','file_link','categorie', 'titre', 'auteur', 'nombre_page', 'langue', 'editeur', 'nombre_telechargement'];


    public function register(){
        self::create();
    }

    /**
     * Overide parent method to make sure prefixing is correct.
     *
     * @return string
     */
    public function getTable(){
        if (isset($this->table)){
            $prefix =  $this->getConnection()->db->prefix;
            return $prefix . $this->table;
        }
        return parent::getTable();
    }

   
    public static function _updateUserMeta($userId, $data){

       if(!is_array($data) || !$userId) return;
        $userMeta = DB::table('usermeta')->where('user_id', $userId)->update($data);
        return $userMeta;
    }    
}