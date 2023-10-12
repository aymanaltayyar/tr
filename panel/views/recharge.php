<div class="card">
    <div class="card-header">شحن باقة ذهب للاعب</div>
    <div class="card-body">
    <?php 
        if(isset($_POST['addGold'])){
            if(is_numeric($_POST['goldA']) && is_numeric($_POST['uid'])){
                $database->query("UPDATE users SET gold = gold + ".$_POST['goldA']." WHERE id = ".$_POST['uid']."");
                $database->sendMessage($_POST['uid'], 6, 'تم الشحن','تم شحن رصيدك بقيمة '.$_POST['goldA'].' ذهبة.', 0, 0, 0, 0,0);
                echo '<div class="alert alert-success">تم شحن رصيد الاعب بنجاح.</div>';
            }else{
                echo '<div class="alert alert-danger">مدخلات خاطئة.</div>';
            }
        }
    ?>
        <form action="" method="post">
        <input type="hidden" name="addGold" value="1">
        <div class="form-group">
        <label>باقة الذهب</label>
            <select name="goldA" class="form-control">
            <?php foreach($packages as $package){ ?>
                <option value="<?php echo $package['gold']; ?>">باقة <?php echo $package['cost'].$package['currency']; ?> - القيمة <?php echo $package['gold']; ?> ذهب</option>
            <?php } ?>
            </select>
        </div>
        <div class="form-group">
        <label>الاعب</label>
            <select name="uid" class="form-control">
            <?php 
                $users = $database->query('SELECT id,username FROM users WHERE id >5');
                foreach($users as $user){
            ?>
                <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
            <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">تأكيد</button>
        </div>
        </form>
    </div>
</div>