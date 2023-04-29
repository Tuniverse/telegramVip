### Telegram自动开会员源代码 php + golang  + e语言 ，这是一个完整的24小时全自动开通TG会员的例子

##### 作者
    TG：@gd801  
    电报群：@phpTRON   
    电报社区：www.telegbot.org
	
##### 图片示例
<img src="https://github.com/smalpony/telegramVip/blob/main/%E6%B5%81%E7%A8%8B%E6%95%B0%E6%8D%AE%E7%A4%BA%E4%BE%8B.png">

### 详细说明
	使用PHP代码构建支付订单,使用golang作为支付网关(因为没有找到TonSDK php版本)
    
    该项目是全自动 输入对方用户名请求就会自动完成TG会员开通（代开赠送）

    GO支付网关(已开源) 里面有个.env 里面填写你的TON钱包助词器,在支付时将使用里面的余额进行支付代开通会员

### 逻辑流程 - 详见vip.php
    1.PHP指定赠送对象(对方TG用户名) 
    2.PHP创建支付订单
    3.PHP确认支付订单
    4.PHP解码支付订单获得支付数据
    5.PHP携带支付数据请求（GO支付网关 - 自动完成转账支付）OK：会员赠送成功


##### 关于电报自动开通会员API文档 请参阅：
https://telegra.ph/%E6%8A%93%E5%8F%96Telegram-Premium-%E8%B5%A0%E9%80%81%E4%BC%9A%E5%91%98API%E6%8E%A5%E5%8F%A3-04-23
