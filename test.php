<<<<<<< Tabnine <<<<<<<
<?php
require_once 'tcpdf/tcpdf.php';

$pdf = new TCPDF();
$pdf->AddPage();//-
$pdf->SetFont('helvetica', '', 12);//-
$pdf->Write(0, 'Hello, TCPDF!');//-
$pdf->Output('example.pdf', 'I');//-
?>
>>>>>>> Tabnine >>>>>>>// {"conversationId":"52a3826b-b009-441a-bcd0-dad09c5ba1ce","source":"instruct"}