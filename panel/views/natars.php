<div class="card mb-2">
    <div class="card-header">مواعيد السيرفر</div>
    <div class="card-body">
    <?php 
        if(isset($_POST['update'])){
            if(is_numeric($_POST['OPENING']) 
            && is_numeric($_POST['ARTEFACTS']) 
            && is_numeric($_POST['WW_PLAN'])){
                foreach($_POST as $key => $value){
                    if($key!='update'){
                        $panel->sUpdate($key, $value);
                    }
                }
                header('Location: index.php?p=natars&m');
            }else{ header('Location: index.php?p=natars&d'); }
        }

        if(isset($_GET['m'])){
            echo '<div class="alert alert-success">تم تعديل الإعدادات بنجاح</div>';
        }elseif(isset($_GET['d'])){
            echo '<div class="alert alert-danger">كل المدخلات يجب أن تكون أرقام.</div>';
        }
    ?>
        <form action="" method="post">
        <input type="hidden" name="update" value="setting">
            <div class="form-group">
                <label>موعد إفتتاح السيرفر</label>
                <input class="form-control" name="OPENING" type="text" value="<?php echo OPENING; ?>">
                <small><?php echo date('Y/m/d h:s', OPENING); ?></small>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <label>موعد ظهور التحف</label>
                    <input class="form-control" name="ARTEFACTS" type="text" value="<?php echo ARTEFACTS; ?>">
                    <small><?php echo date('Y/m/d h:s', ARTEFACTS); ?></small>
                </div>
                <div class="col-md-6">
                    <label>موعد مخططات البناء</label>
                    <input class="form-control" name="WW_PLAN" type="text" value="<?php echo WW_PLAN; ?>">
                    <small><?php echo date('Y/m/d h:s', WW_PLAN); ?></small>
                </div>
            </div>
        <hr>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">تعديل</button>
        </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">هام</div>
    <div class="card-body">
        التاريخ ووقت السيرفر: <?php echo date('Y/m/d h:s', time()); ?><br>
        التاريخ بتوقيت الثانية: <?php echo time(); ?>
        <br>
        <br>
    الصيغة المستعملة للتواريخ هي <b>Timestamp</b>.<br>
        للحصول على صيغة التوقيت المستعملة يمكنك إستعمال إحدي هذه المواقع.
    <br>
    <a href="https://www.unixtimestamp.com/index.php">https://www.unixtimestamp.com/index.php</a><br>
    <a href="https://www.timestampconvert.com/">https://www.timestampconvert.com/</a><br>
    </div>
</div>