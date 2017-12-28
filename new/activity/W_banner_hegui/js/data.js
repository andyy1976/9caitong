//数据
var data = [
	['玖财通银行资金存管系统正式上线','玖财通银行资金存管系统正式上线','/new/activity/W_banner_hegui/images/big/13.png','2017年7月17日，玖财通与宜宾市商业银行合作的资金存管系统公测版正式上线，这标志着玖财通在合规运营方面的建设迈上一新台阶，也标志着玖财通合规建设重要战略部署拉开了新的序幕。','w'],
	['北京市通信行业协会会员单位','玖财通正式成为北京市通信行业协会会员单位','/new/activity/W_banner_hegui/images/big/12.png','当前，随着通信技术的发展、通讯手段的不断更新，为给平台用户提供更优质、更便捷的服务，玖财通积极开展通信升级和技术革新，在互金行业中做出了诸多成绩和贡献。2017年6月，玖财通正式被入选北京市通信行业协会、并成为其核心会员单位。','w'],
	['签约资金银行存管','玖财通与宜宾银行签约资金存管','/new/activity/W_banner_hegui/images/big/1.png','《网络借贷信息中介机构业务活动管理暂行办法》出台，该监管政策明确要求P2P平台应当选择符合条件的银行业金融机构作为存管机构。目前,P2P网贷行业仅有3%的平台上线银行存管系统，玖财通便是其中之一。玖财通携手四川宜宾银行实现“银行直连存管”，是为了更好的保障用户账户资金安全，避免平台直接接触用户资金，符合监管要求的资金存管模式。','w'],
	['信息系统的安全保护第三级','玖财通申请信息系统安全保护（三级）','/new/activity/W_banner_hegui/images/big/2.png','信息系统安全等级测评是验证信息系统是否满足相应安全保护等级的评估过程。信息安全等级保护要求不同安全等级的信息系统应具有不同的安全保护能力，一方面通过在安全技术和安全管理上选用与安全等级相适应的安全控制来实现；另一方面分布在信息系统中的安全技术和安全管理上不同的安全控制，通过连接、交互、依赖、协调、协同等相互关联关系，共同作用于信息系统的安全功能，使信息系统的整体安全功能与信息系统的结构以及安全控制间、层面间和区域间的相互关联关系密切相关。因此，信息系统安全等级测评在安全控制测评的基础上，还要包括系统整体测评。','w'],
	['电信与信息服务业务经营许可证','玖财通获电信与信息服务业务经营许可证','/new/activity/W_banner_hegui/images/big/3.png','中华人民共和国电信与信息服务业务经营许可证（简称：ICP许可证）是指一般性经营性网站的主办者向当地区县申请的证书证明，即《中华人民共和国电信与信息服务业务经营许可证》。根据中华人民共和国国务院令第291号《中华人民共和国电信条例》、第292号《互联网信息服务管理办法》 ，国家对提供互联网信息服务的ICP实行许可证制度。从而，ICP证成为网站经营的许可证，经营性网站必须办理ICP证，否则就属于非法经营。因此，办理ICP证是企业网站合法经营的需要。ICP许可证由各地通信管理部门核发。','h'],
	['电子合同、电子签章认证','玖财通与CFCA就电子合同、签章完成签约','/new/activity/W_banner_hegui/images/big/2.png','近日为加强对网络借贷信息中介机构业务活动的监督管理，促进网络借贷行业健康发展，依据《中华人民共和国民法通则》、《中华人民共和国公司法》、《中华人民共和国合同法》等法律法规，中国银监会、工业和信息化部、公安部、国家互联网信息办公室制定了《网络借贷信息中介机构业务活动管理暂行办法》。暂行办法要求对出借人与借款人的基本信息和交易信息等使用电子签名、电子认证。依据监管规定，玖财通与CFCA达成合作意向，完成签约，由CFCA提供数字签名证书服务和电子签章服务，从而进一步保障了用户的投资权益。','w'],
	['互联网金融行业实名网站认证','玖财通获“互联网金融行业实名网站认证”','/new/activity/W_banner_hegui/images/big/5.png','随着中国电子商务行业的迅猛发展，打造以网络诚信为价值基础的互联网、电子商务环境变得日益迫切。依据商务部和国资委法规政策指导，根据《国务院办公厅关于社会信用体系建设的若干意见》（国办发[2007]17号），同时贯彻落实《关于印发行业信用评价试点工作实施办法>的通知》（整规办发[2006]12号），中国电子商务协会成为全国首批行业信用评价试点单位。','h'],
	['安全联盟行业认证','玖财通通过安全联盟行业认证','/new/activity/W_banner_hegui/images/big/6.png','安全联盟验证是由"安全联盟可信验证服务中心"以细分行业的从业资格为基准，推出的网站主办方实体身份信息验证服务，确保被验证网站的实体身份权威真实。网站通过验证后，可被收录至安全联盟信誉档案库，并获得权威验证 标识及信誉档案证明，可接受全国用户公开查询，有效提升网站信誉。','h'],
	['可信网站身份验证','玖财通获CNIC可信网站身份验证','/new/activity/W_banner_hegui/images/big/7.png','可信网站认证是由中科院计算机网络信息中心(CNIC)提供技术支持推出的第三方网站身份信息权威网站认证服务。它通过对域名注册信息、网站信息和企业工商或事业单位组织机构信息进行严格交互审核来认证网站真实信息，并利用先进的木马扫描技术帮助网站了解自身安全情况，是中国数百万网站的“可信身份证”。','h'],
	['ICP网站征信认证证书','玖财通获ICP网站征信认证证书','/new/activity/W_banner_hegui/images/big/8.png','ICP网站征信:是虚拟网站与实体企业的一致性验证，是对企业工商营业执照、经营许可证、知识产权和网站备案等信息的验证，以及对网络信息安全、网民隐私信息保护和网上交易安全性等方面的评估。iTrust电子标识是国内知名、权威的网站标识，是网民辨识企业官网、查验网站经营者身份、了解企业信用状况的有效工具。','h'],
	['玖财通获计算机软件著作权','玖财通获计算机软件著作权','/new/activity/W_banner_hegui/images/big/10.png','计算机软件著作权是指软件的开发者或者其他权利人依据有关著作权法律的规定,对于软件作品所享有的各项专有权利和国家优惠政策。就权利的性质而言,它属于一种民事权利,具备民事权利的共同特征。著作权是知识产权中的例外，因为著作权的取得无须经过个别确认,这就是人们常说的“自动保护”原则。软件经过登记后，软件著作权人享有发表权、开发者身份权、使用权、使用许可权和获得报酬权。','h'],
	['公司营业执照','北京玖承资产管理有限公司正式成立','/new/activity/W_banner_hegui/images/big/11.png','营业执照是工商行政管理机关发给工商企业、个体经营者的准许从事某项生产经营活动的凭证。其格式由国家工商行政管理局统一规定。没有营业执照的工商企业或个体经营者一律不许开业，不得刻制公章、签订合同、注册商标、刊登广告，银行不予开立帐户。玖财通成立于2012年1月，运营实体为北京玖承资产管理有限公司，注册资本1亿元，公司办公地址在北京市朝阳区十八里店南桥观筑庭园会所。','h']
];