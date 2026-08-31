=== Teraju10 ===
Tema WordPress khusus untuk teraju.id.
Version: 1.2.0
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
   - Site Identity: unggah logo dan isi tagline ("Solusi, alih-alih
     sensasi — portal Kalimantan Barat").
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

5. Setiap kali menulis artikel baru (halaman edit artikel)
   - Kotak "Ringkasan Cepat": isi 40-60 kata jawaban langsung. Ini yang
     tampil di atas artikel dan paling sering dikutip AI Overview/
     Perplexity/ChatGPT.
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
  assets/js/main.js       -> dark mode, progress bar, simpan, bagikan
  assets/js/quote-share.js -> toolbar "kutip & bagikan" (hanya di artikel)
  assets/js/admin-widgets.js -> tombol upload gambar di widget Slot Iklan
