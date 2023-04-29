<?php 
#电报自动开会员 
#技术文档阅读：https://telegra.ph/%E6%8A%93%E5%8F%96Telegram-Premium-%E8%B5%A0%E9%80%81%E4%BC%9A%E5%91%98API%E6%8E%A5%E5%8F%A3-04-23
#作者TG：@gd801  电报群：@phpTRON   电报社区：www.telegbot.org


#基本配置 - 以下两项请使用：获取cookie工具.exe 获得 （代码已开源）
$hash = "ce0fd0deefd80203c6";
$cookie = "stel_ton_token=ctkN2AcU4CN62F5fSR9S62MbjnSU1hL1eU0oxHSGH5SkPvP-WaME-1fAfx5iQUGp1eytyU6BIxMApRov2aZEroCGxZQvZCHFy5fRdyejQnqsw4Z2I25YV_x6QDgZOFJ-cFzc0MikgbJjyAU9I59fsGZeLYB94N9--mTK9MmO851FtySjSTg; stel_ssid=cbf52997911114975d_4754795277556197182";

$user = "gd801";//被开通用户的电报用户名不带@  
$numt = 3; //开通月数 3 6 12  = 3个月 6个月  12个月  



#第一步 获取被赠送用户的会员信息 
echo "【第一步】<br>";
$user=curl_post_https("https://fragment.com/api?hash={$hash}","query={$user}&months={$numt}&method=searchPremiumGiftRecipient",null,$cookie);
$json = json_decode($user,true); //json编码
if(empty($json['ok'])){
    exit("第一步 获取被赠送用户的会员信息  失败");
}
$userName = $json['found']['name']??"未知";//获得用户昵称
$recipient = $json['found']['recipient']; //获得用户唯一标识 第2步需要使用
$photo = $json['found']['photo'];//获得用户头像
echo "用户头像：{$photo}<br>";
echo "用户昵称：{$userName}<br>";
echo "唯一标识：{$recipient}<br><br>";
 


#第二步 创建ton支付订单 注意其中的 $recipient 是第一步获取的
echo "【第二步】<br>";
$order=curl_post_https("https://fragment.com/api?hash={$hash}","recipient={$recipient}&months={$numt}&method=initGiftPremiumRequest",null,$cookie);
$json = json_decode($order,true); //json编码
if(empty($json['req_id'])){
    exit("第二步 创建ton支付订单  失败");
}
$req_id = $json['req_id']; //获得订单号 后续都需要使用
$amount = $json['amount'];

echo "订单号：{$req_id}<br>";
echo "金额(Ton)：{$amount}<br><br>";


#第三步 确认支付订单  
echo "【第三步】<br>";
$order=curl_post_https("https://fragment.com/api?hash={$hash}","id={$req_id}&show_sender=1&method=getGiftPremiumLink",null,$cookie);
$json = json_decode($order,true); //json编码
if(empty($json['ok'])){
    exit("第三步 确认支付订  失败");
} 
$qr_link = $json['qr_link']; //获得支付地址（自己生成二维码） 任何TON钱包扫这个二维码支付就可以自动开通会员，当然这是手动模式了
$expire = time() + $json['expire_after'];


echo "二维码链接：{$qr_link}<br>";
echo "订单有效期time：{$expire}<br>";  
echo "订单有效期date：".date("Y-m-d H:i:s",$expire)."<br><br>";



#第四步 解码订单数据 并调用TON接口 实现自动支付从而实现自动开通会员
echo "【第四步】<br>";
$order=curl_get_https("https://fragment.com/tonkeeper/rawRequest?id={$req_id}&qr=1");
$json = json_decode($order,true); //json编码
if(empty($json['body']['params']['messages'])){
    exit("第四步 解码订单数据 失败");
} 
$money = base64_decode($json['body']['params']['messages'][0]['amount']); //最终支付金额(精度9) 也就是 amount * 1000000000
$base32 = base64_decode($json['body']['params']['messages'][0]['payload']); //不是完整正确的解码  
$base32 = explode("#",$base32);
$base32 = "Telegram Premium for 3 months Ref#".$base32[1];#最终(支付网关)订单数据 需要传递给golang 支付网关

echo "最终(支付网关)订单数据：{$base32}<br><br>"; 



exit("第5步 自动支付并自动开通会员(我注释了代码) 请看源代码 第73行");//代码运行到这里结束了，如果自己要测试支付开通请删除这行




#第5步 由于只找到JAVA C++ GOlang 的 SDK，没有找到PHP版本的,所以这里我使用GOlang 网关（只负责Ton支付业务）  代码一并开源了的
$raw = '{
    "EQBAjaOyi2wGWlk-EDkSabqqnF-MrrwMadnwqrurKpkla9nE": "'.$money.'"  
}';//这里面这个TON钱包地址就是fragment官方开会员的固定收款钱包地址 - 请参阅顶部技术文档

//发起支付
$payok  = curl_get_https("http://127.0.0.1:8888/sendTransactions?comment={$base32}&send_mode=1","Content-Type:application/json",$raw);
//127.0.0.1  是golang 支付网关运行在本地
echo "最终上链支付结果：{$payok}";


//后续可以自己





function curl_get_https($url,$headers=null,$raw=null,$time=6){
    $curl = curl_init(); 
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, $time);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    if(!empty($headers)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);//设置请求头
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
    if($raw){
        curl_setopt($curl, CURLOPT_POSTFIELDS, $raw); // Post提交的数据包 
    }
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    curl_close($curl);
    return $tmpInfo;   
}


function curl_post_https($url,$data,$headers=null,$cookie=null){ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    if(!empty($headers)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);//设置请求头
    }
    if(!empty($cookie)){
        curl_setopt($curl, CURLOPT_COOKIE, $cookie); // 带上COOKIE请求
    } 
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据
}






#ad最具权威电报机器人社区：www.telegbot.com   www.telegbot.org    www.telegbot.cc 