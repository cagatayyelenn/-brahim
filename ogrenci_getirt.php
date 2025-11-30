<?php

include "c/fonk.php";
include "c/config.php";
include "c/user.php";

if(isset($_GET["search"])){
    $search= $_GET["search"];

    $ogrenciler = $Ydil->get("SELECT * FROM ogrenci WHERE CONCAT(ogrenci_adi, ' ', ogrenci_soyadi) LIKE '%$search%'");
    $html = "";

    if (!empty($ogrenciler) && is_array($ogrenciler)) {
        foreach ($ogrenciler as $ogrenci) {
            $html .= '
                <tr>
                    <td>
                        <a href="taksit-odeme.php?ogrenci_id='.htmlspecialchars($ogrenci['ogrenci_id']).'">
                            <button class="btn btn-primary" type="button">Seç</button>
                        </a>
                    </td>
                    <td>'.htmlspecialchars($ogrenci['ogrenci_adi']).'</td>
                    <td>'.htmlspecialchars($ogrenci['ogrenci_soyadi']).'</td>
                </tr>';
        }
    } else {
        $html .= '
    <tr>
        <td colspan="3" class="text-center text-muted">Kayıt bulunamadı</td>
    </tr>';
    }

    echo json_encode(['html'=>$html,'ogrenci_adet'=>count($ogrenciler)]);die;
}

