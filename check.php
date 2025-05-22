<?php
// 基本的な動作確認用ファイル
echo "<h1>警備システム - Laravel基盤</h1>";
echo "<p>サーバー時刻: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP版本: " . PHP_VERSION . "</p>";
echo "<p>状態: システム構築中</p>";

// 基本的なディレクトリ構造確認
$directories = [
    'app',
    'config', 
    'database',
    'resources/views',
    'routes',
    'storage'
];

echo "<h2>ディレクトリ構造確認</h2>";
foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    $status = is_dir($path) ? '✓' : '✗';
    echo "<p>{$status} {$dir}</p>";
}
?>