<script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
<?php if($_POST['submit']){
    $global->updateGlobal($_POST['msg']);
} ?>
<div class="card">
<div class="card-header">رسالة النظام</div>
<div class="card-body">
<form action="" method="post">
    <textarea name="msg"><?php echo $global->getMsg(); ?></textarea>
    <br>
    <button type="submit" value="submit" name="submit" class="btn btn-primary">إرسال</button>    

</form>
<script>
CKEDITOR.replace( 'msg' );

</script>
</div>
</div>