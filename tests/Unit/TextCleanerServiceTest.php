<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TextCleanerService;

class TextCleanerServiceTest extends TestCase
{
    /** UT-013: hapus heading markdown */
    public function test_hapus_heading_markdown(): void
    {
        $input = "## Ringkasan Tren\nIsi narasi...";
        $expected = "Ringkasan Tren\nIsi narasi...";

        $this->assertEquals($expected, TextCleanerService::bersihkanMarkdown($input));
    }

    /** UT-014: hapus bold markdown */
    public function test_hapus_bold_markdown(): void
    {
        $input = "ROE **meningkat signifikan** pada Q2";
        $expected = "ROE meningkat signifikan pada Q2";

        $this->assertEquals($expected, TextCleanerService::bersihkanMarkdown($input));
    }

    /** UT-015: simbol perkalian (spasi di kedua sisi) TIDAK ikut terhapus sebagai italic */
    public function test_simbol_perkalian_tidak_terhapus(): void
    {
        $input = "ROE = NPM * TATO * Leverage";

        $this->assertEquals($input, TextCleanerService::bersihkanMarkdown($input));
    }

    /** UT-016: bullet list dihapus, angka negatif (- 5%) tetap dipertahankan */
    public function test_bullet_dihapus_minus_dipertahankan(): void
    {
        $input = "* Pendapatan naik\n- 5% penurunan margin";
        $expected = "Pendapatan naik\n- 5% penurunan margin";

        $this->assertEquals($expected, TextCleanerService::bersihkanMarkdown($input));
    }

    /** UT-017: hapus blockquote */
    public function test_hapus_blockquote(): void
    {
        $input = "> Catatan: data ilustratif";
        $expected = "Catatan: data ilustratif";

        $this->assertEquals($expected, TextCleanerService::bersihkanMarkdown($input));
    }

    /** UT-018: newline berlebih dipangkas maksimal 2 */
    public function test_newline_berlebih_dipangkas(): void
    {
        $input = "Paragraf 1.\n\n\n\nParagraf 2.";
        $expected = "Paragraf 1.\n\nParagraf 2.";

        $this->assertEquals($expected, TextCleanerService::bersihkanMarkdown($input));
    }
}