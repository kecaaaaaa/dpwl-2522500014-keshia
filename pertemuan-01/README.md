# pertemuan-01
1. kesinambungan PWD–DPW–DPWL;
Kalo diawal PWD tuh diajarin cara pakai aplikasi sublime dengan menggunakan bahasa pemrograman HTML,CSS,PHP sama Javascript yang output akhirnya sebuah web sederhana yang kita buat masing" dengan struktur berbeda, terus kalo DPW mulai naik levelnya kami membuat web yang lebih proper element" yang didalam web dihubungkan dengan database yang kami buat di localhost dengan menggunakan xampp, lalu yang terakhir dpwl makin naik level lagi dengan menggunakan konsep mvc dalam membuat webnya.
2. perbedaan PHP terstruktur dan MVC;
PHP terstruktur biasanya digabung dalam satu file yang berisi HTML, PHP dan lain", lalu pada konsep MVC codingan uda dipisah" berdasarkan fungsi masing" jadi lebih mudah di mengerti.
3. fungsi Model, View, dan Controller;
fungsi model untuk menyimpan database yang akan digunakan misalnya data buku, atau data user. Fungsi View untuk mengatur data dan akses ke basis data. Fungsi Controller adalah sebagai penghubung, yang menerima request dari user lalu dialihkan ke View untuk ditampilin.
4. alur request–response MVC;
Yang pertama user mengirim request di browser, lalu controller menerima request, lalu controller minta data ke Model (bila perlu), lalu Model berhubungan dengan basis data, lalu Controller meneruskan data ke View, lalu View menyusun output, terakhir output dikirim pada user di browser.
5. pemetaan satu atau beberapa bagian/fitur aplikasi DPW ke Model, Controller, dan View disertai alasan; dan
Fitur Sistem Tambah Buku Baru
Pada View berisi form Html dengan inputan Judul Buku, Pengarang, Kategori dan tombol simpan, ini merupakan bagian yang dilihat dan disi langsung sama user.
Pada Controller berisi pengecekan apakah judul buku kosong atau tidak, kalau aman controller memanggil model buat nyimpan lalu ngarahin user ke halaman daftar buku, controller bertindak sebagai otak yang mengatur lalu lintas.
Pada Model berisi inputan database.

6. kesimpulan P1.
Penggunaan struktur MVC bikin codingan jauh lebih rapih, teroganisir, jadi lebih mudah untuk dimengerti alurnya.