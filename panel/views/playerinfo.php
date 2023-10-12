<table class="table" cellpadding="1" cellspacing="1" >

			<thead>

				<tr>

					<th colspan="2">الاعب <a href="index.php?p=player&uid=<?php echo $user['id'];?>"><?php echo $user['username'];?></a></th>

				</tr>

				<tr>

					<td>التفاصيل</td>

					<td>الوصف</td>

				</tr>

			</thead>

			<tbody>

				<tr>

					<td class="empty"></td><td class="empty"></td>

				</tr>

				<tr>

					<td class="details">

						<table cellpadding="0" cellspacing="0">

							<tr>

								<th>الرتبة</th>

								<td>????<?php /* echo $ranking->searchRank($user['id'], "rank");*/ ?></td>

							</tr>

							<tr>

								<th>القبيلة</th>

								<td>

									<?php

										if($user['tribe'] == 1)

										{

											echo "الرومان";

										}

										else if($user['tribe'] == 2)

										{

											echo "الجرمان";

										}

										else if($user['tribe'] == 3)

										{

											echo "الاغريق";

										}

										else if($user['tribe'] == 4)

										{

											echo "وحوش";

										}

										else if($user['tribe'] == 5)

										{

											echo "التتار";

										}

									?>

								</td>

							</tr>

							<tr>

								<th>التحالف</th>

								<td>

									<?php

										if($user['alliance'] == 0)

										{

											echo "-";

										}

										else

										{

											echo "<a href=\"?p=alliance&aid=".$user['alliance']."\">".$database->getAllianceName($user['alliance'])."</a>";

										}

									?>

								</td>

							</tr>

							<tr>

								<th>القري</th>

								<td><?php echo count($varray);?></td>

							</tr>

							<tr>

								<th>السكان</th>

								<td><?php echo $totalpop;?> <a href="?action=recountPopUsr&uid=<?php echo $user['id'];?>"><?php echo $refreshicon; ?></a></td>

							</tr>

							<tr>

								<th>العمر</td>

								<td>

									<?php

										if(isset($user['birthday']) && $user['birthday'] != 0)

										{

											$age = date("Y")-substr($user['birthday'],0,4);

											echo $age;

										}

										else

										{

											echo "<font color=\"red\">غير متاح</font>";

										}

									?>

								</td>

							</tr>

							<tr>

								<th>النوع</td>

								<td>

									<?php

										if(isset($user['gender']) && $user['gender'] != 0)

										{

											$gender = ($user['gender']== 1)? "ذكر" : "انثي";

											echo $gender;

										}

										else

										{

											echo "<font color=\"red\">غير متاح</font>";

										}

									?>

								</td>

							</tr>



							<tr>



								<th>الموقع</th>

								<td>

									<input type="text" style="width: 80%;" disabled="disabled" class="fm" name="location" value="<?php echo $user['location']; ?>">  <a href="index.php?p=editUser&uid=<?php echo $id; ?>"><img src="../img/Admin/edit.gif" title="تعديل الموقع"></a>

								</td>

							</tr>



							<tr>

								<?php if($_SESSION['access'] == 9){?><th>كلمة المرور</th>

								<td>

									تغيير <a href="index.php?p=editPassword&uid=<?php echo $id; ?>"><img src="../img/Admin/edit.gif" title="Change Password"></a>

								</td>

							</tr>



							<tr>

								<?php include("playerplusbonus.php"); ?>

								<tr>

<th>البريد</th>

<td>

	<input disabled="disabled" style="width: 80%;" class="fm" name="email" value="<?php echo $user['email']; ?>"> <a href="index.php?p=editUser&uid=<?php echo $id; ?>"><img src="../img/Admin/edit.gif" title="Edit Email"></a>

</td>

</tr>
<tr>

<th>الاي بي</th>
<?php $pData = $database->queryFetch("SELECT * FROM palevo WHERE uid = ".$user['id'].""); ?>
<td>

	<input disabled="disabled" style="width: 80%;" class="fm" name="email" value="<?php echo $pData['infa']; ?>">

</td>

</tr>

                                   <?php }   ?>



							<tr>

								<td colspan="2" class="empty"></td>

							</tr>



							<?php

								if($_SESSION['access'] >=8)

								{

									echo '

									<tr>

										<td colspan="2">

											<a href="?p=editUser&uid='.$user['id'].'"><font color="blue">&raquo;</font> تعديل الاعب</a>

										</td>

									</tr>';

								}

								else if($_SESSION['access'] == MULTIHUNTER)

								{

									echo '';

								}

								if($_SESSION['access'] == ADMIN)

								{

									echo '

									<tr>

										<td colspan="2">

											<a class="rn3" href="?p=deletion&uid='.$user['id'].'"><font color="red">&raquo;</font> حذف الاعب</a>

										</td>

									</tr>';

								}

								else if($_SESSION['access'] == MULTIHUNTER)

								{

									echo '';

								}

							?>



							<tr>

								<td colspan="2"><a href="?p=ban&uid=<?php echo $user['id']; ?>">&raquo; حظر الاعب</a></td>

							</tr>



							<tr>

								<td colspan="2"><a href="?p=Newmessage&uid=<?php echo $user['id']; ?>">&raquo; ارسال رسالة</a></td>

							</tr>



							<tr>

								<?php if($_SESSION['access'] == 9){ ?><td colspan="2"><a href="?p=editPlus&uid=<?php echo $user['id']; ?>">&raquo; تعديل البلاس & اضافي الموارد</a></td>

							</tr>



							<tr>

								<td colspan="2"><a href="?p=editSitter&uid=<?php echo $user['id']; ?>">&raquo; تعديل الوكلاء</a></td>  <?php  } ?>

							</tr>

                                <?php if($_SESSION['access'] == ADMIN)

								{?>

							<tr>

								<td colspan="2"><a href="?p=editWeek&uid=<?php echo $user['id']; ?>">&raquo; تعديل نقاط الهجوم & الدفاع</a></td>

							</tr>



							<tr>

								<td colspan="2"><a href="?p=editOverall&uid=<?php echo $user['id']; ?>">&raquo; تعديل النقاط الاسبوعية</a></td>

							</tr>

                                    <?php  } ?>

							<tr>

								<td colspan="2"><a href="?p=userlogin&uid=<?php echo $user['id']; ?>">&raquo; سجل الدخول</a></td>

							</tr>







							<tr>

								<td colspan="2" class="desc2">

									<div class="desc2div">

										<center><?php echo nl2br($user['desc1']); ?></center>

									</div>

								</td>

							</tr>

						</table>

					<td class="desc1">

						<center><?php echo nl2br($user['desc2']); ?></center>

					</td>

				</tr>

			</tbody>

		</table>