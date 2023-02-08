<?php 

?>

<style>
    .__page-title{
        display: inline-block;
        font-size: 18px;
        font-weight: 400;
    }
    .__btn{
        display: inline-block;
        text-decoration: none;
        color: black;
        background-color: silver;
        padding: 7px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 400;
    }

    .__btn:hover{
        color: black;
        border-color: silver;
    }

    .__btn:focus{
        color: black;
        border-color: silver;
    }

    table{
        border: solid 1px silver;
    }

    td, th{
        font-size: 12px;
    }
    td>img{
        border-radius: 50px;
        height: 50px;
        width: 50px;
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-12 mt-2">
            <h5 class="__page-title">Messages</h5>
            <a href="<?= admin_url('admin.php?page=message&action=add'); ?>" class="__btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Envoyer un message
            </a>
        </div>
    </div>
  
    <div class="row">
        <div class="col-12 mt-2">
            
            <table class="table table-striped">
                <thead>
                    <th>#</th>
                    <th>Image</th>
                    <th>Categorie</th>
                    <th>Titre</th>
                    <th>Actions</th>
                </thead>
                <tbody>
                    	
                </tbody>
            </table>
        </div>
    </div>
</div>