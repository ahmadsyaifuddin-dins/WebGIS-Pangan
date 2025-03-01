<?php
$title = "Pangan";
$judul = $title;
$url = 'Pangan';
if (isset($_POST['simpan'])) {
    $file = upload('marker', 'marker');
    if ($file != false) {
        $data['marker'] = $file;
        if ($_POST['id_Pangan'] != '') {
            // hapus file di dalam folder
            $db->where('id_Pangan', $_GET['id']);
            $get = $db->ObjectBuilder()->getOne('t_pangan');
            $marker = $get->marker;
            unlink('assets/unggah/marker/' . $marker);
            // end hapus file di dalam folder
        }
    }
    $data['id_kecamatan'] = $_POST['id_kecamatan'];
    $data['keterangan'] = $_POST['keterangan'];
    $data['lokasi'] = $_POST['lokasi'];
    $data['lat'] = $_POST['lat'];
    $data['lng'] = $_POST['lng'];
    $data['tanggal'] = $_POST['tanggal'];
    $data['id_pengguna'] = $session->get('id_pengguna'); // Menyimpan id_pengguna yang mengisi data

    if ($_POST['id_Pangan'] == "") {
        $exec = $db->insert("t_pangan", $data);
        $info = '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Sukses!</h4> Data Sukses Ditambah </div>';
    } else {
        $db->where('id_Pangan', $_POST['id_Pangan']);
        $exec = $db->update("t_pangan", $data);
        $info = '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Sukses!</h4> Data Sukses diubah </div>';
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
    $db->where("id_Pangan", $_GET['id']);
    $exec = $db->delete("t_pangan");
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
    $id_Pangan = "";
    $id_kecamatan = "";
    $lokasi = "";
    $keterangan = "";
    $lat = "";
    $lng = "";
    $tanggal = date('Y-m-d');
    $marker = "";
    if (isset($_GET['ubah']) and isset($_GET['id'])) {
        $id = $_GET['id'];
        $db->where('id_Pangan', $id);
        $row = $db->ObjectBuilder()->getOne('t_pangan');
        if ($db->count > 0) {
            $id_Pangan = $row->id_Pangan;
            $id_kecamatan = $row->id_kecamatan;
            $lokasi = $row->lokasi;
            $keterangan = $row->keterangan;
            $lat = $row->lat;
            $lng = $row->lng;
            $tanggal = $row->tanggal;
            $marker = $row->marker;
        }
    }
?>
    <?= content_open('Form Pangan') ?>
    <form method="post" enctype="multipart/form-data">
        <?= input_hidden('id_Pangan', $id_Pangan) ?>
        <div class="form-group">
            <label>Lokasi</label>
            <div class="row">
                <div class="col-md-4">
                    <?= input_text('lokasi', $lokasi) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Nama Kecamatan</label>
            <div class="row">
                <div class="col-md-6">
                    <?php
                    $op[''] = 'Pilih Kecamatan';
                    foreach ($db->ObjectBuilder()->get('m_kecamatan') as $row) {
                        $op[$row->id_kecamatan] = $row->nm_kecamatan;
                    }
                    ?>
                    <?= select('id_kecamatan', $op, $id_kecamatan) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <div class="row">
                <div class="col-md-4">
                    <?= textarea('keterangan', $keterangan) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Titik Koordinat</label>
            <div class="row">
                <div class="col-md-3">
                    <?= input_text('lat', $lat) ?>
                </div>
                <div class="col-md-3">
                    <?= input_text('lng', $lng) ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Tanggal</label>
            <div class="row">
                <div class="col-md-4">
                    <input class="form-control" type="datetime-local" value="<?= $tanggal ?>" name="tanggal">

                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Marker</label>
            <div class="row">
                <div class="col-md-4">
                    <input class="form-control" type="file" name="marker">
                </div>
            </div>
            <?php if ($marker != "") : ?>
                <p>File saat ini: <a href="<?= assets('unggah/marker/' . $marker) ?>" target="_BLANK"><?= $marker ?></a></p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <button type="submit" name="simpan" class="btn btn-info"><i class="fa fa-save"></i> Simpan</button>
            <a href="<?= url($url) ?>" class="btn btn-danger"><i class="fa fa-reply"></i> Kembali</a>
        </div>
    </form>
    <?= content_close() ?>

<?php  } else { ?>
    <?= content_open('Data Pangan') ?>

    <a href="<?= url($url . '&tambah') ?>" class="btn btn-success"><i class="fa fa-plus"></i> Tambah</a>
    <hr>
    <?= $session->pull("info") ?>

    <div class="table-responsive">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Lokasi</th>
                    <th>Nama Kecamatan</th>
                    <th>Keterangan</th>
                    <th>Lat</th>
                    <th>Lng</th>
                    <th>Tanggal</th>
                    <th>Marker</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $db->join('m_kecamatan b', 'a.id_kecamatan=b.id_kecamatan', 'LEFT');
                $getdata = $db->ObjectBuilder()->get('t_pangan a');
                foreach ($getdata as $row) {
                ?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= $row->lokasi ?></td>
                        <td><?= $row->nm_kecamatan ?></td>
                        <td><?= $row->keterangan ?></td>
                        <td><?= $row->lat ?></td>
                        <td><?= $row->lng ?></td>
                        <td><?= $row->tanggal ?></td>
                        <td class="text-center">
                            <?= ($row->marker == '' ? '-' : '<img src="' . assets('unggah/marker/' . $row->marker) . '" width="40px">') ?>
                        </td>
                        <td>
                            <a href="<?= url($url . '&ubah&id=' . $row->id_Pangan) ?>" class="btn btn-info"><i class="fa fa-edit"></i> Ubah</a>
                            <a href="<?= url($url . '&hapus&id=' . $row->id_Pangan) ?>" class="btn btn-danger" onclick="return confirm('Hapus data?')"><i class="fa fa-trash"></i> Hapus</a>
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