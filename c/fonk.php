<?php
class Ydil {
    public $conn;

    const base_url = "https://sqooler.com/";

    public function __construct(){
        try {
            $this->conn = new PDO("mysql:host=localhost;dbname=u358942480_sqooler", "u358942480_sqooler", "Fc0=0H8Bu0%y");
            $this->conn->exec("set names utf8");
        }
        catch ( PDOException $e )
        {
            print $e->getMessage();

        }
    }

    public function get($querySql){

        $query=$this->conn->prepare($querySql);
        $query->execute();
        $response=$query->fetchAll(PDO::FETCH_ASSOC);
        return $response;
    }

    public function getone($querySql){

        $query=$this->conn->prepare($querySql);
        $query->execute();
        $response=$query->fetch(PDO::FETCH_ASSOC);
        return $response;
    }

    public function updateCustomize($sql){

        $stmt  = $this->conn->prepare($sql);
        $stmt->execute();
        if (!$stmt->execute()) {
            return ['status'=>0,'message'=>$stmt->errorInfo()];
        } else {
            return ['status'=>1,'message'=>'Updated'];
        }
    }

    public function find($endpoint,$column,$id,$projection=[]){
        $sql="SELECT";
        if(count($projection)>0){
            $sql.=implode(",",$projection);
        }
        else
        {
            $sql.=" * FROM ".$endpoint." WHERE $column=".$id;
        }


        $query=$this->conn->prepare($sql);
        $query->execute();
        $response=$query->fetch(PDO::FETCH_ASSOC);
        return $response;
    }

    public function finds($endpoint,$column,$id,$projection=[]){
        $sql="SELECT";
        if(count($projection)>0){
            $sql.=implode(",",$projection);
        }
        else
        {
            $sql.=" * FROM ".$endpoint." WHERE $column=".$id;
        }



        $query=$this->conn->prepare($sql);
        $query->execute();
        $response=$query->fetchAll(PDO::FETCH_ASSOC);
        return $response;
    }

    public function newInsert($table,$dataArray,$values){
        $ins_query = 'INSERT INTO `' . $table . '` ';
        $columns = array();
        $columns_bindings = array();

        foreach ($dataArray as $column_name => $data) {
            $columns[] = $data;
            $columns_bindings[] = ':' . $data;
        }
        $ins_query = $ins_query . '(' . implode(', ', $columns) . ') VALUES (' . implode(', ', $columns_bindings) . ')';


        $stmt = $this->conn->prepare($ins_query);
        foreach ($dataArray as $key => $data) {
            $stmt->bindValue(":" . $data, $values[$key]);
        }
         
        if (!$stmt->execute()) {
            return ['status'=>0,'message'=>$stmt->errorInfo(),'id'=>$this->conn->lastInsertId()];

        } else {
            return ['status'=>1,'message'=>'Inserted','id'=>$this->conn->lastInsertId()];
        }


    }

    public function newUpdate($table,$columns,$values,$columnId){
        $ins_query = 'UPDATE `' . $table . '`  SET ';
        $set = [];
        foreach ($columns as $column) {
            $set[] = "$column = :$column";
        }
        $set = implode(", ", $set);

        $sql="UPDATE $table SET $set WHERE $columnId = :$columnId";


        $stmt = $this->conn->prepare($sql);
        foreach ($columns as $key => $data) {

            $val=$values[$key];
            $stmt->bindValue(":" . $data, $val);


        }
        if (!$stmt->execute()) {
            return ['status'=>0,'message'=>$stmt->errorInfo()];

        } else {
            return ['status'=>1,'message'=>'Updated'];
        }
    }

    public function insert($table,$dataArray,$values){
        $ins_query = 'INSERT INTO `' . $table . '` ';

        $columns = array();
        $columns_bindings = array();
        foreach ($dataArray as $column_name => $data) {
            $columns[] = $data;
            $columns_bindings[] = ':' . $data;
        }

        $ins_query = $ins_query . '(' . implode(', ', $columns) . ') VALUES (' . implode(', ', $columns_bindings) . ')';



        $stmt = $this->conn->prepare($ins_query);

        foreach ($dataArray as $key => $data) {
            if($data=="image" || $table=="vehicle_files"){
                $val=$values[$key];
            }
            else if(gettype($values[$key])=="double" || gettype($values[$key])=="float"){
                $val=$values[$key];
            }
            else
            {
                $val=mb_strtoupper($values[$key]);
            }
            $stmt->bindValue(":" . $data, $val);
        }

        if (!$stmt->execute()) {
            return ['status'=>0,'message'=>$stmt->errorInfo()];

        } else {
            return ['status'=>1,'message'=>'Inserted','id'=>$this->conn->lastInsertId()];
        }
    }

    public function customQuery($query){
        $stmt=$this->conn->prepare($query);
        $resp=$stmt->execute();

        return $resp;
    }

    public function customizedQuery($query){
        $stmt=$this->conn->prepare($query);
        $stmt->execute();
        $response=$stmt->fetch(PDO::FETCH_ASSOC);
        return $response;
    }

    public function removeImageFromDirectory($directory,$filename){
        $p=$directory.$filename;


        $existenceFile=glob($p);
        if($existenceFile){
            unlink($p);
            return ['status'=>1,'message'=>'File Deleted.'];
        }
        else
        {
            return ['status'=>0,'message'=>'File Cannot Find In Path.'];
        }

    }

    public function removeMultipleFilesFromDirectory($data,$directory,$table){
        foreach($data as $file){
            if($file["type"]=="jpeg" || $file["type"]=="jpg" || $file["type"]=="png"){
                $path="images/";
            }
            else
            {
                $path="files/";
            }
            $newPath=$directory.$path;
            $this->removeImageFromDirectory($newPath.$file["name"],$file["name"]);
            $this->delete($table,$file["id"]);
        }
        return ['status'=>1,'message'=>'Files Deleted.'];
    }

    public function update($table,$columns,$values){
        $set = [];

        foreach ($columns as $column) {
            $set[] = "$column = :$column";
        }

        $set = implode(", ", $set);
        $sql="UPDATE $table SET $set WHERE id = :id";


        $stmt = $this->conn->prepare($sql);
        foreach ($columns as $key => $data) {
            if($data=="image"){
                $val=$values[$key];
            }
            else
            {
                $val=mb_strtoupper($values[$key]);
            }
            $stmt->bindValue(":" . $data, $val);
        }

        if (!$stmt->execute()) {
            return ['status'=>0,'message'=>$stmt->errorInfo()];

        } else {
            return ['status'=>1,'message'=>'Updated'];
        }
    }

    public function delete($table,$id,$idKey){
        $sql="DELETE FROM $table WHERE $idKey=".$id;
        $stmt=$this->conn->prepare($sql);
        if (!$stmt->execute()) {
            return ['status'=>0,'message'=>$stmt->errorInfo()];

        } else {
            return ['status'=>1,'message'=>'Deleted'];
        }
    }

    public function deleteMessage(){
        echo "<script>alert('Kayıt Silindi!'); setTimeout(()=>{
                window.open('index.php','_self')
            },1000)</script>";
    }

    public function swalToggle($variant,$title,$message,$redirect=""){
        if($redirect!=""){
            echo "<script>swal('$title', '$message', '$variant').then((value) => {
				  window.open('$redirect','_self');
				});</script>";
        }
        else
        {
            echo "<script>swal('$title', '$message', '$variant');</script>";
        }


    }

    public function customStatusBadge($status,$endpoint=""){
        $badge="";
        if($endpoint==""){
            if($status==0){
                $badge='<span class="badge badge-danger">Pasif</span>';
            }
            if($status==1){
                $badge='<span class="badge badge-success">Aktif</span>';
            }
        }
        else{
            if($endpoint=="vehicles"){
                if($status==0){
                    $badge='<span class="badge badge-danger">PASİF</span>';
                }
                if($status==1){
                    $badge='<span class="badge badge-success">BOŞTA</span>';
                }
                if($status==2){
                    $badge='<span class="badge badge-primary">KİRADA</span>';
                }
                if($status==3){
                    $badge='<span class="badge badge-warning">SERVİSTE</span>';
                }
                if($status==4){
                    $badge='<span class="badge badge-light">ÖZEL KULLANIM</span>';
                }
                if($status==5){
                    $badge='<span class="badge badge-danger">HASARLI</span>';
                }
                if($status==6){
                    $badge='<span class="badge badge-dark">REZERVE</span>';
                }
                if($status==7){
                    $badge='<span class="badge badge-success">SATILDI</span>';
                }
            }
        }

        return $badge;
    }

    public function getRelatedFiles($endpoint,$field,$id){
        $sql="SELECT * FROM ${endpoint} WHERE  ${field}=${id}";
        $files=$this->get($sql);

        $mapped["images"]=[];
        $mapped["files"]=[];
        foreach($files as $f){
            if($f["type"]=="jpg" || $f["type"]=="jpeg" || $f["type"]=="png")
            {
                $mapped["images"][]=$f;
            }
            else
            {
                $mapped["files"][]=$f;
            }
        }
        return $mapped;

    }

    public function checkLoggedIn(){
        if(!isset($_SESSION["userData"])){
            $login_path=self::base_url."giris.php";
            session_destroy();
            header('Location: '.$login_path);
        }
        else
        {
            return $_SESSION["userData"];
        }
    }

}

?>
