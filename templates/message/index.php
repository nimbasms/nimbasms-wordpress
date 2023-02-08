<?php if(isset($_GET['action'])): ?>

<?php if(!empty($_GET['action'])): ?>

    <?php $action = $_GET['action']; ?>

    <?php if($action == 'add'):  ?>

        <?php include('add.php') ?>

    <?php elseif($action == 'liste'):  ?>

        <?php include('liste.php') ?>	
        
    <?php elseif($action == 'edit'):  ?>

        <?php include('editer.php') ?>		

    <?php endif; ?>

<?php else: ?>

    <?php include('liste.php') ?>	

<?php endif; ?>

<?php else: ?>

<?php include('liste.php') ?>

<?php endif; ?>		