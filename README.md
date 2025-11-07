# 🏡 Portal Warga Digital RT005 Muara Telang

Portal Warga Digital RT005 adalah sistem informasi berbasis web yang dikembangkan untuk memudahkan warga RT005, Kecamatan Muara Telang, dalam mengakses layanan administrasi, menyampaikan aspirasi, dan mempererat komunikasi antara warga serta pengurus RT.

---

## 🌐 Deskripsi Singkat
Website ini dibangun menggunakan **metode Web Engineering** dengan teknologi **PHP**, **MySQL**, dan **Bootstrap**, sehingga tampil modern, responsif, dan mudah digunakan di berbagai perangkat.  
Portal ini bertujuan untuk mendukung digitalisasi pelayanan publik di tingkat RT agar lebih cepat, transparan, dan efisien.

---

## ⚙️ Fitur Utama

- 🧾 **Pengajuan Surat Online** — Warga dapat mengajukan surat pengantar, domisili, atau keterangan usaha secara digital.
- 📢 **Pengaduan & Aspirasi Warga** — Warga dapat melaporkan masalah atau saran, dan admin dapat menanggapi dengan sistem status.
- 💬 **Forum Diskusi Warga** — Tempat interaksi antara warga dan admin melalui topik dan balasan langsung.
- 👥 **Manajemen Data Warga & Admin** — Sistem login terpisah dengan keamanan berbasis sesi dan validasi data.
- 🪪 **Profil Digital Warga** — Warga memiliki profil pribadi dengan foto dan data diri yang dapat diperbarui.

---

## 🎯 Tujuan Pengembangan

1. Meningkatkan kualitas pelayanan publik di tingkat RT.
2. Mendorong partisipasi aktif warga dalam kegiatan lingkungan.
3. Mengurangi proses manual dalam administrasi RT.
4. Mewujudkan sistem informasi warga yang efisien, aman, dan mudah diakses.

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|-----------|------------|
| **Frontend** | HTML5, CSS3, Bootstrap 5 |
| **Backend** | PHP 8, MySQL |
| **Server** | XAMPP (Apache) |
| **Framework UI** | Bootstrap Icons, Responsive Layout |

---

## 📂 Struktur Folder

```
rt005_portal/
├── admin/             # Halaman admin (dashboard, kelola data, forum, surat, pengaduan)
├── user/              # Halaman untuk warga (dashboard, profil, forum, surat, pengaduan)
├── core/              # File inti seperti koneksi database dan helper
├── uploads/           # Direktori penyimpanan file lampiran dan foto profil
├── config.php         # File konfigurasi database dan konstanta sistem
├── index.php          # Halaman utama portal
└── README.md          # Dokumentasi proyek
```

---

## 🚀 Cara Menjalankan

1. Pastikan **XAMPP** sudah terinstal dan aktif (Apache + MySQL).
2. Salin folder proyek ke `htdocs` (misal: `C:\xampp\htdocs\rt005_portal`).
3. Buat database baru di phpMyAdmin dengan nama `rt005_portal`.
4. Import file SQL (`rt005_portal.sql`) ke database.
5. Akses website melalui browser:
   ```
   http://localhost/rt005_portal
   ```
6. Login sebagai admin atau daftar sebagai warga baru.

---

## 👨‍💻 Pengembang

**Nama:** Rzky Pratama  
**Peran:** Full-Stack Developer  
**Dosen Pembimbing:** Edi Wahyudi, S.Kom  
**Universitas:** Universitas Teknokrat Indonesia

---

## 📜 Lisensi
Proyek ini dibuat untuk keperluan akademik dan pengembangan masyarakat.  
Dilarang memperjualbelikan kode tanpa izin pengembang asli.

---

> “Mewujudkan RT Digital yang Modern, Transparan, dan Efisien.”  
> — Portal Warga Digital RT005 Muara Telang
