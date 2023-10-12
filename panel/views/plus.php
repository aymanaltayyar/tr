<div class="card">
    <div class="card-header">إعدادات البلاس</div>
    <div class="card-body">
    <?php 
        if(isset($_POST['update'])){
            if(is_numeric($_POST['goldClub']) && 
            is_numeric($_POST['Plus']) && 
            is_numeric($_POST['PLUS_TIME']) && 
            is_numeric($_POST['addonProduction']) &&
            is_numeric($_POST['PLUS_PRODUCTION']) &&
            is_numeric($_POST['plusFeatures']) &&
            is_numeric($_POST['storageUpgrade']) &&
            is_numeric($_POST['25pFaster']) &&
            is_numeric($_POST['allSmithy']) &&
            is_numeric($_POST['searchAll']) &&
            is_numeric($_POST['resources01']) &&
            is_numeric($_POST['resources02']) &&
            is_numeric($_POST['resources03']) &&
            is_numeric($_POST['resources01A']) &&
            is_numeric($_POST['resources02A']) &&
            is_numeric($_POST['resources03A']) &&
            is_numeric($_POST['protect01']) &&
            is_numeric($_POST['protect02']) &&
            is_numeric($_POST['protect03'])){
                foreach($_POST as $key => $value){
                    if($key!='update'){
                        $panel->sUpdate($key, $value);
                    }
                }
                header('Location: index.php?p=plus&m');
            }else{ header('Location: index.php?p=plus&d'); }
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
            <label>سعر نادي الذهب</label>
            <input class="form-control" name="goldClub" value="<?php echo $config['goldClub']; ?>">
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label>سعر حساب بلاس</label>
                <input class="form-control" name="Plus" value="<?php echo $config['Plus']; ?>">
            </div>
                <div class="col-md-6">
                    <label>مدة حساب بلاس</label>
                    <select name="PLUS_TIME" class="form-control">
                        <option <?php if(PLUS_TIME == 43200){ echo "selected"; } ?> value="43200">12 ساعة</option>
                        <option <?php if(PLUS_TIME == 86400){ echo "selected"; } ?> value="86400">24 ساعة</option>
                        <option <?php if(PLUS_TIME == 172800){ echo "selected"; } ?> value="172800">يومان</option>
                        <option <?php if(PLUS_TIME == 259200){ echo "selected"; } ?> value="259200">3 أيام</option>
                        <option <?php if(PLUS_TIME == 345600){ echo "selected"; } ?> value="345600">4 أيام</option>
                        <option <?php if(PLUS_TIME == 432000){ echo "selected"; } ?> value="432000">5 أيام</option>
                        <option <?php if(PLUS_TIME == 518400){ echo "selected"; } ?> value="518400">6 أيام</option>
                        <option <?php if(PLUS_TIME == 604800){ echo "selected"; } ?> value="604800">7 أيام</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <label>سعر زيادة الموارد</label>
                    <input class="form-control" name="addonProduction" value="<?php echo $config['addonProduction']; ?>">
                </div>
                <div class="col-md-6">
                    <label>مدة زيادة الموارد</label>
                    <select name="PLUS_PRODUCTION" class="form-control">
                        <option <?php if(PLUS_PRODUCTION == 43200){ echo "selected"; } ?> value="43200">12 ساعة</option>
                        <option <?php if(PLUS_PRODUCTION == 86400){ echo "selected"; } ?> value="86400">24 ساعة</option>
                        <option <?php if(PLUS_PRODUCTION == 172800){ echo "selected"; } ?> value="172800">يومان</option>
                        <option <?php if(PLUS_PRODUCTION == 259200){ echo "selected"; } ?> value="259200">3 أيام</option>
                        <option <?php if(PLUS_PRODUCTION == 345600){ echo "selected"; } ?> value="345600">4 أيام</option>
                        <option <?php if(PLUS_PRODUCTION == 432000){ echo "selected"; } ?> value="432000">5 أيام</option>
                        <option <?php if(PLUS_PRODUCTION == 518400){ echo "selected"; } ?> value="518400">6 أيام</option>
                        <option <?php if(PLUS_PRODUCTION == 604800){ echo "selected"; } ?> value="604800">7 أيام</option>
                    </select>
                </div>
            </div>
            <hr>
            <h6>إعدادات البلاس الإضافية <small class="alert-danger">* قم بجعل السعر 0 لتعطيل الخاصية</small></h6> 
            <br><div class="form-group">
                <label>تشغيل/تعطيل مميزات بلاس</label>
                <select name="plusFeatures" class="form-control">
                    <option <?php if($config['plusFeatures'] == 0){ echo "selected"; } ?> value="0">تعطيل</option>
                    <option <?php if($config['plusFeatures'] == 1){ echo "selected"; } ?> value="1">تشغيل</option>
                </select>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <label>سعر زيادة التخزين</label>
                    <input name="storageUpgrade" class="form-control" value="<?php echo $config['storageUpgrade']; ?>">
                </div>
                <div class="col-md-6">
                    <label>سعر +25% تدريب أسرع</label>
                    <input name="25pFaster" class="form-control" value="<?php echo $config['25pFaster']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <label>سعر ترقية الكل بالحداد</label>
                    <input name="allSmithy" class="form-control" value="<?php echo $config['allSmithy']; ?>">
                </div>
                <div class="col-md-6">
                    <label>سعر البحث عن جميع الوحدات بالأكاديمية</label>
                    <input name="searchAll" class="form-control" value="<?php echo $config['searchAll']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <label>سعر باقة الموارد الأولي</label>
                    <input name="resources01" class="form-control" value="<?php echo $config['resources01']; ?>">
                </div>
                <div class="col-md-6">
                    <label>سعر باقة الموارد الثانية</label>
                    <input name="resources02" class="form-control" value="<?php echo $config['resources02']; ?>">
                </div>
            </div>
            <div class="form-group">
                <label>سعر باقة الموارد الثالثة</label>
                <input name="resources03" class="form-control" value="<?php echo $config['resources03']; ?>">
            </div>

            <div class="form-group row">
                <div class="col-md-6">
                    <label>كمية الموارد الأولي</label>
                    <input name="resources01A" class="form-control" value="<?php echo $config['resources01A']; ?>">
                </div>
                <div class="col-md-6">
                    <label>كمية الموارد الثانية</label>
                    <input name="resources02A" class="form-control" value="<?php echo $config['resources02A']; ?>">
                </div>
            </div>
            <div class="form-group">
                <label>كمية الموارد الثالثة</label>
                <input name="resources03A" class="form-control" value="<?php echo $config['resources03A']; ?>">
            </div>

            <div class="form-group row">
                <div class="col-md-6">
                    <label>سعر ساعة الحماية</label>
                    <input name="protect01" class="form-control" value="<?php echo $config['protect01']; ?>">
                </div>
                <div class="col-md-6">
                    <label>سعر 3 ساعات حماية</label>
                    <input name="protect02" class="form-control" value="<?php echo $config['protect02']; ?>">
                </div>
            </div>
            <div class="form-group">
                <label>سعر 6 ساعات حماية</label>
                <input name="protect03" class="form-control" value="<?php echo $config['protect03']; ?>">
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">تعديل</button>
            </div>
        </form>
    </div>
</div>