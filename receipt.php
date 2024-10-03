<?php
require_once __DIR__ . "/config.php";
require_once DB_PATH . "/money.php";
require_once VENDOR_PATH . "/autoload.php";
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImagePNG, QRCodeOutputException};
class QRImageWithLogo extends QRGdImagePNG
{

    /**
     * @param string|null $file
     * @param string|null $logo
     *
     * @return string
     * @throws \chillerlan\QRCode\Output\QRCodeOutputException
     */
    public function dump(string $file = null, string $logo = null): string
    {
        // set returnResource to true to skip further processing for now
        $this->options->returnResource = true;

        // of course, you could accept other formats too (such as resource or Imagick)
        // I'm not checking for the file type either for simplicity reasons (assuming PNG)
        if (!is_file($logo) || !is_readable($logo)) {
            throw new QRCodeOutputException('invalid logo');
        }

        // there's no need to save the result of dump() into $this->image here
        parent::dump($file);

        $im = imagecreatefrompng($logo);

        // get logo image size
        $w = imagesx($im);
        $h = imagesy($im);

        // set new logo size, leave a border of 1 module (no proportional resize/centering)
        $lw = (($this->options->logoSpaceWidth - 2) * $this->options->scale);
        $lh = (($this->options->logoSpaceHeight - 2) * $this->options->scale);

        // get the qrcode size
        $ql = ($this->matrix->getSize() * $this->options->scale);

        // scale the logo and copy it over. done!
        imagecopyresampled($this->image, $im, (($ql - $lw) / 2), (($ql - $lh) / 2), 0, 0, $lw, $lh, $w, $h);

        $imageData = $this->dumpImage();

        $this->saveToFile($imageData, $file);

        if ($this->options->outputBase64) {
            $imageData = $this->toBase64DataURI($imageData);
        }

        return $imageData;
    }
}
if (isset($_GET["id"])) {
    $transaction = get_transaction_from_id($_GET["id"]);

    $options = new QROptions;

    $options->outputBase64 = false;
    $options->scale = 6;
    $options->imageTransparent = false;
    $options->drawCircularModules = true;
    $options->circleRadius = 0.45;
    $options->keepAsSquare = [
        QRMatrix::M_FINDER,
        QRMatrix::M_FINDER_DOT,
    ];
    // ecc level H is required for logo space
    $options->eccLevel = EccLevel::H;
    $options->addLogoSpace = true;
    $options->logoSpaceWidth = 13;
    $options->logoSpaceHeight = 13;


    $qrcode = new QRCode($options);
    $qrcode->addByteSegment(json_encode([
        "transactionid" => $transaction->id,
        "amount" => $transaction->amount,
        "time" => $transaction->time,
    ]));

    $qrOutputInterface = new QRImageWithLogo($options, $qrcode->getQRMatrix());

    // dump the output, with an additional logo
// the logo could also be supplied via the options, see the svgWithLogo example
    $out = $qrOutputInterface->dump(null, __DIR__ . '/assets/logo.png');

    $qr = base64_encode($out);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Generate Receipt</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <?php
        require_once DB_PATH . "/money.php";
        if (!isset($_GET["id"])) {
            echo "<h2>No Voucher ID Provided</h2>";
        } else {
            $transaction = get_transaction_from_id($_GET["id"]);
            if (logged_in() && $transaction->username == current_user()->username) { ?>
        <h1 class="text-primary text-2xl">Please screenshot this page.</h1>
        <h3 class="text-xl">Voucher ID: <?php echo $transaction->id; ?></h3>
        <h3 class="text-xl">Value: £<?php echo number_format((float) $transaction->amount, 2); ?></h3>
        <h3 class="text-xl">Time of Transaction: <?php echo date("d/m/Y H:i:s", $transaction->time); ?></h3>
        <img src="data:image/png;base64,<?php echo $qr; ?>" class="qr">
        <?php } else if (!logged_in()) {
                header("location: /voucher/accounts/login.php");
            } else {
                header("location: /voucher/");
            }
        } ?>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script src="/voucher/script/alert.js"></script>

</html>