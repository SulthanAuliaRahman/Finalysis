<?php

namespace App\Services;

class TextCleanerService
{
    /**
     * Bersihkan output AI dari sintaks markdown, supaya jadi plain text
     * yang rapi ditampilkan di UI (React tidak render markdown mentah,
     * jadi tanda **, ##, -, dst akan tampil apa adanya kalau tidak dibersihkan).
     */
    public static function bersihkanMarkdown(string $text): string
    {
        // Hapus heading markdown (#, ##, ### dst di awal baris)
        $text = preg_replace('/^#{1,6}\s*/m', '', $text);

        // Hapus bold/italic (**teks**, __teks__, *teks*, _teks_)
        // Urutan penting: bold dulu (2 karakter) baru italic (1 karakter),
        // supaya **teks** tidak kebobol jadi *teks* dulu baru diproses ulang.
        $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);
        $text = preg_replace('/__(.*?)__/s', '$1', $text);

        // Italic: hanya cocokkan tanda bintang/underscore yang menempel
        // langsung ke teks (pola italic asli), BUKAN yang dipisah spasi
        // seperti simbol perkalian di narasi DuPont ("NPM * TATO * Leverage").
        $text = preg_replace('/(?<!\w)\*(?!\s)(.*?)(?<!\s)\*(?!\w)/s', '$1', $text);
        $text = preg_replace('/(?<!\w)_(?!\s)(.*?)(?<!\s)_(?!\w)/s', '$1', $text);

        // Hapus bullet list markdown ("* teks" di awal baris) -> jadi teks polos.
        // SENGAJA tidak menyentuh tanda minus ("- teks") di awal baris, karena
        // itu bisa jadi angka negatif ("- 5% penurunan..."), bukan cuma bullet.
        $text = preg_replace('/^\*\s+/m', '', $text);

        // Hapus blockquote (> teks)
        $text = preg_replace('/^>\s*/m', '', $text);

        // Hapus multiple newline berlebihan jadi maksimal 2
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}