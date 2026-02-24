<?php
/**
 * صفحه تعمیرات
 * @package NovelTheme
 */
status_header(503);
header('Retry-After: 3600');
header('Content-Type: text/html; charset=utf-8');
$message = novel_get_option('novel_maintenance_message', 'در حال به‌روزرسانی هستیم. به زودی برمی‌گردیم!');
$primary = novel_get_option('novel_primary_color', '#7c3aed');
?><!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>در حال به‌روزرسانی | <?php echo esc_html(get_bloginfo('name')); ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Tahoma,sans-serif;background:#f3f4f6;display:flex;align-items:center;justify-content:center;min-height:100vh;direction:rtl;color:#333}
.wrap{text-align:center;padding:40px 20px;max-width:500px}
.icon{font-size:80px;margin-bottom:24px;animation:bounce 2s infinite}
h1{font-size:24px;margin-bottom:16px;color:<?php echo esc_attr($primary); ?>}
p{font-size:16px;line-height:1.8;color:#666}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
</style>
</head>
<body>
<div class="wrap">
    <div class="icon">🔧</div>
    <h1>در حال به‌روزرسانی هستیم!</h1>
    <p><?php echo esc_html($message); ?></p>
</div>
</body>
</html>
<?php exit; ?>