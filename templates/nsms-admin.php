<?php
    global $config;
    use App\Services\ConfigService;
    use App\Models\Config;

    if(isset($_POST['Enregistrer'])){
        ConfigService::save(new Config('1', '2'));        
    }
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>NIMBA SMS</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <p>
                Vous n'avez pas de creer chez NIMBA SMS ?
            </p>
            <a href="https://www.nimbasms.com/" target="_blank" class="btn btn-primary">Creer un compte</a>
        </div>
        <div class="col-6">
            <form action="" method="post">
                <div class="form-group">
                    <label for="sender_id">Service ID (SID)</label>
                    <input type="text" name="sender_id" id="sender_id" class="form-control">
                </div>
                <div class="form-group">
                    <label for="token">Secret (Token)</label>
                    <input type="text" name="token" id="token" class="form-control">
                </div>
                <div class="form-group">
                    <input type="submit" value="Enregistrer" name="Enregistrer" class="btn btn-primary mt-2">
                </div>
            </form>
        </div>
    </div>
</div>
