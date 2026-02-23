<?php

// Ydil sınıfını çağırmadan önce config.php'yi dahil ettiğinizden emin olun!
// Örn: require_once 'config.php';

class Ydil
{
    public $conn;

    public function __construct()
    {
        // Yapılandırma dosyasından sabitleri kullanarak güvenli bağlantı
        $host = DB_HOST;
        $dbname = DB_NAME;
        $user = DB_USER;
        $password = DB_PASS;

        try {
            $dsn = "mysql:host={$host};dbname={$dbname}";
            $this->conn = new PDO($dsn, $user, $password);
            $this->conn->exec("set names utf8");
            // Hata modunu PDOException fırlatacak şekilde ayarlıyoruz.
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Hata detayını loglama.
            error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
            // Kullanıcıya genel hata mesajı gösterme.
            exit("Sistem şu anda teknik bir sorun yaşıyor. Lütfen daha sonra tekrar deneyin.");
        }
    }

    // --- TEMEL CRUD İŞLEMLERİ (GÜVENLİ) ---

    /**
     * 🔒 Ekleme (Insert): Veritabanına yeni bir kayıt ekler.
     */
    /**
     * insert($table, $assocData)          — assosiatif dizi: ['kolon' => 'deger']
     * insert($table, $columns, $values)   — eski imza: ayrı kolon ve değer dizileri
     */
    public function insert($table, $columns, $values = null)
    {
        // Assosiatif dizi desteği
        if ($values === null && is_array($columns) && array_keys($columns) !== range(0, count($columns) - 1)) {
            $assoc = $columns;
            $columns = array_keys($assoc);
            $values = array_values($assoc);
        }

        $column_names = array_map(fn($col) => "`{$col}`", $columns);
        $bindings = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO `{$table}` (" . implode(', ', $column_names) . ') VALUES (' . implode(', ', $bindings) . ')';
        $stmt = $this->conn->prepare($sql);

        foreach ($columns as $key => $column) {
            $stmt->bindValue(":" . $column, $values[$key]);
        }

        try {
            $stmt->execute();
            return ['status' => 1, 'message' => 'Kayıt başarıyla eklendi.', 'id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return ['status' => 0, 'message' => 'Kayıt eklenirken bir hata oluştu.'];
        }
    }

    /**
     * 🔒 Tekli Sorgu (Find): Belirtilen koşula uyan tek bir kaydı çeker.
     */
    public function find($table, $column, $value, $projection = [])
    {
        $fields = count($projection) > 0 ? implode(",", $projection) : "*";

        $sql = "SELECT {$fields} FROM `{$table}` WHERE `{$column}` = :value LIMIT 1";

        $query = $this->conn->prepare($sql);
        $query->bindValue(':value', $value);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function get($querySql, $params = [])
    {
        $query = $this->conn->prepare($querySql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function gets($querySql, $params = [])
    {
        $query = $this->conn->prepare($querySql);
        $query->execute($params);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 🔒 Çoğul Sorgu (Finds): Belirtilen koşula uyan birden fazla kaydı çeker.
     */
    public function finds($table, $column = null, $value = null, $projection = [])
    {
        $fields = count($projection) > 0 ? implode(",", $projection) : "*";

        $sql = "SELECT {$fields} FROM `{$table}`";

        if ($column !== null && $value !== null) {
            $sql .= " WHERE `{$column}` = :value";
        }

        $query = $this->conn->prepare($sql);

        if ($column !== null && $value !== null) {
            $query->bindValue(':value', $value);
        }

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 🔒 Güncelleme (Update): Belirtilen bir kaydı günceller.
     */
    /**
     * update($table, $assocData, $columnId, $idValue)    — assosiatif dizi: ['kolon' => 'deger']
     * update($table, $columns, $values, $columnId, $idValue) — eski imza: ayrı diziler
     */
    public function update($table, $columns, $values, $columnId = null, $idValue = null)
    {
        // Assosiatif dizi desteği: update($table, ['alan'=>'val'], 'id_kol', $id)
        if ($columnId === null && $idValue === null) {
            // Hatalı çağrı
            return ['status' => 0, 'message' => 'Eksik parametre.'];
        }
        if (is_string($values) && $idValue === null) {
            // update($table, assoc, $columnId, $idValue) — 4 parametre
            $idValue = $columnId;
            $columnId = $values;
            $data = $columns; // assoc
            $columns = array_keys($data);
            $values = array_values($data);
        }

        $set = [];
        foreach ($columns as $column) {
            $set[] = "`$column` = :$column";
        }
        $set = implode(", ", $set);

        $sql = "UPDATE `{$table}` SET {$set} WHERE `{$columnId}` = :idValue";
        $stmt = $this->conn->prepare($sql);

        foreach ($columns as $key => $col) {
            $stmt->bindValue(":" . $col, $values[$key] ?? null);
        }
        $stmt->bindValue(":idValue", $idValue);

        try {
            $stmt->execute();
            return ['status' => 1, 'message' => 'Kayıt başarıyla güncellendi.'];
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return ['status' => 0, 'message' => 'Kayıt güncellenirken bir hata oluştu.'];
        }
    }

    /**
     * 🔒 Silme (Delete): Belirtilen bir kaydı siler.
     */
    /**
     * 🔒 Silme (Delete): Belirtilen bir kaydı siler.
     */
    public function delete($table, $id, $idKey = 'id')
    {
        $sql = "DELETE FROM `{$table}` WHERE `{$idKey}` = :idValue";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':idValue', $id);

        try {
            $stmt->execute();
            return ['status' => 1, 'message' => 'Silme işlemi başarılı.'];
        } catch (PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return ['status' => 0, 'message' => 'Kayıt silinirken bir hata oluştu.'];
        }
    }

    // --- TRANSACTION & RAW QUERY ---

    /**
     * İşlem başlatır (Transaction begin)
     */
    public function beginTransaction(): bool
    {
        return $this->conn->beginTransaction();
    }

    /**
     * İşlemi onaylar (Commit)
     */
    public function commit(): bool
    {
        return $this->conn->commit();
    }

    /**
     * İşlemi geri alır (Rollback)
     */
    public function rollBack(): bool
    {
        return $this->conn->rollBack();
    }

    /**
     * Ham SQL sorgusu çalıştırır (INSERT/UPDATE/DELETE için)
     * $db->query("DELETE FROM tablo WHERE id = :id", [':id' => 1])
     */
    public function query($sql, $params = []): bool
    {
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * 📝 Loglama (İşlem Geçmişi): Yapılan işlemleri veritabanına kaydeder.
     */
    public function log($tablo, $islem_id, $islem_turu, $aciklama)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $personel_id = $_SESSION['personel_id'] ?? 0;
        $sube_id = $_SESSION['sube_id'] ?? 0;

        // Eğer personel_id yoksa (örn. cron job veya dış erişim), 0 olarak kaydedilir.

        $sql = "INSERT INTO `islem_gecmisi` (`personel_id`, `sube_id`, `tablo`, `islem_id`, `islem_turu`, `aciklama`) 
                VALUES (:personel_id, :sube_id, :tablo, :islem_id, :islem_turu, :aciklama)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':personel_id', $personel_id);
            $stmt->bindValue(':sube_id', $sube_id);
            $stmt->bindValue(':tablo', $tablo);
            $stmt->bindValue(':islem_id', $islem_id);
            $stmt->bindValue(':islem_turu', $islem_turu);
            $stmt->bindValue(':aciklama', $aciklama);
            $stmt->execute();
        } catch (PDOException $e) {
            // Loglama hatası ana akışı bozmamalı, sadece hata günlüğüne yazalım.
            error_log("Log Insert Error: " . $e->getMessage());
        }
    }

    // --- YARDIMCI VE UI İŞLEMLERİ ---

    /**
     * SweetAlert (SWAL) ile özelleştirilmiş bildirim gösterir.
     */
    public function swalToggle($variant, $title, $message, $redirect = "")
    {
        echo "<script src=\"" . SWEET_ALERT_CDN . "\"></script>";
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$variant',
                title: '$title',
                text: '$message'
            }).then(function() {
                " . ($redirect ? "window.location.href = '$redirect';" : "") . "
            });
        });
    </script>";
    }

    /**
     * ✅ Silme Onayı için SweetAlert (SWAL) ile Modal açan HTML linkini oluşturur.
     */
    public function confirmDeleteLink($table, $id, $returnPath)
    {
        $deleteUrl = "{$returnPath}?action=delete&table={$table}&id={$id}";

        echo "<script src=\"" . SWEET_ALERT_CDN . "\"></script>";

        return '
            <a href="#" onclick="
                event.preventDefault();
                Swal.fire({
                    title: \'Emin misiniz?\',
                    text: \'Bu kaydı geri alamayacaksınız!\',
                    icon: \'warning\',
                    buttons: [\'Hayır, İptal Et\', \'Evet, Sil\'],
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        window.location.href = \'' . $deleteUrl . '\';
                    }
                });
            " class="btn btn-danger btn-sm">Sil</a>
        ';
    }

    /**
     * Oturum kontrolü yapar. Eğer kullanıcı giriş yapmadıysa login sayfasına yönlendirir.
     */
    public function checkLoggedIn()
    {
        if (!isset($_SESSION["userData"])) {
            // BASE_URL'i config dosyasından çeker
            $login_path = BASE_URL . "giris-yap.php";
            session_destroy();
            header('Location: ' . $login_path);
            exit();
        } else {
            return $_SESSION["userData"];
        }
    }
}


// === Ydil.php'nin en altına ekle (class Ydil kapandıktan SONRA) ===

if (!function_exists('formatDateTR')) {
    function formatDateTR(?string $date, bool $withDayName = false): string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            $dt = new DateTime($date);
        } catch (Exception $e) {
            return '-';
        }

        $aylar = [
            1 => 'Ocak',
            2 => 'Şubat',
            3 => 'Mart',
            4 => 'Nisan',
            5 => 'Mayıs',
            6 => 'Haziran',
            7 => 'Temmuz',
            8 => 'Ağustos',
            9 => 'Eylül',
            10 => 'Ekim',
            11 => 'Kasım',
            12 => 'Aralık'
        ];
        $gunler = [
            0 => 'Pazar',
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi'
        ];

        $d = $dt->format('d');
        $m = (int) $dt->format('n');
        $y = $dt->format('Y');
        $ay = $aylar[$m] ?? $dt->format('m');

        if ($withDayName) {
            $gn = $gunler[(int) $dt->format('w')];
            return "{$gn} {$d} {$ay} {$y}";
        }
        return "{$d} {$ay} {$y}";
    }
}
?>