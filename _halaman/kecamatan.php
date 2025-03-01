<?php
date_default_timezone_set("Asia/Ujung_Pandang");

function convertDayNameToIndonesian($dateString)
{
    // Daftar nama hari dalam bahasa Inggris dan bahasa Indonesia
    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    // Ganti nama hari dalam string tanggal
    foreach ($days as $english => $indonesian) {
        if (strpos($dateString, $english) !== false) {
            $dateString = str_replace($english, $indonesian, $dateString);
            break; // Setelah mengganti, tidak perlu mencari lebih lanjut
        }
    }

    return $dateString;
}

$title = "Kecamatan";
$judul = $title;
$url = 'kecamatan';
if (!in_array($session->get('level'), ['Super Admin', 'Administrator'])) {
    redirect(url('beranda'));
}

if (isset($_POST['simpan'])) {
    $file = upload('geojson_kecamatan', 'geojson');
    $tgl_isi =  convertDayNameToIndonesian(date('l/d/M/Y-H:i:s:a')); // Menambahkan konversi nama hari
    if ($file !== false) {
        $data['geojson_kecamatan'] = $file;
        if ($_POST['id_kecamatan'] != '') {
            // Hapus file lama jika ada file baru yang diunggah
            $db->where('id_kecamatan', $_POST['id_kecamatan']);
            $get = $db->ObjectBuilder()->getOne('m_kecamatan');
            $geojson_kecamatan = $get->geojson_kecamatan;
            unlink('assets/unggah/geojson/' . $geojson_kecamatan);
        }
    } else {
        // Jika tidak ada file baru yang diunggah, gunakan file lama
        if ($_POST['id_kecamatan'] != '') {
            $db->where('id_kecamatan', $_POST['id_kecamatan']);
            $get = $db->ObjectBuilder()->getOne('m_kecamatan');
            $data['geojson_kecamatan'] = $get->geojson_kecamatan;
        } else {
            // Menambahkan handling error jika upload gagal dan ini adalah input baru
            $session->set("info", '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Error!</h4> Upload file gagal dilakukan
              </div>');
            redirect(url($url));
            return;
        }
    }

    // Cek validasi dan input data ke database
    $validation = null;
    if ($_POST['id_kecamatan'] != "") {
        $db->where('id_kecamatan', $_POST['id_kecamatan'], '!=');
    }
    $db->where('kd_kecamatan', $_POST['kd_kecamatan']);
    $db->get('m_kecamatan');
    if ($db->count > 0) {
        $validation[] = 'Kode Kecamatan Sudah Ada';
    }
    if ($_POST['nm_kecamatan'] == '') {
        $validation[] = 'Nama Kecamatan Tidak Boleh Kosong';
    }
    if (($_POST['jml_pangan'] == '')) {
        $validation[] = 'Jumlah Produk Pangan Tidak Boleh Kosong!';
    }
    if (!is_numeric($_POST['jml_pangan'])) {
        $validation[] = 'Inputan Produk Pangan Harus Angka!';
    }

    if ($validation != null) {
        $setTemplate = false;
        $session->set('error_validation', $validation);
        $session->set('error_value', $_POST);
        redirect($_SERVER['HTTP_REFERER']);
        return false;
    }

    // Insert atau update data ke database
    $data['kd_kecamatan'] = $_POST['kd_kecamatan'];
    $data['nm_kecamatan'] = $_POST['nm_kecamatan'];
    $data['warna_kecamatan'] = $_POST['warna_kecamatan'];
    $data['jml_pangan'] = $_POST['jml_pangan'];
    $data['tgl_isi'] = $tgl_isi; // Menambahkan tanggal dan waktu saat ini

    if ($_POST['id_kecamatan'] == "") {
        $exec = $db->insert("m_kecamatan", $data);
        $info = '<div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i> Sukses!</h4> Data Sukses Ditambah
          </div>';

        // Menambahkan data ke tabel data_entries
        if ($exec) {
            $id_pengguna = $session->get('id_pengguna');
            $id_kecamatan = $db->getInsertId();
            $entryData = [
                'id_pengguna' => $id_pengguna,
                'id_kecamatan' => $id_kecamatan
            ];
            $db->insert('data_entries', $entryData);
        }
    } else {
        $db->where('id_kecamatan', $_POST['id_kecamatan']);
        $exec = $db->update("m_kecamatan", $data);
        $info = '<div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i> Sukses!</h4> Data Sukses diubah
          </div>';
    }

    if ($exec) {
        $session->set('info', $info);
    } else {
        $session->set("info", '<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> Error!</h4> Proses gagal dilakukan <br>' . $db->getLastError() . '
          </div>');
    }
    redirect(url($url));
}

if (isset($_GET['hapus'])) {
    $setTemplate = false;
    // hapus file di dalam folder
    $db->where('id_kecamatan', $_GET['id']);
    $get = $db->ObjectBuilder()->getOne('m_kecamatan');
    $geojson_kecamatan = $get->geojson_kecamatan;
    unlink('assets/unggah/geojson/' . $geojson_kecamatan);
    // end hapus file di dalam folder
    $db->where("id_kecamatan", $_GET['id']);
    $exec = $db->delete("m_kecamatan");
    $info = '<div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> Sukses!</h4> Data Sukses dihapus </div>';
    if ($exec) {
        $session->set('info', $info);
    } else {
        $session->set("info", '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Error!</h4> Proses gagal dilakukan
              </div>');
    }
    redirect(url($url));
} elseif (isset($_GET['tambah']) or isset($_GET['ubah'])) {
    $id_kecamatan = "";
    $kd_kecamatan = "";
    $nm_kecamatan = "";
    $geojson_kecamatan = "";
    $jml_pangan = "";
    $warna_kecamatan = "";
    if (isset($_GET['ubah']) and isset($_GET['id'])) {
        $id = $_GET['id'];
        $db->where('id_kecamatan', $id);
        $row = $db->ObjectBuilder()->getOne('m_kecamatan');
        if ($db->count > 0) {
            $id_kecamatan = $row->id_kecamatan;
            $kd_kecamatan = $row->kd_kecamatan;
            $nm_kecamatan = $row->nm_kecamatan;
            $geojson_kecamatan = $row->geojson_kecamatan;
            $jml_pangan = $row->jml_pangan;
            $warna_kecamatan = $row->warna_kecamatan;
        }
    }
    // value ketika validasi
    if ($session->get('error_value')) {
        extract($session->pull('error_value'));
    }
?>
    <?= content_open('Form Kecamatan') ?>
    <form method="post" enctype="multipart/form-data">
        <?php
        // menampilkan error validasi
        if ($session->get('error_validation')) {
            foreach ($session->pull('error_validation') as $key => $value) {
                echo '<div class="alert alert-danger">' . $value . '</div>';
            }
        }
        ?>
        <?= input_hidden('id_kecamatan', $id_kecamatan) ?>
        <div class="form-group">
            <label>Kode Kecamatan</label>
            <div class="row">
                <div class="col-md-4">
                    <?= input_text('kd_kecamatan', $kd_kecamatan) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Nama Kecamatan</label>
            <div class="row">
                <div class="col-md-6">
                    <?= input_text('nm_kecamatan', $nm_kecamatan) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>GeoJSON</label>
            <div class="row">
                <div class="col-md-4">
                    <input class="form-control" type="file" name="geojson_kecamatan">
                </div>
            </div>
            <?php if ($geojson_kecamatan != "") : ?>
                <p>File saat ini: <a href="<?= assets('unggah/geojson/' . $geojson_kecamatan) ?>" target="_BLANK"><?= $geojson_kecamatan ?></a></p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Jumlah Produk Pangan | contoh : 27000</label>
            <div class="row">
                <div class="col-md-4">
                    <?= input_text('jml_pangan', $jml_pangan) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Warna</label>
            <div class="row">
                <div class="col-md-3">
                    <?= input_color('warna_kecamatan', $warna_kecamatan) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" name="tgl_isi" value="<?php echo convertDayNameToIndonesian(date('l/d/M/Y-H:i:s:a')); ?>">
            <button type="submit" name="simpan" class="btn btn-info"><i class="fa fa-save"></i> Simpan</button>
            <a href="<?= url($url) ?>" class="btn btn-danger"><i class="fa fa-reply"></i> Kembali</a>
        </div>
    </form>
    <?= content_close() ?>

<?php } else { ?>
    <?= content_open('Data Kecamatan') ?>

    <a href="<?= url($url . '&tambah') ?>" class="btn btn-success"><i class="fa fa-plus"></i> Tambah</a>
    <hr>
    <?= $session->pull("info") ?>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Kecamatan</th>
                    <th>GeoJSON</th>
                    <th>Produk Pangan Unggul (dalam Ton)</th>
                    <th>Warna</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $getdata = $db->ObjectBuilder()->get('m_kecamatan');
                foreach ($getdata as $row) {
                ?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= $row->kd_kecamatan ?></td>
                        <td><?= $row->nm_kecamatan ?></td>
                        <td><a href="<?= assets('unggah/geojson/' . $row->geojson_kecamatan) ?>" target="_BLANK"><?= $row->geojson_kecamatan ?></a></td>
                        <td><?= $row->jml_pangan ?></td>
                        <td style="background: <?= $row->warna_kecamatan ?>"></td>
                        <td>
                            <a href="<?= url($url . '&ubah&id=' . $row->id_kecamatan) ?>" class="btn btn-info"><i class="fa fa-edit"></i> Ubah</a>
                            <a href="<?= url($url . '&hapus&id=' . $row->id_kecamatan) ?>" class="btn btn-danger" onclick="return confirm('Hapus data?')"><i class="fa fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                <?php
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
    <?= content_close() ?>
<?php } ?>