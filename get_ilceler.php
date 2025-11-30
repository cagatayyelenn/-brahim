<?php
include "c/fonk.php";
include "c/config.php";
include "c/user.php";


if (isset($_POST['il_id'])) {
    $il_id = $_POST['il_id'];

    $gruplar="SELECT ilce_id, ilce_adi FROM ilce WHERE il_id = $il_id";
    $ilceler=$Ydil->get($gruplar);

    if ($ilceler) {
        // İlçeleri select içine ekle
        foreach ($ilceler as $ilce) {
            echo '<option value="' . $ilce['ilce_id'] . '">' . $ilce['ilce_adi'] . '</option>';
        }
    } else {
        echo '<option selected disabled>İlçe Bulunamadı</option>';
    }
}
?>
