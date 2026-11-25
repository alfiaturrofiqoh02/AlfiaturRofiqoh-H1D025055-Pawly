<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function pawly_bg_style() {
    $svg = rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160">' .
        '<g fill="#F4C7CE" fill-opacity="0.34">' .
        '<circle cx="26" cy="24" r="5"/>' .
        '<circle cx="38" cy="18" r="5"/>' .
        '<circle cx="50" cy="24" r="5"/>' .
        '<ellipse cx="38" cy="34" rx="10" ry="8"/>' .
        '<circle cx="112" cy="96" r="5"/>' .
        '<circle cx="124" cy="90" r="5"/>' .
        '<circle cx="136" cy="96" r="5"/>' .
        '<ellipse cx="124" cy="106" rx="10" ry="8"/>' .
        '</g>' .
        '<g fill="#F6DFA2" fill-opacity="0.26">' .
        '<circle cx="118" cy="30" r="3"/>' .
        '<circle cx="36" cy="118" r="3"/>' .
        '<circle cx="84" cy="68" r="2.5"/>' .
        '</g>' .
        '</svg>'
    );

    return '<style>
        body.pawly-bg {
            background-color: #FFF9F7;
            background-image:
                radial-gradient(circle at 12% 14%, rgba(255, 224, 229, 0.72) 0%, transparent 42%),
                radial-gradient(circle at 88% 20%, rgba(255, 241, 204, 0.68) 0%, transparent 45%),
                url("data:image/svg+xml,' . $svg . '");
            background-repeat: repeat;
            background-size: auto, auto, 160px 160px;
        }
        .pawly-brand-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .pawly-paw-icon {
            flex-shrink: 0;
            filter: drop-shadow(0 2px 4px rgba(217, 107, 120, 0.22));
            transition: transform 0.2s ease;
        }
        .pawly-brand-wrap:hover .pawly-paw-icon {
            transform: rotate(-8deg) scale(1.06);
        }
        .pawly-brand-text {
            font-size: 22px;
            font-weight: 800;
            color: #D96B78;
            letter-spacing: -0.3px;
        }
    </style>';
}

function pawly_logo_html() {
    return '
    <span class="pawly-brand-wrap">
        <svg class="pawly-paw-icon" viewBox="0 0 64 64" width="32" height="32" aria-hidden="true">
            <circle cx="15" cy="23" r="6.5" fill="#E58C96"/>
            <circle cx="27" cy="14" r="6.5" fill="#F29CA6"/>
            <circle cx="39" cy="14" r="6.5" fill="#F29CA6"/>
            <circle cx="50" cy="23" r="6.5" fill="#E58C96"/>
            <path d="M18 43c0-8.5 6.8-14 14.5-14S47 34.5 47 43c0 6.8-5.5 11-14.5 11S18 49.8 18 43z" fill="#D96B78"/>
            <circle cx="27.5" cy="39.5" r="1.8" fill="#FFF7F8"/>
            <circle cx="37.5" cy="39.5" r="1.8" fill="#FFF7F8"/>
            <path d="M30.5 44.5q2 2.5 4 0" stroke="#FFF7F8" stroke-width="2" stroke-linecap="round" fill="none"/>
        </svg>
        <span class="pawly-brand-text">Pawly</span>
    </span>';
}
function pawly_foto_kucing_src($foto_kucing, $prefix = '../') {
    $file = basename(trim((string)$foto_kucing));
    if ($file !== '') {
        $fisik = __DIR__ . '/assets/kucing/' . $file;
        if (is_file($fisik)) {
            return $prefix . 'assets/kucing/' . rawurlencode($file);
        }
    }

    $svg = rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">' .
        '<rect width="120" height="120" rx="24" fill="#FFE8EC"/>' .
        '<circle cx="42" cy="40" r="8" fill="#E58C96"/>' .
        '<circle cx="60" cy="31" r="8" fill="#F29CA6"/>' .
        '<circle cx="78" cy="40" r="8" fill="#E58C96"/>' .
        '<ellipse cx="60" cy="68" rx="22" ry="18" fill="#D96B78"/>' .
        '<circle cx="52" cy="64" r="2.5" fill="#FFF7F8"/>' .
        '<circle cx="68" cy="64" r="2.5" fill="#FFF7F8"/>' .
        '<path d="M56 73q4 4 8 0" stroke="#FFF7F8" stroke-width="2.5" stroke-linecap="round" fill="none"/>' .
        '</svg>'
    );

    return 'data:image/svg+xml,' . $svg;
}

function pawly_upload_foto_kucing($field_name = 'foto_kucing', $foto_lama = '') {
    $nama_file_lama = basename(trim((string)$foto_lama));

    if (!isset($_FILES[$field_name]) || ($_FILES[$field_name]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'filename' => $nama_file_lama, 'error' => ''];
    }

    $err = (int)$_FILES[$field_name]['error'];
    if ($err !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'filename' => $nama_file_lama, 'error' => 'Gagal mengupload foto kucing (kode error: ' . $err . ').'];
    }

    $tmp_path = $_FILES[$field_name]['tmp_name'] ?? '';
    $ukuran   = (int)($_FILES[$field_name]['size'] ?? 0);
    $max_size = 2 * 1024 * 1024; // 2 MB

    if ($ukuran <= 0 || $ukuran > $max_size) {
        return ['ok' => false, 'filename' => $nama_file_lama, 'error' => 'Ukuran foto maksimal 2 MB.'];
    }

    $info = @getimagesize($tmp_path);
    $mime = $info['mime'] ?? '';
    $map_ext = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!isset($map_ext[$mime])) {
        return ['ok' => false, 'filename' => $nama_file_lama, 'error' => 'Format foto harus JPG, PNG, WEBP, atau GIF.'];
    }

    $target_dir = __DIR__ . '/assets/kucing';
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    $ext = $map_ext[$mime];
    $new_name = 'cat_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest_path = $target_dir . '/' . $new_name;

    if (!move_uploaded_file($tmp_path, $dest_path)) {
        return ['ok' => false, 'filename' => $nama_file_lama, 'error' => 'Gagal menyimpan file foto ke folder assets/kucing.'];
    }

    if ($nama_file_lama !== '' && $nama_file_lama !== $new_name) {
        $old_path = $target_dir . '/' . $nama_file_lama;
        if (is_file($old_path)) {
            @unlink($old_path);
        }
    }

    return ['ok' => true, 'filename' => $new_name, 'error' => ''];
}
function pawly_font_style() {
    return '
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body, input, select, textarea, button, p, label, td, th, a, span, div {
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, sans-serif;
        }
        h1, h2, .pawly-brand-text, .cat-name {
            font-family: "Fredoka", "Plus Jakarta Sans", sans-serif;
            letter-spacing: -0.01em;
        }
        .pawly-brand-text {
            font-size: 24px;
            font-weight: 700;
            color: #D96B78;
        }
    </style>';
}