<?php
require 'koneksi.php';
$url = strip_tags(@$_POST['url'], '.');
$ip = $_SERVER['REMOTE_ADDR'];
$selct['url'] = '';
$selct['link'] = '';

function gen_uuid($len = 4)
{
  $hex = md5("URLS" . uniqid("", true));
  $pack = pack('H*', $hex);
  $tmp =  base64_encode($pack);
  $uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", "", $tmp);
  $len = max(4, min(128, $len));
  while (strlen($uid) < $len)
    $uid .= gen_uuid(22);
  return substr($uid, 0, $len);
}

$id = gen_uuid();
$shortened = 'muf-hax.net/URLS/' . $id;

if (isset($_GET)) {
  $uniqueid = @$_GET['shortURL'];
  $select = $db->prepare('SELECT * FROM tb_url WHERE uniqueid = ?');
  $select->bindParam(1, $uniqueid);
  $select->execute();
  if ($select->rowCount() > 0) {
    while ($data = $select->fetch(PDO::FETCH_LAZY)) {
      if ($data['uniqueid'] == @$_GET['shortURL']) {
        header('location: ' . $data['url']);
        exit;
      } else if ($data['uniqueid'] != @$_GET['shortURL']){
        echo 'Maaf URL yang anda mungkin tidak ada, atau anda memasukkan URL yang salah!';
        exit;
      }
    }
  }
}
?>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>URL Shortener</title>
  <link rel="stylesheet" href="https://muf-hax.net/URLS/style.css?ver=<?= time(); ?>">
</head>

<body>
  <div class="container">
    <?php if (!isset($_POST['url'])) : ?>
      <form action="" method="post">
        <label for="url">URL Shortener</label>
        <input type="url" name="url" id="url" autocomplete="off" autofocus>
        <button type="submit" name="submit" id="submit">Submit</button>
      </form>
    <?php endif; ?>
    <?php if (isset($_POST['url'])) : ?>
      <?php if (@$_POST['url'] != null || @$_POST['url'] != '') : ?>
        <?php
        $insert = $db->prepare("INSERT INTO tb_url (id, url, uniqueid, shortened, ip) VALUES ('', ?, ?, ?, ?)");
        $insert->bindParam(1, $url);
        $insert->bindParam(2, $id);
        $insert->bindParam(3, $shortened);
        $insert->bindParam(4, $ip);
        $insert->execute();
        if ($insert->rowCount() > 0) :
        ?>
          <?php
          $select = $db->prepare('SELECT * FROM tb_url WHERE uniqueid=?');
          $select->bindParam(1, $id);
          $select->execute();
          ?>
          <?php if ($select->rowCount() > 0) : ?>
            <?php while ($dataURL = $select->fetch(PDO::FETCH_LAZY)) : ?>
              <div class="copy-form">
                <label for="url">Copy URL</label>
                <input type="url" name="url" id="url" value="<?= $dataURL['shortened']; ?>" autofocus>
                <button id="copy" onclick="copy()">Copy</button>
              </div>
              <script>
                function copy() {
                  var copyText = document.getElementById("url");
                  copyText.select();
                  copyText.setSelectionRange(0, 99999);
                  document.execCommand("copy");
                  alert("Copied the link: " + copyText.value);
                  window.location.href = "https://muf-hax.net/URLS/";
                }
              </script>
            <?php endwhile; ?>
          <?php endif ?>
        <?php endif ?>
      <?php else : ?>
        <script>
          alert('URL tidak boleh kosong!')
        </script>
        <?php header('refresh: 0'); ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>

</html>