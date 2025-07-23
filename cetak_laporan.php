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


        $this->Ln(5);
        $this->SetFont('Arial', 'i', 10);
        $this->cell(30, 10, 'Telp/Fax : 0822-9170-0778');


        $this->Ln(5);
        $this->SetFont('Arial', 'i', 10);
        $this->cell(30, 10, 'Data Laporan Tanggal : ' . $_POST['tgl_laporan'] . '');

        $this->Ln(5);
        $this->SetFont('Arial', 'i', 10);
        $this->cell(30, 10, 'Jenis : ' . $_POST['jenis_laporan'] . '');

        $this->cell(130);
        $this->SetFont('Arial', '', 10);
        $this->cell(30, 10, 'Pabiring, ' . date("d-m-Y") . '');

        $this->Line(10, 45, 200, 45);
    }
    function data_barang()
    {
        // Menghubungkan ke database
        $conn = mysqli_connect("localhost", "root", "", "imk");

        // Cek koneksi
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Mendapatkan tanggal dari input
        $tanggal = $_POST['tgl_laporan'];

        // Memeriksa jenis laporan
        if ($_POST['jenis_laporan'] == "perhari") {
            // Format tanggal ke format 'YYYY-MM-DD'
            $split1 = explode('-', $tanggal);
            $tanggal = $split1[2] . "-" . $split1[1] . "-" . $split1[0];
            $query = "SELECT transaksi.id_transaksi, transaksi.tgl_transaksi, transaksi.no_invoice, transaksi.total_bayar, transaksi.nama_pembeli, user.username 
                      FROM transaksi 
                      INNER JOIN user ON transaksi.kode_kasir = user.id 
                      WHERE transaksi.tgl_transaksi LIKE '%$tanggal%' 
                      ORDER BY transaksi.id_transaksi DESC";
        } else {
            // Format tanggal ke format 'YYYY-MM'
            $split1 = explode('-', $tanggal);
            $tanggal = $split1[1] . "-" . $split1[0];
            $query = "SELECT transaksi.id_transaksi, transaksi.tgl_transaksi, transaksi.no_invoice, transaksi.total_bayar, transaksi.nama_pembeli, user.username 
                      FROM transaksi 
                      INNER JOIN user ON transaksi.kode_kasir = user.id 
                      WHERE transaksi.tgl_transaksi LIKE '%$tanggal%' 
                      ORDER BY transaksi.id_transaksi DESC";
        }

        // Melakukan query ke database
        $result = mysqli_query($conn, $query);

        // Memeriksa apakah ada hasil
        if (mysqli_num_rows($result) > 0) {
            // Mengambil hasil query dan memasukkannya ke dalam array
            while ($r = mysqli_fetch_array($result)) {
                $hasil[] = $r;
            }
            // Mengembalikan hasil dalam bentuk array
            return $hasil;
        } else {
            return []; // Mengembalikan array kosong jika tidak ada hasil
        }

        // Menutup koneksi
        mysqli_close($conn);
    }

    function set_table($data)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 7, "No", 1);
        $this->Cell(40, 7, "No Invoice", 1);
        $this->Cell(20, 7, "Kasir", 1);
        $this->Cell(40, 7, "Nama Pembeli", 1);
        $this->Cell(40, 7, "Tanggal Transaksi", 1);
        $this->Cell(40, 7, "Total Bayar", 1);
        $this->Ln();

        $this->SetFont('Arial', '', 9);
        $no = 1;
        foreach ($data as $row) {
            $this->Cell(10, 7, $no++, 1);
            $this->Cell(40, 7, $row['no_invoice'], 1);
            $this->Cell(20, 7, $row['username'], 1);
            $this->Cell(40, 7, $row['nama_pembeli'], 1);
            $this->Cell(40, 7, date("d-m-Y h:i:s", strtotime($row['tgl_transaksi'])), 1);
            $this->Cell(40, 7, "Rp. " . number_format($row['total_bayar']), 1);
            $this->Ln();
        }
    }
}

$pdf = new PDF();
$pdf->SetTitle('Cetak Data Barang');

$data = $pdf->data_barang();

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->Ln(20);
$pdf->set_table($data);
$pdf->Output('', 'RAONE/laporan/' . date("d-m-Y") . '.pdf');
