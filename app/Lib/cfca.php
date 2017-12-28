<?php
/**
 *  CFCA接口操作类
 *  2017-4-13
 *  GJQ
 */
class cfca
{
    //url 后面的数字代表接口文档中的第X章接口,个别情况特殊请查看文档
    private $url23 = 'http://47.93.126.155:8080/Seal/MakeSealServlet';
    private $url4 = 'http://47.93.126.155:8080/Seal/PdfSealServlet';
    /**
     *  2017-4-14 自动化签章
     *  @param string   $pdfFile PDF文件
     *  @param array    $data 签章策略文件参数
     */
    public function sealAutoPdf($pdfFile,$data = array()) {
        if(empty($data) || empty($pdfFile)) return false;
        $url = $this->url4;
        
        // 客户端证书
        $cert4Client = 'client.pem';
        $certPwd = 'cfca1234';
        
        // 服务器端证书链
        $ca4Server="chain.pem";
        
        // XML
        
        $xml  = "<Request>";
        $xml .= "<Type>".$data['type']."</Type>";
        $xml .= "<PdfURL>".$data['pdfURL']."</PdfURL>";
        $xml .= "<SealCode>".$data['sealCode']."</SealCode>";
        $xml .= "<SealPassword>".$data['sealPassword']."</SealPassword>";
        $xml .= "<Page>".$data['page']."</Page>";
        $xml .= "<SealPerson>".$data['sealPerson']."</SealPerson>";
        $xml .= "<SealLocation>".$data['sealLocation']."</SealLocation>";
        $xml .= "<SealResson>".$data['sealResson']."</SealResson>";
        $xml .= "<LX>".$data['lX']."</LX>";
        $xml .= "<LY>".$data['lY']."</LY>";
        $xml .= "<Keyword>".$data['keyword']."</Keyword>";
        $xml .= "<LocationStyle>".$data['locationStyle']."</LocationStyle>";
        $xml .= "<OffsetX>".$data['offsetX']."</OffsetX>";
        $xml .= "<OffsetY>".$data['offsetY']."</OffsetY>";
        $xml .= "<CertificationLevel>".$data['certificationLevel']."</CertificationLevel>";
        $xml .= "</Request>";
        
        // Call
        $para = "type=sealAutoPdf&pdf=".urlencode(base64_encode($pdfFile))."&sealStrategyXML=".urlencode(base64_encode($xml));
        $result = $this->curl_post_ssl($url, $cert4Client, $certPwd, $ca4Server, $para);
        $result = base64_decode($result);
        return $result;
    }
    
/**
     *  2017-10-23 自动化制章
     *  @param array    $data 制章策略文件参数
     */
    public function makeSealAutomation($data = array()){
        
        
        if(empty($data)) return false;
        $url = $this->url23;
        
        // 客户端证书
        $cert4Client = 'client.pem';
        $certPwd = 'cfca1234';
        
        // 服务器端证书链
        $ca4Server="chain.pem";
        
        $certApplyXML  = "<Request>";
        $certApplyXML .= "<CustomerType>"."2"."</CustomerType>";
        $certApplyXML .= "<KeyAlg>"."RSA"."</KeyAlg>";
        $certApplyXML .= "<KeyLength>"."2048"."</KeyLength>";
        $certApplyXML .= "<UserName>".$data['real_name']."</UserName>";
        $certApplyXML .= "<IdentificationType>"."Z"."</IdentificationType>";
        $certApplyXML .= "<IdentificationNo>".$data['idno']."_".date("Ymd")."</IdentificationNo>";
        $certApplyXML .= "</Request>";
        
        $SealInfoXML = "<Request>";
        $SealInfoXML .= "<SealPerson>".$data['real_name']."</SealPerson>";//制章人
        $SealInfoXML .= "<SealOrg>".$data['real_name']."</SealOrg>";//制章单位
        $SealInfoXML .= "<SealName>".$data['real_name']."</SealName>";//印章名称
        $SealInfoXML .= "<SealCode>".$data['seqno']."</SealCode>";//印章编码
        $SealInfoXML .= "<SealPassword>"."123456"."</SealPassword>";//印章密码
        $SealInfoXML .= "<SealType>"."0"."</SealType>";
        $SealInfoXML .= "<ImageCode>".$data['real_name']."</ImageCode>";//印模编码
        $SealInfoXML .= "<OrgCode>"."root"."</OrgCode>";
        $SealInfoXML .= "<SealFlag>"."0"."</SealFlag>";
        $SealInfoXML .= "</Request>";
        
        $para = "type=makeSealAutomation&certApplyXML=".urlencode(base64_encode($certApplyXML))."&image=&sealInfoXML=".urlencode(base64_encode($SealInfoXML));
        $result = $this->curl_post_ssl($url, $cert4Client, $certPwd, $ca4Server, $para);
        $result = base64_decode($result);
        return $result;//200successfully!1
    }
    
    /**
     *  2017-10-23 自动化制人名章
     *  @param array    $data 制章策略文件参数
     */
    public function makeNamedSealAutomation($data = array()){
        
        
        if(empty($data)) return false;
        $url = $this->url23;
        
        // 客户端证书
        $cert4Client = 'client.pem';
        $certPwd = 'cfca1234';
        
        // 服务器端证书链
        $ca4Server="chain.pem";
        
        $certApplyXML  = "<Request>";
        $certApplyXML .= "<KeyAlg>"."RSA"."</KeyAlg>";
        $certApplyXML .= "<KeyLength>"."2048"."</KeyLength>";
        $certApplyXML .= "<UserName>".$data['real_name']."</UserName>";
        $certApplyXML .= "<IdentificationType>"."Z"."</IdentificationType>";
        $certApplyXML .= "<IdentificationNo>".$data['idno']."_".date("Ymd")."</IdentificationNo>";
        $certApplyXML .= "</Request>";
        
        $SealInfoXML = "<Request>";
        $SealInfoXML .= "<SealPerson>".$data['real_name']."</SealPerson>";//制章人
        $SealInfoXML .= "<SealOrg>"."北京玖承资产管理有限公司"."</SealOrg>";//制章单位
        $SealInfoXML .= "<SealName>".$data['real_name']."</SealName>";//印章名称
        $SealInfoXML .= "<SealCode>".$data['seqno']."</SealCode>";//印章编码
        $SealInfoXML .= "<SealPassword>"."123456"."</SealPassword>";//印章密码
        $SealInfoXML .= "<ImageShape>"."3"."</ImageShape>";
        $SealInfoXML .= "<ImageWidth>"."200"."</ImageWidth>";
        $SealInfoXML .= "<ImageHeight>"."100"."</ImageHeight>";
        $SealInfoXML .= "<Color>"."FF0000"."</Color>";
        $SealInfoXML .= "<FontSize>"."50"."</FontSize>";
        $SealInfoXML .= "<OrgCode>"."1001"."</OrgCode>";
        $SealInfoXML .= "<SealFlag>"."0"."</SealFlag>";
        $SealInfoXML .= "</Request>";
        
        $para = "type=makeNamedSealAutomation&certApplyXML=".urlencode(base64_encode($certApplyXML))."&sealInfoXML=".urlencode(base64_encode($SealInfoXML));
        $result = $this->curl_post_ssl($url, $cert4Client, $certPwd, $ca4Server, $para);
        $result = base64_decode($result);
        return $result;//200successfully!
    }
    
    /**
     *  2017-10-23 批量签章 一个文件盖多个章
     *  @param string   $pdf PDF文件流
     *  @param array    $batchSealStrategyXML 签章策略文件参数
     *  KT系统地址
     */
    public function batchSealAutoUncheckedPdf($pdfFile,$contract_id,$loan_info,$invest_info,$title){
        
        if(empty($contract_id) ||empty($loan_info) || empty($invest_info)) return false;
        $url = $this->url4;
        
        // 客户端证书
        $cert4Client = 'client.pem';
        $certPwd = 'cfca1234';
        
        // 服务器端证书链
        $ca4Server="chain.pem";
        if($contract_id == 11){
            //位置
            $gs_lx = "100";
            $gs_ly = "170";
            $jk_lx = "150";
            $jk_ly = "420";
            $tz_lx = "400";
            $tz_ly = "420";
            $title = $title."签订";
            $page = "9";
        }elseif ($contract_id == 14){
            //位置
            $gs_lx = "120";
            $gs_ly = "80";
            $jk_lx = "150";
            $jk_ly = "320";
            $tz_lx = "400";
            $tz_ly = "320";
            $title = $title."签订";
            $page = "9";
        }elseif ($contract_id == 15){
            //位置
            $gs_lx = "100";
            $gs_ly = "10";
            $jk_lx = "420";
            $jk_ly = "180";
            $tz_lx = "190";
            $tz_ly = "180";
            $title = $title."签订";
            $page = "4";
        }elseif ($contract_id == 16){
            $gs_lx = "100";
            $gs_ly = "400";
            $jk_lx = "100";
            $jk_ly = "550";
            $tz_lx = "400";
            $tz_ly = "620";
            $title = $title."签订";
            $page = "14";
        }
        
        
            
        //公司章位置
        $xml  = "<Request>";
        $xml .= "<Type>"."2"."</Type>";// 签章类型（不能为空），1=空白标签签章,2=坐标签章,3=关键字签章
        $xml .= "<PdfURL>".""."</PdfURL>";
        $xml .= "<SealCode>"."1001"."</SealCode>";//印章编码
        $xml .= "<SealPassword>"."123456"."</SealPassword>";//印章密码
        $xml .= "<Page>".$page."</Page>";//页数
        $xml .= "<SealPerson>"."北京玖承资产管理有限公司"."</SealPerson>"; //签章人
        $xml .= "<SealLocation>"."北京"."</SealLocation>"; //签章地点
        $xml .= "<SealResson>".$title."</SealResson>"; //签章理由
        $xml .= "<LX>".$gs_lx."</LX>";//左侧的x坐标
        $xml .= "<LY>".$gs_ly."</LY>";//左侧的y坐标
        $xml .= "<Keyword>".""."</Keyword>";//关键字，按关键字签章时不能为空
        $xml .= "<LocationStyle>"."C"."</LocationStyle>";// 上:U；下:D；左:L；右:R；中:C；默认：C
        $xml .= "<OffsetX>"."0"."</OffsetX>";//横轴偏移，默认为0
        $xml .= "<OffsetY>"."0"."</OffsetY>"; //纵轴偏移，默认为0
        $xml .= "<CertificationLevel>"."0"."</CertificationLevel>";// 0:Approval signature(NOT_CERTIFIED)2:Author signature, form filling allowed
        $xml .= "</Request>";
        
        //借款人章位置
        $xml2  = "<Request>";
        $xml2 .= "<Type>"."2"."</Type>";// 签章类型（不能为空），1=空白标签签章,2=坐标签章,3=关键字签章
        $xml2 .= "<PdfURL>".""."</PdfURL>";
        $xml2 .= "<SealCode>".$loan_info['seqno']."</SealCode>";//印章编码
        $xml2 .= "<SealPassword>"."123456"."</SealPassword>";//印章密码
        $xml2 .= "<Page>".$page."</Page>";//页数
        $xml2 .= "<SealPerson>".$loan_info['real_name']."</SealPerson>"; //签章人
        $xml2 .= "<SealLocation>"."北京"."</SealLocation>"; //签章地点
        $xml2 .= "<SealResson>".$title."</SealResson>"; //签章理由
        $xml2 .= "<LX>".$jk_lx."</LX>";//左侧的x坐标
        $xml2 .= "<LY>".$jk_ly."</LY>";//左侧的y坐标
        $xml2 .= "<Keyword>".""."</Keyword>";//关键字，按关键字签章时不能为空
        $xml2 .= "<LocationStyle>"."C"."</LocationStyle>";// 上:U；下:D；左:L；右:R；中:C；默认：C
        $xml2 .= "<OffsetX>"."0"."</OffsetX>";//横轴偏移，默认为0
        $xml2 .= "<OffsetY>"."0"."</OffsetY>"; //纵轴偏移，默认为0
        $xml2 .= "<CertificationLevel>"."0"."</CertificationLevel>";// 0:Approval signature(NOT_CERTIFIED)2:Author signature, form filling allowed
        $xml2 .= "</Request>";
        
        
        //出借人章位置
        $xml3  = "<Request>";
        $xml3 .= "<Type>"."2"."</Type>";// 签章类型（不能为空），1=空白标签签章,2=坐标签章,3=关键字签章
        $xml3 .= "<PdfURL>".""."</PdfURL>";
        $xml3 .= "<SealCode>".$invest_info['seqno']."</SealCode>";//印章编码
        $xml3 .= "<SealPassword>"."123456"."</SealPassword>";//印章密码
        $xml3 .= "<Page>".$page."</Page>";//页数
        $xml3 .= "<SealPerson>".$invest_info['real_name']."</SealPerson>"; //签章人
        $xml3 .= "<SealLocation>"."北京"."</SealLocation>"; //签章地点
        $xml3 .= "<SealResson>".$title."</SealResson>"; //签章理由
        $xml3 .= "<LX>".$tz_lx."</LX>";//左侧的x坐标
        $xml3 .= "<LY>".$tz_ly."</LY>";//左侧的y坐标
        $xml3 .= "<Keyword>".""."</Keyword>";//关键字，按关键字签章时不能为空
        $xml3 .= "<LocationStyle>"."C"."</LocationStyle>";// 上:U；下:D；左:L；右:R；中:C；默认：C
        $xml3 .= "<OffsetX>"."0"."</OffsetX>";//横轴偏移，默认为0
        $xml3 .= "<OffsetY>"."0"."</OffsetY>"; //纵轴偏移，默认为0
        $xml3 .= "<CertificationLevel>"."0"."</CertificationLevel>";// 0:Approval signature(NOT_CERTIFIED)2:Author signature, form filling allowed
        $xml3 .= "</Request>";
        $batchSealStrategyXML = "<List>".$xml.",".$xml2.",".$xml3."</List>";
        
        // Call
        $para = "type=batchSealAutoPdf&pdf=".urlencode(base64_encode($pdfFile))."&batchSealStrategyXML=".urlencode(base64_encode($batchSealStrategyXML));
        $result = $this->curl_post_ssl($url, $cert4Client, $certPwd, $ca4Server, $para);
        $result = base64_decode($result);
        return $result;
    }
    
    /**
     *  2017-4-13
     *  @param string   $url 接口地址
     *  @param string   $cert4Client 客户端证书 //暂时无用
     *  @param string   $certPwd 客户端证书密码 //暂时无用
     *  @param string   $ca4Server 服务器端证书链 //暂时无用
     *  @param string   $vars 参数拼接
     */
    private function curl_post_ssl($url, $cert4Client, $certPwd, $ca4Server, $vars) {
        // 初始化
        $ch = curl_init($url);
    
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
        if ($SSL) {
            // SSL Cert
            curl_setopt($ch, CURLOPT_SSLCERT, $this->getPath($cert4Client));
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $certPwd);
    
            // SSL Config
            curl_setopt($ch, CURLOPT_CAINFO, $this->getPath($ca4Server));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
        }
        // I/O
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    
        // 执行
        $result = curl_exec($ch);
    
        // 错误信息
        if (curl_errno($ch)) {
            $result = curl_error($ch);
            echo 'Curl error: '.$result;
            exit();
        }
    
        // 关闭
        curl_close($ch);
    
        // 返回值
        return $result;
    }
    
    
    // 获取文件路径
    private function getPath($fileName) {
        return getcwd()."/".$fileName;
    }

    
}
?>