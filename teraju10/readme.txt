=== Teraju10 ===
Tema WordPress khusus untuk teraju.id.
Version: 1.10.0
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
License: GPLv2 or later

== Deskripsi ==
Teraju10 adalah tema berita yang dibangun sesuai brief redaksi: ringan, sadar
SEO/GEO/AEO, dan sebagian besar bisa diatur dari dashboard tanpa Elementor
atau plugin page builder.

== Cara Instalasi ==
1. Kompres ulang folder ini jika belum berbentuk .zip (biasanya sudah dikirim
   sebagai teraju10.zip).
2. Masuk ke wp-admin > Appearance > Themes > Add New > Upload Theme.
3. Unggah teraju10.zip, lalu klik Activate.

== Yang WAJIB diatur setelah aktivasi ==

1. Appearance > Menus
   - Buat menu, isi dengan kategori (Berita, Ekonomi, Politik, dst).
   - Set "Menu Location" ke "Menu Utama".
   - Supaya rapi, kategori serumpun (mis. Daerah, Nasional, Internasional di
     bawah "Berita") bisa dijadikan submenu dropdown: di editor menu, seret
     item itu sedikit ke kanan (indent) sampai posisinya jadi "sub item" di
     bawah "Berita". Tema otomatis menampilkannya sebagai dropdown (buka
     lewat hover di desktop, tap ikon panah di HP/tablet) — lihat "Submenu
     dropdown" di bawah.

2. Appearance > Widgets > Sidebar Artikel
   - Seret widget "Teraju: Postingan Terpopuler" ke sini untuk Top 10.
   - Seret widget "Teraju: Slot Iklan / Gambar" untuk slot iklan/gambar promosi.
   - Sidebar ini otomatis sticky (ikut discroll) dan hanya tampil kalau diisi
     minimal satu widget — kalau dikosongkan, halaman artikel otomatis
     melebar satu kolom tanpa sidebar kosong yang aneh.

3. Appearance > Widgets > Footer - Kanal / Tentang / Ikuti Kami
   - Isi masing-masing dengan widget "Menu Kustom" atau "Teks" sesuai
     kebutuhan (link rubrik, halaman Redaksi, media sosial, dsb).

4. Appearance > Customize
   - Site Identity: unggah logo. Tagline ("Solusi, alih-alih sensasi")
     terisi otomatis begitu tema ini aktif (lihat "Tagline otomatis" di
     bawah) — tetap bebas diubah kapan saja lewat Site Identity kalau perlu.
   - Ticker Harga (Emas & Kurs): sudah update otomatis setiap hari secara
     default (lihat bagian "Ticker harga otomatis" di bawah). Angka manual
     di section ini tetap wajib diisi sebagai cadangan kalau sumber
     otomatis gagal diakses.
   - Kirim Kabar Kalbar (WhatsApp): isi nomor WA aktif.
   - Teaser English Version: isi judul & deskripsi teaser di homepage.
   - Rubrik Homepage: isi slug kategori/tag yang benar-benar dipakai di
     situs (default: otomotif, politik, kultur, dan tag komunitas untuk
     Kabar Kalbar). Kalau slug salah ketik, bagian itu otomatis
     disembunyikan, bukan error.
   - "ID artikel Warisan Kalbar yang di-pin": kosongkan untuk perilaku lama
     (tampil artikel TERBARU dari kategori Warisan Kalbar). Isi dengan ID
     artikel untuk MENGUNCI satu artikel liputan mendalam/pilar supaya
     terus tampil di slot itu, tidak tergeser artikel baru lain di
     kategori yang sama — cocok untuk artikel semacam "Siapa Sultan Hamid
     II" yang ingin terus jadi sorotan homepage.

5. Setiap kali menulis artikel baru (halaman edit artikel)
   - Kotak "Ringkasan Cepat": tulis SATU POIN INTI PER BARIS (Enter untuk
     baris baru), 1-4 kalimat pendek — gaya "Smart Brevity" (Axios/
     Semafor), BUKAN satu paragraf panjang. Tiap baris harus benar-benar
     berdiri sendiri sebagai fakta/inti terpenting berita ini, bukan basa-
     basi pembuka. Contoh yang BENAR:
       Harga rumah di Pontianak naik rata-rata 11% dalam setahun terakhir
       Kenaikan paling tajam terjadi di kawasan Pontianak Selatan
       Pemicunya: proyek jalan lingkar baru dan lonjakan pendatang
     Kalau cuma diisi satu baris, tampil sebagai kalimat biasa (tidak jadi
     daftar ber-bullet). Ini yang tampil di atas artikel dan paling sering
     dikutip AI Overview/Perplexity/ChatGPT. Kosongkan untuk memakai
     excerpt otomatis.
   - Kotak "Fakta Cepat" (opsional): satu baris satu fakta, format
     "Label|Nilai", contoh:
       Kenaikan harga rata-rata dalam setahun|+11%
       Kawasan dengan kenaikan tertinggi|Pontianak Selatan
   - Isi juga bio penulis lewat Users > Profil > Biographical Info, supaya
     kartu penulis di bawah artikel tidak kosong.

== Ticker harga otomatis ==
Sejak versi 1.1.0, ticker emas & USD/IDR diperbarui sendiri lewat WP-Cron,
2x sehari, tanpa perlu API key:

  - Kurs USD/IDR  : Frankfurter.app (menyajikan ulang kurs referensi resmi
                     harian Bank Sentral Eropa/ECB).
  - Harga emas    : endpoint publik goldprice.org, dikonversi dari harga
                     spot dunia (XAU/USD per troy ounce) ke Rupiah per gram.
                     Ini HARGA EMAS DUNIA, bukan harga jual resmi Antam
                     (yang punya premi/ongkos cetak lokal) — makanya label
                     ticker ditulis "Harga emas / gram", bukan "Emas Antam".

Kalau salah satu sumber gagal diakses, field itu tetap memakai angka
terakhir yang berhasil diambil. Kalau gagal terus lebih dari 4 hari, ticker
otomatis kembali memakai angka manual di Customizer, jadi tidak pernah
kosong atau error tampil ke pembaca.

Untuk mematikan mode otomatis dan kembali 100% manual: Appearance >
Customize > Ticker Harga (Emas & Kurs) > matikan "Update otomatis setiap
hari".

Kalau nanti ingin menyambungkan ke sumber harga Antam resmi (misalnya lewat
langganan data API berbayar), developer tinggal pakai filter:

  add_filter( 'teraju10_ticker_value', function( $value, $key, $is_auto ) {
      if ( 'gold_price' === $key ) {
          return teraju_ambil_harga_antam_dari_api(); // fungsi custom
      }
      return $value;
  }, 10, 3 );

Filter yang sama ($key: gold_price, gold_change, usd_idr, usd_change,
updated) berlaku baik untuk nilai otomatis maupun manual.

== Tagline otomatis ==
Begitu tema ini aktif, tagline situs otomatis diisi "Solusi, alih-alih
sensasi" — SEKALI SAJA saat pertama kali versi ini dimuat, tidak setiap
kunjungan. Kalau nanti tagline diubah manual lewat Appearance > Customize >
Site Identity, perubahan tersebut permanen — tema tidak akan menimpanya
lagi. Ini murni supaya tagline langsung benar begitu tema di-upload, tanpa
redaksi wajib buka Customizer dulu.

== Postingan Terpopuler: berdasar tayangan, bukan komentar lagi ==
Sejak v1.6.0, mode "otomatis" widget "Teraju: Postingan Terpopuler" mengurut
artikel dari jumlah TAYANGAN 7 hari terakhir (rolling window, bukan reset
tiap Senin) — bukan jumlah komentar seperti sebelumnya, yang sudah tidak
relevan karena kolom komentar sudah dihapus dari tema (lihat v1.5.0) dan di
banyak situs berita komentar memang sudah sepi walau pembacanya ramai.

Cara kerja singkat (murni kode tema, tidak perlu plugin analytics):
- Tayangan TIDAK dihitung lewat render halaman di server — itu tidak akan
  akurat begitu situs dipasangi plugin cache, karena PHP tak dieksekusi
  ulang untuk kunjungan yang disajikan dari cache. Sebagai gantinya,
  browser pembaca yang lapor lewat satu request kecil terpisah ke
  admin-ajax.php (endpoint yang memang selalu dijalankan dinamis, tidak
  ikut ke-cache) setelah halaman selesai dimuat. Halaman artikelnya sendiri
  tetap 100% bisa di-cache utuh oleh plugin cache apa pun.
- Satu pembaca dihitung maksimal sekali per 30 menit per artikel (dicek di
  localStorage browser), supaya reload berkali-kali tidak menggelembungkan
  angka.
- Total 7 hari terakhir dihitung ulang tiap ada tayangan baru, DAN oleh satu
  cron harian tambahan — supaya artikel yang sudah berhenti dibaca ikut
  "meluruh" dari daftar populer (bukan nyangkut selamanya dengan angka
  lama dari minggu sebelumnya).
- Kalau situs baru saja pindah ke tema ini dan belum ada data tayangan sama
  sekali, widget otomatis jatuh ke artikel terbaru dulu (bukan tampil
  kosong/acak) sampai data tayangan asli mulai terkumpul.

Catatan jujur soal batasan: kalau memakai plugin CACHE HALAMAN dengan masa
simpan (TTL) sangat panjang (mis. beberapa hari), sebagian kecil tayangan
pada masa itu bisa tidak tercatat (kode keamanan/nonce di halaman ikut
"membeku" selama page-nya belum di-generate ulang oleh plugin cache) —
efeknya cuma sedikit kurang presisi di kondisi ekstrem itu, bukan halaman
menjadi error. Kalau ke depannya butuh statistik pengunjung yang lebih
detail/akurat (per negara, per perangkat, dst), tetap pertimbangkan
memasang Google Analytics/Plausible/plugin statistik terpisah — fitur ini
sengaja dibuat ringan hanya untuk kebutuhan "urutkan artikel terpopuler",
bukan pengganti alat analitik.

Widget ini masih bisa dipakai manual (isi ID artikel sendiri) lewat opsi
"Manual" di pengaturan widget, kalau redaksi ingin mengatur urutannya
sendiri untuk momen tertentu.

== Efek Kesadaran Karhutla (SEMENTARA) ==
Di halaman artikel (single post), ada efek kabut asap tipis + pesan
kesadaran singkat — respons untuk musim karhutla tahun ini yang terparah
dalam beberapa tahun terakhir di Kalimantan Barat. Diatur di Appearance >
Customize > "Efek Kesadaran Karhutla":

  - "Tampilkan efek kabut asap" (aktif secara default): centang/hilangkan
    kapan pun untuk menampilkan/menghilangkan efeknya. Begitu musim hujan
    tiba dan asap karhutla mereda, tinggal HILANGKAN CENTANG ini — efek
    kabut & pesannya langsung hilang dari seluruh artikel, tanpa perlu
    ubah kode atau upload ulang tema.
  - "Pesan kesadaran": teks singkat yang tampil di kotak banner atas
    artikel, bisa diedit bebas.
  - "Slug kategori/tag liputan Karhutla" (opsional): kalau diisi dan
    kategori/tag-nya ada di situs, banner-nya jadi tautan "Baca liputan
    Karhutla" ke situ. Kalau kosong atau slug-nya belum ada, banner tetap
    tampil sebagai teks biasa tanpa tautan — tidak pernah error.

Sejak v1.8.0, efeknya memakai VIDEO asap sungguhan (bukan animasi CSS),
di-loop otomatis (muted, tanpa suara) — file-nya ada di
assets/video/karhutla-smoke.webm (utama, ~100KB) dan
assets/video/karhutla-smoke.mp4 (cadangan untuk Safari lama, ~135KB).
Videonya sudah dikompres dari sumber asli ~980KB tanpa penurunan kualitas
yang kentara, supaya loading halaman tetap ringan. Zona efeknya ~60% tinggi
layar dengan fade lembut di tepi atas (menyatu ke halaman, bukan seperti
kotak video yang ditempel).

Mau ganti videonya? Tinggal timpa kedua file itu (nama & lokasi file harus
sama) dengan video lain — idealnya video pendek (2-6 detik), sudah
dikompres kecil (dioptimalkan lewat ffmpeg atau tool serupa: turunkan
resolusi ke ~640px lebar, hapus audio, gunakan codec H.264 untuk .mp4 dan
VP9 untuk .webm), supaya tetap ringan.

Catatan desain: efek video dibuat murni dekoratif (aria-hidden,
pointer-events:none — tidak pernah menghalangi klik/seleksi teks) dan
terkonsentrasi di zona bawah halaman, dengan fade di tepi atas supaya
keterbacaan bagian lain artikel tetap utuh. Otomatis berhenti diputar
(bukan cuma diam di background) untuk pembaca yang mengaktifkan pengaturan
"reduce motion" di perangkatnya. Kecerahannya disesuaikan otomatis di mode
gelap supaya tidak menyilaukan.

== Perubahan v1.10.0 (Ringkasan Cepat & Fakta Cepat kebuka ke REST API) ==
- Field "Ringkasan Cepat" (_teraju_summary) dan "Fakta Cepat" (_teraju_facts)
  sekarang didaftarkan ke REST API WordPress (register_post_meta, show_in_rest).
  Sebelumnya field ini cuma bisa diisi manual lewat wp-admin; sekarang otomasi
  (mis. workflow n8n yang publish artikel lewat /wp-json/wp/v2/posts) bisa ikut
  mengisi field "meta" ini langsung saat artikel dibuat/diupdate, tanpa perlu
  buka wp-admin. Butuh user yang punya izin edit_posts (Application Password)
  di sisi otomasi.

== Perubahan v1.9.0 (pin artikel Warisan Kalbar) ==
- Slot "Warisan Kalbar" di homepage sekarang bisa dikunci ke satu artikel
  tertentu (Appearance > Customize > Rubrik Homepage > "ID artikel Warisan
  Kalbar yang di-pin"), supaya artikel liputan mendalam/pilar (mis. "Siapa
  Sultan Hamid II") bisa terus jadi sorotan homepage tanpa tergeser artikel
  baru lain di kategori yang sama. Kosongkan field-nya untuk kembali ke
  perilaku lama (otomatis artikel terbaru di kategori).

== Perubahan v1.8.0 (efek asap pakai video asli) ==
- Efek Kesadaran Karhutla diganti dari animasi CSS jadi video asap
  sungguhan yang di-loop — lihat "Efek Kesadaran Karhutla (SEMENTARA)" di
  atas untuk detail lengkap, termasuk cara mengganti videonya nanti.

== Perubahan v1.7.0 (Efek Kesadaran Karhutla) ==
- Lihat "Efek Kesadaran Karhutla (SEMENTARA)" di atas untuk detail lengkap
  dan cara mematikannya nanti.

== Perubahan v1.6.0 (Postingan Terpopuler berbasis tayangan) ==
- Lihat "Postingan Terpopuler: berdasar tayangan, bukan komentar lagi" di
  atas untuk detail lengkap. Ringkasnya: mode otomatis widget ini sekarang
  memakai penghitung tayangan 7 hari terakhir buatan sendiri (ramah cache
  halaman, tanpa plugin), bukan jumlah komentar.

== Perubahan v1.5.0 (bungkus akhir: hapus elemen tak perlu, speed & SEO/AEO) ==
- Tombol cari di header dibuang. Halaman hasil pencarian (search.php) tetap
  ada dan tetap bisa diakses lewat tautan langsung (?s=) kalau suatu saat
  mau dipasang lagi di tempat lain (mis. widget), tapi tidak lagi tampil
  otomatis di header.
- Tagline diganti jadi "Solusi, alih-alih sensasi", ukurannya dibuat
  proporsional terhadap logo yang sudah diperkecil (caption tipis ala motto
  masthead), tetap rata tengah. Lihat "Tagline otomatis" di atas.
- Form komentar & daftar komentar di bawah artikel dihapus total (bukan
  cuma disembunyikan) — file comments.php dan CSS terkaitnya dibuang dari
  paket tema. Kotak "Tinggalkan komentar" tidak akan muncul lagi di artikel
  mana pun.
- Speed & cache:
  - Preconnect ke domain Google Fonts ditambahkan di <head>, supaya koneksi
    ke font sudah siap sebelum stylesheet-nya diminta browser.
  - Script emoji bawaan WordPress (polyfill lama yang jarang relevan lagi)
    dan elemen <head> yang jarang dipakai (RSD/WLW, shortlink, tag versi
    WP) dimatikan — beberapa request/inline-script kecil lebih sedikit di
    SETIAP halaman.
  - Semua script tema (main.js, quote-share.js) sudah dimuat di footer
    (non-blocking) sejak awal; tidak ada perubahan di sini, sekadar
    dikonfirmasi ulang saat audit.
  - Tema ini tetap kompatibel dengan plugin cache halaman (WP Super Cache,
    LiteSpeed Cache, WP Rocket, dst) — tidak ada output yang beda per
    pengunjung di halaman yang di-cache (mode gelap otomatis lewat CSS,
    ticker harga lewat WP-Cron tersimpan sama untuk semua pengunjung).
- SEO & AI search: file baru inc/seo-meta.php menambahkan <meta
  name="description">, Open Graph (og:title/og:description/og:image/dst),
  dan Twitter Card di setiap halaman — dipakai mesin pencari, pratinjau
  saat dibagikan ke WhatsApp/media sosial, dan beberapa AI search/crawler
  yang membaca og:description sebagai salah satu sinyal ringkasan halaman.
  Otomatis DILEWATI kalau situs sudah pakai plugin SEO populer (Yoast,
  RankMath, All in One SEO, SEOPress) supaya tidak dobel dengan tag yang
  sudah dihasilkan plugin tersebut.

== Perubahan v1.2.0 (perbaikan tampilan mobile & header) ==
- Hero homepage (headline utama + daftar berita di sampingnya) diganti dari
  CSS grid ke susunan blok biasa saat layar sempit (<=860px). Ini memperbaiki
  bug di beberapa browser/WebView Android tempat headline besar bisa
  bertumpuk (overlap) dengan daftar berita di bawahnya.
- Header dirapikan: tanggal/hari dan tombol mode gelap-terang dibuang dari
  utility bar. Mode gelap/terang tetap jalan otomatis mengikuti pengaturan
  perangkat pembaca (prefers-color-scheme), tanpa perlu tombol manual —
  bar utilitas juga otomatis tersembunyi kalau tidak ada isinya sama sekali.
- Menu utama: item pertama (biasanya "Berita"/Beranda) sekarang punya jarak
  dan garis pemisah dari kategori lain di sebelahnya, pola umum di nav
  situs media besar (Home dipisah dari daftar rubrik).
- Versi mobile: kartu berita per kategori (Otomotif, Politik, dst.) memakai
  jarak antar-kartu yang lebih lega dan thumbnail bersudut rounded,
  menggantikan garis pembatas — mengikuti gaya aplikasi berita modern
  (Google News, Apple News, BBC News) di layar sempit.

== Perubahan v1.4.0 (penyempurnaan header, nav, byline, Ringkasan Cepat) ==
- Menu utama mobile: dikembalikan jadi satu baris yang bisa digeser
  (horizontal-scroll) kalau item tidak muat, bukan melipat ke baris kedua.
  Dropdown "Berita" tetap berfungsi normal — teknis: dropdown-nya sekarang
  memakai position:fixed dengan posisi dihitung oleh main.js, supaya tidak
  ikut terpotong oleh scroll container (overflow-x:auto pada elemen induk
  otomatis membuat overflow-y ikut "auto", yang akan memotong dropdown
  kalau dropdown-nya masih position:absolute seperti versi sebelumnya).
- Logo di header diperkecil dan logo+tagline sekarang ditengahkan; tombol
  cari dipindah jadi ikon melayang di pojok kanan atas.
- Utility bar atas kini hanya menampilkan "English version" dan "Kirim
  Kabar Kalbar" — link "Redaksi" dihapus dari situ (halaman Redaksi tetap
  bisa ditautkan lewat menu utama atau footer kalau perlu).
- Artikel: kalau ada tanggal "Diperbarui", tanggal "Diterbitkan" tidak lagi
  ditampilkan berdampingan — cukup salah satu, sesuai yang paling relevan.
- Ringkasan Cepat: mesin ekstraksi otomatis (dipakai kalau kotaknya
  dikosongkan redaksi) dirombak supaya benar-benar mengambil poin dari
  SELURUH artikel, bukan cuma paragraf pembuka — lihat "Fitur AHA" nomor 3
  di bawah untuk detail cara kerjanya.

== Perubahan v1.3.0 (submenu dropdown & Ringkasan Cepat ala Smart Brevity) ==
- Submenu dropdown: item menu yang punya sub-item (mis. "Berita" dengan
  anak menu Daerah/Nasional/Internasional) sekarang tampil sebagai dropdown
  yang rapi, bukan sebaris flat yang berantakan. Terbuka lewat hover di
  desktop; di HP/tablet, sebuah tombol panah kecil ditambahkan otomatis di
  sebelah link untuk tap-to-toggle (karena hover tidak ada di layar sentuh).
  Lihat "Submenu dropdown" di bawah untuk cara mengaturnya di Appearance >
  Menus. Sebagai efek samping perbaikan ini, menu utama di HP kini melipat
  ke baris kedua kalau item terlalu banyak, bukan discroll ke samping —
  supaya dropdown tidak ikut terpotong oleh scroll container.
- Ringkasan Cepat "diracik ulang" jadi gaya Smart Brevity (dipopulerkan
  Axios, dipakai juga Semafor/Politico Playbook): sekarang berupa 1-4 poin
  fakta inti yang tampil ber-bullet, bukan satu paragraf umum. Kotak edit
  di halaman artikel juga diperbarui instruksinya (satu poin per baris).
  Lihat "Fitur AHA" nomor 3 di bawah.

== Submenu dropdown ==
Di Appearance > Menus:
1. Tambahkan semua item (Berita, Daerah, Nasional, Internasional, Ekonomi,
   dst) ke menu seperti biasa.
2. Untuk menjadikan Daerah/Nasional/Internasional sebagai sub-item di bawah
   "Berita": seret kotak item itu sedikit ke KANAN sampai posisinya
   ter-indent di bawah "Berita" (WordPress otomatis menandainya "sub item").
3. Simpan menu. Tidak perlu setting tambahan di tema — begitu strukturnya
   berjenjang, dropdown otomatis aktif.

Kalau lebih suka menyembunyikannya sama sekali (bukan dropdown), cukup
hapus item tersebut dari menu, atau jangan ditambahkan sejak awal — tanpa
perlu mengubah kode.

== Fitur AHA (ringan, jarang dipakai portal lokal) ==

1. Kutip & bagikan (highlight-to-share)
   Sorot kalimat mana pun di badan artikel — muncul toolbar kecil untuk
   membagikan kutipan itu ke WhatsApp/X, atau menyalin tautannya. Tautan
   yang dibuat memakai teknik "Scroll-To-Text-Fragment" (#:~:text=...) yang
   didukung Chrome/Edge/Android sejak 2020: saat dibuka, browser langsung
   menyorot kalimat yang dikutip di halaman, tanpa pembaca perlu mencari
   manual. Dipakai New York Times, The Guardian, dan Medium, tapi hampir
   tidak ada di tema WordPress berbahasa Indonesia. Murni JavaScript
   client-side, tanpa dependensi, hanya dimuat di halaman artikel.

2. Speakable schema (AEO/asisten suara)
   Kotak "Ringkasan Cepat" ditandai dengan schema.org SpeakableSpecification
   di JSON-LD NewsArticle, teknik yang dipopulerkan Washington Post supaya
   asisten suara (Google Assistant, dsb.) bisa membacakan ringkasan artikel
   dengan aman. Tidak menambah beban halaman sama sekali (hanya JSON-LD).

3. Ringkasan Cepat ala Smart Brevity + ekstraksi otomatis
   Kotak "Ringkasan Cepat" di atas artikel berupa 1-4 poin fakta inti
   ber-bullet (bukan satu paragraf basa-basi), gaya yang dipopulerkan Axios
   dan ditiru Semafor/Politico Playbook — pembaca (dan AI Overview/
   Perplexity) bisa langsung menangkap inti berita dalam 3 detik. Redaksi
   tinggal menulis satu fakta terpenting per baris di kotak edit artikel.

   Kalau kotak itu DIKOSONGKAN, tema tidak lagi sekadar memotong paragraf
   pertama. Urutan yang dipakai:
     1) Kotak Ringkasan Cepat (kalau diisi) — selalu prioritas utama.
     2) Excerpt manual WordPress (kalau redaksi mengisi kotak Excerpt).
     3) Ekstraksi otomatis dari ISI artikel: tiap kalimat diskor dari
        seberapa sering kata-katanya muncul di seluruh artikel (dikurangi
        stopword Bahasa Indonesia), lalu artikel dibagi jadi beberapa
        "seksi" berurutan (awal/tengah/akhir) dan kalimat berskor
        tertinggi dari TIAP seksi yang dipilih — supaya poinnya benar-benar
        mewakili keseluruhan artikel, bukan cuma paragraf pembuka. Kalimat
        yang isinya mirip satu sama lain otomatis dibuang salah satunya.
     4) Excerpt otomatis WordPress (potongan kata pertama) sebagai jaring
        pengaman paling akhir, kalau artikelnya terlalu pendek untuk (3).
   Cara (3) tetap sekadar tebakan mesin berbasis statistik kata — bukan
   pemahaman makna seperti AI generatif — jadi kotak Ringkasan Cepat manual
   selalu dianjurkan untuk hasil terbaik. Tidak ada API pihak ketiga yang
   dipanggil untuk ini, semuanya jalan di server sendiri (PHP murni).
   Sepenuhnya kompatibel dengan artikel lama yang sudah ditulis satu
   paragraf — tetap tampil sebagai kalimat biasa, bukan bullet aneh untuk
   satu poin.

== Batasan yang perlu diketahui ==
- Tema ini adalah tema klasik (PHP template), bukan block theme/FSE.
- Seksi "English Version" memakai kategori biasa (english-version), belum
  memakai sistem multibahasa penuh (hreflang otomatis antar bahasa). Untuk
  itu, pertimbangkan plugin seperti Polylang/WPML di kemudian hari.
- Kode iklan pihak ketiga (mis. tag <script> AdSense) yang ditempel di
  widget "Slot Iklan" hanya tampil utuh untuk akun dengan izin
  "unfiltered_html" (biasanya Administrator di instalasi single-site).
- Ticker harga diperbarui via WP-Cron (bergantung ada kunjungan situs untuk
  memicu WP-Cron, seperti umumnya WordPress) — bukan real-time streaming
  harga. Lihat bagian "Ticker harga otomatis" di atas untuk detail sumber
  dan mekanisme fallback-nya.
- Harga emas di ticker adalah harga emas dunia (spot) yang dikonversi ke
  Rupiah/gram, BUKAN harga jual resmi Antam — Antam belum punya API resmi
  gratis yang bisa diandalkan tema ini tanpa API key pihak ketiga.
- Tautan "Kutip & bagikan" mengandalkan Scroll-To-Text-Fragment, fitur
  browser yang didukung Chrome/Edge/Android tapi belum di semua browser
  (Firefox/Safari). Di browser yang belum mendukung, tautan tetap terbuka
  normal ke artikel, hanya tanpa efek sorot otomatis — bukan error.
- Setiap file PHP sudah dicek dengan `php -l` (tidak ada syntax error), dan
  mengikuti praktik keamanan WordPress standar (nonce, sanitasi, escaping).
  Namun tema ini belum diuji langsung di server WordPress sungguhan dengan
  data dan plugin milik teraju.id — uji dulu di staging sebelum dipakai di
  situs utama.

== Struktur file ==
teraju10/
  style.css              -> semua desain (token warna, tipografi, layout)
  functions.php          -> pendaftaran fitur tema
  header.php / footer.php / sidebar.php
  front-page.php         -> homepage
  single.php             -> halaman artikel
  page.php               -> halaman statis
  archive.php / author.php / search.php / 404.php / index.php
  template-parts/        -> kartu artikel yang dipakai berulang
  inc/
    customizer.php       -> semua pengaturan di Appearance > Customize
    meta-boxes.php        -> kotak Ringkasan Cepat & Fakta Cepat
    schema-markup.php     -> JSON-LD NewsArticle + BreadcrumbList
    widgets.php           -> widget Postingan Terpopuler & Slot Iklan
    template-tags.php     -> fungsi bantu (breadcrumb, waktu baca, dst)
    price-ticker.php      -> ambil & cache harga emas/kurs otomatis (WP-Cron)
    seo-meta.php           -> meta description, Open Graph, Twitter Card
    view-counter.php       -> penghitung tayangan 7 hari (utk Postingan Terpopuler)
  assets/js/main.js       -> dropdown menu, progress bar, simpan, bagikan
  assets/js/quote-share.js -> toolbar "kutip & bagikan" (hanya di artikel)
  assets/js/view-tracker.js -> lapor tayangan artikel (ramah cache, lihat atas)
  assets/video/karhutla-smoke.webm/.mp4 -> video efek asap (lihat Efek Kesadaran Karhutla)
  assets/js/admin-widgets.js -> tombol upload gambar di widget Slot Iklan
