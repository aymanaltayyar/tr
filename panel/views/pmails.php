<?php 
    $players = $database->query('SELECT * FROM users WHERE id > 6 ORDER BY id ');

    foreach($players as $p){
        echo $p['email'];
        echo '<br>';
    }
?>
