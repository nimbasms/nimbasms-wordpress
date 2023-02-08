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

    .__btn-upload-poster{
        display: block !important;
        margin-top: 2px;
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
            <h5 class="__page-title">Enregistrer un document</h5>
            <a href="<?= admin_url('admin.php?page=documents'); ?>" class="__btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Messages
            </a>
        </div>
    </div>

    <div class="row">
		<div class="col-12 mt-2">
			<div class="panel panel-primary">
	            <div class="panel-body">
	                <form action="#" method="POST" enctype="multipart/form-data">
						<div class="row">
							<div class="col-6">
                                <!-- <div class="form-group">
                                    <label>Categories</label>
                                    <select style="min-width: 100%" name="categorie" class="form-control">
                                        <option selected disabled>Selectionnez une categorie</option>
                                        <?php foreach($categories as $key => $categorie): ?>
                                            <option value="<?= $key ?>"><?= $categorie ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div> -->
								<div class="form-group">
									<label>Titre</label>
									<input type="text" name="titre" class="form-control">
								</div>
                                <div class="form-group">
									<label>Auteur</label>
									<input type="text" name="auteur" class="form-control">
								</div>
							</div>
                            <div class="col-6">
                                <div class="form-group">
									<label>Nombre de page</label>
									<input type="text" name="nombre_page" class="form-control">
								</div>
                                <div class="form-group">
									<label>Editeur</label>
									<input type="text" name="editeur" class="form-control">
								</div>
                            </div>

                            <div class="form-group mt-2">
                                <button type="submit" name="btn_submit" class="__btn">Enregistrer</button>
                            </div>
						</div>
					</form>                
	            </div>
        	</div>	
		</div>
	</div>
</div>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>


<script>
    document.addEventListener("DOMContentLoaded", function(){
        
       
    });
</script>