<?php
require('assets/lib/fpdf.php');
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 30);
        $this->Cell(30, 10, 'RAONE');

        $this->Ln(10);
        $this->SetFont('Arial', 'i', 10);
        $this->cell(30, 10, 'Jl. Poros Kolaka Bombana');

        $this->cell(80);
        $this->SetFont('Arial', '', 10);
        $this->cell(30, 10, 'Pabiring, ' . base64_decode($_GET['uuid']) . '');
        $this->Line(10, 40, 200, 40);

        $this->Ln(5);
        $this->SetFont('Arial', 'i', 10);
        $this->cell(30, 10, 'Telp/Fax : 0822-9170-0778');
        $this->Line(10, 40, 200, 40);

        $this->Cell(80);
        $this->SetFont('Arial', 'u', 15);
        $this->Cell(30, 10, 'Kepada : ' . base64_decode($_GET['id-uid']) . '', 0, 'C');

        $this->Ln(5);
        $this->SetFont('Arial', 'i', 10);
        $this->cell(30, 10, 'No Invoice : ' . base64_decode($_GET['inf']) . '');
        $this->Line(10, 40, 200, 40);
    }
    function LoadData()
    {
        // Membuat koneksi ke database
        $conn = mysqli_connect("localhost", "root", "", "imk");

        // Memeriksa apakah koneksi berhasil
        if (!$conn) {
            die("Koneksi gagal: " . mysqli_connect_error());
        }

        // Mendekode ID dari URL
        $id = base64_decode($_GET['oid']);

        // Menyiapkan query SQL
        $sql = "SELECT sub_transaksi.jumlah_beli, barang.nama_barang, barang.harga_jual, sub_transaksi.total_harga 
            FROM sub_transaksi 
            INNER JOIN barang ON barang.id_barang = sub_transaksi.id_barang 
            WHERE sub_transaksi.id_transaksi = '$id'";

        // Menjalankan query
        $result = mysqli_query($conn, $sql);

        // Inisialisasi array hasil
        $hasil = [];

        // Memeriksa apakah query berhasil
        if ($result) {
            // Mengambil data dan menyimpannya di array hasil
            while ($r = mysqli_fetch_assoc($result)) {
                $hasil[] = $r;
            }
        } else {
            // Menangani kesalahan jika query gagal
            die("Query gagal: " . mysqli_error($conn));
        }

        // Menutup koneksi ke database
        mysqli_close($conn);

        // Mengembalikan data yang diambil
        return $hasil;
    }

    function BasicTable($header, $data)
    {
        // Mengatur font dan membuat header tabel
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(15, 7, $header[0], 1);
        $this->Cell(90, 7, $header[1], 1);
        $this->Cell(40, 7, $header[2], 1);
        $this->Cell(40, 7, $header[3], 1);
        $this->Ln();

        // Mengatur font untuk data tabel
        $this->SetFont('Arial', '', 12);
        foreach ($data as $row) {
            $this->Cell(15, 7, $row['jumlah_beli'], 1);
            $this->Cell(90, 7, $row['nama_barang'], 1);
            $this->Cell(40, 7, "Rp " . number_format($row['harga_jual']), 1);
            $this->Cell(40, 7, "Rp " . number_format($row['total_harga']), 1);
            $this->Ln();
        }

        // Membuat koneksi ke database
        $conn = mysqli_connect("localhost", "root", "", "imk");

        // Memeriksa apakah koneksi berhasil
        if (!$conn) {
            die("Koneksi gagal: " . mysqli_connect_error());
        }

        // Mendekode ID dari URL
        $id = base64_decode($_GET['oid']);

        // Menyiapkan query untuk mendapatkan total harga dan jumlah beli
        $sql = "SELECT SUM(total_harga) AS grand_total, SUM(jumlah_beli) AS jumlah_beli 
                FROM sub_transaksi 
                WHERE id_transaksi = '$id'";

        // Menjalankan query
        $result = mysqli_query($conn, $sql);
        $getsum1 = mysqli_fetch_assoc($result);

        // Menutup koneksi ke database
        mysqli_close($conn);

        // Menampilkan subtotal di tabel
        $this->Cell(15);
        $this->Cell(90);
        $this->Cell(40, 7, 'Sub total : ');
        $this->Cell(40, 7, 'Rp. ' . number_format($getsum1['grand_total']) . '', 1);
        $this->Ln(30);

        // Menampilkan penerima
        $this->SetFont('Arial', '', 15);
        session_start();
        $this->Cell(30, -10, 'Diterima Oleh : ' . $_SESSION['username'] . '');
        $this->Ln(10);

        // Menampilkan catatan
        $this->SetFont('Arial', '', 11);
        $this->Cell(30, -10, '* Barang Yang Sudah Dibeli Tidak Bisa Dikembalikan.');
    }
}

$pdf = new PDF();
$pdf->SetTitle('Invoice : ' . base64_decode($_GET['inf']) . '');
$pdf->AliasNbPages();
$header = array('Qty', 'Nama Barang', 'Harga', 'Total Harga');
$data = $pdf->LoadData();
$pdf->AddPage();
$pdf->Ln(20);
$pdf->BasicTable($header, $data);
$filename = base64_decode($_GET['inf']);
$pdf->Output('', 'RAONE/' . $filename . '.pdf');
