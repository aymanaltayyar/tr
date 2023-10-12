<div class="card">
<div class="card-header">التحكم بالتسجيل</div>
<div class="card-body">
<?php 
if(isset($_POST['rEdit'])){
    if($_POST['regstatus']){
        $database->query("UPDATE config SET regstatus = 1");
    }else{
        $database->query("UPDATE config SET regstatus = 0");
    }

    echo '<div class="alert alert-success">تم تعديل حالة التسجيل بنجاح</div>';
}
?>

<form action="" method="post">
	<div class="form-group">
		<label>حالة التسجيل</label>
		<select class="form-control" name="regstatus">
            <option <?php if($database->config()['regstatus']){ echo 'selected'; } ?> value="1">يعمل</option>
            <option <?php if(!$database->config()['regstatus']){ echo 'selected'; } ?> value="0">موقوف</option>
            </select>

	</div>
	<div class="form-group">
		<button name="rEdit" class="btn btn-primary">تعديل</button>
	</div>
</form>
</div>
</div>
