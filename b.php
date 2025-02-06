<?php
$token = "7528317589:AAE1Wd3GtJuCf6xqX4O4kRqQILZ-pAVrdGM"; // Ganti dengan token bot Telegram
$admin_id = "1820443547"; // Ganti dengan ID Telegram admin
$file = "licenses.json"; // File untuk menyimpan status lisensi

$update = json_decode(file_get_contents("php://input"), true);

if (isset($update["message"])) {
    $message = $update["message"];
    $chat_id = $message["chat"]["id"];
    $text = strtolower(trim($message["text"]));

    // Jika admin yang mengirim pesan
    if ($chat_id == $admin_id) {
        // Otomatis setujui lisensi jika admin membalas "ya"
        if ($text == "ya") {
            $last_license = approveLastLicense(true);
            if ($last_license) {
                sendMessage($chat_id, "Lisensi $last_license telah disetujui.");
            } else {
                sendMessage($chat_id, "Tidak ada lisensi yang perlu disetujui.");
            }
        } 
        // Menolak lisensi jika admin membalas "false"
        elseif ($text == "false") {
            $last_license = approveLastLicense(false);
            if ($last_license) {
                sendMessage($chat_id, "Lisensi $last_license ditolak.");
            } else {
                sendMessage($chat_id, "Tidak ada lisensi yang perlu ditolak.");
            }
        }
    }
}

// Fungsi untuk mengirim pesan ke Telegram
function sendMessage($chat_id, $text) {
    global $token;
    file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($text));
}

// Fungsi untuk menyetujui atau menolak lisensi terakhir
function approveLastLicense($approve = true) {
    global $file;
    if (!file_exists($file)) return false;

    $data = json_decode(file_get_contents($file), true);
    if (!$data) return false;

    // Ambil lisensi terakhir yang belum disetujui
    foreach ($data as $key => $status) {
        if (!$status) {
            $data[$key] = $approve; // Tandai sebagai disetujui atau ditolak
            file_put_contents($file, json_encode($data)); // Simpan perubahan
            return $key; // Kembalikan lisensi yang disetujui atau ditolak
        }
    }
    return false;
}
?>