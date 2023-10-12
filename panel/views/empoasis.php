<div class="card">
    <div class="card-header">تفريغ قوات الواحات</div>
    <div class="card-body">
        <?php 
            if($_POST['eOasis']){
                $panel->emptyO();
                echo '<div class="alert alert-success">تم تفريغ الواحات من الوحوش.</div>';
            }
        ?>
        <input type="button" style="width:100%" name="btn" value="تفريغ" id="submitBtn" data-toggle="modal" data-target="#confirm-submit" class="btn btn-primary" />
        <div class="modal fade" id="confirm-submit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    تأكيد
                </div>
                <div class="modal-body">
                    هل أنت متأكد من العملية؟ العملية غير قابلة للاستعادة بعد تفريغ جيوش الواحات.<br><br>
                    قد تستغرق العملية بعض الوقت.
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">إلغاء</button>
                    <form action="" method="post">
                        <input type="hidden" name="eOasis" value="1">
                        <button href="#" type="submit" class="btn btn-danger danger">تأكيد</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>