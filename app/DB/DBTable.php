<?php

namespace App\DB;


final class DBTable
{
    public static function createTable($nameTable, array $column)
    {
        $string =  implode(',',$column);
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . $nameTable;
        $sql = "CREATE TABLE  IF NOT EXISTS `$table_name`(id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,$string) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        return $charset_collate;
    }

    public static function insert($table_name,$columns,$values)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table_name;
        
        $wpdb->query("INSERT INTO `$table_name` (`$columns`) VALUES ('$values');");
    }

    public static function selectAll($table, $cols = null, $ordre = null, $limit = null){
         global $wpdb;

        //  $table = $wpdb->prefix . $table;
        if ($cols == null){
            $sql = "SELECT * FROM $table";
        }
        if($ordre != null)
        {
            $sql = $sql." ORDER BY $ordre";
        }
        if($limit != null)
        {
            $sql = $sql." LIMIT $limit";
        }
        $selects = $wpdb->get_results($sql,ARRAY_A);

        return $selects;
        
    }

    public static function selectFirst($table, $id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $first = $wpdb->get_results("SELECT * FROM $table_name
                                    LIMIT 0,1");

        return $first;
    }

    public static function last($table)
    {
        global $wpdb;
        $last = $wpdb->get_results("SELECT * FROM $table
                                      ORDER BY ID DESC
                                      LIMIT 1");

        return $last[0];
    }

    public static function exist($table, $colname, $value){
         if(count(self::selectAll($table) )> 0){
             foreach (self::selectAll($table) as $table){
                 var_dump(strcmp($table[$colname],$value));
                 if(strcmp($table[$colname],$value)  === 0) {
                     return $trouve = true;
                 }else{
                     return $trouve = false;
                 }
             }
         }

         return false;
    }

    public static function drop($table)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
         $wpdb->query("DROP TABLE `$table_name`");
    }

    public static function truncate($table)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $wpdb->query("TRUNCATE TABLE `$table_name`");
    }

    /*
    * format des données
    * sous la forme '%s', '%d','%f'
    *
    *@data array associatif ['key'=>valeur]
    *
    * @params $table, $id, $data, $format
    * */
    public static function update($table, $id, $data, $format)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;

        $wpdb->update(
            $table_name,
            $data,
            array('id' => $id),
            $format, array('%d')
        );
        $annonce = self::selectFirst($table,$id);

        return $annonce;
    }

    /*
    * format des données
    * sous la forme '%s', '%d','%f'
    *
    *@data array associatif ['key'=>valeur]
    *
    * @params $table, $data, $format
    * */
    public static function save_orm($table, $data, $format)
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix .$table, $data, $format);
        return $wpdb->insert_id;
    }
    
    public static function insertGetId($table, $data, $format)
    {
        global $wpdb;
        $wpdb->insert($table, $data, $format);
        return $wpdb->insert_id;
    }

    public static function delete($table_name, $id)
    {
        global $wpdb;
        $table = $wpdb->prefix . $table_name;
        $wpdb->delete( $table, array( 'id' => $id ) );
    }

    public static function selectWhere($table, $champs, $value){

        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        $select = $wpdb->get_results("SELECT * FROM $table_name WHERE $champs = $value");
        return $select;

    }

}