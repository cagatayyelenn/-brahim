<?php
// 🔹 İlk adım — Kurs Bilgisi LOCK/UNLOCK
// Doğrudan kopyalayabiliriz, sadece dosya adını değiştiriyoruz

ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../dosyalar/config.php';
require_once '../dosyalar/Ydil.php';

$action     = $_POST['action'] ?? $_GET['action'] ?? '';
$ogrenci_id = (int)($_POST['ogrenci_id'] ?? $_GET['ogrenci_id'] ?? 0);

$KEY        = 'sozlesme_wizard';

function ok($extra = []){ echo json_encode(['ok'=>true] + $extra, JSON_UNESCAPED_UNICODE); exit; }
function bad($msg){ echo json_encode(['ok'=>false, 'msg'=>$msg], JSON_UNESCAPED_UNICODE); exit; }

if(!isset($_SESSION[$KEY])) $_SESSION[$KEY] = [];

$sections = [
  [
      "ad" => "kurs_section",
      "locked" => false,
      "values" => [
          "donem_id" => "",
          "sinif_id" => "",
          "grup_id" => "",
          "alan_id" => "",
        ]
  ],
    [
        "ad" => "kurs_sozlesme_section",
        "locked" => false,
        "values" => [
            "birimId"           => "",
            "birimFiyat"        => "",
            "miktar"            => "",
            "toplamTutar"       => ""
        ]
    ],
    [
        "ad" => "odeme_section",
        "locked" => false,
        "values" => [
            "odmBirimTutar" => "",
            "odmTutar"      => "",
            "odmToplam"     => "",
            "pesinat"       => "",
            "odemeSecenegi" => "",
            "kasaId"        => ""
        ]
    ],
    [
        "ad" => "taksit_section",
        "locked" => false,
        "values" => [
            "kalanTutar"     => "",
            "taksitBaslangic" => "",
            "taksitSayisi"    => ""
        ]

    ],

];



foreach($sections as $bolum){
    $b=$bolum["ad"];
    if(isset($_SESSION[$KEY][$ogrenci_id][$b])){

        $bolum = $_SESSION[$KEY][$ogrenci_id][$b];
        if($b=="kurs_sozlesme_section"){
            $_SESSION[$KEY][$ogrenci_id]["odeme_section"]["values"]["odmBirimTutar"]=$bolum["values"]["birimFiyat"];
            $_SESSION[$KEY][$ogrenci_id]["odeme_section"]["values"]["odmTutar"]=$bolum["values"]["toplamTutar"];
            $_SESSION[$KEY][$ogrenci_id]["odeme_section"]["values"]["odmToplam"]=$bolum["values"]["toplamTutar"];
        }
        if($b=="odeme_section"){
            $_SESSION[$KEY][$ogrenci_id]["taksit_section"]["values"]["kalanTutar"]=(float)$bolum["values"]["odmToplam"]-(float)$bolum["values"]["pesinat"];
        }



    }
    $_SESSION[$KEY][$ogrenci_id][$b] =[
        'locked' => $bolum["locked"],
        'values' => $bolum["values"],
        'ts' => time()
    ];
}



switch ($action) {
    case 'lock':
        $values = [];
        $bolum_id   = $_POST['section_id'] ?? 'kurs_section';
        switch ($bolum_id){
            case "kurs_section":
                $donem_id  = (int)($_POST['donem_id'] ?? 0);
                $sinif_id  = (int)($_POST['sinif_id'] ?? 0);
                $grup_id   = (int)($_POST['grup_id']  ?? 0);
                $alan_id   = (int)($_POST['alan_id']  ?? 0);
                $values=[
                    'donem_id' => $donem_id,
                    'sinif_id' => $sinif_id,
                    'grup_id'  => $grup_id,
                    'alan_id'  => $alan_id,
                ];
                break;
            case "kurs_sozlesme_section":
                $birimId  = (int)($_POST['birimId'] ?? 0);
                $birimFiyat  = ($_POST['birimFiyat'] ?? 0);
                $miktar   = ($_POST['miktar']  ?? 0);
                $toplamTutar   = ($_POST['toplamTutar']  ?? 0);
                $values= [
                    "birimId"           => $birimId,
                    "birimFiyat"        => $birimFiyat,
                    "miktar"            => $miktar,
                    "toplamTutar"       => $toplamTutar
                ];
                break;

            case "odeme_section":
                $pesinat            = ($_POST['pesinat'] ?? 0);
                $kasaId             = $_POST['kasaId'];
                $odemeSecenegi      = $_POST['odemeSecenegi'];



                 $values = [
                     "pesinat"       => $pesinat,
                     "odemeSecenegi" => $odemeSecenegi,
                     "kasaId"        => $kasaId
                 ];




                break;
        }




        $_SESSION[$KEY][$ogrenci_id][$bolum_id] = [
            'locked' => true,
            'values' => $values,
            'ts' => time()
        ];
        ok(['state'=>$_SESSION[$KEY][$ogrenci_id]]);
        break;

    case 'unlock':
        $bolum_id   = $_POST['section_id'] ?? 'kurs_section';
        $_SESSION[$KEY][$ogrenci_id][$bolum_id]['locked'] = false;
        ok(['state'=>$_SESSION[$KEY][$ogrenci_id]]);
        break;

    case 'get':
        $state = $_SESSION[$KEY][$ogrenci_id] ?? ['locked'=>false, 'values'=>null];
        if($state){
            if($state["kurs_section"]["locked"]){
                if(!$state["kurs_sozlesme_section"]["locked"]){
                    $state["odeme_section"]["locked"]=true;
                    $state["taksit_section"]["locked"]=true;
                }
                else{
                    if($state["odeme_section"]["locked"]){
                        $state["taksit_section"]["locked"]=false;
                    }else{
                        $state["odeme_section"]["locked"]=false;
                        $state["taksit_section"]["locked"]=true;
                    }
                }
            }
            else
            {
                $state["kurs_sozlesme_section"]["locked"]=true;
                $state["odeme_section"]["locked"]=true;
                $state["taksit_section"]["locked"]=true;
            }
        }

        ok(['state'=>$state]);
        break;

    default:
        bad('Geçersiz işlem');
}