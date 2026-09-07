<?php

namespace Tests\Unit;

use App\Services\TextCleanerService;
use PHPUnit\Framework\TestCase;

/**
 * Implementasi kode dari sheet "Unit testing" (UT-031 s.d. UT-036) pada
 * Test_Case.xlsx. Method bersihkanMarkdown() bersifat static dan murni
 * (tidak ada state/DB), jadi cukup PHPUnit\Framework\TestCase biasa.
 */
class TextCleanerServiceTest extends TestCase
{
    /** UT-031: Hapus heading markdown */
    public function test_hapus_heading_markdown(): void
    {
        $input = "## Ringkasan Tren\nIsi narasi...";

        $result = TextCleanerService::bersihkanMarkdown($input);

        $this->assertSame("Ringkasan Tren\nIsi narasi...", $result);
    }

    /** UT-032: Hapus bold markdown */
    public function test_hapus_bold_markdown(): void
    {
        $input = 'ROE **meningkat signifikan** pada Q2';

        $result = TextCleanerService::bersihkanMarkdown($input);

        $this->assertSame('ROE meningkat signifikan pada Q2', $result);
    }

    /**
     * UT-033: Simbol perkalian tidak ikut terhapus (bukan italic).
     * Tanda bintang dengan spasi di kedua sisi TIDAK dianggap italic,
     * sesuai regex lookahead/lookbehind spasi pada bersihkanMarkdown().
     */
    public function test_simbol_perkalian_tidak_terhapus(): void
    {
        $input = 'ROE = NPM * TATO * Leverage';

        $result = TextCleanerService::bersihkanMarkdown($input);

        $this->assertSame('ROE = NPM * TATO * Leverage', $result);
    }

    /**
     * UT-034: Bullet list dihapus, angka negatif tetap dipertahankan.
     * Baris '* ' jadi teks polos, baris '- 5%' TIDAK diutak-atik karena
     * dianggap potensi angka negatif, bukan bullet.
     */
    public function test_bullet_dihapus_angka_negatif_dipertahankan(): void
    {
        $input = "* Pendapatan naik\n- 5% penurunan margin";

        $result = TextCleanerService::bersihkanMarkdown($input);

        $this->assertSame("Pendapatan naik\n- 5% penurunan margin", $result);
    }

    /** UT-035: Hapus blockquote */
    public function test_hapus_blockquote(): void
    {
        $input = '> Catatan: data ilustratif';

        $result = TextCleanerService::bersihkanMarkdown($input);

        $this->assertSame('Catatan: data ilustratif', $result);
    }

    /** UT-036: Newline berlebih dipangkas maksimal 2 */
    public function test_newline_berlebih_dipangkas(): void
    {
        $input = "Paragraf 1.\n\n\n\nParagraf 2.";

        $result = TextCleanerService::bersihkanMarkdown($input);

        $this->assertSame("Paragraf 1.\n\nParagraf 2.", $result);
    }
}
