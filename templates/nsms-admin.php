<?php
    global $config;
    use App\Services\ConfigService;
    use App\Models\Config;

    $credentials = ConfigService::getCredentials();

    if(isset($_POST['Enregistrer'])){
        $service_id = $_POST['service_id'];
        $secret_token = $_POST['secret_token'];
        ConfigService::save(new Config($service_id, $secret_token));        
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
                    <label for="service_id">Service ID (SID)</label>
                    <input type="text" 
                        value="<?= $credentials->service_id ?>"
                        name="service_id" id="service_id" class="form-control">
                </div>
                <div class="form-group">
                    <label for="secret_token">Secret (Token)</label>
                    <input type="text" 
                        value="<?= $credentials->secret_token ?>"
                        name="secret_token" id="secret_token" class="form-control">
                </div>
                <div class="form-group">
                    <input type="submit" value="Enregistrer" name="Enregistrer" class="btn btn-primary mt-2">
                </div>
            </form>
        </div>
    </div>
</div>
