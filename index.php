<?php

// 讀取目錄下的所有 JSON 文件
$directory = 'data/';
$files = glob($directory . '*.json');

$data = [];
foreach ($files as $file) {
    $json = file_get_contents($file);
    $decodedData = json_decode($json, true);
    if (is_array($decodedData)) {
        $data = array_merge($data, $decodedData);
    }
}

// 如果 JSON 是單一物件，將其轉換為陣列
if (!isset($data[0])) {
    $data = [$data]; // 將單一物件轉換為包含一個元素的陣列
}

// 搜尋功能
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

if ($searchQuery) {
    $filteredData = array_filter($data, function($item) use ($searchQuery) {
// 在「神明名稱」、「地區~通則」和「點餐」中搜尋關鍵字
        return stripos($item['神明名稱'], $searchQuery) !== false ||
               stripos($item['地區~通則'], $searchQuery) !== false ||
               stripos($item['點餐'], $searchQuery) !== false;
    });
} else {
    $filteredData = $data;
}

// 確保 $filteredData 是一個陣列
if (empty($filteredData)) {
    $filteredData = [];
}

// 分頁功能
$perPage = 20;
$totalItems = count($filteredData);
$totalPages = ceil($totalItems / $perPage);
$currentPage = isset($_GET['page']) ? max(1, min($_GET['page'], $totalPages)) : 1;
$offset = ($currentPage - 1) * $perPage;
$pagedData = array_slice($filteredData, $offset, $perPage);

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>療癒系神婆PODCAST搜尋</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>療癒系神婆PODCAST搜尋</h1>
        <form id="searchForm" method="GET" action="">
            <input type="text" name="search" placeholder="輸入想知道好奇內容，沒有也可以私訊IG討論" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">搜尋</button>
            <a href="https://www.instagram.com/yi_tarot/" target="_blank" class="ig-button">私訊YI-IG討論</a>
        </form>

        <?php if (empty($filteredData)): ?>
            <p>沒有找到符合條件的結果。您可以嘗試搜尋以下關鍵字：</p>
            <div class="suggestions">
                <button onclick="fillSearch('塔羅')">塔羅</button>
                <button onclick="fillSearch('事業')">事業</button>
                <button onclick="fillSearch('健康')">健康</button>
                <button onclick="fillSearch('人際關係')">人際關係</button>
                <button onclick="fillSearch('催眠')">催眠</button>
                <button onclick="fillSearch('靈性')">靈性</button>
                <button onclick="fillSearch('夢想')">夢想</button>
                <button onclick="fillSearch('今生課題')">今生課題</button>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>節目名稱</th>
                        <th>網址</th>
                        <th>摘要</th>
                        <th>重點目錄</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagedData as $item): ?>
                        <tr>
                            <td><?php echo $item['節目名稱']; ?></td>
                            <td><a href="<?php echo $item['網址']; ?>" target="_blank">連結</a></td>
                            <td><?php echo nl2br($item['摘要']); ?></td>
                            <td><?php echo nl2br($item['重點目錄']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?search=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage - 1; ?>">上一頁</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?search=<?php echo urlencode($searchQuery); ?>&page=<?php echo $i; ?>" class="<?php echo $i === $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?search=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage + 1; ?>">下一頁</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
    function fillSearch(keyword) {
        document.querySelector('input[name="search"]').value = keyword;
        document.getElementById('searchForm').submit();
    }
    </script>
</body>
</html>
