DROP DATABASE IF EXISTS pawly_db;
CREATE DATABASE pawly_db;
USE pawly_db;

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    PRIMARY KEY (id)
);

CREATE TABLE kucing (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    nama_kucing VARCHAR(60) NOT NULL,
    foto_kucing VARCHAR(255) NOT NULL DEFAULT '',
    ras VARCHAR(50) NOT NULL,
    jenis_kelamin ENUM('Jantan', 'Betina') NOT NULL,
    usia_bulan INT(11) NOT NULL,
    berat_kg DECIMAL(4,2) NOT NULL,
    nafsu_makan ENUM('Baik / Aktif', 'Menurun', 'Lesu') NOT NULL DEFAULT 'Baik / Aktif',
    status_vaksin ENUM('Sudah Vaksin', 'Belum Vaksin') NOT NULL DEFAULT 'Belum Vaksin',
    jenis_vaksin VARCHAR(60) NOT NULL DEFAULT '-',
    tgl_pantau DATE NULL,
    catatan_kesehatan TEXT,
    PRIMARY KEY (id),
    CONSTRAINT fk_kucing_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE riwayat_pemantauan (
    id INT(11) NOT NULL AUTO_INCREMENT,
    kucing_id INT(11) NOT NULL,
    tanggal_catat DATE NOT NULL,
    usia_bulan INT(11) NOT NULL,
    berat_kg DECIMAL(4,2) NOT NULL,
    nafsu_makan ENUM('Baik / Aktif', 'Menurun', 'Lesu') NOT NULL DEFAULT 'Baik / Aktif',
    status_vaksin ENUM('Sudah Vaksin', 'Belum Vaksin') NOT NULL DEFAULT 'Belum Vaksin',
    jenis_vaksin VARCHAR(60) NOT NULL DEFAULT '-',
    catatan TEXT,
    PRIMARY KEY (id),
    CONSTRAINT fk_riwayat_kucing FOREIGN KEY (kucing_id) REFERENCES kucing(id) ON DELETE CASCADE
);

INSERT INTO users (username, password, role) VALUES
('admin', 'admin123', 'admin'),
('alfi', 'alfi123', 'user');

INSERT INTO kucing (
    user_id, nama_kucing, foto_kucing, ras, jenis_kelamin, usia_bulan,
    berat_kg, nafsu_makan, status_vaksin, jenis_vaksin, tgl_pantau, catatan_kesehatan
) VALUES (
    2, 'Mochi', '', 'Campuran', 'Betina', 10,
    3.60, 'Baik / Aktif', 'Sudah Vaksin', 'F3 / Tricat', '2026-11-20',
    'Berat naik stabil, aktif bermain dan nafsu makan baik.'
);

INSERT INTO riwayat_pemantauan (
    kucing_id, tanggal_catat, usia_bulan, berat_kg, nafsu_makan, status_vaksin, jenis_vaksin, catatan
) VALUES
(1, '2026-09-20', 8,  3.10, 'Baik / Aktif', 'Belum Vaksin', '-', 'Pencatatan awal, berat badan kitten sehat.'),
(1, '2026-10-20', 9,  3.35, 'Baik / Aktif', 'Sudah Vaksin', 'F3 / Tricat', 'Sudah vaksin pertama, berat naik pelan.'),
(1, '2026-11-20', 10, 3.60, 'Baik / Aktif', 'Sudah Vaksin', 'F3 / Tricat', 'Berat naik stabil, aktif bermain dan nafsu makan baik.');