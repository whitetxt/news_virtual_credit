<?php
require "config.php";
require_once DB_PATH . "/money.php";
require_once VENDOR_PATH . "/autoload.php";
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImagePNG, QRCodeOutputException};
class QRImageWithLogo extends QRGdImagePNG{

	/**
	 * @param string|null $file
	 * @param string|null $logo
	 *
	 * @return string
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(string $file = null, string $logo = null):string{
		// set returnResource to true to skip further processing for now
		$this->options->returnResource = true;

		// of course, you could accept other formats too (such as resource or Imagick)
		// I'm not checking for the file type either for simplicity reasons (assuming PNG)
		if(!is_file($logo) || !is_readable($logo)){
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

		if($this->options->outputBase64){
			$imageData = $this->toBase64DataURI($imageData);
		}

		return $imageData;
	}
}

$voucher = get_voucher_from_id($_GET["id"]);

$options = new QROptions;

$options->outputBase64        = false;
$options->scale               = 6;
$options->imageTransparent    = false;
$options->drawCircularModules = true;
$options->circleRadius        = 0.45;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER,
	QRMatrix::M_FINDER_DOT,
];
// ecc level H is required for logo space
$options->eccLevel            = EccLevel::H;
$options->addLogoSpace        = true;
$options->logoSpaceWidth      = 13;
$options->logoSpaceHeight     = 13;


$qrcode = new QRCode($options);
$qrcode->addByteSegment(json_encode([
    "voucherid" => $voucher->voucherid,
    "value" => $voucher->amount,
    "time_given" => $voucher->time_given,
    "used" => $voucher->used,
    "secret" => $voucher->secret,
]));

$qrOutputInterface = new QRImageWithLogo($options, $qrcode->getQRMatrix());

// dump the output, with an additional logo
// the logo could also be supplied via the options, see the svgWithLogo example
$out = $qrOutputInterface->dump(null, __DIR__.'/assets/logo.png');

$qr = base64_encode($out);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require (PREFAB_PATH . "/global/head.php"); ?>
    <title>Generate Receipt</title>
    <link rel="stylesheet" href="/voucher/style/index.css">
    <link rel="stylesheet" href="/voucher/style/receipt.css">
</head>

<body>
    <div id="head">
        <?php require (PREFAB_PATH . "/nav/nav.php"); ?>
    </div>
    <div id="site">
        <div id="vouchers">
            <?php
                require_once DB_PATH . "/money.php";
            if (!isset($_GET["id"])) {
                echo "<h2>No Voucher ID Provided</h2>";
            } else {
                $voucher = get_voucher_from_id($_GET["id"]);
                if (logged_in() && $voucher->username == current_user()->username) {?>
            <div id="vouchers2">
                <h1>Please screenshot this page.</h1>
                <img src="/voucher/assets/reload.png" alt="Reload Voucher" class="reload">
                <h3 class="id">Voucher ID: <?php echo $voucher->voucherid; ?></h3>
                <h3 class="value">Value: £<?php echo number_format((float)$voucher->amount, 2); ?></h3>
                <h3 class="time">Time Given: <?php echo date("d/m/Y H:i:s", $voucher->time_given); ?></h3>
                <h3 class="used">Time Used: <?php echo date("d/m/Y H:i:s", $voucher->used); ?></h3>
                <img src="data:image/png;base64,<?php echo $qr; ?>" class="qr">
            </div>
            <?php } else if (!logged_in()) { 
                header("location: /voucher/accounts/login.php");
                } else {
                    header("location: /voucher/");
                }
            }?>
        </div>
    </div>
    <?php require (PREFAB_PATH . "/global/footer.php"); ?>
    <?php require (PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script src="/voucher/script/alert.js"></script>

</html>
