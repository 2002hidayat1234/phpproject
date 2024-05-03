<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$database = "specta";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$kode_agen = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_agen = $_POST["kode_agen"];

    $sql = "SELECT * FROM data_agen WHERE kode_agen = '$kode_agen'";
    $result = $conn->query($sql);
    
    $sql_username = "SELECT kode_agen FROM data_agen WHERE username = '$kode_agen'";
    $result_username = $conn->query($sql_username);

    if($result->num_rows == 0 && $result_username->num_rows > 0){
        $row = $result_username->fetch_assoc();
        $beri_kode_agen = $row["kode_agen"];
        $kelas = "";
        echo "<script>alert('Yang Anda masukkan adalah Username bukan Kode Agen. Klik OK untuk melihat Kode Agen Anda');</script>";
        echo "<script>alert('Kode Agen Anda: $beri_kode_agen');</script>";


    }else{
        if ($result->num_rows > 0) {
        // Mengambil data dan disimpan di variabel
        $row = $result->fetch_assoc();
        $kode = $row["kode_agen"];
        $nama = $row["nama"];
        $username = $row["username"];
        $wilayah = $row["wilayah"];
        $cabang = $row["cabang"];
        $kelas = $row["kelas"];
    } else {
        $kelas = "";
        echo "<script>alert('Kode Agen yang Anda masukkan salah, tidak terdaftar, atau sudah tidak aktif!!!');</script>";
    }
    }


    


    #Mencari jumlah transaksi berbayar
    $sql_jumlah_transaksi_berbayar = "SELECT * FROM Sultan WHERE kode_agen = '$kode_agen'
    UNION
    SELECT * FROM Jagoan WHERE kode_agen = '$kode_agen'
    UNION
    SELECT * FROM Perintis WHERE kode_agen = '$kode_agen'";
    $result_jumlah_transaksi_berbayar = $conn->query($sql_jumlah_transaksi_berbayar);

    if($result_jumlah_transaksi_berbayar->num_rows > 0){
        $row = $result_jumlah_transaksi_berbayar->fetch_assoc();
        $ranking = $row["Ranking"];
        $jumlah_transaksi_berbayar = $row["Jumlah_Trx_Berbayar"];

    }else{
        #Jika di database kosong
        $jumlah_transaksi_berbayar = 0;
        
    }

    #Jika jumlah_transaksi_berbayar di database 0
    if($jumlah_transaksi_berbayar == 0){
        $ranking = "N/A";
    }


    #Mencari jumlah pembukaan rekening pandai
    $sql_pembukaan_rek_pandai =  "SELECT * FROM pembukaan_rek_pandai WHERE kode_agen = '$kode_agen'";
    $result_pembukaan_rek_pandai = $conn->query($sql_pembukaan_rek_pandai);
    if($result_pembukaan_rek_pandai->num_rows > 0){
        $row = $result_pembukaan_rek_pandai->fetch_assoc();
        $pembukaan_rek_pandai = intval($row["buka_rekening"]);
    }else{
        $pembukaan_rek_pandai = 0;
    }

    #Mencari jumlah transaksi asuransi mikro
    $sql_asuransi_mikro = "SELECT * FROM transaksi_asuransi_mikro WHERE kode_agen='$kode_agen'";
    $result_asuransi_mikro = $conn->query($sql_asuransi_mikro);
    if($result_asuransi_mikro -> num_rows > 0){
        $row = $result_asuransi_mikro->fetch_assoc();
        $asuransi_mikro =intval($row["asuransi_mikro"]);
    }else{
        $asuransi_mikro = 0;
    }

    #Mencari jumlah referal pembukaan rekening
    $sql_ref_buka_rekening = "SELECT * FROM ref_bk_rek WHERE kode_agen='$kode_agen'";
    $result_ref_buka_rekening = $conn->query($sql_ref_buka_rekening);
    if($result_ref_buka_rekening -> num_rows > 0){
        $row = $result_ref_buka_rekening -> fetch_assoc();
        $ref_buka_rekening = intval($row["ref_bk_rek"]);
    }else{
        $ref_buka_rekening = 0;
    }

    #Menghitung total transaksi referral
    $total_transaksi_referral = $pembukaan_rek_pandai + $asuransi_mikro + $ref_buka_rekening;

    #Untuk periode 1 dan hadiah
    $sql_info_kelas = "SELECT * FROM kelas WHERE kelas = '$kelas'";
    $result_info_kelas = $conn->query($sql_info_kelas);
    if($result_info_kelas -> num_rows > 0){
        $row = $result_info_kelas -> fetch_assoc();
        $rank1 = $row["hadiah_rank1"];
        $rank2 = $row["hadiah_rank2"];
        $rank3 = $row["hadiah_rank3"];
        $min_trx_berbayar = intval($row["min_trx_berbayar"]);
        $min_trx_referral = intval($row["min_trx_referral"]);
    }else{
        $rank1 = $rank2 = $rank3 = $min_trx_berbayar = $min_trx_referral = "-";
    }

    
    #Mencari thereshold transaksi berbayar
    #Di atas thereshold yang ada di min transaksi dan min referral
    if($jumlah_transaksi_berbayar > $min_trx_berbayar){
        $thereshold_transaksi_berbayar = "Sudah Tercapai";
    }else{
        $thereshold_transaksi_berbayar = "Belum Tercapai";
    }

    if($total_transaksi_referral > $min_trx_referral){
        $thereshold_transaksi_referral = "Sudah Tercapai";
    }else{
        $thereshold_transaksi_referral = "Belum Tercapai";
    }



}

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spekta</title>
</head>

<body style="background:wheat; font-size: 24px;">
    <div style="position:absolute; color:black; left:210px">
        <div style="width: 1287px; height: 112px; left: 25px; top: 74px; position: absolute">
            <div style="width: 1240px; height: 112px; left: 79px; top: 0px; position: absolute; background: #FF6600; border-radius: 60px"></div>
            <div style="width: 1146px; left: 170px; top: 35px; position: absolute; text-align: center; ; font-size: 45px; font-family: Inter; font-weight: 800; word-wrap: break-word;color:white;">PRANKINGAN PENCAPAIAN HARIAN BNI AGEN46</div>
            <img style="width: 220px; height: 120px; left: 0px; top: 0px; position: absolute" src="Image/spekta_logo.png" />
        </div>

    
        <div style="width: 635px; height: 285px; left: 110px; top: 240px; position: absolute;">
            <form method="post" action="index.php" style="display: inline;">
                <label for="kode_agen"><b>Masukkan Kode Agen : </b></label>
                <input type="text" name="kode_agen" id="kode_agen">
                <input type="submit" value="Submit">
            </form> 
            <p id="kode"></p>
            <p id="nama_agen">Nama Agen :</p>
            <p id="username">Username :</p>
            <p id="ranking">Ranking: </p>
            <p id="wilayah">Wilayah :</p>
            <p id="cabang">Cabang :</p>
            <p id="kelas">Kelas :</p>

        </div>
        <div style="width: 577px; height: 240px; left: 107px; top: 630px; position: absolute">
            <p><b>Pencapaian</b></p>
            <p id="jumlah_trx_berbayar">Jumlah Transaksi Berbayar :</p>
            <p id="jumlah_trx_rek_bni_pandai">&nbsp;&nbsp;Jumlah Transaksi Rek BNI Pandai :</p>
            <p id="jumlah_trx_asuransi_mikro">&nbsp;&nbsp;Jumlah Transaksi Asuransi Mikro :</p>
            <p id="jumlah_ref_pembukaan_rek">&nbsp;&nbsp;Jumlah Referral Pembukaan Rekening :</p>
            <p id="total_jumlah_trx_ref">Total Jumlah Transaksi Referral :</p>
        </div>
        <div style="width: 568px; height: 117px; left: 110px; top: 950px; position: absolute">
            <p><b>Pencapaian Therehold</b></p>
            <p id="threshold_trx_berbayar">Threshold Transaksi Berbayar :</p>
            <p id="threshold_trx_ref">Threshold Transaksi Referral :</p>
        </div>
        <div style="width: 443px; height: 231px; left: 862px; top: 488px; position: absolute; background: #03a2e6; border-radius: 10px">
            <div style="padding-left: 20px; font-size:25px">
                <p><b>Reward</b></p>
                <p id="rank1">Rank 1 :</p>
                <p id="rank2">Rank 2 :</p>
                <p id="rank3">Rank 3 :</p>
            </div>
        </div>
        <div style="width: 443px; height: 231px; left: 862px; top: 230px; position: absolute; background: #03a2e6; border-radius: 10px">
            <div style="padding-left: 20px; font-size:25px">
                <p><b>Periode 1</b></p>
                <p id="threshold">Thereshold :</p>
                <p id="min_trx_berbayar">Minimal Transaksi Berbayar :</p>
                <p id="trx_ref">Minimal Transaksi Referal :</p>
            </div>
        </div>
        <img style="width: 290px; height: 290px; left: 900px; top: 850px; position: absolute" src="Image/award.png" />
        <!--width: 477px; height: 226px; left: 900px; top: 757px; position: absolute-->

        <!-- Menggunakan javascript -->
        <script>
            // Untuk data agen
            document.getElementById('kode').innerHTML = 'Kode Agen : <?php echo $kode; ?>';
            document.getElementById('nama_agen').innerHTML = 'Nama Agen : <?php echo $nama; ?>';
            document.getElementById('username').innerHTML = 'Username : <?php echo $username; ?>';
            document.getElementById('ranking').innerHTML = 'Ranking : <?php echo $ranking; ?>';
            document.getElementById('wilayah').innerHTML = 'Wilayah : <?php echo $wilayah; ?>';
            document.getElementById('cabang').innerHTML = 'Cabang : <?php echo $cabang; ?>';
            document.getElementById('kelas').innerHTML = 'Kelas : <?php echo $kelas; ?>';

            //Untuk pencapaian agen
            document.getElementById('jumlah_trx_berbayar').innerHTML = 'Jumlah Transaksi Berbayar : <?php echo $jumlah_transaksi_berbayar; ?>';
            document.getElementById('jumlah_trx_rek_bni_pandai').innerHTML = '&nbsp;&nbsp;Jumlah Transaksi Rek BNI Pandai : <?php echo $pembukaan_rek_pandai; ?>';
            document.getElementById('jumlah_trx_asuransi_mikro').innerHTML = '&nbsp;&nbsp;Jumlah Transaksi Asuransi Mikro : <?php echo $asuransi_mikro; ?>';
            document.getElementById('jumlah_ref_pembukaan_rek').innerHTML = '&nbsp;&nbsp;Jumlah Referral Pembukaan Rekening : <?php echo $ref_buka_rekening; ?>';
            document.getElementById('total_jumlah_trx_ref').innerHTML = 'Total Jumlah Transaksi Referral : <?php echo $total_transaksi_referral; ?>';

            //Untuk kotak periode 1
            document.getElementById('threshold').innerHTML = 'Thereshold : <?php echo $kelas; ?>';
            document.getElementById('min_trx_berbayar').innerHTML = 'Min Transaksi Berbayar : <?php echo $min_trx_berbayar; ?>';
            document.getElementById('trx_ref').innerHTML = 'Min Transaksi Referral : <?php echo $min_trx_referral; ?>';
            document.getElementById('rank1').innerHTML = 'Rank 1 : <?php echo $rank1; ?>';
            document.getElementById('rank2').innerHTML = 'Rank 2 : <?php echo $rank2; ?>';
            document.getElementById('rank3').innerHTML = 'Rank 3 : <?php echo $rank3; ?>';

            //Untuk pencapaian thereshold
            document.getElementById('threshold_trx_berbayar').innerHTML = 'Threshold Transaksi Berbayar : <?php echo $thereshold_transaksi_berbayar; ?>';
            document.getElementById('threshold_trx_ref').innerHTML = 'Threshold Transaksi Referral : <?php echo $thereshold_transaksi_referral; ?>';


        </script>
    </div>
</body>

</html>
