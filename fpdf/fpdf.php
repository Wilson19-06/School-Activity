<?php
// Minimal placeholder for fpdf.php
class FPDF {
    function AddPage() {}
    function SetFont($family, $style='', $size=null) {}
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {}
    function Output($dest='', $name='', $isUTF8=false) {}
    function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='') {}
    function Ln($h=null) {}
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {}
    function SetTextColor($r, $g=-1, $b=-1) {}
    function SetDrawColor($r, $g=-1, $b=-1) {}
    function Line($x1, $y1, $x2, $y2) {}
    function SetY($y) {}
}
?>
