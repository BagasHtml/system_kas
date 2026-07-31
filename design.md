Desain Landing Page: Interaktif & OrganikDokumen ini berisi panduan tata letak, warna, tipografi, serta integrasi pustaka gulir halus untuk membangun situs yang hidup dan bernyawa.Konsep & Palet WarnaTema: Hijau botol muda yang agak gelap (memberikan kesan natural, segar, namun tetap elegan dan tenang).Warna Utama (Primary): #2D5A27 (Hijau gelap alami)Warna Aksen (Accent): #85B079 (Hijau muda lembut)Warna Latar (Background): #142212 (Hijau sangat gelap/hampir hitam)Warna Teks (Text): #E8F0E5 (Putih kehijauan pudar)Struktur Bagian (Sections)Hero Section: Teks sambutan besar dengan efek parallax ringan dan tombol aksi utama.Tentang Kami (About): Grid bercerita dengan transisi kemunculan saat digulir (fade-in).Fitur/Layanan (Features): Kartu interaktif yang merespons kursor mouse (hover tilt).Kontak/Footer: Formulir sederhana dengan umpan balik visual yang jelas.Integrasi Lenis (Smooth Scroll)Gunakan pustaka Lenis untuk pengalaman gulir yang halus dan ringan di peramban web.javascriptimport Lenis from '@studio-freight/lenis'

const lenis = new Lenis({
  duration: 1.2,
  easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
  direction: 'vertical',
  smooth: true,
})

function raf(time) {
  lenis.raf(time)
  requestAnimationFrame(raf)
}

requestAnimationFrame(raf)