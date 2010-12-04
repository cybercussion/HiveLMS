<?

$s = ob_get_clean();

require('JSMin.php');

$jsMin = new JSMin($s);
echo $jsMin->minify();

?>