<?

$s = ob_get_clean();

require('ECMAScriptPacker.php');

$packer = new ECMAScriptPacker();
echo $packer->pack($s);

?>