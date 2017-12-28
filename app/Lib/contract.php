<?php
/**
 *  网站合同,生成PDF
 *  2017-1-16
 *  GJQ
 */
class contract
{

    /**
     *  封装生成合同方法
     *  2017-1-16
     *  @param string   $content 合同内容
     *  @param string   $pdfname 生成的PDF名字
     *  @param string   $pdftitle 生成pdf的标题
     */
    public function contractOutputByHtml($content,$pdfname,$dest='I',$title){

        // Include the main TCPDF library (search for installation path).
        require_once(APP_ROOT_PATH.'/system/tcpdf/tcpdf_include.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle($title ? $title : "协议");
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $header_phone = '                                                                                                                    010-53608035';
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', $header_phone , array(190,190,190), array(0,0,0) );
        $pdf->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 14));
//         $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//         $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 23);

        // set image scale factor
        $pdf->setImageScale(1);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
//         $pdf->SetFont('droidsansfallback', '', 14, '', true);
        $pdf->SetFont('stsongstdlight', '', 14, '', true);


        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect 设置文本阴影效果
//         $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        // Set some content to print

        // Print text using writeHTMLCell()
        
        $pdf->writeHTMLCell(0, 0, '', '', $content, 0, 1, 0, true, '', true);

        // ---------------------------------------------------------

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        if($dest == 'S'){
            return $pdf->Outnewput($pdfname, $dest);
        }
        $pdf->Outnewput($pdfname, $dest);
        //============================================================+
        // END OF FILE
        //============================================================+

    }


}
?>